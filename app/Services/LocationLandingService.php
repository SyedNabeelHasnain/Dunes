<?php

namespace App\Services;

class LocationLandingService
{
    /**
     * Return all available programmatic location landing datasets.
     */
    public static function getLocations(): array
    {
        return [
            'dubai-marina' => [
                'key' => 'dubai-marina',
                'name' => 'Dubai Marina & JBR',
                'district' => 'Dubai Marina, Jumeirah Beach Residence (JBR) & Bluewaters Island',
                'slug' => 'desert-safari-from-dubai-marina',
                'meta_title' => 'Desert Safari from Dubai Marina & JBR (2026): Doorstep Hotel Pickup',
                'meta_desc' => 'Book top-rated Dubai desert safari with complimentary 4x4 pickup from any hotel or residence in Dubai Marina, JBR & Bluewaters. Live BBQ, dune bashing & shows.',
                'headline' => 'Desert Safari from Dubai Marina & JBR',
                'subheadline' => 'Doorstep hotel & residence pickup across Dubai Marina, Jumeirah Beach Residence, and Bluewaters Island in air-conditioned luxury 4x4 Land Cruisers.',
                'pickup_window' => '02:30 PM – 03:00 PM',
                'return_time' => '09:30 PM – 10:00 PM',
                'transit_time' => '45 Minutes direct drive via E44 / Al Ain Highway',
                'geo' => ['lat' => 25.0772, 'lng' => 55.1330],
                'landmarks' => [
                    'Dubai Marina Mall', 'The Walk JBR', 'Address Beach Resort', 'Rixos Premium Dubai',
                    'Grosvenor House', 'Bluewaters Island & Ain Dubai', 'Habtoor Grand Resort'
                ],
                'faqs' => [
                    [
                        'q' => 'Do you pick up from Airbnb apartments or private towers in Dubai Marina?',
                        'a' => 'Yes! We provide direct door-to-door pickup from all residential towers, hotels, and holiday homes in Dubai Marina and JBR. Your safari captain will contact you via WhatsApp with live vehicle updates.'
                    ],
                    [
                        'q' => 'What is the pickup timing for Dubai Marina guests?',
                        'a' => 'For the Evening Desert Safari, pickup from Dubai Marina takes place between 02:30 PM and 03:00 PM, returning you comfortably by 09:30 PM to 10:00 PM.'
                    ],
                    [
                        'q' => 'Is there any additional surcharge for pickup from Bluewaters Island or JBR?',
                        'a' => 'No additional surcharge. Complimentary 4x4 hotel and residence pickup is 100% included in all 4x4 Land Cruiser and VIP safari packages.'
                    ],
                    [
                        'q' => 'Can we bring luggage if we are checking out from our Marina hotel?',
                        'a' => 'Yes. All our Land Cruiser 300 series vehicles have spacious trunks to safely lock and store luggage during the desert safari adventure.'
                    ]
                ]
            ],

            'downtown-dubai' => [
                'key' => 'downtown-dubai',
                'name' => 'Downtown Dubai & Business Bay',
                'district' => 'Downtown Dubai, Business Bay, DIFC & Burj Khalifa Area',
                'slug' => 'desert-safari-from-downtown-dubai',
                'meta_title' => 'Desert Safari from Downtown Dubai & Business Bay (2026): Best Deals',
                'meta_desc' => 'Direct 4x4 pickup from Burj Khalifa, Dubai Mall & Downtown hotels. Premium red dunes bashing, camel rides, 5-star live BBQ dinner & cultural shows.',
                'headline' => 'Desert Safari from Downtown Dubai & Burj Khalifa',
                'subheadline' => 'Direct VIP and 4x4 Land Cruiser pickup across Downtown Dubai, Business Bay, DIFC, and Sheikh Zayed Road hotels.',
                'pickup_window' => '02:45 PM – 03:15 PM',
                'return_time' => '09:30 PM – 10:00 PM',
                'transit_time' => '40 Minutes direct highway drive via Ras Al Khor & E44',
                'geo' => ['lat' => 25.1972, 'lng' => 55.2744],
                'landmarks' => [
                    'Burj Khalifa & Dubai Mall', 'Address Downtown', 'Armani Hotel Dubai', 'Palace Downtown',
                    'JW Marriott Marquis Business Bay', 'The Ritz-Carlton DIFC', 'SLS Dubai'
                ],
                'faqs' => [
                    [
                        'q' => 'Can you pick up from hotels near Burj Khalifa and Dubai Mall?',
                        'a' => 'Absolutely. We pick up directly from the lobby of all Downtown Dubai hotels, including Armani Hotel, Address Downtown, Palace Downtown, and surrounding towers.'
                    ],
                    [
                        'q' => 'How long is the drive from Downtown Dubai to Lahbab Red Dunes?',
                        'a' => 'From Downtown, the drive is only about 40 minutes along the smooth Al Ain / Ras Al Khor highway, ensuring a swift and scenic trip to the red dunes.'
                    ],
                    [
                        'q' => 'Are Business Bay and DIFC offices or hotels eligible for doorstep pickup?',
                        'a' => 'Yes, our 4x4 safari fleet covers all streets and towers across Business Bay, DIFC, and Financial Centre with zero extra pickup fees.'
                    ],
                    [
                        'q' => 'Can we be dropped off at Dubai Mall or a Downtown restaurant after the tour?',
                        'a' => 'Yes! Simply inform your safari captain when boarding, and we will happily drop you off at Dubai Mall, Souk Al Bahar, or your chosen Downtown dining venue.'
                    ]
                ]
            ],

            'palm-jumeirah' => [
                'key' => 'palm-jumeirah',
                'name' => 'Palm Jumeirah & Dubai Media City',
                'district' => 'Palm Jumeirah Crescent, The Trunk, Dubai Media City & Al Sufouh',
                'slug' => 'desert-safari-from-palm-jumeirah',
                'meta_title' => 'Desert Safari from Palm Jumeirah (2026): Luxury 4x4 Hotel Pickup',
                'meta_desc' => 'Exclusive desert safari pickup from Atlantis The Palm, Royal Atlantis, Five Palm & Palm Jumeirah villas. 5-star VIP dinner & quad buggy options.',
                'headline' => 'Desert Safari from Palm Jumeirah',
                'subheadline' => 'Doorstep hotel and private villa pickup across Palm Jumeirah (Crescent & Fronds), Atlantis The Palm, and Dubai Media City.',
                'pickup_window' => '02:15 PM – 02:45 PM',
                'return_time' => '09:45 PM – 10:15 PM',
                'transit_time' => '50 Minutes direct drive via Sheikh Zayed Road & E44',
                'geo' => ['lat' => 25.1124, 'lng' => 55.1390],
                'landmarks' => [
                    'Atlantis The Palm', 'Atlantis The Royal', 'FIVE Palm Jumeirah', 'W Dubai - The Palm',
                    'Anantara The Palm Resort', 'Waldorf Astoria Palm Jumeirah', 'One&Only The Palm'
                ],
                'faqs' => [
                    [
                        'q' => 'Do you pick up from private villas on Palm Jumeirah Fronds?',
                        'a' => 'Yes, our private 4x4 Land Cruisers provide direct gate pickup from all residential Fronds (A to P) as well as the Palm Crescent luxury resorts.'
                    ],
                    [
                        'q' => 'Is pickup available from Atlantis The Royal and Atlantis The Palm?',
                        'a' => 'Yes, we provide daily pickup from both Atlantis The Royal and Atlantis The Palm main reception lobbies.'
                    ],
                    [
                        'q' => 'Is private car hire recommended for Palm Jumeirah families?',
                        'a' => 'Yes, our Private Land Cruiser option gives your family an exclusive vehicle with customized departure times and maximum comfort.'
                    ],
                    [
                        'q' => 'What happens if we want to combine the safari with Dune Buggy driving?',
                        'a' => 'You can select the Can-Am or Polaris Dune Buggy add-on during checkout, and your buggy ride will be seamlessly integrated into your safari itinerary.'
                    ]
                ]
            ],

            'deira-bur-dubai' => [
                'key' => 'deira-bur-dubai',
                'name' => 'Deira, Bur Dubai & Dubai Creek',
                'district' => 'Deira, Bur Dubai, Al Rigga, Al Fahidi & Dubai Creek Harbour',
                'slug' => 'desert-safari-from-deira',
                'meta_title' => 'Desert Safari from Deira & Bur Dubai (2026): Best Value Pickup',
                'meta_desc' => 'Affordable & luxury desert safaris with pickup from Deira, Bur Dubai, Al Rigga & Old Dubai hotels. Red dune bashing, camel ride & BBQ buffet from AED 99.',
                'headline' => 'Desert Safari from Deira & Bur Dubai',
                'subheadline' => 'Convenient doorstep pickup across historic Deira, Bur Dubai, Al Seef, and Dubai International Airport (DXB) transit hotels.',
                'pickup_window' => '02:45 PM – 03:15 PM',
                'return_time' => '09:30 PM – 10:00 PM',
                'transit_time' => '40 Minutes drive via E66 / Dubai-Al Ain Road',
                'geo' => ['lat' => 25.2697, 'lng' => 55.3095],
                'landmarks' => [
                    'City Centre Deira', 'Al Seef Heritage District', 'Gold Souk & Spice Souk',
                    'Hyatt Regency Dubai Creek', 'Grand Hyatt Dubai', 'Al Rigga Metro Station area'
                ],
                'faqs' => [
                    [
                        'q' => 'Can airport layover passengers be picked up from DXB Airport hotels?',
                        'a' => 'Yes! We pick up from all hotels around Dubai International Airport (Terminal 1, 2, 3), Holiday Inn Express, Le Meridien, and Premier Inn.'
                    ],
                    [
                        'q' => 'Are bus pickup meeting points available in Deira for budget travelers?',
                        'a' => 'Yes, in addition to direct 4x4 hotel pickup, our AED 99 Standard Package offers centralized meeting points near major Deira metro stations.'
                    ],
                    [
                        'q' => 'How far is the desert from Deira and Bur Dubai?',
                        'a' => 'The scenic Lahbab high red dunes are approximately 40 minutes away, making it a very quick and smooth journey.'
                    ],
                    [
                        'q' => 'Are vegetarian and Jain food options provided at the camp for Indian travelers?',
                        'a' => 'Yes, our 5-star live buffet features extensive vegetarian curry, lentils, naan bread, salads, and specially prepared Jain dishes upon request.'
                    ]
                ]
            ],

            'al-barsha' => [
                'key' => 'al-barsha',
                'name' => 'Al Barsha & Mall of the Emirates',
                'district' => 'Al Barsha 1, Al Barsha Heights (Tecom) & Barsha South',
                'slug' => 'desert-safari-from-al-barsha',
                'meta_title' => 'Desert Safari from Al Barsha (2026): Mall of Emirates Pickup',
                'meta_desc' => 'Desert safari pickup from Al Barsha, Tecom & Mall of the Emirates hotels. Red dunes 4x4 bashing, live BBQ dinner, camel trekking & shows.',
                'headline' => 'Desert Safari from Al Barsha & Barsha Heights',
                'subheadline' => 'Fast, direct pickup from all hotels and residences in Al Barsha 1, 2, 3, Tecom (Barsha Heights), and near Mall of the Emirates.',
                'pickup_window' => '02:30 PM – 03:00 PM',
                'return_time' => '09:30 PM – 10:00 PM',
                'transit_time' => '40 Minutes direct highway access via Umm Suqeim Street & E44',
                'geo' => ['lat' => 25.1112, 'lng' => 55.2005],
                'landmarks' => [
                    'Mall of the Emirates & Ski Dubai', 'Kempinski Hotel Mall of the Emirates', 'Sheraton Mall of the Emirates',
                    'Grand Millennium Dubai Barsha Heights', 'Novotel Suites Dubai Mall of the Emirates'
                ],
                'faqs' => [
                    [
                        'q' => 'Do you pick up from hotels near Mall of the Emirates?',
                        'a' => 'Yes, we provide direct door-to-door 4x4 pickup from all hotels around Mall of the Emirates, Al Barsha 1, and Barsha Heights.'
                    ],
                    [
                        'q' => 'Is Al Barsha well located for desert safari departures?',
                        'a' => 'Yes, Al Barsha has immediate access to Umm Suqeim Road and the E44 Al Khail / Al Ain highways, reaching the red dunes in just 40 minutes.'
                    ],
                    [
                        'q' => 'Can we book on the same day if staying in Al Barsha?',
                        'a' => 'Yes! If you book before 01:30 PM, same-day pickup from Al Barsha is guaranteed. Instant booking confirmation is sent via WhatsApp.'
                    ],
                    [
                        'q' => 'Is camel riding and sandboarding included in the package?',
                        'a' => 'Yes, all standard and VIP safari packages from Al Barsha include dune bashing, camel riding, sandboarding, live shows, and dinner.'
                    ]
                ]
            ],

            'sharjah' => [
                'key' => 'sharjah',
                'name' => 'Sharjah & Ajman',
                'district' => 'Sharjah Corniche, Al Majaz, Al Nahda & Ajman Beach Hotels',
                'slug' => 'desert-safari-from-sharjah',
                'meta_title' => 'Desert Safari from Sharjah & Ajman (2026): Doorstep Hotel Pickup',
                'meta_desc' => 'Daily desert safari transfers from Sharjah & Ajman hotels. High red dunes bashing, Bedouin camp dinner, belly dance, Tanoura show & camel rides.',
                'headline' => 'Desert Safari from Sharjah & Ajman',
                'subheadline' => 'Daily pickup from all Sharjah and Ajman hotels, Al Majaz Waterfront, Al Nahda, and Corniche residences.',
                'pickup_window' => '02:00 PM – 02:30 PM',
                'return_time' => '10:00 PM – 10:30 PM',
                'transit_time' => '45 Minutes direct via Emirates Road (E611)',
                'geo' => ['lat' => 25.3463, 'lng' => 55.4209],
                'landmarks' => [
                    'Al Majaz Waterfront', 'Sharjah Corniche', 'Sheraton Sharjah Beach Resort',
                    'Radisson Blu Resort Sharjah', 'Ajman Hotel (Kempinski)', 'Sahara Centre Sharjah'
                ],
                'faqs' => [
                    [
                        'q' => 'Do you provide daily desert safari pickup from Sharjah hotels?',
                        'a' => 'Yes! We have dedicated 4x4 vehicles operating daily in Sharjah and Ajman, picking up directly from hotel lobbies and residences.'
                    ],
                    [
                        'q' => 'Which highway route is used from Sharjah to the desert?',
                        'a' => 'We travel via the wide Emirates Road (E611) directly to Lahbab Desert, bypassing downtown city traffic for a fast and comfortable ride.'
                    ],
                    [
                        'q' => 'Can we book a private vehicle for our family from Sharjah?',
                        'a' => 'Yes, our Private Land Cruiser package (up to 6 guests) is very popular with Sharjah families, offering private door-to-door service.'
                    ],
                    [
                        'q' => 'What is the return time to Sharjah after the safari?',
                        'a' => 'You will be dropped back at your Sharjah or Ajman hotel between 10:00 PM and 10:30 PM after the live entertainment shows conclude.'
                    ]
                ]
            ]
        ];
    }

    /**
     * Find a location by slug or key.
     */
    public static function find(string $slugOrKey): ?array
    {
        $locations = self::getLocations();
        if (isset($locations[$slugOrKey])) {
            return $locations[$slugOrKey];
        }

        foreach ($locations as $loc) {
            if ($loc['slug'] === $slugOrKey || $loc['key'] === $slugOrKey || $loc['slug'] === 'desert-safari-from-' . $slugOrKey) {
                return $loc;
            }
        }

        return null;
    }
}
