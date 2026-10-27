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
                    'Grosvenor House', 'Bluewaters Island & Ain Dubai', 'Habtoor Grand Resort',
                ],
                'faqs' => [
                    [
                        'q' => 'Do you pick up from Airbnb apartments or private towers in Dubai Marina?',
                        'a' => 'Yes! We provide direct door-to-door pickup from all residential towers, hotels, and holiday homes in Dubai Marina and JBR. Your safari captain will contact you via WhatsApp with live vehicle updates.',
                    ],
                    [
                        'q' => 'What is the pickup timing for Dubai Marina guests?',
                        'a' => 'For the Evening Desert Safari, pickup from Dubai Marina takes place between 02:30 PM and 03:00 PM, returning you comfortably by 09:30 PM to 10:00 PM.',
                    ],
                    [
                        'q' => 'Is there any additional surcharge for pickup from Bluewaters Island or JBR?',
                        'a' => 'No additional surcharge. Complimentary 4x4 hotel and residence pickup is 100% included in all 4x4 Land Cruiser and VIP safari packages.',
                    ],
                    [
                        'q' => 'Can we bring luggage if we are checking out from our Marina hotel?',
                        'a' => 'Yes. All our Land Cruiser 300 series vehicles have spacious trunks to safely lock and store luggage during the desert safari adventure.',
                    ],
                ],
            ],

            'downtown-dubai' => [
                'key' => 'downtown-dubai',
                'name' => 'Downtown Dubai & Burj Khalifa',
                'district' => 'Downtown Dubai, Business Bay, DIFC & Burj Khalifa Area',
                'slug' => 'desert-safari-from-downtown-dubai',
                'meta_title' => 'Desert Safari from Downtown Dubai & Burj Khalifa (2026): Best Deals',
                'meta_desc' => 'Direct 4x4 pickup from Burj Khalifa, Dubai Mall & Downtown hotels. Premium red dunes bashing, camel rides, 5-star live BBQ dinner & cultural shows.',
                'headline' => 'Desert Safari from Downtown Dubai & Burj Khalifa',
                'subheadline' => 'Direct VIP and 4x4 Land Cruiser pickup across Downtown Dubai, Business Bay, DIFC, and Sheikh Zayed Road hotels.',
                'pickup_window' => '02:45 PM – 03:15 PM',
                'return_time' => '09:30 PM – 10:00 PM',
                'transit_time' => '40 Minutes direct highway drive via Ras Al Khor & E44',
                'geo' => ['lat' => 25.1972, 'lng' => 55.2744],
                'landmarks' => [
                    'Burj Khalifa & Dubai Mall', 'Address Downtown', 'Armani Hotel Dubai', 'Palace Downtown',
                    'JW Marriott Marquis Business Bay', 'The Ritz-Carlton DIFC', 'SLS Dubai',
                ],
                'faqs' => [
                    [
                        'q' => 'Can you pick up from hotels near Burj Khalifa and Dubai Mall?',
                        'a' => 'Absolutely. We pick up directly from the lobby of all Downtown Dubai hotels, including Armani Hotel, Address Downtown, Palace Downtown, and surrounding towers.',
                    ],
                    [
                        'q' => 'How long is the drive from Downtown Dubai to Lahbab Red Dunes?',
                        'a' => 'From Downtown, the drive is only about 40 minutes along the smooth Al Ain / Ras Al Khor highway, ensuring a swift and scenic trip to the red dunes.',
                    ],
                    [
                        'q' => 'Are Business Bay and DIFC offices or hotels eligible for doorstep pickup?',
                        'a' => 'Yes, our 4x4 safari fleet covers all streets and towers across Business Bay, DIFC, and Financial Centre with zero extra pickup fees.',
                    ],
                    [
                        'q' => 'Can we be dropped off at Dubai Mall or a Downtown restaurant after the tour?',
                        'a' => 'Yes! Simply inform your safari captain when boarding, and we will happily drop you off at Dubai Mall, Souk Al Bahar, or your chosen Downtown dining venue.',
                    ],
                ],
            ],

            'palm-jumeirah' => [
                'key' => 'palm-jumeirah',
                'name' => 'Palm Jumeirah & Atlantis',
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
                    'Anantara The Palm Resort', 'Waldorf Astoria Palm Jumeirah', 'One&Only The Palm',
                ],
                'faqs' => [
                    [
                        'q' => 'Do you pick up from private villas on Palm Jumeirah Fronds?',
                        'a' => 'Yes, our private 4x4 Land Cruisers provide direct gate pickup from all residential Fronds (A to P) as well as the Palm Crescent luxury resorts.',
                    ],
                    [
                        'q' => 'Is pickup available from Atlantis The Royal and Atlantis The Palm?',
                        'a' => 'Yes, we provide daily pickup from both Atlantis The Royal and Atlantis The Palm main reception lobbies.',
                    ],
                    [
                        'q' => 'Is private car hire recommended for Palm Jumeirah families?',
                        'a' => 'Yes, our Private Land Cruiser option gives your family an exclusive vehicle with customized departure times and maximum comfort.',
                    ],
                    [
                        'q' => 'What happens if we want to combine the safari with Dune Buggy driving?',
                        'a' => 'You can select the Can-Am or Polaris Dune Buggy add-on during checkout, and your buggy ride will be seamlessly integrated into your safari itinerary.',
                    ],
                ],
            ],

            'deira-bur-dubai' => [
                'key' => 'deira-bur-dubai',
                'name' => 'Deira & Old Dubai',
                'district' => 'Deira, Bur Dubai, Al Rigga, Al Fahidi & Dubai Creek Harbour',
                'slug' => 'desert-safari-from-deira',
                'meta_title' => 'Desert Safari from Deira & Bur Dubai (2026): Doorstep 4x4 Pickup',
                'meta_desc' => 'Affordable & luxury desert safaris with complimentary 4x4 pickup from Deira, Bur Dubai, Al Rigga & Old Dubai hotels. Red dune bashing, camel ride & BBQ buffet from AED 79.',
                'headline' => 'Desert Safari from Deira & Bur Dubai',
                'subheadline' => 'Convenient doorstep pickup across historic Deira, Bur Dubai, Al Seef, and Dubai International Airport (DXB) transit hotels.',
                'pickup_window' => '02:45 PM – 03:15 PM',
                'return_time' => '09:30 PM – 10:00 PM',
                'transit_time' => '40 Minutes drive via E66 / Dubai-Al Ain Road',
                'geo' => ['lat' => 25.2697, 'lng' => 55.3095],
                'landmarks' => [
                    'City Centre Deira', 'Al Seef Heritage District', 'Gold Souk & Spice Souk',
                    'Hyatt Regency Dubai Creek', 'Grand Hyatt Dubai', 'Al Rigga Metro Station area',
                ],
                'faqs' => [
                    [
                        'q' => 'Can airport layover passengers be picked up from DXB Airport hotels?',
                        'a' => 'Yes! We pick up from all hotels around Dubai International Airport (Terminal 1, 2, 3), Holiday Inn Express, Le Meridien, and Premier Inn.',
                    ],
                    [
                        'q' => 'Is doorstep 4x4 pickup available from Deira hotels and residences?',
                        'a' => 'Yes! We provide complimentary door-to-door 4x4 Land Cruiser pickup from all hotels, apartments, and residential buildings across Deira and Bur Dubai.',
                    ],
                    [
                        'q' => 'How far is the desert from Deira and Bur Dubai?',
                        'a' => 'The scenic Lahbab high red dunes are approximately 40 minutes away, making it a very quick and smooth journey.',
                    ],
                    [
                        'q' => 'Are vegetarian and Jain food options provided at the camp for Indian travelers?',
                        'a' => 'Yes, our 5-star live buffet features extensive vegetarian curry, lentils, naan bread, salads, and specially prepared Jain dishes upon request.',
                    ],
                ],
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
                    'Grand Millennium Dubai Barsha Heights', 'Novotel Suites Dubai Mall of the Emirates',
                ],
                'faqs' => [
                    [
                        'q' => 'Do you pick up from hotels near Mall of the Emirates?',
                        'a' => 'Yes, we provide direct door-to-door 4x4 pickup from all hotels around Mall of the Emirates, Al Barsha 1, and Barsha Heights.',
                    ],
                    [
                        'q' => 'Is Al Barsha well located for desert safari departures?',
                        'a' => 'Yes, Al Barsha has immediate access to Umm Suqeim Road and the E44 Al Khail / Al Ain highways, reaching the red dunes in just 40 minutes.',
                    ],
                    [
                        'q' => 'Can we book on the same day if staying in Al Barsha?',
                        'a' => 'Yes! If you book before 01:30 PM, same-day pickup from Al Barsha is guaranteed. Instant booking confirmation is sent via WhatsApp.',
                    ],
                    [
                        'q' => 'Is camel riding and sandboarding included in the package?',
                        'a' => 'Yes, all standard and VIP safari packages from Al Barsha include dune bashing, camel riding, sandboarding, live shows, and dinner.',
                    ],
                ],
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
                    'Radisson Blu Resort Sharjah', 'Ajman Hotel (Kempinski)', 'Sahara Centre Sharjah',
                ],
                'faqs' => [
                    [
                        'q' => 'Do you provide daily desert safari pickup from Sharjah hotels?',
                        'a' => 'Yes! We have dedicated 4x4 vehicles operating daily in Sharjah and Ajman, picking up directly from hotel lobbies and residences.',
                    ],
                    [
                        'q' => 'Which highway route is used from Sharjah to the desert?',
                        'a' => 'We travel via the wide Emirates Road (E611) directly to Lahbab Desert, bypassing downtown city traffic for a fast and comfortable ride.',
                    ],
                    [
                        'q' => 'Can we book a private vehicle for our family from Sharjah?',
                        'a' => 'Yes, our Private Land Cruiser package (up to 6 guests) is very popular with Sharjah families, offering private door-to-door service.',
                    ],
                    [
                        'q' => 'What is the return time to Sharjah after the safari?',
                        'a' => 'You will be dropped back at your Sharjah or Ajman hotel between 10:00 PM and 10:30 PM after the live entertainment shows conclude.',
                    ],
                ],
            ],

            'jbr' => [
                'key' => 'jbr',
                'name' => 'JBR (Jumeirah Beach Residence)',
                'district' => 'JBR The Walk, Rimal, Bahar, Sadaf, Murjan & Amwaj Towers',
                'slug' => 'desert-safari-from-jbr',
                'meta_title' => 'Desert Safari from JBR (2026): Direct Hotel & The Walk Pickup',
                'meta_desc' => 'Doorstep desert safari pickup from JBR The Walk, Hilton, Rixos, Sofitel & beachfront towers. 4x4 red dune bashing, camel trekking & live BBQ dinner.',
                'headline' => 'Desert Safari from JBR (Jumeirah Beach Residence)',
                'subheadline' => 'Convenient doorstep 4x4 pickup from all beachfront resorts, hotel lobbies, and holiday towers along The Walk JBR.',
                'pickup_window' => '02:30 PM – 03:00 PM',
                'return_time' => '09:30 PM – 10:00 PM',
                'transit_time' => '45 Minutes direct via Al Khail Road & E44 Al Ain Highway',
                'geo' => ['lat' => 25.0780, 'lng' => 55.1328],
                'landmarks' => [
                    'The Walk JBR', 'Hilton Dubai Jumeirah', 'Rixos Premium Dubai JBR',
                    'Sofitel Dubai Jumeirah Beach', 'Sheraton Jumeirah Beach Resort', 'Flying Cup JBR',
                ],
                'faqs' => [
                    [
                        'q' => 'Do you pick up directly from hotels along The Walk in JBR?',
                        'a' => 'Yes! We provide direct lobby pickup from all JBR hotels including Hilton, Rixos, Sofitel, and JA Ocean View Hotel.',
                    ],
                    [
                        'q' => 'Can guests staying in JBR residential apartments be picked up?',
                        'a' => 'Yes, our captains pick up from the main drop-off plazas of all JBR clusters: Sadaf, Bahar, Rimal, Murjan, Amwaj, and Shams.',
                    ],
                    [
                        'q' => 'How can we find our driver at JBR?',
                        'a' => 'Your safari captain contacts you on WhatsApp 30–45 minutes before arrival with vehicle details, plate number, and live tracking.',
                    ],
                    [
                        'q' => 'What clothing is recommended for a desert safari from JBR?',
                        'a' => 'Comfortable loose cotton clothing, sneakers or sandals, and sunglasses. In winter months (Nov–March), a light jacket is advised for the cooler desert evening.',
                    ],
                ],
            ],

            'business-bay' => [
                'key' => 'business-bay',
                'name' => 'Business Bay & Canal',
                'district' => 'Business Bay, Marasi Marina, Bay Square, Executive Towers & Dubai Canal',
                'slug' => 'desert-safari-from-business-bay',
                'meta_title' => 'Desert Safari from Business Bay (2026): Fast 4x4 Hotel Pickup',
                'meta_desc' => 'Direct 4x4 Land Cruiser pickup from Business Bay hotels and towers. Experience red dunes bashing, quad biking, BBQ dinner & cultural entertainment.',
                'headline' => 'Desert Safari from Business Bay & Dubai Canal',
                'subheadline' => 'Quick 35-minute transfer from JW Marriott Marquis, SLS, Paramount, and all Business Bay residences directly to Lahbab Desert.',
                'pickup_window' => '02:45 PM – 03:15 PM',
                'return_time' => '09:30 PM – 10:00 PM',
                'transit_time' => '35 Minutes direct drive via Ras Al Khor Road & E44',
                'geo' => ['lat' => 25.1857, 'lng' => 55.2638],
                'landmarks' => [
                    'JW Marriott Marquis Hotel', 'SLS Dubai Hotel & Residences', 'The Oberoi Dubai',
                    'Paramount Hotel Dubai', 'Radisson Blu Hotel Dubai Canal View', 'Bay Square',
                ],
                'faqs' => [
                    [
                        'q' => 'How long does it take to reach the desert from Business Bay?',
                        'a' => 'Business Bay has direct access to Ras Al Khor Road (E44), reaching the Lahbab Red Dunes in just 35 minutes.',
                    ],
                    [
                        'q' => 'Do you pick up from Airbnb towers in Business Bay?',
                        'a' => 'Yes! We pick up from all executive towers, holiday apartments, and waterfront residences across Business Bay.',
                    ],
                    [
                        'q' => 'Can we book a private 4x4 for business colleagues or family?',
                        'a' => 'Yes, our Private Land Cruiser option accommodates up to 6 or 7 passengers with flexible pickup timing and private desert camp seating.',
                    ],
                    [
                        'q' => 'Is hotel pickup complimentary in Business Bay?',
                        'a' => 'Yes, complimentary 4x4 doorstep pickup is included in all our 4x4 Land Cruiser and VIP packages.',
                    ],
                ],
            ],

            'difc' => [
                'key' => 'difc',
                'name' => 'DIFC & Trade Centre',
                'district' => 'Dubai International Financial Centre (DIFC), Gate Village & Trade Centre',
                'slug' => 'desert-safari-from-difc',
                'meta_title' => 'Desert Safari from DIFC & Trade Centre (2026): VIP Hotel Pickup',
                'meta_desc' => 'Premium desert safari pickup from DIFC, Gate Village, Ritz-Carlton & Emirates Towers. High red dunes bashing, VIP table service & 5-star live BBQ.',
                'headline' => 'Desert Safari from DIFC & Financial Centre',
                'subheadline' => 'Executive 4x4 pickup from The Ritz-Carlton, Waldorf Astoria, Four Seasons DIFC, and Emirates Towers.',
                'pickup_window' => '02:45 PM – 03:15 PM',
                'return_time' => '09:30 PM – 10:00 PM',
                'transit_time' => '35 Minutes direct drive via Al Majaz & E66/E44',
                'geo' => ['lat' => 25.2104, 'lng' => 55.2810],
                'landmarks' => [
                    'Gate Village DIFC', 'The Ritz-Carlton DIFC', 'Waldorf Astoria DIFC',
                    'Four Seasons Hotel DIFC', 'Jumeirah Emirates Towers', 'Museum of the Future',
                ],
                'faqs' => [
                    [
                        'q' => 'Is VIP safari seating available for DIFC business guests?',
                        'a' => 'Yes! Our VIP Desert Safari package includes table-side waiter service, priority buffet access, and reserved premium sofa seating.',
                    ],
                    [
                        'q' => 'Can we be picked up from our office building in DIFC?',
                        'a' => 'Yes, we pick up directly from the valet drop-off zones of all Gate Buildings and office towers throughout DIFC.',
                    ],
                    [
                        'q' => 'Is the desert safari suitable for corporate team building?',
                        'a' => 'Absolutely. We regularly host corporate groups with customized convoy vehicles, exclusive camp pavilions, and dune buggy challenges.',
                    ],
                    [
                        'q' => 'What is the cancellation policy for DIFC bookings?',
                        'a' => 'You enjoy 100% free cancellation up to 24 hours prior to departure, with zero cancellation fees.',
                    ],
                ],
            ],

            'jumeirah' => [
                'key' => 'jumeirah',
                'name' => 'Jumeirah Beach & Madinat Jumeirah',
                'district' => 'Jumeirah 1, 2, 3, Umm Suqeim, Kite Beach, Madinat Jumeirah & Burj Al Arab area',
                'slug' => 'desert-safari-from-jumeirah',
                'meta_title' => 'Desert Safari from Jumeirah & Madinat (2026): Luxury Pickup',
                'meta_desc' => 'Doorstep pickup from Burj Al Arab, Jumeirah Beach Hotel, Madinat Jumeirah & beachfront villas. Red dunes bashing, camel ride & live evening BBQ.',
                'headline' => 'Desert Safari from Jumeirah Beach & Madinat',
                'subheadline' => 'Exclusive pickup from luxury beach resorts, private coastal villas, and boutique hotels across Jumeirah and Umm Suqeim.',
                'pickup_window' => '02:30 PM – 03:00 PM',
                'return_time' => '09:45 PM – 10:15 PM',
                'transit_time' => '40 Minutes direct drive via Umm Suqeim Street & E44',
                'geo' => ['lat' => 25.1412, 'lng' => 55.1852],
                'landmarks' => [
                    'Burj Al Arab Jumeirah', 'Jumeirah Beach Hotel', 'Madinat Jumeirah Resort',
                    'Mandarin Oriental Jumeira', 'Four Seasons Resort Dubai at Jumeirah Beach', 'Kite Beach',
                ],
                'faqs' => [
                    [
                        'q' => 'Do you pick up from private residences and villas in Jumeirah?',
                        'a' => 'Yes, we provide private door-to-door gate pickup for villas located anywhere in Jumeirah 1, 2, 3 and Umm Suqeim.',
                    ],
                    [
                        'q' => 'Can we request pickup from Madinat Jumeirah hotels (Al Qasr, Mina A’Salam)?',
                        'a' => 'Yes, our safari vehicles have full access to pick up from the entrance porte-cochère of all Madinat Jumeirah properties.',
                    ],
                    [
                        'q' => 'Can elderly guests or pregnant women join the safari from Jumeirah?',
                        'a' => 'Yes! Guests seeking a gentle experience can travel in our direct scenic transfer vehicle to relax at the Bedouin camp without rough dune bashing.',
                    ],
                    [
                        'q' => 'Are child booster seats provided for young families?',
                        'a' => 'Yes, baby and child booster seats are available upon advance request at no additional charge.',
                    ],
                ],
            ],

            'sheikh-zayed-road' => [
                'key' => 'sheikh-zayed-road',
                'name' => 'Sheikh Zayed Road & DWTC',
                'district' => 'Sheikh Zayed Road Corridor, Dubai World Trade Centre (DWTC) & Financial Metro Station',
                'slug' => 'desert-safari-from-sheikh-zayed-road',
                'meta_title' => 'Desert Safari from Sheikh Zayed Road (2026): 4x4 Hotel Pickup',
                'meta_desc' => 'Complimentary 4x4 Land Cruiser pickup from all Sheikh Zayed Road hotels & DWTC. Lahbab red dunes bashing, live Tanoura & fire shows, camel ride & BBQ.',
                'headline' => 'Desert Safari from Sheikh Zayed Road & World Trade Centre',
                'subheadline' => 'Convenient hotel and tower pickup along the iconic Sheikh Zayed Road corridor with rapid highway desert access.',
                'pickup_window' => '02:45 PM – 03:15 PM',
                'return_time' => '09:30 PM – 10:00 PM',
                'transit_time' => '35 Minutes direct highway route via E44',
                'geo' => ['lat' => 25.2185, 'lng' => 55.2780],
                'landmarks' => [
                    'Dubai World Trade Centre (DWTC)', 'Conrad Dubai', 'Shangri-La Hotel Dubai',
                    'Fairmont Dubai', 'Sheraton Grand Hotel', 'Dusit Thani Dubai',
                ],
                'faqs' => [
                    [
                        'q' => 'Can exhibition and conference delegates at DWTC be picked up for the evening safari?',
                        'a' => 'Yes! We pick up directly from Dubai World Trade Centre hotels around 03:00 PM, returning you comfortably by 09:30 PM.',
                    ],
                    [
                        'q' => 'Which hotels on Sheikh Zayed Road are covered?',
                        'a' => 'We cover all hotels from Trade Centre Roundabout down to Dubai Marina, including Conrad, Fairmont, Shangri-La, and Four Points.',
                    ],
                    [
                        'q' => 'Can we pay with cash on pickup or credit card at the hotel?',
                        'a' => 'Yes, we support full flexibility: pay online in advance, pay online upon boarding via instant payment link, or pay in cash on the day.',
                    ],
                    [
                        'q' => 'Is dune bashing safe in your 4x4 vehicles?',
                        'a' => '100% safe. All our 4x4 Land Cruisers are fitted with safety roll-cages, comprehensive passenger insurance, and driven by DTCM-certified desert master captains.',
                    ],
                ],
            ],

            'bur-dubai' => [
                'key' => 'bur-dubai',
                'name' => 'Bur Dubai & Al Fahidi',
                'district' => 'Bur Dubai, Al Fahidi Historical Neighbourhood, Meena Bazaar, Al Mankhool & Oud Metha',
                'slug' => 'desert-safari-from-bur-dubai',
                'meta_title' => 'Desert Safari from Bur Dubai & Al Fahidi (2026): Best Value Deals',
                'meta_desc' => 'Doorstep 4x4 pickup from Bur Dubai, Meena Bazaar, Al Mankhool & Oud Metha hotels. Evening desert safari with red dune bashing, BBQ dinner & live shows from AED 79.',
                'headline' => 'Desert Safari from Bur Dubai & Al Fahidi',
                'subheadline' => 'Door-to-door pickup across historic Bur Dubai, Meena Bazaar, and Al Mankhool directly to the golden sands of Lahbab.',
                'pickup_window' => '02:45 PM – 03:15 PM',
                'return_time' => '09:30 PM – 10:00 PM',
                'transit_time' => '35 Minutes direct drive via Dubai-Al Ain Road (E66)',
                'geo' => ['lat' => 25.2532, 'lng' => 55.2972],
                'landmarks' => [
                    'Al Fahidi Historical Neighbourhood', 'Meena Bazaar', 'Dubai Museum',
                    'Grand Excelsior Hotel Bur Dubai', 'Mövenpick Hotel & Apartments Bur Dubai', 'Raffles Dubai Oud Metha',
                ],
                'faqs' => [
                    [
                        'q' => 'Is pickup available from small boutique hotels or apartments in Meena Bazaar?',
                        'a' => 'Yes, our captains pick up directly from hotels or the nearest accessible main road entrance across Meena Bazaar and Al Fahidi.',
                    ],
                    [
                        'q' => 'Do you offer Indian vegetarian or Jain meals on the safari?',
                        'a' => 'Yes, our buffet includes authentic vegetarian curries, dal, paneer, warm naan, fresh salads, and Jain meal options.',
                    ],
                    [
                        'q' => 'Are children under 3 years old charged?',
                        'a' => 'Infants under 3 years travel completely FREE of charge on all our desert safaris.',
                    ],
                    [
                        'q' => 'How can I get an instant booking voucher for Bur Dubai pickup?',
                        'a' => 'Simply book online; an instant digital boarding pass and WhatsApp voucher with your captain’s direct contact will be issued immediately.',
                    ],
                ],
            ],

            'dubai-creek' => [
                'key' => 'dubai-creek',
                'name' => 'Dubai Creek Harbour & Festival City',
                'district' => 'Dubai Creek Harbour, Dubai Festival City & Al Jaddaf Waterfront',
                'slug' => 'desert-safari-from-dubai-creek',
                'meta_title' => 'Desert Safari from Dubai Creek & Festival City (2026): 4x4 Pickup',
                'meta_desc' => 'Fast desert safari transfers from Dubai Creek Harbour, Festival City & Al Jaddaf. High red dunes 4x4 bashing, live BBQ dinner, camel ride & fire show.',
                'headline' => 'Desert Safari from Dubai Creek Harbour & Festival City',
                'subheadline' => 'Swift 30-minute transfer from Vida Creek Harbour, Address Grand, and InterContinental Festival City straight to Lahbab dunes.',
                'pickup_window' => '03:00 PM – 03:30 PM',
                'return_time' => '09:30 PM – 10:00 PM',
                'transit_time' => '30 Minutes fast direct highway access via E44 / Ras Al Khor',
                'geo' => ['lat' => 25.2014, 'lng' => 55.3524],
                'landmarks' => [
                    'Vida Creek Harbour', 'Address Grand Creek Harbour', 'InterContinental Dubai Festival City',
                    'Palazzo Versace Dubai', 'Crowne Plaza Dubai Festival City', 'Dubai Creek Marina',
                ],
                'faqs' => [
                    [
                        'q' => 'Why is Dubai Creek Harbour ideal for desert safari departures?',
                        'a' => 'Dubai Creek Harbour has immediate, traffic-free access to Ras Al Khor Road, reaching the red dunes in just 30 minutes!',
                    ],
                    [
                        'q' => 'Do you pick up from Palazzo Versace and Al Jaddaf waterfront hotels?',
                        'a' => 'Yes, we provide daily 4x4 pickup from Palazzo Versace, Marriott Al Jaddaf, and all surrounding waterfront properties.',
                    ],
                    [
                        'q' => 'What activities are included at the desert camp?',
                        'a' => 'Included activities: high red dunes bashing, sandboarding, short camel ride, henna painting, fire show, Tanoura dance, belly dance, and 5-star live BBQ dinner.',
                    ],
                    [
                        'q' => 'Can we upgrade to private table dining?',
                        'a' => 'Yes, you can select the VIP Table Service add-on during checkout for table-side dining service.',
                    ],
                ],
            ],

            'al-seef' => [
                'key' => 'al-seef',
                'name' => 'Al Seef & Heritage Port Rashid',
                'district' => 'Al Seef Heritage Area, Dubai Creek Promenade, Port Rashid Cruise Terminal & Umm Hurair',
                'slug' => 'desert-safari-from-al-seef',
                'meta_title' => 'Desert Safari from Al Seef & Cruise Port Rashid (2026): Fast Transfers',
                'meta_desc' => 'Desert safari pickup from Al Seef Heritage district and Port Rashid Cruise Terminal. Red dunes 4x4 bashing, Bedouin cultural show & live BBQ.',
                'headline' => 'Desert Safari from Al Seef & Port Rashid',
                'subheadline' => 'Direct transfers from Al Seef heritage promenade, Port Rashid cruise terminals, and Queen Elizabeth 2 (QE2) floating hotel.',
                'pickup_window' => '02:45 PM – 03:15 PM',
                'return_time' => '09:30 PM – 10:00 PM',
                'transit_time' => '35 Minutes direct drive via E66',
                'geo' => ['lat' => 25.2608, 'lng' => 55.3045],
                'landmarks' => [
                    'Al Seef Heritage Promenade', 'Canopy by Hilton Dubai Al Seef', 'Al Seef Heritage Hotel Dubai',
                    'Queen Elizabeth 2 (QE2) Port Rashid', 'Port Rashid Cruise Terminal 2 & 3', 'Consulate Area Umm Hurair',
                ],
                'faqs' => [
                    [
                        'q' => 'Can cruise ship passengers docked at Port Rashid be picked up for a desert safari?',
                        'a' => 'Yes! We coordinate directly with cruise arrival schedules at Port Rashid Terminals 2 & 3 to provide prompt pickup and guarantee timely return before ship departure.',
                    ],
                    [
                        'q' => 'Do you pick up from Al Seef Heritage Hotel and Canopy by Hilton?',
                        'a' => 'Yes, our 4x4 vehicles pick up directly from the main guest reception area of Al Seef hotels.',
                    ],
                    [
                        'q' => 'Is sandboarding included for beginners?',
                        'a' => 'Yes! Sandboarding is free and unlimited. Our safari captains provide boards and guide you safely down the red sand slopes.',
                    ],
                    [
                        'q' => 'Can we book last-minute on the day our cruise docks in Dubai?',
                        'a' => 'Yes! We accept same-day bookings up until 01:30 PM, subject to 4x4 fleet seat availability.',
                    ],
                ],
            ],

            'jlt' => [
                'key' => 'jlt',
                'name' => 'Jumeirah Lake Towers (JLT)',
                'district' => 'JLT Clusters A to Z, DMCC, Dubai Marina Promenade & Emirates Living',
                'slug' => 'desert-safari-from-jlt',
                'meta_title' => 'Desert Safari from JLT (2026): Doorstep Cluster Pickup',
                'meta_desc' => 'Convenient desert safari pickup from Jumeirah Lake Towers (JLT Clusters A–Z). 4x4 red dunes bashing, quad biking, camel ride, live BBQ & shows.',
                'headline' => 'Desert Safari from Jumeirah Lake Towers (JLT)',
                'subheadline' => 'Door-to-door 4x4 pickup from every residential tower and hotel across JLT Clusters A to Z, DMCC, and Emirates Living.',
                'pickup_window' => '02:30 PM – 03:00 PM',
                'return_time' => '09:30 PM – 10:00 PM',
                'transit_time' => '45 Minutes direct drive via First Al Khail Road & E44',
                'geo' => ['lat' => 25.0740, 'lng' => 55.1488],
                'landmarks' => [
                    'DMCC Headquarters & Almas Tower', 'Mövenpick Hotel Jumeirah Lakes Towers',
                    'Pullman Dubai Jumeirah Lakes Towers', 'Taj Jumeirah Lakes Towers',
                    'Bonnington JLT', 'Armada Towers Cluster P',
                ],
                'faqs' => [
                    [
                        'q' => 'Do you pick up from every specific cluster in JLT (Clusters A to Z)?',
                        'a' => 'Yes! We provide direct doorstep pickup from the drop-off lobby of every building and residential tower across all 26 JLT clusters.',
                    ],
                    [
                        'q' => 'What is the pickup window for JLT residents?',
                        'a' => 'For the Evening Safari, pickup from JLT is between 02:30 PM and 03:00 PM, returning you back to your tower by 09:30 PM to 10:00 PM.',
                    ],
                    [
                        'q' => 'Can we add 1000cc Can-Am Buggy driving to our JLT package?',
                        'a' => 'Yes! You can add high-performance Can-Am Maverick or Polaris RZR Dune Buggy sessions directly during the online booking process.',
                    ],
                    [
                        'q' => 'Is customer support available on WhatsApp if our plans change?',
                        'a' => 'Yes! Our customer support team is available 24/7 on WhatsApp to assist with instant date rescheduling or booking inquiries.',
                    ],
                ],
            ],
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
            if ($loc['slug'] === $slugOrKey || $loc['key'] === $slugOrKey || $loc['slug'] === 'desert-safari-from-'.$slugOrKey) {
                return $loc;
            }
        }

        return null;
    }
}
