<?php

namespace Database\Seeders;

class SeederHelper
{
    public static function getOrganizationData(): array
    {
        return [
            // 1) Straight Pipes Plumbing (org-level partner)
            'org_straight_pipes_plumbing' => [
                'organization' => [
                    'key' => 'org_straight_pipes_plumbing',
                    'type' => 'vendor',
                    'name' => 'Straight Pipes Plumbing',
                    'phone_country_code' => 'US',
                    'phone' => '8015551201',
                    'email' => 'office@straightpipesplumbing.com',
                    'website' => 'https://straightpipesplumbing.com',
                ],
                'address' => [
                    'key' => 'addr_straight_pipes_hq',
                    'name' => 'Straight Pipes Plumbing HQ',
                    'line1' => '1146 Mustang Ln',
                    'line2' => null,
                    'city' => 'Saratoga Springs',
                    'state' => 'UT',
                    'postal_code' => '84045',
                    'country' => 'US',
                ],
                'locations' => [
                    'loc_straight_pipes_main' => [
                        'key' => 'loc_straight_pipes_main',
                        'name' => 'Main Office',
                        'phone_country_code' => 'US',
                        'phone' => '8015551201',
                        'email' => 'office@straightpipesplumbing.com',
                        'address_key' => 'addr_straight_pipes_hq',
                        'is_active' => true,
                        'is_default' => true,
                    ],
                ],
                'users' => [
                    'user_kyle_riggs' => [
                        'key' => 'user_kyle_riggs',
                        'name' => 'Kyle Riggs',
                        'email' => 'kyle@straightpipesplumbing.com',
                        'phone_country_code' => 'US',
                        'phone' => '8015552201',
                        'is_location_primary_for' => 'loc_straight_pipes_main',
                    ],
                ],
                'vendor_categories' => ['plumber'],
                'vendor_profile' => [
                    'tier' => 'partner',
                    'status' => 'approved',
                    'participation_scope' => 'organization',
                    'is_gateway' => false,
                    'preferred_contact_channel' => 'sms',
                    'primary_contact_user' => 'user_kyle_riggs',
                    'primary_location' => 'loc_straight_pipes_main',
                ],
                'notification_preferences' => [
                    [
                        'user' => 'user_kyle_riggs',
                        'type' => 'touchpoints',
                        'channel' => 'sms',
                        'enabled' => true,
                        'is_primary' => true,
                    ],
                    [
                        'user' => 'user_kyle_riggs',
                        'type' => 'touchpoints',
                        'channel' => 'email',
                        'enabled' => true,
                        'is_primary' => false,
                    ],
                ],
            ],

            // 2) Cold Water Bath Plumbing (location-level, multi-branch)
            'org_cold_water_bath_plumbing' => [
                'organization' => [
                    'key' => 'org_cold_water_bath_plumbing',
                    'type' => 'vendor',
                    'name' => 'Cold Water Bath Plumbing',
                    'phone_country_code' => 'US',
                    'phone' => '8015551310',
                    'email' => 'office@coldwaterbaths.com',
                    'website' => 'https://coldwaterbaths.com',
                ],
                'address' => [
                    'key' => 'addr_cold_water_em',
                    'name' => 'Cold Water Bath - Eagle Mountain',
                    'line1' => '9277 N Scenic Mountain Dr',
                    'line2' => null,
                    'city' => 'Eagle Mountain',
                    'state' => 'UT',
                    'postal_code' => '84005',
                    'country' => 'US',
                ],
                'locations' => [
                    'loc_cold_water_em' => [
                        'key' => 'loc_cold_water_em',
                        'name' => 'Eagle Mountain Branch',
                        'phone_country_code' => 'US',
                        'phone' => '8015551310',
                        'email' => 'office@coldwaterbaths.com',
                        'address_key' => 'addr_cold_water_em',
                        'is_active' => true,
                        'is_default' => true,
                    ],
                ],
                'users' => [
                    'user_melissa_vance' => [
                        'key' => 'user_melissa_vance',
                        'name' => 'Melissa Vance',
                        'email' => 'melissa@coldwaterbaths.com',
                        'phone_country_code' => 'US',
                        'phone' => '8015552310',
                        'is_location_primary_for' => 'loc_cold_water_em',
                    ],
                ],
                'vendor_categories' => ['plumber'],
                'vendor_profile' => [
                    'tier' => 'referral_tree',
                    'status' => 'approved',
                    'participation_scope' => 'location',
                    'is_gateway' => false,
                    'preferred_contact_channel' => 'email',
                    'primary_contact_user' => 'user_melissa_vance',
                    'primary_location' => 'loc_cold_water_em',
                ],
                'notification_preferences' => [
                    [
                        'user' => 'user_melissa_vance',
                        'type' => 'touchpoints',
                        'channel' => 'email',
                        'enabled' => true,
                        'is_primary' => true,
                    ],
                    [
                        'user' => 'user_melissa_vance',
                        'type' => 'touchpoints',
                        'channel' => 'sms',
                        'enabled' => true,
                        'is_primary' => false,
                    ],
                ],
            ],

            // 3) After Hours Dan Plumbing (individual gateway)
            'org_after_hours_dan_plumbing' => [
                'organization' => [
                    'key' => 'org_after_hours_dan_plumbing',
                    'type' => 'vendor',
                    'name' => 'After Hours Dan Plumbing',
                    'phone_country_code' => 'US',
                    'phone' => '8015551414',
                    'email' => 'dan@afterhoursdanplumbing.com',
                    'website' => 'https://afterhoursdanplumbing.com',
                ],
                'address' => [
                    'key' => 'addr_after_hours_dan',
                    'name' => 'After Hours Dan - Dispatch',
                    'line1' => '4112 W Pony Express Pkwy',
                    'line2' => null,
                    'city' => 'Eagle Mountain',
                    'state' => 'UT',
                    'postal_code' => '84005',
                    'country' => 'US',
                ],
                'locations' => [
                    'loc_after_hours_dan_dispatch' => [
                        'key' => 'loc_after_hours_dan_dispatch',
                        'name' => 'Dispatch',
                        'phone_country_code' => 'US',
                        'phone' => '8015551414',
                        'email' => 'dan@afterhoursdanplumbing.com',
                        'address_key' => 'addr_after_hours_dan',
                        'is_active' => true,
                        'is_default' => true,
                    ],
                ],
                'users' => [
                    'user_dan_howard' => [
                        'key' => 'user_dan_howard',
                        'name' => 'Dan Howard',
                        'email' => 'dan@afterhoursdanplumbing.com',
                        'phone_country_code' => 'US',
                        'phone' => '8015551414',
                        'is_location_primary_for' => 'loc_after_hours_dan_dispatch',
                    ],
                ],
                'vendor_categories' => ['plumber'],
                'vendor_profile' => [
                    'tier' => 'partner',
                    'status' => 'trial_period',
                    'participation_scope' => 'individual',
                    'is_gateway' => true,
                    'preferred_contact_channel' => 'call',
                    'primary_contact_user' => 'user_dan_howard',
                    'primary_location' => 'loc_after_hours_dan_dispatch',
                ],
                'notification_preferences' => [
                    [
                        'user' => 'user_dan_howard',
                        'type' => 'touchpoints',
                        'channel' => 'sms',
                        'enabled' => true,
                        'is_primary' => true,
                    ],
                    [
                        'user' => 'user_dan_howard',
                        'type' => 'touchpoints',
                        'channel' => 'call',
                        'enabled' => true,
                        'is_primary' => false,
                    ],
                ],
            ],

            // 4) Degree or Two HVAC (org-level)
            'org_degree_or_two_hvac' => [
                'organization' => [
                    'key' => 'org_degree_or_two_hvac',
                    'type' => 'vendor',
                    'name' => 'Degree or Two HVAC',
                    'phone_country_code' => 'US',
                    'phone' => '8015551602',
                    'email' => 'service@degreeortwohvac.com',
                    'website' => 'https://degreeortwohvac.com',
                ],
                'address' => [
                    'key' => 'addr_degree_or_two',
                    'name' => 'Degree or Two - HQ',
                    'line1' => '76 N 1200 W',
                    'line2' => null,
                    'city' => 'Lehi',
                    'state' => 'UT',
                    'postal_code' => '84043',
                    'country' => 'US',
                ],
                'locations' => [
                    'loc_degree_or_two_hq' => [
                        'key' => 'loc_degree_or_two_hq',
                        'name' => 'Lehi HQ',
                        'phone_country_code' => 'US',
                        'phone' => '8015551602',
                        'email' => 'service@degreeortwohvac.com',
                        'address_key' => 'addr_degree_or_two',
                        'is_active' => true,
                        'is_default' => true,
                    ],
                ],
                'users' => [
                    'user_ben_cartwright' => [
                        'key' => 'user_ben_cartwright',
                        'name' => 'Ben Cartwright',
                        'email' => 'ben@degreeortwohvac.com',
                        'phone_country_code' => 'US',
                        'phone' => '8015552602',
                        'is_location_primary_for' => 'loc_degree_or_two_hq',
                    ],
                ],
                'vendor_categories' => ['hvac'],
                'vendor_profile' => [
                    'tier' => 'prospect',
                    'status' => 'approved',
                    'participation_scope' => 'organization',
                    'is_gateway' => false,
                    'preferred_contact_channel' => 'sms',
                    'primary_contact_user' => 'user_ben_cartwright',
                    'primary_location' => 'loc_degree_or_two_hq',
                ],
                'notification_preferences' => [
                    [
                        'user' => 'user_ben_cartwright',
                        'type' => 'touchpoints',
                        'channel' => 'sms',
                        'enabled' => true,
                        'is_primary' => true,
                    ],
                ],
            ],

            // 5) Cold Snap Heating & Air (flagged)
            'org_cold_snap_hvac' => [
                'organization' => [
                    'key' => 'org_cold_snap_hvac',
                    'type' => 'vendor',
                    'name' => 'Cold Snap Heating & Air',
                    'phone_country_code' => 'US',
                    'phone' => '8015551717',
                    'email' => 'dispatch@coldsnapheatandair.com',
                    'website' => 'https://coldsnapheatandair.com',
                ],
                'address' => [
                    'key' => 'addr_cold_snap',
                    'name' => 'Cold Snap - Orem',
                    'line1' => '1440 W 800 N',
                    'line2' => null,
                    'city' => 'Orem',
                    'state' => 'UT',
                    'postal_code' => '84057',
                    'country' => 'US',
                ],
                'locations' => [
                    'loc_cold_snap_orem' => [
                        'key' => 'loc_cold_snap_orem',
                        'name' => 'Orem Office',
                        'phone_country_code' => 'US',
                        'phone' => '8015551717',
                        'email' => 'dispatch@coldsnapheatandair.com',
                        'address_key' => 'addr_cold_snap',
                        'is_active' => true,
                        'is_default' => true,
                    ],
                ],
                'users' => [
                    'user_trina_shaw' => [
                        'key' => 'user_trina_shaw',
                        'name' => 'Trina Shaw',
                        'email' => 'trina@coldsnapheatandair.com',
                        'phone_country_code' => 'US',
                        'phone' => '8015552717',
                        'is_location_primary_for' => 'loc_cold_snap_orem',
                    ],
                ],
                'vendor_categories' => ['hvac'],
                'vendor_profile' => [
                    'tier' => 'prospect',
                    'status' => 'flagged',
                    'participation_scope' => 'organization',
                    'is_gateway' => false,
                    'preferred_contact_channel' => 'email',
                    'primary_contact_user' => 'user_trina_shaw',
                    'primary_location' => 'loc_cold_snap_orem',
                ],
                'notification_preferences' => [
                    [
                        'user' => 'user_trina_shaw',
                        'type' => 'touchpoints',
                        'channel' => 'email',
                        'enabled' => true,
                        'is_primary' => true,
                    ],
                ],
            ],

            // 6) Wasatch Mutual Assurance (insurance carrier)
            'org_wasatch_mutual' => [
                'organization' => [
                    'key' => 'org_wasatch_mutual',
                    'type' => 'vendor',
                    'name' => 'Wasatch Mutual Assurance',
                    'phone_country_code' => 'US',
                    'phone' => '8015552006',
                    'email' => 'claims@wasatchmutual.com',
                    'website' => 'https://wasatchmutual.com',
                ],
                'address' => [
                    'key' => 'addr_wasatch_mutual',
                    'name' => 'Wasatch Mutual - Claims',
                    'line1' => '260 S 400 W',
                    'line2' => 'Suite 210',
                    'city' => 'Salt Lake City',
                    'state' => 'UT',
                    'postal_code' => '84101',
                    'country' => 'US',
                ],
                'locations' => [
                    'loc_wasatch_mutual_claims' => [
                        'key' => 'loc_wasatch_mutual_claims',
                        'name' => 'Claims Office',
                        'phone_country_code' => 'US',
                        'phone' => '8015552006',
                        'email' => 'claims@wasatchmutual.com',
                        'address_key' => 'addr_wasatch_mutual',
                        'is_active' => true,
                        'is_default' => true,
                    ],
                ],
                'users' => [
                    'user_aaron_pierce' => [
                        'key' => 'user_aaron_pierce',
                        'name' => 'Aaron Pierce',
                        'email' => 'aaron.pierce@wasatchmutual.com',
                        'phone_country_code' => 'US',
                        'phone' => '8015553006',
                        'is_location_primary_for' => 'loc_wasatch_mutual_claims',
                    ],
                ],
                'vendor_categories' => ['insurance'],
                'vendor_profile' => [
                    'tier' => 'partner',
                    'status' => 'approved',
                    'participation_scope' => 'organization',
                    'is_gateway' => false,
                    'preferred_contact_channel' => 'email',
                    'primary_contact_user' => 'user_aaron_pierce',
                    'primary_location' => 'loc_wasatch_mutual_claims',
                ],
                'notification_preferences' => [
                    [
                        'user' => 'user_aaron_pierce',
                        'type' => 'touchpoints',
                        'channel' => 'email',
                        'enabled' => true,
                        'is_primary' => true,
                    ],
                ],
            ],

            // 7) Mountain Shield Insurance Group (agent/broker)
            'org_mountain_shield' => [
                'organization' => [
                    'key' => 'org_mountain_shield',
                    'type' => 'vendor',
                    'name' => 'Mountain Shield Insurance Group',
                    'phone_country_code' => 'US',
                    'phone' => '8015552077',
                    'email' => 'hello@mountainshieldinsurance.com',
                    'website' => 'https://mountainshieldinsurance.com',
                ],
                'address' => [
                    'key' => 'addr_mountain_shield',
                    'name' => 'Mountain Shield - Draper',
                    'line1' => '1213 E 12300 S',
                    'line2' => null,
                    'city' => 'Draper',
                    'state' => 'UT',
                    'postal_code' => '84020',
                    'country' => 'US',
                ],
                'locations' => [
                    'loc_mountain_shield_draper' => [
                        'key' => 'loc_mountain_shield_draper',
                        'name' => 'Draper Office',
                        'phone_country_code' => 'US',
                        'phone' => '8015552077',
                        'email' => 'hello@mountainshieldinsurance.com',
                        'address_key' => 'addr_mountain_shield',
                        'is_active' => true,
                        'is_default' => true,
                    ],
                ],
                'users' => [
                    'user_jessica_reed' => [
                        'key' => 'user_jessica_reed',
                        'name' => 'Jessica Reed',
                        'email' => 'jessica@mountainshieldinsurance.com',
                        'phone_country_code' => 'US',
                        'phone' => '8015553077',
                        'is_location_primary_for' => 'loc_mountain_shield_draper',
                    ],
                ],
                'vendor_categories' => ['insurance'],
                'vendor_profile' => [
                    'tier' => 'referral_tree',
                    'status' => 'approved',
                    'participation_scope' => 'location',
                    'is_gateway' => false,
                    'preferred_contact_channel' => 'sms',
                    'primary_contact_user' => 'user_jessica_reed',
                    'primary_location' => 'loc_mountain_shield_draper',
                ],
                'notification_preferences' => [
                    [
                        'user' => 'user_jessica_reed',
                        'type' => 'touchpoints',
                        'channel' => 'sms',
                        'enabled' => true,
                        'is_primary' => true,
                    ],
                ],
            ],

            // 8) Keys & Headaches Property Management
            'org_keys_and_headaches_pm' => [
                'organization' => [
                    'key' => 'org_keys_and_headaches_pm',
                    'type' => 'vendor',
                    'name' => 'Keys & Headaches Property Management',
                    'phone_country_code' => 'US',
                    'phone' => '8015552208',
                    'email' => 'maintenance@keysandheadachespm.com',
                    'website' => 'https://keysandheadachespm.com',
                ],
                'address' => [
                    'key' => 'addr_keys_and_headaches',
                    'name' => 'Keys & Headaches - Office',
                    'line1' => '88 W Center St',
                    'line2' => 'Floor 2',
                    'city' => 'Provo',
                    'state' => 'UT',
                    'postal_code' => '84601',
                    'country' => 'US',
                ],
                'locations' => [
                    'loc_keys_and_headaches_provo' => [
                        'key' => 'loc_keys_and_headaches_provo',
                        'name' => 'Provo Office',
                        'phone_country_code' => 'US',
                        'phone' => '8015552208',
                        'email' => 'maintenance@keysandheadachespm.com',
                        'address_key' => 'addr_keys_and_headaches',
                        'is_active' => true,
                        'is_default' => true,
                    ],
                ],
                'users' => [
                    'user_rachel_ives' => [
                        'key' => 'user_rachel_ives',
                        'name' => 'Rachel Ives',
                        'email' => 'rachel@keysandheadachespm.com',
                        'phone_country_code' => 'US',
                        'phone' => '8015553208',
                        'is_location_primary_for' => 'loc_keys_and_headaches_provo',
                    ],
                ],
                'vendor_categories' => ['property_manager'],
                'vendor_profile' => [
                    'tier' => 'partner',
                    'status' => 'approved',
                    'participation_scope' => 'organization',
                    'is_gateway' => false,
                    'preferred_contact_channel' => 'email',
                    'primary_contact_user' => 'user_rachel_ives',
                    'primary_location' => 'loc_keys_and_headaches_provo',
                ],
                'notification_preferences' => [
                    [
                        'user' => 'user_rachel_ives',
                        'type' => 'touchpoints',
                        'channel' => 'email',
                        'enabled' => true,
                        'is_primary' => true,
                    ],
                ],
            ],

            // 9) Permit Pending Construction (contractor)
            'org_permit_pending_gc' => [
                'organization' => [
                    'key' => 'org_permit_pending_gc',
                    'type' => 'contractor',
                    'name' => 'Permit Pending Construction',
                    'phone_country_code' => 'US',
                    'phone' => '8015552409',
                    'email' => 'projects@permitpendinggc.com',
                    'website' => 'https://permitpendinggc.com',
                ],
                'address' => [
                    'key' => 'addr_permit_pending',
                    'name' => 'Permit Pending - Office',
                    'line1' => '512 N State St',
                    'line2' => null,
                    'city' => 'American Fork',
                    'state' => 'UT',
                    'postal_code' => '84003',
                    'country' => 'US',
                ],
                'locations' => [
                    'loc_permit_pending_af' => [
                        'key' => 'loc_permit_pending_af',
                        'name' => 'American Fork Office',
                        'phone_country_code' => 'US',
                        'phone' => '8015552409',
                        'email' => 'projects@permitpendinggc.com',
                        'address_key' => 'addr_permit_pending',
                        'is_active' => true,
                        'is_default' => true,
                    ],
                ],
                'users' => [
                    'user_chase_morrow' => [
                        'key' => 'user_chase_morrow',
                        'name' => 'Chase Morrow',
                        'email' => 'chase@permitpendinggc.com',
                        'phone_country_code' => 'US',
                        'phone' => '8015553409',
                        'is_location_primary_for' => 'loc_permit_pending_af',
                    ],
                ],
                'vendor_categories' => [],
                'vendor_profile' => null,
                'notification_preferences' => [],
            ],

            // 10) Built Ox Internal
            'org_built_ox_internal' => [
                'organization' => [
                    'key' => 'org_built_ox_internal',
                    'type' => 'internal',
                    'name' => 'Built Ox Internal',
                    'phone_country_code' => 'US',
                    'phone' => '8015550001',
                    'email' => 'ops@builtox.com',
                    'website' => 'https://builtox.com',
                ],
                'address' => [
                    'key' => 'addr_built_ox',
                    'name' => 'Built Ox - Office',
                    'line1' => '548 W Muskmelon Way',
                    'line2' => null,
                    'city' => 'Saratoga Springs',
                    'state' => 'UT',
                    'postal_code' => '84045',
                    'country' => 'US',
                ],
                'locations' => [
                    'loc_built_ox_main' => [
                        'key' => 'loc_built_ox_main',
                        'name' => 'Main Office',
                        'phone_country_code' => 'US',
                        'phone' => '8015550001',
                        'email' => 'ops@builtox.com',
                        'address_key' => 'addr_built_ox',
                        'is_active' => true,
                        'is_default' => true,
                    ],
                ],
                'users' => [
                    'user_jay_lara' => [
                        'key' => 'user_jay_lara',
                        'name' => 'Jay Lara',
                        'email' => 'jay@builtox.com',
                        'phone_country_code' => 'US',
                        'phone' => '8015559001',
                        'is_location_primary_for' => 'loc_built_ox_main',
                    ],
                ],
                'vendor_categories' => [],
                'vendor_profile' => null,
                'notification_preferences' => [
                    [
                        'user' => 'user_jay_lara',
                        'type' => 'system',
                        'channel' => 'email',
                        'enabled' => true,
                        'is_primary' => true,
                    ],
                    [
                        'user' => 'user_jay_lara',
                        'type' => 'touchpoints',
                        'channel' => 'sms',
                        'enabled' => true,
                        'is_primary' => true,
                    ],
                ],
            ],
        ];
    }
}

