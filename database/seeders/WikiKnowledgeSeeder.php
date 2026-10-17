<?php

namespace Database\Seeders;

use App\Models\Tour;
use App\Models\WikiArticle;
use App\Models\WikiGlossaryTerm;
use App\Models\WikiPillar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WikiKnowledgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed 6 Pillars
        $pillars = [
            [
                'slug' => 'geography',
                'name' => [
                    'en' => 'Desert Geography & Terrains',
                    'ar' => 'جغرافية الصحراء والتضاريس',
                    'ru' => 'География пустыни и рельеф',
                    'es' => 'Geografía del Desierto y Terrenos',
                    'it' => 'Geografia del Deserto e Terreni',
                ],
                'headline' => [
                    'en' => 'Explore the geological formations, high crimson dunes, and protected conservation reserves of the UAE.',
                    'ar' => 'استكشف التكوينات الجيولوجية والكثبان الرملية الحمراء الشاهقة والمحميات الطبيعية المحمية في دولة الإمارات.',
                ],
                'description' => [
                    'en' => 'From the iron-rich red sands of Lahbab to the historic marine fossils at Fossil Rock and the protected biodiversity of the Dubai Desert Conservation Reserve, discover the fascinating landscapes of Arabia.',
                    'ar' => 'من الرمال الحمراء الغنية بالحديد في لهباب إلى الحفريات البحرية التاريخية في صخرة الأحفورة والتنوع البيولوجي المحمي في محمية دبي الصحراوية، اكتشف المناظر الطبيعية الساحرة لشبه الجزيرة العربية.',
                ],
                'icon' => 'bi-geo-alt',
                'hero_image' => 'images/desert-safari-poster.avif',
                'priority' => 1,
            ],
            [
                'slug' => 'adventures',
                'name' => [
                    'en' => 'Desert Adventures & Vehicles',
                    'ar' => 'مغامرات الصحراء والمركبات',
                    'ru' => 'Пустынные приключения и техника',
                    'es' => 'Aventuras en el Desierto y Vehículos',
                    'it' => 'Avventure nel Deserto e Veicoli',
                ],
                'headline' => [
                    'en' => 'The technical mechanics, driving physics, and engineering behind desert motorsports and sandboarding.',
                    'ar' => 'الميكانيكا الفنية وفيزياء القيادة والهندسة الكامنة وراء رياضات المحركات الصحراوية والتزلج على الرمال.',
                ],
                'description' => [
                    'en' => 'Master the differences between high-speed Can-Am Maverick buggies, 400cc quad bikes, and 4x4 dune bashing dynamics with expert tips from licensed safari marshals.',
                    'ar' => 'تعرف على الفروق الجوهرية بين عربات كان-آم مافريك عالية السرعة ودراجات الدفع الرباعي 400 سي سي وديناميكيات صعود الكثبان بالدفع الرباعي بتوجيهات مرخصة.',
                ],
                'icon' => 'bi-speedometer2',
                'hero_image' => 'images/evening-desert-safari-dubai-hero.avif',
                'priority' => 2,
            ],
            [
                'slug' => 'safety',
                'name' => [
                    'en' => 'Health, Safety & Protocols',
                    'ar' => 'الصحة والسلامة والبروتوكولات',
                    'ru' => 'Здоровье, безопасность и правила',
                    'es' => 'Salud, Seguridad y Protocolos',
                    'it' => 'Salute, Sicurezza e Protocolli',
                ],
                'headline' => [
                    'en' => 'Comprehensive safety standards, medical advisories, and eligibility guidelines for every traveler.',
                    'ar' => 'معايير سلامة شاملة وإرشادات طبية وضوابط أهلية تضمن راحة وأمان كل مسافر في الصحراء.',
                ],
                'description' => [
                    'en' => 'Essential reading on pregnancy considerations, motion sickness mitigation, child safety seat rules, heat hydration, and DET licensing regulations.',
                    'ar' => 'قراءة أساسية حول إرشادات الحمل، والوقاية من دوار الحركة، وقواعد مقاعد الأطفال، وترطيب الجسم في درجات الحرارة، ولوائح ترخيص دائرة الاقتصاد والسياحة.',
                ],
                'icon' => 'bi-shield-check',
                'hero_image' => 'images/dubai-desert-safari-dune-bashing-dune-discovery-tourism.avif',
                'priority' => 3,
            ],
            [
                'slug' => 'culture',
                'name' => [
                    'en' => 'Bedouin Heritage & Camp Life',
                    'ar' => 'التراث البدوي وحياة المخيم',
                    'ru' => 'Бедуинское наследие и лагерь',
                    'es' => 'Herencia Beduina y Vida de Campamento',
                    'it' => 'Eredità Beduina e Vita da Campo',
                ],
                'headline' => [
                    'en' => 'Centuries of Emirati hospitality, folkloric arts, desert gastronomy, and cultural traditions.',
                    'ar' => 'قرون من كرم الضيافة الإماراتية والفنون الفولكلورية وفنون الطهي الصحراوي والتقاليد الأصيلة.',
                ],
                'description' => [
                    'en' => 'Unpack the ceremonial significance of Arabian Gahwa coffee, ancient falconry practices, hypnotic Tanoura whirling, henna art, and traditional desert camp etiquette.',
                    'ar' => 'تعرف على الدلالات الرمزية للقهوة العربية الأصيلة، وتقاليد الصيد بالصقور العريقة، ورقصة التنورة الساحرة، ونقوش الحناء، وآداب الضيافة في المخيم البدوي.',
                ],
                'icon' => 'bi-cup-hot',
                'hero_image' => 'images/dubai-desert-safari-camp-dune-discovery-tourism.avif',
                'priority' => 4,
            ],
            [
                'slug' => 'nature',
                'name' => [
                    'en' => 'Wildlife, Botany & Astronomy',
                    'ar' => 'الحياة البرية والنباتات وعلم الفلك',
                    'ru' => 'Дикая природа, флора и астрономия',
                    'es' => 'Fauna, Botánica y Astronomía',
                    'it' => 'Fauna Selvatica, Botanica e Astronomia',
                ],
                'headline' => [
                    'en' => 'Discover the resilient flora, protected fauna, and breathtaking dark skies of the Arabian desert.',
                    'ar' => 'اكتشف النباتات المقاومة للجفاف، والحيوانات المحمية، وسماء الصحراء المظلمة الساحرة لعشاق النجوم.',
                ],
                'description' => [
                    'en' => 'Learn how the legendary Arabian Oryx was saved from extinction, why the Ghaf tree is the UAE national tree, and where to observe constellations under pitch-black desert skies.',
                    'ar' => 'تعرف على قصة إنقاذ المها العربي من الانقراض، وأهمية شجرة الغاف بوصفها الشجرة الوطنية للإمارات، ومواقع رصد النجوم في سماء الصحراء الصافية.',
                ],
                'icon' => 'bi-stars',
                'hero_image' => 'images/dubai-desert-safar-camel-ride-dune-discovery-tourism.avif',
                'priority' => 5,
            ],
            [
                'slug' => 'planning',
                'name' => [
                    'en' => 'Trip Planning & Seasons',
                    'ar' => 'تخطيط الرحلات والمواسم',
                    'ru' => 'Планирование поездки и сезоны',
                    'es' => 'Planificación de Viajes y Estaciones',
                    'it' => 'Pianificazione del Viaggio e Stagioni',
                ],
                'headline' => [
                    'en' => 'Expert seasonal planning, timing differences, dress codes, and practical insider recommendations.',
                    'ar' => 'تخطيط موسمي احترافي، ومقارنات التوقيت، وإرشادات اللباس، وتوصيات عملية من خبراء السياحة الصحراوية.',
                ],
                'description' => [
                    'en' => 'Compare morning, evening, and overnight safari schedules, understand Ramadan operational modifications, and prepare for temperature drops after sunset.',
                    'ar' => 'قارن بين جداول رحلات السفاري الصباحية والمسائية والمبيت، وتعرف على التعديلات التشغيلية خلال شهر رمضان المبارك، وكيفية الاستعداد لانخفاض درجات الحرارة ليلاً.',
                ],
                'icon' => 'bi-calendar-check',
                'hero_image' => 'images/dubai-desert-safari-bbq-dune-discovery-tourism.avif',
                'priority' => 6,
            ],
        ];

        $savedPillars = [];
        foreach ($pillars as $pData) {
            $savedPillars[$pData['slug']] = WikiPillar::updateOrCreate(
                ['slug' => $pData['slug']],
                $pData
            );
        }

        // 2. Fetch tours for pivot linking
        $eveningTour = Tour::where('slug', 'like', '%evening%')->first();
        $buggyTour = Tour::where('slug', 'like', '%buggy%')->first() ?: Tour::where('slug', 'like', '%quad%')->first();
        $vipTour = Tour::where('slug', 'like', '%vip%')->first() ?: $eveningTour;

        // 3. Seed Cornerstone Articles
        $articles = [
            [
                'pillar_id' => $savedPillars['geography']->id,
                'slug' => 'lahbab-red-dunes-geology-guide',
                'title' => [
                    'en' => 'Why Lahbab Red Dunes Are Crimson: Geology, Sand Elevation & Safari Guide',
                    'ar' => 'سر الرمال الحمراء في صحراء لهباب: الجيولوجيا وارتفاع الكثبان ودليل السفاري',
                ],
                'subtitle' => [
                    'en' => 'Discover the natural science behind Dubai’s famous Big Red dunes and why off-road marshals consider Lahbab the gold standard for desert safaris.',
                    'ar' => 'اكتشف العلوم الطبيعية وراء كثبان دبي الحمراء الشهيرة وسبب اعتبار مرشدي الصحراء لهباب المعيار الذهبي لرحلات السفاري.',
                ],
                'excerpt' => [
                    'en' => 'Located 50km southeast of Downtown Dubai along the Oman border, Lahbab is renowned for its high-crested terracotta-red sand dunes. Here is the complete geological breakdown and visitor guide.',
                    'ar' => 'تقع منطقة لهباب على بعد 50 كم جنوب شرق وسط مدينة دبي بمحاذاة حدود عُمان، وتشتهر بكثبانها الرملية الحمراء الشاهقة. إليك الدليل الجيولوجي والسياحي الشامل.',
                ],
                'content' => [
                    'en' => '## The Geological Origins of Lahbab’s Red Sand

Unlike the pale, beige sands found near the Dubai coast and Jumeirah beaches, the sand in Lahbab features a vivid crimson-terracotta hue. This striking color is not artificial; it is the result of millennia of natural chemical weathering and geological erosion.

### Iron Oxide Coating
Each individual grain of sand in the Lahbab desert contains high concentrations of iron oxide (hematite). Over thousands of years, as wind and solar exposure oxidized trace minerals transported from the nearby Hajar Mountains, an iron-rich microscopic coating formed around each quartz grain. The deeper you travel into the Lahbab interior toward Fossil Rock and the Oman border, the deeper the scarlet and reddish-orange color becomes.

## Sand Dune Elevation: Big Red (Al Hamar)

Lahbab is home to **Al Hamar**, colloquially known to off-roaders and tour captains as **Big Red**. Rising over 300 feet (100 meters) from the desert floor, Big Red is the steepest and highest accessible sand dune in the Emirate of Dubai.

* **Dune Incline:** Slopes in Lahbab regularly reach gradients between 30 and 45 degrees.
* **Wind Shaping:** Prevailing Shamal winds from the northwest create classic *barchan* crescent dunes with sharp, knife-edge slip faces.
* **Sand Density:** The fine grain size makes the sand extraordinarily soft, requiring drivers to deflate 4x4 tires to 12-15 PSI to avoid sinking.

## Lahbab vs. Al Qudra Desert: What Is the Difference?

Many first-time visitors confuse the Al Qudra desert with Lahbab. While Al Qudra is wonderful for cycling around man-made lakes and gentle camping, its dunes are low, flat, and pale yellow. Lahbab, by contrast, is the premier destination for thrilling high-crest dune bashing, sandboarding, and deep-desert stargazing.

## Recommended Tour Experiences in Lahbab
To experience the true majesty of Lahbab red dunes with maximum safety, choose a tour led by Department of Economy and Tourism (DET) licensed safari marshals in high-clearance 4x4 vehicles equipped with full roll-cage protection.',
                ],
                'hero_image' => 'images/desert-safari-poster.avif',
                'reading_time_minutes' => 6,
                'reviewed_by_name' => 'Malik Awan',
                'reviewed_by_title' => 'Senior Safari Marshal & Head of Field Operations (DET #1430583)',
                'last_reviewed_at' => now(),
                'is_featured' => true,
                'meta_title' => [
                    'en' => 'Lahbab Red Dunes Dubai: Sand Geology, Elevation & Safari Guide (2026)',
                    'ar' => 'صحراء لهباب دبي: جيولوجيا الرمال الحمراء وارتفاع الكثبان ودليل الزوار (2026)',
                ],
                'meta_description' => [
                    'en' => 'Learn why Dubai Lahbab desert sand is deep crimson red. Understand iron oxide mineralogy, Big Red dune elevation, and comparison with Al Qudra.',
                    'ar' => 'تعرف على سبب الحمرة الشديدة لرمال صحراء لهباب في دبي، وتكوينات أكسيد الحديد، وارتفاع كثيب البيج ريد والمقارنة مع القدرة.',
                ],
                'tours' => $eveningTour ? [$eveningTour->id] : [],
            ],
            [
                'pillar_id' => $savedPillars['adventures']->id,
                'slug' => 'dune-bashing-physics-safety-mechanics',
                'title' => [
                    'en' => 'The Physics of Dune Bashing: Tire Deflation (12-15 PSI), Roll Dynamics & Marshal Guide',
                    'ar' => 'فيزياء التقطيع في الرمال: تنفيس الإطارات (12-15 رطل) وديناميكيات الأمان',
                ],
                'subtitle' => [
                    'en' => 'An insider look into how 4x4 Land Cruisers navigate 40-degree slip faces without flipping or bogging down.',
                    'ar' => 'نظرة متخصصة من الكواليس حول كيفية قيادة مركبات لاندكروزر على منحدرات الكثبان الرملية بزاوية 40 درجة بأمان تام.',
                ],
                'excerpt' => [
                    'en' => 'Dune bashing is not reckless driving; it is a calibrated mechanical discipline governed by tire pressure reduction, momentum management, and vehicle stability physics.',
                    'ar' => 'التقطيع في الرمال ليس قيادة متهورة، بل هو علم ميكانيكي منضبط يقوم على خفض ضغط الإطارات وإدارة قوة الاندفاع وفيزياء ثبات المركبة.',
                ],
                'content' => [
                    'en' => '## Why Must 4x4 Tires Be Deflated in the Desert?

Before any licensed safari vehicle enters the Lahbab red dunes, the driver immediately pulls over at the desert deflating station. Standard street tire pressure in a Toyota Land Cruiser or Nissan Patrol is roughly **32 to 35 PSI** (pounds per square inch).

On sand, that high pressure acts like a narrow knife, digging deep into the loose quartz grains and instantly trapping the vehicle to its axles.

### The Science of Flotation
By deflating the tires down to **12 to 15 PSI**, two crucial physical changes occur:
1. **Contact Patch Expansion:** The tire footprint elongates by over 250%, spreading the vehicle’s 2.8-ton curb weight across a much broader surface area.
2. **Tread Conformation:** Lower pressure allows the rubber to cup and wrap around the sand grains rather than shearing through them, enabling the tire to float on top of the dune surface.

## Slip Face Navigation: The 90-Degree Rule

The most critical principle taught to DET-certified desert drivers is the **90-degree angle rule** when descending steep slip faces.

* **Never Stop on a Crest:** Stopping with the chassis belly touching the razor-edge crest results in "beaching", leaving wheels spinning helplessly in mid-air.
* **Descend Perpendicular to the Ridge:** Drivers must never turn the steering wheel sideways while sliding down a steep slip face. Gravitational roll torque can cause the downhill wheels to bite into the sand, creating a rollover risk.
* **Controlled Momentum:** By descending perpendicular (straight down) with light engine braking, the vehicle surfs safely to the dune valley.

## Safety Infrastructure on Licensed Safari Vehicles
Every Dunes Discovery Tourism vehicle operating in the dunes carries rigorous safety equipment:
* Internal structural roll cage bars.
* Advanced 3-point passenger safety seatbelts.
* High-volume onboard air compressors for reinflating tires back to 35 PSI before entering the highway.
* Digital GPS transponders and satellite emergency beacons linked directly to Dubai Police and Civil Defense desert rescue divisions.',
                ],
                'hero_image' => 'images/dubai-desert-safari-dune-bashing-dune-discovery-tourism.avif',
                'reading_time_minutes' => 5,
                'reviewed_by_name' => 'Malik Awan',
                'reviewed_by_title' => 'Chief Safari Marshal (DET License #1430583)',
                'last_reviewed_at' => now(),
                'is_featured' => true,
                'meta_title' => [
                    'en' => 'The Physics of Dune Bashing: Tire Deflation & Safety Mechanics Guide',
                    'ar' => 'فيزياء التقطيع في الرمال: تنفيس الإطارات وقواعد الأمان للمارشال',
                ],
                'meta_description' => [
                    'en' => 'Understand the mechanical physics of desert dune bashing in Dubai. Learn why tires deflate to 12-15 PSI, slip face navigation, and safety standards.',
                    'ar' => 'افهم القواعد الميكانيكية والفيزيائية للتقطيع في الرمال بدبي، وأهمية خفض ضغط الإطارات إلى 12 رطل، ومعايير أمان مركبات السفاري.',
                ],
                'tours' => $eveningTour ? [$eveningTour->id] : [],
            ],
            [
                'pillar_id' => $savedPillars['adventures']->id,
                'slug' => 'dune-buggy-vs-quad-bike-guide',
                'title' => [
                    'en' => 'Dune Buggy vs. Quad Bike (ATV): Roll Cages, Power, Safety & Driving Comparison',
                    'ar' => 'مقارنة دباب الصحراء (البجي) ودراجة الكواد (ATV): الأمان والمحرك والقيادة',
                ],
                'subtitle' => [
                    'en' => 'Everything you need to know before choosing between a high-horsepower Can-Am Maverick buggy and an open-chassis ATV quad bike.',
                    'ar' => 'كل ما تحتاج معرفته قبل الاختيار بين عربات كان-آم مافريك الرياضية ودراجات الكواد الرباعية المفتوحة.',
                ],
                'excerpt' => [
                    'en' => 'Wondering whether to rent a dune buggy or a quad bike in Dubai? Compare engine power, roll cage protection, steering wheel vs. handlebars, and passenger capacities.',
                    'ar' => 'هل تحتار بين استئجار عربة باجي أو دراجة كواد في صحراء دبي؟ إليك مقارنة تفصيلية حول قوة المحرك، وأقفاص الحماية، ونظام التوجيه وسعة الركاب.',
                ],
                'content' => [
                    'en' => '## Fundamental Differences: Buggy vs. Quad Bike

While both vehicles offer exhilarating off-road freedom in the Lahbab red dunes, they belong to fundamentally different engineering categories.

| Feature | Dune Buggy (Can-Am / Polaris) | Quad Bike (ATV 250cc–400cc) |
| :--- | :--- | :--- |
| **Chassis** | Fully enclosed FIA-certified tubular roll cage | Open straddle frame (motorcycle-style) |
| **Control** | Automotive steering wheel, foot pedals | Handlebars, thumb throttle, hand brakes |
| **Seating** | Bucket seats with 4-point racing harnesses | Straddle seat (no seatbelts) |
| **Passenger Capacity** | 1, 2, or 4 Passengers | Single rider (or 2 on designated dual ATVs) |
| **Horsepower** | Up to 195 HP (Turbocharged) | 20 to 45 HP (Naturally aspirated) |
| **Recommended For** | Adrenaline seekers, couples, families | Solo riders wanting lightweight agility |

## Safety Analysis: Why Dune Buggies Offer Superior Protection

For travelers concerned about safety, the **dune buggy is substantially safer** than a quad bike for two primary reasons:
1. **Tubular Roll Cage:** In the rare event of a rollover on an uneven dune crest, the reinforced steel roll cage absorbs all impact energy, keeping occupants completely protected inside the cabin.
2. **Four-Point Racing Harnesses:** Buggy drivers and passengers are buckled tightly into molded sport bucket seats. On a quad bike, riders have no restraints, meaning an unexpected bump can throw the rider over the handlebars.

## Driving Ease: Which Is Easier for Beginners?

* **Dune Buggy:** If you can drive an automatic road car, you can effortlessly drive a Can-Am buggy. There are only two pedals (gas and brake) and an automotive steering wheel.
* **Quad Bike:** Riding an ATV requires physical body positioning. When carving a dune slope on a quad, you must actively lean your body weight *into* the hill to counterbalance the vehicle.

## Age and License Requirements in Dubai
Under Dubai Department of Economy and Tourism (DET) and RTA guidelines:
* No conventional road driver’s license is mandatory because the tours operate inside designated off-road desert tracks.
* Drivers for dune buggies and quad bikes must be at least 16 years of age.
* Children aged 6 and older can safely ride as passengers in 2-seater and 4-seater buggies when strapped into 4-point safety harnesses (and as passengers on designated quad tours with adult drivers).
* Dune bashing passengers in 4x4 vehicles must be at least 3 years of age.',
                ],
                'hero_image' => 'images/evening-desert-safari-dubai-hero.avif',
                'reading_time_minutes' => 6,
                'reviewed_by_name' => 'Malik Awan',
                'reviewed_by_title' => 'Chief Safari Marshal (DET License #1430583)',
                'last_reviewed_at' => now(),
                'is_featured' => true,
                'meta_title' => [
                    'en' => 'Dune Buggy vs Quad Bike Dubai: Safety & Mechanics Guide (2026)',
                    'ar' => 'مقارنة دباب الصحراء ودراجة الكواد في دبي: دليل الأمان والميكانيكا',
                ],
                'meta_description' => [
                    'en' => 'Compare Dune Buggy vs Quad Bike in Dubai. Discover horsepower, roll cages, passenger seating, safety for beginners, and age restrictions.',
                    'ar' => 'مقارنة بين دباب الصحراء ودراجات الكواد في دبي. اكتشف قوة المحركات، وأقفاص الحماية، وأمان المبتدئين والسن القانوني.',
                ],
                'tours' => $buggyTour ? [$buggyTour->id] : [],
            ],
            [
                'pillar_id' => $savedPillars['safety']->id,
                'slug' => 'pregnancy-back-issues-desert-safari-advisory',
                'title' => [
                    'en' => 'Is Desert Safari Safe During Pregnancy or with Back Issues? Official Marshal Advisory',
                    'ar' => 'هل رحلة السفاري آمنة للحوامل ومرضى الظهر؟ الدليل الطبي والتوجيهي الرسمي',
                ],
                'subtitle' => [
                    'en' => 'Clear medical guidelines, insurance rules, and gentle desert alternatives for expectant mothers and seniors.',
                    'ar' => 'إرشادات طبية واضحة، وقواعد التأمين والبدائل الهادئة والآمنة للأمهات الحوامل وكبار السن.',
                ],
                'excerpt' => [
                    'en' => 'Expecting a baby or recovering from spine surgery? Learn why intense dune bashing is medically prohibited and how you can still enjoy the sunset, camp dinner, and shows safely.',
                    'ar' => 'هل أنت حامل أو تعانين من مشاكل في الظهر؟ تعرفي على أسباب منع التقطيع العنيف في الرمال طبياً والبدائل الآمنة للاستمتاع بغروب الشمس وعشاء المخيم.',
                ],
                'content' => [
                    'en' => '## The Official Medical Advisory on Dune Bashing

At Dunes Discovery Tourism LLC, passenger welfare is our highest priority. We receive hundreds of inquiries from guests asking: *"I am 4 months pregnant — can I still join the desert safari?"*

The direct answer: **Expectant mothers must strictly NOT participate in dune bashing.**

### Why Dune Bashing Is Prohibited During Pregnancy
Even the most skilled safari marshal cannot eliminate sudden vertical g-forces and rapid drops when traversing dunes. These sharp jolts can cause:
* Premature placental abruption (separation of the placenta from the uterine wall).
* Lumbar strain and uterine contractions triggered by sudden decelerations.
* Inability of standard vehicle seatbelts to properly protect pregnant bellies during severe angles.

## What If You Have Chronic Back or Neck Issues?
If you have a history of lumbar disc herniation, spinal fusion surgery, or chronic cervical nerve compression, high-crest dune bashing can compress the vertebrae. We strongly recommend opting for our **Gentle Desert Safari** option.

## The Gentle Safari Alternative: How to Still Enjoy the Experience

You do not need to miss out on the magic of the desert! Expectant mothers, seniors, and travelers with physical limitations can book the **Direct Camp Transfer**:
1. **Paved Road Transit:** Our driver picks you up from your hotel in a comfortable luxury SUV and drives you along paved highways straight to the desert camp gates.
2. **Smooth Desert Approach:** The vehicle drives at a gentle 10 km/h over flat sand without climbing or dropping down dunes.
3. **Enjoy All Sunset & Camp Amenities:** You relax in the VIP Majlis, watch the golden sunset, enjoy the Arabic coffee ceremony, feast on the 5-star BBQ buffet, and watch the Tanoura and Fire shows in complete comfort.
4. **Gentle Camel Walk:** If approved by your physician, enjoy a calm, stationary camel photo or slow, flat-ground walk.',
                ],
                'hero_image' => 'images/dubai-desert-safari-camp-dune-discovery-tourism.avif',
                'reading_time_minutes' => 4,
                'reviewed_by_name' => 'Malik Awan',
                'reviewed_by_title' => 'Chief Safari Marshal (DET License #1430583)',
                'last_reviewed_at' => now(),
                'is_featured' => true,
                'meta_title' => [
                    'en' => 'Desert Safari When Pregnant or with Back Pain: Safety Guide & Alternatives',
                    'ar' => 'سفاري دبي للحوامل ومرضى آلام الظهر: دليل الأمان والبدائل الهادئة',
                ],
                'meta_description' => [
                    'en' => 'Official medical advisory on Dubai desert safaris during pregnancy or with spinal issues. Learn about gentle direct camp transfers and safe alternatives.',
                    'ar' => 'التوجيهات الطبية الرسمية حول رحلات السفاري في دبي خلال فترة الحمل أو لمرضى العمود الفقري، والبدائل الهادئة المتاحة.',
                ],
                'tours' => $vipTour ? [$vipTour->id] : [],
            ],
            [
                'pillar_id' => $savedPillars['culture']->id,
                'slug' => 'arabic-gahwa-coffee-dates-ceremony',
                'title' => [
                    'en' => 'The Emirati Gahwa & Dates Ceremony: History, Spices, Etiquette & Bedouin Symbolism',
                    'ar' => 'مراسم القهوة العربية والتمور الإماراتية: التاريخ والبهارات وآداب الضيافة البدوية',
                ],
                'subtitle' => [
                    'en' => 'Why UNESCO recognized Arabic Gahwa as intangible cultural heritage and how to observe traditional etiquette in camp.',
                    'ar' => 'لماذا أدرجت اليونسكو القهوة العربية ضمن التراث الثقافي غير المادي، وكيف تراعي آداب الضيافة في المخيم.',
                ],
                'excerpt' => [
                    'en' => 'From the distinctive curved Dallah pot to the delicate Cawa cup, understand the ancient rules of Emirati hospitality that greet every guest at our desert camp.',
                    'ar' => 'من دلة القهوة التراثية إلى فناجين القهوة الرقيقة، تعرف على قواعد كرم الضيافة الإماراتية الأصيلة التي تستقبلك في مخيمنا الصحراوي.',
                ],
                'content' => [
                    'en' => '## UNESCO Recognition of Arabic Gahwa

In 2015, UNESCO officially inscribed **Arabic Coffee (Gahwa)** on the Representative List of the Intangible Cultural Heritage of Humanity. In Emirati and Bedouin society, offering Gahwa is not simply serving a beverage; it is an ancient diplomatic ceremony that conveys respect, peace, and unconditional hospitality to travelers.

## Brewing the Perfect Emirati Gahwa

Unlike dark, bitter Western espresso, authentic Emirati Gahwa has a pale golden-greenish tint and an aromatic, spiced bouquet.

* **Green Coffee Beans:** Made from lightly roasted Arabica beans ground coarsely.
* **Cardamom Infusion:** Freshly crushed green cardamom pods provide the signature floral warmth.
* **Saffron & Cloves:** Premium threads of saffron add a golden hue, while cloves lend a subtle spiciness.
* **Zero Sugar:** Gahwa is traditionally prepared without any added sugar. The natural bitterness is meant to be balanced by the rich, honeyed sweetness of fresh Emirati dates (*Rutab* or *Khalas*).

## Cultural Etiquette: How to Receive and Drink Gahwa

When visiting our traditional desert camp, keep these timeless Bedouin customs in mind:
1. **Always Use Your Right Hand:** Receive the small cup (*Finjan*) and drink exclusively using your right hand. In Arab culture, using the left hand is considered impolite.
2. **Never Fill the Cup to the Brim:** The host will only fill the finjan one-third to one-half full. Filling a cup to the brim implies that the host wants you to finish quickly and leave. A partially filled cup ensures the coffee does not burn your fingers and invites you to stay for refills.
3. **The Cup-Shaking Gesture (*Hazz Al-Finjan*):** When you have enjoyed enough coffee (traditionally two or three cups), gently shake your wrist side to side while holding the cup before handing it back to the host. This communicates that you are satisfied.',
                ],
                'hero_image' => 'images/dubai-desert-safari-bbq-dune-discovery-tourism.avif',
                'reading_time_minutes' => 5,
                'reviewed_by_name' => 'Malik Awan',
                'reviewed_by_title' => 'Chief Safari Marshal (DET License #1430583)',
                'last_reviewed_at' => now(),
                'is_featured' => true,
                'meta_title' => [
                    'en' => 'Emirati Gahwa & Dates Ceremony: History & Cultural Etiquette',
                    'ar' => 'مراسم القهوة العربية والتمور الإماراتية: التاريخ وآداب الضيافة',
                ],
                'meta_description' => [
                    'en' => 'Explore the UNESCO-recognized Emirati Gahwa coffee tradition. Learn brewing spices (cardamom, saffron), serving etiquette, and finjan cup shaking.',
                    'ar' => 'اكتشف تقاليد القهوة العربية الإماراتية المدرجة في اليونسكو، ومكوناتها من الهيل والزعفران، وآداب تقديم وتناول الفنجان.',
                ],
                'tours' => $eveningTour ? [$eveningTour->id] : [],
            ],
            [
                'pillar_id' => $savedPillars['nature']->id,
                'slug' => 'arabian-oryx-desert-conservation-guide',
                'title' => [
                    'en' => 'The Arabian Oryx in Dubai: How the "Desert Unicorn" Was Saved from Extinction',
                    'ar' => 'المها العربي في دبي: قصة إنقاذ "وحيد القرن الصحراوي" من الانقراض',
                ],
                'subtitle' => [
                    'en' => 'The inspiring conservation history of the UAE’s national animal and where to spot them in their natural desert habitat.',
                    'ar' => 'تاريخ الحماية الملهم للحيوان الوطني لدولة الإمارات وأفضل المواقع لمشاهدته في بيئته الصحراوية الطبيعية.',
                ],
                'excerpt' => [
                    'en' => 'Declared extinct in the wild in 1972, the magnificent Arabian Oryx underwent one of the greatest wildlife rehabilitation miracles in human history under the vision of UAE Founding Father Sheikh Zayed.',
                    'ar' => 'بعد إعلانه منقرضاً في البرية عام 1972، خضع المها العربي لواحد من أعظم برامج إعادة التوطين في العالم بفضل رؤية الشيخ زايد طيب الله ثراه.',
                ],
                'content' => [
                    'en' => '## The Legend of the Desert Unicorn

With its luminous white coat and long, parallel, spear-like horns that can reach up to 75 cm in length, the **Arabian Oryx (*Oryx leucoryx*)** is widely believed to be the biological inspiration for the mythical unicorn. When viewed in profile across the shimmering desert heat haze, the animal appears to have a single magnificent horn.

## Extinction and Resurrection: Operation Oryx

By the early 1970s, modern motorized hunting rifles had wiped out the last wild Arabian Oryx on the Arabian Peninsula. The species was officially declared **Extinct in the Wild (EW)**.

Under the visionary leadership of the UAE Founding Father, **Sheikh Zayed bin Sultan Al Nahyan**, captive breeding programs were initiated with specimens known as the "World Herd". Through decades of scientific breeding and habitat protection:
* In 2011, the IUCN downlisted the Arabian Oryx from Endangered to **Vulnerable** — marking the first time in history a species once extinct in the wild achieved such a recovery.
* Today, thousands of Oryx roam protected habitats, including the **Dubai Desert Conservation Reserve (DDCR)**.

## Incredible Biological Adaptations to Desert Life
How does the Arabian Oryx survive in summer temperatures exceeding 48°C (118°F)?
1. **White Solar Reflector Coat:** Its bright white fur reflects intense solar radiation during the day. In winter, its legs and body turn slightly darker to absorb ambient heat.
2. **Moisture Condensation Breathing:** Oryx can survive for months without drinking standing water, obtaining hydration solely from the morning dew on desert shrubs and succulent roots.
3. **Specialized Brain Cooling (Carotid Rete):** A network of microscopic blood vessels beneath the brain cools arterial blood through evaporative nasal breathing before it reaches the brain.',
                ],
                'hero_image' => 'images/dubai-desert-safar-camel-ride-dune-discovery-tourism.avif',
                'reading_time_minutes' => 5,
                'reviewed_by_name' => 'Malik Awan',
                'reviewed_by_title' => 'Chief Safari Marshal (DET License #1430583)',
                'last_reviewed_at' => now(),
                'is_featured' => true,
                'meta_title' => [
                    'en' => 'The Arabian Oryx in Dubai: Conservation, Facts & Safari Spotting',
                    'ar' => 'المها العربي في دبي: قصة الإنقاذ والحقائق ومواقع الرصد في السفاري',
                ],
                'meta_description' => [
                    'en' => 'Discover the inspiring comeback of the Arabian Oryx in Dubai. Learn about Operation Oryx, desert cooling biology, and sightings in protected reserves.',
                    'ar' => 'اكتشف قصة عودة المها العربي في دبي، وعمليات إعادة التوطين وتكيفاته الفسيولوجية المدهشة مع حرارة الصحراء.',
                ],
                'tours' => $eveningTour ? [$eveningTour->id] : [],
            ],
            [
                'pillar_id' => $savedPillars['planning']->id,
                'slug' => 'dubai-desert-safari-seasons-weather-packing-guide',
                'title' => [
                    'en' => 'Dubai Desert Safari Seasons: Temperature Guides, Winter Evenings vs. Summer Sunsets',
                    'ar' => 'مواسم سفاري دبي الصحراوي: دليل درجات الحرارة واللباس بين الشتاء والصيف',
                ],
                'subtitle' => [
                    'en' => 'How desert temperatures shift throughout the year, what to pack, and how to select between morning and evening departures.',
                    'ar' => 'كيف تتقلب درجات حرارة الصحراء على مدار العام، وماذا ترتدي، وكيف تختار بين رحلات الصباح والمساء.',
                ],
                'excerpt' => [
                    'en' => 'Did you know winter desert evenings in Dubai can drop below 12°C (54°F)? Here is our marshal-tested seasonal guide for dressing, packing, and timing your safari.',
                    'ar' => 'هل تعلم أن ليالي الصحراء الشتوية في دبي قد تنخفض إلى ما دون 12 درجة مئوية؟ إليك دليل المارشال المعتمد لاختيار التوقيت والملابس المناسبة.',
                ],
                'content' => [
                    'en' => '## Understanding Desert Temperature Dynamics

The most common misconception among international travelers is assuming Dubai’s desert is scorching hot at all times. Because dry sand has a remarkably low specific heat capacity, the desert loses thermal energy rapidly as soon as the sun dips below the horizon.

## Season-by-Season Breakdown

### 1. Peak Winter Season (November through March)
* **Daytime Temps:** 24°C to 28°C (75°F to 82°F) — comfortable, golden sunshine.
* **Nighttime Temps:** 12°C to 16°C (54°F to 60°F) — chilly, brisk breezes across the open dunes.
* **What to Wear:** Breathable layers during the day; bring a denim jacket, fleece, or shawl for the open-air dinner and stargazing.

### 2. Spring & Autumn Shoulder Seasons (April, May, October)
* **Daytime Temps:** 32°C to 38°C (90°F to 100°F).
* **Nighttime Temps:** 22°C to 26°C (72°F to 79°F).
* **What to Wear:** Light linen clothing, sunglasses, and open sandals or breathable sneakers.

### 3. Summer Season (June through September)
* **Daytime Temps:** 40°C to 45°C (104°F to 113°F).
* **Nighttime Temps:** 28°C to 32°C (82°F to 90°F).
* **Tour Recommendations:** In summer, opt for our air-conditioned luxury 4x4 pickups departing later in the afternoon, or choose the Morning Sunrise Safari (5:30 AM to 9:00 AM) when the desert is cool and tranquil.

## Footwear Advice: Shoes vs. Sandals
* **Sandals / Flip-flops:** Excellent for walking in the sand; easily removed if sand gets between your toes.
* **Closed-toe Sneakers:** Recommended if you plan to drive a Can-Am dune buggy or quad bike to protect your feet from hot engine covers.',
                ],
                'hero_image' => 'images/dubai-desert-safari-tour-dune-discovery-tourism.avif',
                'reading_time_minutes' => 5,
                'reviewed_by_name' => 'Malik Awan',
                'reviewed_by_title' => 'Chief Safari Marshal (DET License #1430583)',
                'last_reviewed_at' => now(),
                'is_featured' => true,
                'meta_title' => [
                    'en' => 'Dubai Desert Safari Seasons & Packing Guide: Winter vs Summer',
                    'ar' => 'مواسم سفاري دبي الصحراوي ودليل اللباس: الشتاء مقابل الصيف',
                ],
                'meta_description' => [
                    'en' => 'Prepare for Dubai desert weather. Month-by-month temperature guides, packing lists, winter evening chill advice, and morning vs evening comparisons.',
                    'ar' => 'استعد لطقس صحراء دبي بدليل درجات الحرارة شهراً بشهر، وقائمة الملابس الموصى بها، والبرودة الليلية في الشتاء.',
                ],
                'tours' => $eveningTour ? [$eveningTour->id] : [],
            ],
        ];

        foreach ($articles as $aData) {
            $toursToSync = $aData['tours'] ?? [];
            unset($aData['tours']);

            $article = WikiArticle::updateOrCreate(
                ['slug' => $aData['slug']],
                $aData
            );

            if (! empty($toursToSync)) {
                $article->tours()->sync($toursToSync);
            }
        }

        // 4. Seed Glossary Terms
        $glossary = [
            [
                'term_key' => 'lahbab-red-dunes',
                'term' => [
                    'en' => 'Lahbab (Red Dunes)',
                    'ar' => 'لهباب (الرمال الحمراء)',
                    'ru' => 'Лахбаб (Красные дюны)',
                    'es' => 'Lahbab (Dunas Rojas)',
                    'it' => 'Lahbab (Dune Rosse)',
                ],
                'definition' => [
                    'en' => 'A premier desert region 50km southeast of Dubai famous for high-elevation crimson sand dunes rich in iron oxide minerals.',
                    'ar' => 'منطقة صحراوية شهيرة تقع على بعد 50 كم جنوب شرق دبي تشتهر بكثبانها الرملية الحمراء الشاهقة الغنية بمعادن أكسيد الحديد.',
                ],
                'article_slug' => 'lahbab-red-dunes-geology-guide',
            ],
            [
                'term_key' => 'dune-bashing',
                'term' => [
                    'en' => 'Dune Bashing',
                    'ar' => 'التقطيع في الرمال',
                    'ru' => 'Дюн-башинг',
                    'es' => 'Dune Bashing (Paseo en Dunas)',
                    'it' => 'Dune Bashing (Corsa sulle Dune)',
                ],
                'definition' => [
                    'en' => 'An exhilarating off-road driving discipline where certified marshals navigate steep sand dunes in 4x4 vehicles with deflated tires (12-15 PSI).',
                    'ar' => 'أسلوب قيادة صحراوي مشوق يقوده مارشال مرخص لتسلق الكثبان الرملية والانحدار منها بمركبات الدفع الرباعي بعد تنفيس الإطارات.',
                ],
                'article_slug' => 'dune-bashing-physics-safety-mechanics',
            ],
            [
                'term_key' => 'gahwa',
                'term' => [
                    'en' => 'Arabic Gahwa',
                    'ar' => 'القهوة العربية (القهوة)',
                    'ru' => 'Арабский кофе (Гахва)',
                    'es' => 'Gahwa Árabe',
                    'it' => 'Gahwa Araba',
                ],
                'definition' => [
                    'en' => 'UNESCO-recognized traditional Arabic coffee brewed with lightly roasted beans, cardamom, and saffron, served in small cawa cups as a symbol of hospitality.',
                    'ar' => 'قهوة عربية أصيلة معترف بها لدى اليونسكو، تحضر من بن محمص خفيف مع الهيل والزعفران وتقدم كرمز لكرم الضيافة.',
                ],
                'article_slug' => 'arabic-gahwa-coffee-dates-ceremony',
            ],
            [
                'term_key' => 'can-am-maverick',
                'term' => [
                    'en' => 'Can-Am Maverick X3 Turbo',
                    'ar' => 'كان-آم مافريك إكس 3 توربو',
                    'ru' => 'Can-Am Maverick X3 Turbo',
                    'es' => 'Can-Am Maverick X3 Turbo',
                    'it' => 'Can-Am Maverick X3 Turbo',
                ],
                'definition' => [
                    'en' => 'A purpose-built desert racing buggy featuring up to 195 horsepower, full roll cage, and 24 inches of suspension travel for tackling massive dunes.',
                    'ar' => 'مركبة سباق صحراوية مخصصة تصل قوتها إلى 195 حصاناً ومزودة بقفص حماية فولاذي ونظام تعليق رياضي لقطع الكثبان الوعرة.',
                ],
                'article_slug' => 'dune-buggy-vs-quad-bike-guide',
            ],
            [
                'term_key' => 'arabian-oryx',
                'term' => [
                    'en' => 'Arabian Oryx',
                    'ar' => 'المها العربي',
                    'ru' => 'Аравийский орикс',
                    'es' => 'Órix Árabe',
                    'it' => 'Orice d\'Arabia',
                ],
                'definition' => [
                    'en' => 'The national animal of the UAE, a luminous white desert antelope with long spear-like horns, historically known as the biological inspiration for the unicorn.',
                    'ar' => 'الحيوان الوطني لدولة الإمارات، وهو ظبي صحراوي أبيض ناصع ذو قرنين طويلين مستقيمين ارتبط تاريخياً بأسطورة وحيد القرن.',
                ],
                'article_slug' => 'arabian-oryx-desert-conservation-guide',
            ],
            [
                'term_key' => 'big-red',
                'term' => [
                    'en' => 'Big Red (Al Hamar)',
                    'ar' => 'الرملة الحمراء (البيج ريد)',
                    'ru' => 'Биг Ред (Аль-Хамар)',
                    'es' => 'Big Red (Al Hamar)',
                    'it' => 'Big Red (Al Hamar)',
                ],
                'definition' => [
                    'en' => 'Dubai’s highest and most famous sand dune rising over 300 feet (100 meters) on the Dubai-Hatta highway, renowned for extreme off-road climbing.',
                    'ar' => 'أعلى وأشهر كثيب رملي في دبي يرتفع أكثر من 300 قدم على طريق دبي-حتا، ويعد مقصداً شهيراً لمتسابقي الصحراء.',
                ],
                'article_slug' => 'lahbab-red-dunes-geology-guide',
            ],
            [
                'term_key' => 'slipface',
                'term' => [
                    'en' => 'Slipface',
                    'ar' => 'منحدر الرمال (الوجه الانزلاقي)',
                    'ru' => 'Осыпной склон (Слипфейс)',
                    'es' => 'Cara de Deslizamiento (Slipface)',
                    'it' => 'Fronte di Scivolamento (Slipface)',
                ],
                'definition' => [
                    'en' => 'The steep, leeward slope of a sand dune formed at the natural angle of repose (approx. 32-34 degrees) down which sand cascades during dune bashing maneuvers.',
                    'ar' => 'المنحدر الحاد المواجه للريح في الكثيب الرملي المتكون عند زاوية الاستقرار الطبيعية حيث تتساقط حبيبات الرمل أثناء صعود وهبوط المركبات.',
                ],
                'article_slug' => 'dune-bashing-physics-safety-mechanics',
            ],
        ];

        foreach ($glossary as $gData) {
            $article = WikiArticle::where('slug', $gData['article_slug'])->first();
            WikiGlossaryTerm::updateOrCreate(
                ['term_key' => $gData['term_key']],
                [
                    'term' => $gData['term'],
                    'definition' => $gData['definition'],
                    'wiki_article_id' => $article ? $article->id : null,
                ]
            );
        }
    }
}
