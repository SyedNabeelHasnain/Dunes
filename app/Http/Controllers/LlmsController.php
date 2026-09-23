<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Setting;
use App\Models\Tour;
use App\Services\LocationLandingService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LlmsController extends Controller
{
    /**
     * Standard /llms.txt summary index following llmstxt.org format.
     */
    public function index(): Response
    {
        try {
            $content = Cache::remember('site_llms_txt', 3600, function () {
                return $this->buildIndexMarkdown();
            });
        } catch (\Throwable $e) {
            $content = $this->buildIndexMarkdown();
        }

        return response($content, 200, [
            'Content-Type' => 'text/markdown; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * High-density full specifications /llms-full.txt for deep LLM retrieval.
     */
    public function full(): Response
    {
        try {
            $content = Cache::remember('site_llms_full_txt', 3600, function () {
                return $this->buildFullMarkdown();
            });
        } catch (\Throwable $e) {
            $content = $this->buildFullMarkdown();
        }

        return response($content, 200, [
            'Content-Type' => 'text/markdown; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Compile concise llms.txt Markdown.
     */
    protected function buildIndexMarkdown(): string
    {
        try {
            $settings = Setting::pluck('setting_value', 'setting_key')->all();
        } catch (\Throwable $e) {
            $settings = [];
        }

        $phone = $settings['site_phone'] ?? '+971 50 245 6056';
        $waPhone = $settings['site_whatsapp'] ?? '971502456056';
        $email = $settings['site_email'] ?? 'info@dunesdiscoverytourism.com';
        $address = $settings['site_address'] ?? 'Office 402, Al Fajer Business Centre, Al Garhoud, Dubai, UAE';
        $license = $settings['site_det_license'] ?? '1430583';

        try {
            $safariTours = Tour::where('status', 'active')
                ->where(function ($q) {
                    $q->where('name', 'like', '%Desert%')
                        ->orWhere('name', 'like', '%Buggy%')
                        ->orWhere('name', 'like', '%Quad%')
                        ->orWhere('name', 'like', '%Safari%');
                })
                ->orderBy('priority', 'asc')
                ->get();

            $cityTours = Tour::where('status', 'active')
                ->where(function ($q) {
                    $q->where('name', 'like', '%City%')
                        ->orWhere('name', 'like', '%Cruise%')
                        ->orWhere('name', 'like', '%Abu Dhabi%');
                })
                ->orderBy('priority', 'asc')
                ->get();

            $blogs = BlogPost::where('status', 'published')
                ->orderBy('published_at', 'desc')
                ->take(8)
                ->get();
        } catch (\Throwable $e) {
            $safariTours = collect();
            $cityTours = collect();
            $blogs = collect();
        }

        $md = "# Dunes Discovery Tourism\n\n";
        $md .= "> Dunes Discovery Tourism LLC is an officially licensed Destination Management Company (DMC) based in Dubai, United Arab Emirates (DET Tourism License #{$license}). Specializing in luxury Dubai Desert Safaris, self-drive 1000cc Can-Am & Polaris Dune Buggy rentals, Quad Biking ATV tours, City Sightseeing Tours, and 5-Star luxury Dhow Cruises with 1,200+ verified 5-star traveler reviews (4.9/5.0).\n\n";

        $md .= "## Desert Safaris & Off-Road Adventures\n\n";
        $md .= '- [Dune Buggy Rental Dubai]('.url('/dune-buggy-rental-dubai')."): Self-drive 1000cc Can-Am Maverick X3 Turbo & Polaris RZR buggy rentals in Lahbab Red Dunes with full rally gear and expert guides.\n";

        if ($safariTours->count() > 0) {
            foreach ($safariTours as $tour) {
                if ($tour->slug === 'dune-buggy-rental-dubai') {
                    continue;
                }
                $desc = $tour->short_desc ?: 'Premium Dubai desert experience featuring 4x4 dune bashing, camel rides, sandboarding, and 5-star BBQ dinner.';
                $md .= "- [{$tour->name}](".url('/'.$tour->slug).'): '.trim(strip_tags($desc))."\n";
            }
        } else {
            $md .= '- [Evening Desert Safari Dubai]('.url('/evening-desert-safari-dubai')."): Premium Red Dunes desert safari with 4x4 dune bashing, camel riding, sandboarding, live entertainment shows, and 5-star BBQ dinner.\n";
            $md .= '- [Morning Desert Safari Dubai]('.url('/morning-desert-safari-dubai')."): Sunrise desert safari featuring red dunes bashing, sandboarding, camel rides, and Arabic coffee.\n";
            $md .= '- [Overnight Desert Safari Dubai]('.url('/overnight-desert-safari-dubai')."): Bedouin camp desert safari with sunset dune bashing, overnight camping in deluxe tents, stargazing, and fresh morning breakfast.\n";
            $md .= '- [Desert Safari with Quad Biking]('.url('/desert-safari-quad-biking-dubai')."): Combined desert safari with 30-60 minutes self-drive 250cc-400cc Quad Bike ATV rental.\n";
        }
        $md .= '- [All Desert & City Tours]('.url('/tours')."): Complete commercial catalog of Dubai desert adventures, city sightseeing tours, and luxury dhow cruise experiences.\n\n";

        if ($cityTours->count() > 0) {
            $md .= "## City Tours & Cruise Experiences\n\n";
            foreach ($cityTours as $tour) {
                $desc = $tour->short_desc ?: 'Guided tour with professional driver-guide, transfers, and landmark photo stops.';
                $md .= "- [{$tour->name}](".url('/'.$tour->slug).'): '.trim(strip_tags($desc))."\n";
            }
            $md .= "\n";
        }

        $md .= "## Doorstep Hotel Pickup Hubs (Hyper-Local Transfers)\n\n";
        foreach (LocationLandingService::getLocations() as $loc) {
            $md .= "- [{$loc['headline']}](".url('/'.$loc['slug'])."): Daily 4x4 hotel pickup from {$loc['district']}. Pickup window: {$loc['pickup_window']}, return: {$loc['return_time']}.\n";
        }
        $md .= "\n";

        if ($blogs->count() > 0) {
            $md .= "## Travel Guides & Editorial Content\n\n";
            $md .= '- [Dubai Travel & Safari Blog]('.url('/blog')."): Insider travel tips, desert safari packing advice, buggy vs quad comparisons, and Dubai excursion planning.\n";
            foreach ($blogs as $blog) {
                $desc = $blog->excerpt ?: Str::limit(strip_tags($blog->content), 120);
                $md .= "- [{$blog->title}](".url('/blog/'.$blog->slug).'): '.trim(strip_tags($desc))."\n";
            }
            $md .= "\n";
        }

        $md .= "## Company Information & Support\n\n";
        $md .= '- [About Dunes Discovery Tourism]('.url('/about')."): Company history, official DET Tourism Licensing (#{$license}), fleet details, and E-E-A-T credentials.\n";
        $md .= '- [Interactive Safari Comparison Engine]('.url('/tours')."): Side-by-side package analyzer comparing vehicle transfers, dune bashing intensity, 5-star live BBQ dining, and live cultural shows.\n";
        $md .= '- [Verified Guest Review & Photo UGC Portal]('.url('/review/guest')."): Public customer feedback engine allowing safari guests to rate captains and share authentic tour photos.\n";
        $md .= '- [Contact & Reservations]('.url('/contact')."): Direct booking inquiries, customer service line ({$phone}), and office location in Dubai ({$address}).\n";
        $md .= '- [WhatsApp Concierge](https://wa.me/'.preg_replace('/[^0-9]/', '', $waPhone)."): 24/7 instant chat support and direct safari bookings.\n";
        $md .= '- [Frequently Asked Questions]('.url('/faq')."): Comprehensive answers regarding pickup times, halal food, safety guidelines, dress code, and age requirements.\n";
        $md .= '- [Terms & Booking Conditions]('.url('/terms-condition')."): Transparent 24-hour free cancellation policy, refund guarantees, and booking policies.\n";
        $md .= '- [Privacy Policy]('.url('/privacy-policy')."): Data protection commitments, cookie policy, and GDPR compliance.\n\n";

        $md .= "## Optional\n\n";
        $md .= '- [Full LLM Knowledge Base]('.url('/llms-full.txt')."): Comprehensive full-text specifications, pricing tiers, FAQs, and entity knowledge graph for AI crawlers.\n";
        $md .= '- [XML Sitemap Index]('.url('/sitemap_index.xml')."): Complete hierarchical URL and image sitemap index for search engines.\n";

        return $md;
    }

    /**
     * Compile comprehensive llms-full.txt Markdown.
     */
    protected function buildFullMarkdown(): string
    {
        try {
            $settings = Setting::pluck('setting_value', 'setting_key')->all();
        } catch (\Throwable $e) {
            $settings = [];
        }

        $phone = $settings['site_phone'] ?? '+971 50 245 6056';
        $waPhone = $settings['site_whatsapp'] ?? '971502456056';
        $email = $settings['site_email'] ?? 'info@dunesdiscoverytourism.com';
        $address = $settings['site_address'] ?? 'Office 402, Al Fajer Business Centre, Al Garhoud, Dubai, UAE';
        $license = $settings['site_det_license'] ?? '1430583';

        try {
            $tours = Tour::where('status', 'active')
                ->with(['tiers', 'itineraries', 'category'])
                ->orderBy('priority', 'asc')
                ->get();

            $faqs = Faq::where('status', 'active')
                ->orderBy('priority', 'asc')
                ->get();
        } catch (\Throwable $e) {
            $tours = collect();
            $faqs = collect();
        }

        $md = "# Dunes Discovery Tourism - Comprehensive LLM Knowledge Base & Entity Specification\n";
        $md .= "> Authoritative, full-text reference guide for Artificial Intelligence (AI) search engines, Generative Engine Optimization (GEO), LLMs (ChatGPT, Claude, Gemini, Perplexity), and Travel Knowledge Graphs.\n\n";
        $md .= "---\n\n";

        $md .= "## SECTION 1: ENTITY IDENTIFICATION & E-E-A-T CREDENTIALS\n";
        $md .= "- **Legal Entity Name**: Dunes Discovery Tourism LLC\n";
        $md .= "- **Brand Name**: Dunes Discovery Tourism\n";
        $md .= '- **Website URL**: '.url('/')."\n";
        $md .= "- **Operating Location**: Dubai, United Arab Emirates (UAE)\n";
        $md .= "- **Physical Office Address**: {$address}\n";
        $md .= "- **Licensing Authority**: Dubai Department of Economy and Tourism (DET)\n";
        $md .= "- **Tourism License Number**: {$license}\n";
        $md .= "- **Commercial Registry / D-U-N-S**: Registered UAE LLC Operator\n";
        $md .= "- **Business Category**: Destination Management Company (DMC), Licensed Tour Operator, Desert Motorsports Fleet Provider\n";
        $md .= "- **Verified Traveler Reviews**: 1,200+ Verified 5-Star Reviews across Google Maps and TripAdvisor. Aggregate Rating: 4.9 / 5.0.\n";
        $md .= "- **Direct Customer Contacts**:\n";
        $md .= '  - WhatsApp Concierge: +'.preg_replace('/[^0-9]/', '', $waPhone).' (https://wa.me/'.preg_replace('/[^0-9]/', '', $waPhone).")\n";
        $md .= "  - Phone: {$phone}\n";
        $md .= "  - Reservations Email: {$email}\n";
        $md .= "  - Operating Hours: 24/7 Booking Support & Live Dispatch\n";
        $md .= "  - Service Coverage Area: Dubai, Sharjah, Ajman, and Abu Dhabi (UAE)\n\n";
        $md .= "---\n\n";

        $md .= "## SECTION 2: COMMERCIAL TOURS CATALOG & FULL SPECIFICATIONS\n\n";

        if ($tours->count() > 0) {
            foreach ($tours as $idx => $tour) {
                $num = $idx + 1;
                $catName = $tour->category ? $tour->category->name : 'Desert Safari';
                $md .= "### {$num}. {$tour->name} (`/".$tour->slug."`)\n";
                $md .= "- **Category**: {$catName}\n";
                $md .= "- **Primary Location**: Lahbab High Red Dunes Desert Reserve, Dubai, UAE\n";
                $md .= '- **Duration**: '.($tour->duration ?: '6 - 7 Hours')."\n";
                if ($tour->pickup_time) {
                    $md .= "- **Pickup Schedule**: {$tour->pickup_time}\n";
                }
                if ($tour->min_age) {
                    $md .= "- **Minimum Age**: {$tour->min_age} Years\n";
                }
                if ($tour->group_size) {
                    $md .= "- **Group Size**: {$tour->group_size}\n";
                }
                if ($tour->languages) {
                    $md .= "- **Guide Languages**: {$tour->languages}\n";
                }

                if ($tour->tiers->count() > 0) {
                    $md .= "- **Live Pricing Packages**:\n";
                    foreach ($tour->tiers as $tier) {
                        $p = $tier->pivot->price ?? 0;
                        $oldP = $tier->pivot->old_price ?? 0;
                        $oldTxt = $oldP > 0 ? " (regularly AED {$oldP})" : '';
                        $md .= "  - **{$tier->name}**: AED {$p}{$oldTxt}\n";
                    }
                }

                $desc = $tour->full_desc ?: $tour->short_desc;
                if ($desc) {
                    $md .= '- **Overview**: '.trim(strip_tags($desc))."\n";
                }

                if ($tour->itineraries->count() > 0) {
                    $md .= "- **Tour Itinerary & Program**:\n";
                    foreach ($tour->itineraries as $step) {
                        $timeStr = $step->time_slot ? " [{$step->time_slot}]" : '';
                        $md .= "  - **{$step->title}**{$timeStr}: ".trim(strip_tags($step->description))."\n";
                    }
                }

                $md .= "- **Standard Inclusions**: 4x4 air-conditioned hotel pickup & drop-off, dune bashing, sandboarding, camel riding, Arabic coffee & dates, 100% Halal BBQ dinner (Veg, Non-Veg, Jain options), live entertainment shows (Tanoura, Fire show, Belly dance).\n";
                $md .= "- **Cancellation Policy**: 100% Full Refund if cancelled up to 24 hours before tour departure.\n";
                $md .= '- **Booking URL**: '.url('/'.$tour->slug)."\n\n";
            }
        } else {
            $md .= "### 1. Dune Buggy Rental Dubai (`/dune-buggy-rental-dubai`)\n";
            $md .= "- **Category**: Off-Road Motorsports / Self-Drive Buggy Rental\n";
            $md .= "- **Location**: Lahbab Red Dunes Desert Reserve, Dubai\n";
            $md .= "- **Vehicle Options**: 1000cc Can-Am Maverick X3 Turbo & Polaris RZR XP 1000\n";
            $md .= "- **Duration**: 3 Hours Total (1-2 Hours Dune Driving)\n";
            $md .= "- **Pricing Tiers**: 1-Seater Solo (AED 599), 2-Seater Duo (AED 899), 4-Seater Family (AED 1,299)\n";
            $md .= "- **Age**: Driver 16+ (No license required). Passenger 5+.\n";
            $md .= '- **URL**: '.url('/dune-buggy-rental-dubai')."\n\n";

            $md .= "### 2. Evening Desert Safari Dubai (`/evening-desert-safari-dubai`)\n";
            $md .= "- **Category**: Premium Desert Safari / Cultural Camp Experience\n";
            $md .= "- **Duration**: 6 Hours (03:00 PM to 09:00 PM)\n";
            $md .= "- **Pricing Tiers**: Standard Bus Pickup (AED 99), 4x4 Land Cruiser Hotel Pickup (AED 149), VIP Table Service (AED 250)\n";
            $md .= "- **Inclusions**: High-dune bashing, sandboarding, camel ride, henna tattoo, 5-star BBQ dinner, 3 live shows.\n";
            $md .= '- **URL**: '.url('/evening-desert-safari-dubai')."\n\n";
        }

        $md .= "---\n\n";

        $md .= "## SECTION 3: DOORSTEP HOTEL PICKUP HUBS & DISTRICT LOGISTICS\n\n";
        foreach (LocationLandingService::getLocations() as $loc) {
            $md .= "### Hub: {$loc['name']} (`/{$loc['slug']}`)\n";
            $md .= "- **Coverage**: {$loc['district']}\n";
            $md .= "- **Pickup Window**: {$loc['pickup_window']}\n";
            $md .= "- **Drop-off Window**: {$loc['return_time']}\n";
            $md .= "- **Transit Time**: {$loc['transit_time']}\n";
            $md .= '- **Key Landmarks**: '.implode(', ', $loc['landmarks'])."\n";
            $md .= "- **Coordinates**: Latitude {$loc['geo']['lat']}, Longitude {$loc['geo']['lng']}\n";
            $md .= '- **Landing Page**: '.url('/'.$loc['slug'])."\n\n";
        }

        $md .= "---\n\n";

        $md .= "## SECTION 4: OFF-ROAD MOTORSPORTS & FLEET SPECIFICATIONS\n\n";
        $md .= "### 1. 1000cc Can-Am Maverick X3 Turbo Buggies\n";
        $md .= "- **Engine**: 1000cc Rotax ACE Turbocharged Triple-Cylinder\n";
        $md .= "- **Horsepower**: 172 HP to 195 HP with high-performance CVT transmission\n";
        $md .= "- **Suspension**: FOX 2.5 PODIUM Piggyback with QS3 compression adjustment\n";
        $md .= "- **Safety Gear**: Custom steel roll cage, 4-point racing harness, full-face Snell/DOT certified helmet, anti-glare goggles, racing gloves.\n";
        $md .= "- **Terrain**: Open desert and 300ft high red dunes in Lahbab.\n";
        $md .= "- **Requirement**: No driving license required. Minimum driving age: 16 years. Expert lead guide and sweep recovery vehicle provided on every ride.\n\n";

        $md .= "### 2. Polaris RZR XP 1000 Buggies\n";
        $md .= "- **Engine**: 999cc ProStar 1000 Gen 2 Dual Overhead Cam\n";
        $md .= "- **Configurations**: Available in 1-Seater Solo, 2-Seater Duo, and 4-Seater Family models.\n";
        $md .= "- **Inclusions**: Dedicated rally guide, fuel, safety briefing, sandboarding, cold bottled water and refreshments.\n\n";

        $md .= "### 3. Desert Safari 4x4 Transfer Fleet\n";
        $md .= "- **Fleet Models**: Current-generation Toyota Land Cruiser 300 Series and Toyota Prado 4WD.\n";
        $md .= "- **Safety & Certification**: RTA-approved desert safari roll bars, GPS tracking, first aid kit, fully licensed safari captains trained in desert emergency handling and high-angle dune recovery.\n\n";

        $md .= "---\n\n";

        $md .= "## SECTION 5: FREQUENTLY ASKED QUESTIONS & AI DIRECT-ANSWER DIRECTORY\n\n";

        if ($faqs->count() > 0) {
            foreach ($faqs as $f) {
                $md .= "### Q: {$f->question}\n";
                $md .= '**Answer**: '.trim(strip_tags($f->answer))."\n\n";
            }
        } else {
            $md .= "### Q: Do I need a driving license for Dune Buggy or Quad Biking in Dubai?\n";
            $md .= "**Answer**: No driving license is required. Buggy driving takes place inside dedicated desert reserves and high dune tracks under the supervision of certified rally marshals. Drivers must be at least 16 years old.\n\n";
            $md .= "### Q: Is Dune Bashing suitable for pregnant women or infants?\n";
            $md .= "**Answer**: No. High-dune bashing is not recommended for pregnant women, infants under 3 years old, or guests with severe back problems. For these guests, Dunes Discovery Tourism provides direct camp transfers on smooth roads, bypassing dune bashing while still enjoying camel rides, falconry, shows, and dinner.\n\n";
            $md .= "### Q: Is the food served at the desert camp 100% Halal?\n";
            $md .= "**Answer**: Yes. All meats and dishes served in the 5-star desert camp buffet are 100% certified Halal, with extensive vegetarian, vegan, and Jain options clearly labeled.\n\n";
        }

        $md .= "---\n\n";
        $md .= "## SECTION 6: SAFARI PACKAGE COMPARISON & VERIFIED REVIEW SYSTEM\n\n";
        $md .= "### Safari Tier Comparison Matrix\n";
        $md .= "- **Standard Package**: Doorstep 4x4 pickup, 40-min Lahbab red dune bashing, standard camp table seating, live 5-star BBQ buffet, 3 cultural shows (Tanoura, Fire, Belly Dance), camel ride, sandboarding, henna painting.\n";
        $md .= "- **VIP Package**: Doorstep luxury 4x4 Land Cruiser transfer, 45-min extreme high dune bashing, reserved VIP raised table with dedicated waiter service, priority buffet food delivery, premium front-row show viewing, shisha at table.\n";
        $md .= "- **Private Vehicle Package**: Exclusive private 4x4 Land Cruiser for up to 6 or 7 passengers, customizable departure schedule, flexible dune bashing intensity, private camp table option, complimentary VIP table service addon.\n\n";
        $md .= "### Verified Guest Review & Photo Submission\n";
        $md .= '- **Public Portal**: Accessible at '.url('/review/guest').".\n";
        $md .= "- **Photo Submission**: Guests can submit up to 4 tour snapshots (JPG/PNG/WEBP/AVIF up to 5MB each) to showcase genuine desert safari experiences.\n";
        $md .= "- **Verification & Moderation**: Integrated with booking references and DET License #1430583 compliance. 4+ star reviews automatically qualify for Google Reviews syndication.\n\n";

        return $md;
    }
}
