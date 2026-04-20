<?php

namespace App\Services;

use Aws\PinpointSMSVoiceV2\PinpointSMSVoiceV2Client;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected ?PinpointSMSVoiceV2Client $client = null;
    protected string $originationNumber;
    protected bool $forceSend;

    public function __construct()
    {
        $this->originationNumber = config('services.aws_sms.origination_number', '');
        $this->forceSend = (bool) config('services.aws_sms.force_send', false);
    }

    /**
     * Get the AWS Pinpoint SMS client (lazy loaded)
     */
    protected function getClient(): PinpointSMSVoiceV2Client
    {
        if ($this->client === null) {
            $this->client = new PinpointSMSVoiceV2Client([
                'version' => 'latest',
                'region' => config('services.aws_sms.region', 'us-east-1'),
                'credentials' => [
                    'key' => config('services.aws_sms.key'),
                    'secret' => config('services.aws_sms.secret'),
                ],
            ]);
        }

        return $this->client;
    }

    /**
     * Send an SMS message via AWS Pinpoint SMS
     */
    public function send(string $to, string $message, string $messageType = 'TRANSACTIONAL'): bool
    {
        // In local/testing, just log the message (unless force send)
        if (app()->environment('local', 'testing') && !$this->forceSend) {
            Log::info('SMS (local)', ['to' => $to, 'message' => $message, 'type' => $messageType]);
            return true;
        }

        try {
            $params = [
                'DestinationPhoneNumber' => $to,
                'MessageBody' => $message,
                'MessageType' => $messageType, // TRANSACTIONAL or PROMOTIONAL
            ];

            // Add origination number if configured
            if (!empty($this->originationNumber)) {
                $params['OriginationIdentity'] = $this->originationNumber;
            }

            $result = $this->getClient()->sendTextMessage($params);

            Log::info('SMS sent via AWS', [
                'to' => $to,
                'message_id' => $result['MessageId'] ?? null,
            ]);

            return true;

        } catch (AwsException $e) {
            Log::error('AWS SMS failed', [
                'to' => $to,
                'error' => $e->getAwsErrorMessage(),
                'code' => $e->getAwsErrorCode(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('SMS exception', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send a login code via SMS (transactional)
     * 
     * Format optimized for iOS/Android OTP autofill:
     * - Code at the beginning
     * - @domain #code format for Web OTP API
     */
    public function sendLoginCode(string $to, string $code): bool
    {
        $message = "{$code} is your Built Ox code.\n\nExpires in 10 min.\n\n@builtox.com #{$code}";
        return $this->send($to, $message, 'TRANSACTIONAL');
    }

    /**
     * Send a marketing/promotional message
     * Must have explicit opt-in from recipient
     */
    public function sendPromotional(string $to, string $message): bool
    {
        return $this->send($to, $message, 'PROMOTIONAL');
    }
}
