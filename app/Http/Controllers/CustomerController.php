<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Services\PhoneService;
use App\Support\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function __construct(
        protected PhoneService $phoneService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Customer::class);

        $companyId = app(CompanyContext::class)->companyId();

        $query = Customer::query()
            ->with([
                'primaryContact.primaryPhoneNumber',
                'primaryContact.primaryEmailAddress',
                'primaryAddress',
            ])
            ->withCount(['contacts', 'jobs'])
            ->where('company_id', $companyId);

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOL));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($customerQuery) use ($search) {
                $customerQuery->where('name', 'ilike', "%{$search}%")
                    ->orWhereHas('contacts', function ($contactQuery) use ($search) {
                        $contactQuery->where('name', 'ilike', "%{$search}%")
                            ->orWhereHas('emailAddresses', fn ($emailQuery) => $emailQuery->where('email', 'ilike', "%{$search}%"))
                            ->orWhereHas('phoneNumbers', fn ($phoneQuery) => $phoneQuery->where('phone_number', 'ilike', "%{$search}%"));
                    });
            });
        }

        $customers = $query->orderBy('name')->paginate(50);

        return CustomerResource::collection($customers);
    }

    public function store(StoreCustomerRequest $request): CustomerResource
    {
        $companyId = app(CompanyContext::class)->companyId();
        $validated = $request->validated();

        $customer = DB::transaction(function () use ($validated, $companyId) {
            $contacts = $validated['contacts'] ?? [];
            $addresses = $validated['addresses'] ?? [];

            unset($validated['contacts'], $validated['addresses']);

            $customer = Customer::create([
                ...$validated,
                'company_id' => $companyId,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $this->syncAddresses($customer, $addresses);
            $this->syncContacts($customer, $contacts);

            return $customer;
        });

        return $this->resource($customer->fresh());
    }

    public function show(Customer $customer): CustomerResource
    {
        $this->authorize('view', $customer);

        return $this->resource($customer);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): CustomerResource
    {
        $validated = $request->validated();

        DB::transaction(function () use ($customer, $validated) {
            $contacts = $validated['contacts'] ?? null;
            $addresses = $validated['addresses'] ?? null;

            unset($validated['contacts'], $validated['addresses']);

            if ($validated !== []) {
                $customer->update($validated);
            }

            if ($addresses !== null) {
                $this->syncAddresses($customer, $addresses);
            }

            if ($contacts !== null) {
                $this->syncContacts($customer, $contacts);
            }
        });

        return $this->resource($customer->fresh());
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);

        DB::transaction(function () use ($customer) {
            $this->deleteContacts($customer);
            $customer->addresses()->delete();
            $customer->delete();
        });

        return response()->json(['message' => 'Customer deleted successfully.']);
    }

    protected function resource(Customer $customer): CustomerResource
    {
        return new CustomerResource(
            $customer->load([
                'primaryContact.primaryPhoneNumber',
                'primaryContact.primaryEmailAddress',
                'primaryAddress',
                'billingAddress',
                'contacts.phoneNumbers',
                'contacts.emailAddresses',
                'addresses',
            ])->loadCount(['contacts', 'jobs'])
        );
    }

    protected function syncAddresses(Customer $customer, array $addresses): void
    {
        $existingAddresses = $customer->addresses()->get()->keyBy('id');
        $keptAddressIds = [];

        foreach ($addresses as $index => $addressData) {
            $addressId = Arr::pull($addressData, 'id');
            $payload = [
                ...$addressData,
                'is_primary' => $addressData['is_primary'] ?? $index === 0,
                'is_active' => $addressData['is_active'] ?? true,
            ];

            if ($addressId && $existingAddresses->has($addressId)) {
                $address = $existingAddresses->get($addressId);
                $address->update($payload);
            } else {
                $address = $customer->addresses()->create($payload);
            }

            $keptAddressIds[] = $address->id;
        }

        $addressesToDelete = $keptAddressIds === []
            ? $customer->addresses()->get()
            : $customer->addresses()->whereNotIn('id', $keptAddressIds)->get();

        $addressesToDelete->each->delete();
    }

    protected function syncContacts(Customer $customer, array $contacts): void
    {
        $existingContacts = $customer->contacts()->get()->keyBy('id');
        $keptContactIds = [];

        foreach ($contacts as $index => $contactData) {
            $contactId = Arr::pull($contactData, 'id');
            $phoneNumbers = Arr::pull($contactData, 'phone_numbers', []);
            $emailAddresses = Arr::pull($contactData, 'email_addresses', []);

            $payload = [
                ...$contactData,
                'is_primary' => $contactData['is_primary'] ?? $index === 0,
                'is_active' => $contactData['is_active'] ?? true,
            ];

            if ($contactId && $existingContacts->has($contactId)) {
                $contact = $existingContacts->get($contactId);
                $contact->update($payload);
            } else {
                $contact = $customer->contacts()->create($payload);
            }

            $keptContactIds[] = $contact->id;

            $this->syncPhoneNumbers($contact, $phoneNumbers);
            $this->syncEmailAddresses($contact, $emailAddresses);
        }

        $contactsToDelete = $keptContactIds === []
            ? $customer->contacts()->get()
            : $customer->contacts()->whereNotIn('id', $keptContactIds)->get();

        foreach ($contactsToDelete as $contact) {
            $contact->phoneNumbers()->delete();
            $contact->emailAddresses()->delete();
            $contact->delete();
        }
    }

    protected function syncPhoneNumbers(CustomerContact $contact, array $phoneNumbers): void
    {
        $existingPhoneNumbers = $contact->phoneNumbers()->get()->keyBy('id');
        $keptPhoneNumberIds = [];

        foreach ($phoneNumbers as $index => $phoneData) {
            $phoneId = Arr::pull($phoneData, 'id');
            $countryCode = $phoneData['phone_country_code'] ?? 'US';
            $formattedPhone = $this->phoneService->formatToE164(
                $phoneData['phone_number'],
                $countryCode
            );

            $payload = [
                ...$phoneData,
                'phone_number' => $formattedPhone,
                'phone_country_code' => $countryCode,
                'is_primary' => $phoneData['is_primary'] ?? $index === 0,
                'is_active' => $phoneData['is_active'] ?? true,
            ];

            if ($phoneId && $existingPhoneNumbers->has($phoneId)) {
                $phoneNumber = $existingPhoneNumbers->get($phoneId);
                $phoneNumber->update($payload);
            } else {
                $phoneNumber = $contact->phoneNumbers()->create($payload);
            }

            $keptPhoneNumberIds[] = $phoneNumber->id;
        }

        $phoneNumbersToDelete = $keptPhoneNumberIds === []
            ? $contact->phoneNumbers()->get()
            : $contact->phoneNumbers()->whereNotIn('id', $keptPhoneNumberIds)->get();

        $phoneNumbersToDelete->each->delete();
    }

    protected function syncEmailAddresses(CustomerContact $contact, array $emailAddresses): void
    {
        $existingEmailAddresses = $contact->emailAddresses()->get()->keyBy('id');
        $keptEmailAddressIds = [];

        foreach ($emailAddresses as $index => $emailData) {
            $emailId = Arr::pull($emailData, 'id');
            $payload = [
                ...$emailData,
                'is_primary' => $emailData['is_primary'] ?? $index === 0,
                'is_active' => $emailData['is_active'] ?? true,
            ];

            if ($emailId && $existingEmailAddresses->has($emailId)) {
                $emailAddress = $existingEmailAddresses->get($emailId);
                $emailAddress->update($payload);
            } else {
                $emailAddress = $contact->emailAddresses()->create($payload);
            }

            $keptEmailAddressIds[] = $emailAddress->id;
        }

        $emailAddressesToDelete = $keptEmailAddressIds === []
            ? $contact->emailAddresses()->get()
            : $contact->emailAddresses()->whereNotIn('id', $keptEmailAddressIds)->get();

        $emailAddressesToDelete->each->delete();
    }

    protected function deleteContacts(Customer $customer): void
    {
        foreach ($customer->contacts as $contact) {
            $contact->phoneNumbers()->delete();
            $contact->emailAddresses()->delete();
            $contact->delete();
        }
    }
}
