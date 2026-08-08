import json, os

data_dir = r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel\database\seeders\data"

# 1. Update tours.json
tours_file = os.path.join(data_dir, "tours.json")
with open(tours_file, "r", encoding="utf-8") as f:
    tours = json.load(f)

# Check if ID 8 exists
if not any(t["id"] == 8 for t in tours):
    new_tour = {
        "id": 8,
        "slug": "dune-buggy-rental-dubai",
        "name": "Dune Buggy Rental Dubai",
        "category": "desert-safari",
        "short_desc": "Unleash the ultimate adrenaline rush in Dubai's Lahbab Red Dunes with our self-drive 1000cc Can-Am Maverick X3 and Polaris RZR dune buggies. Conquer towering sand dunes with full safety gear, expert guide instruction, and complimentary hotel pickup.",
        "full_desc": "Take control of a high-powered 1000cc Can-Am Maverick X3 Turbo or Polaris RZR dune buggy and conquer the open desert of Dubai's famous Lahbab Red Dunes. Designed for thrill-seekers, couples, and friends, our self-drive dune buggy tours deliver an unparalleled off-road adventure under the guidance of certified desert rally instructors.\r\n\r\nYour experience begins with convenient hotel pick-up in a comfortable air-conditioned 4x4 vehicle. Upon arrival at our desert staging base, you will be briefed on vehicle operation, safety protocols, and equipped with full protective gear including helmets and goggles.\r\n\r\nFollowing a practice trail, follow your lead captain into the deep red dunes for 60 to 120 minutes of heart-stopping dune climbs, steep drops, and high-speed ridge carving. Pause atop the tallest dunes for breathtaking desert photo stops before returning to base for light refreshments, camel rides, and sandboarding.\r\n\r\nWhether you choose a single-seat, 2-seater, or 4-seater buggy, Dunes Discovery Tourism provides brand-new, meticulously maintained off-road buggies fitted with roll cages, 4-point safety harnesses, and GPS tracking.",
        "duration": "3 Hours",
        "pickup_time": "7:00 AM / 3:00 PM",
        "dropoff_time": "10:00 AM / 6:00 PM",
        "min_age": 16,
        "group_size": None,
        "languages": "English, Arabic",
        "hero_image": "quad-biking-desert-safari-dubai-dune-discovery-tourism.avif",
        "thumb_image": "quad-biking-desert-safari-dubai-dune-discovery-tourism.avif",
        "og_image": "quad-biking-desert-safari-dubai-dune-discovery-tourism.avif",
        "video_url": None,
        "rating": 4.9,
        "review_count": 642,
        "is_bestseller": 1,
        "is_featured": 1,
        "status": "active",
        "priority": 4,
        "meta_title": "Dune Buggy Rental Dubai | 1000cc Can-Am & Polaris | Dunes Discovery",
        "meta_desc": "Rent self-drive 1000cc Can-Am Maverick & Polaris dune buggies in Dubai Lahbab Red Dunes. High-power off-road desert safari with safety gear & hotel pickup.",
        "meta_keywords": "dune buggy rental dubai, can am dune buggy dubai, polaris rzr dubai, self drive buggy desert safari, red dunes buggy rental",
        "created_at": "2026-02-08 10:00:00",
        "updated_at": "2026-02-08 10:00:00"
    }
    tours.append(new_tour)
    with open(tours_file, "w", encoding="utf-8") as f:
        json.dump(tours, f, indent=4)
    print("Tour ID 8 added to tours.json")

# 2. Update tour_tiers.json
tiers_file = os.path.join(data_dir, "tour_tiers.json")
with open(tiers_file, "r", encoding="utf-8") as f:
    tour_tiers = json.load(f)

if not any(tt["tour_id"] == 8 for tt in tour_tiers):
    new_tour_tiers = [
        {"id": 40, "tour_id": 8, "tier_id": 1, "price": 599, "old_price": 750, "price_type": "per buggy"},
        {"id": 41, "tour_id": 8, "tier_id": 2, "price": 899, "old_price": 1100, "price_type": "per buggy"},
        {"id": 42, "tour_id": 8, "tier_id": 3, "price": 1299, "old_price": 1500, "price_type": "per buggy"}
    ]
    tour_tiers.extend(new_tour_tiers)
    with open(tiers_file, "w", encoding="utf-8") as f:
        json.dump(tour_tiers, f, indent=4)
    print("Tour ID 8 tiers added to tour_tiers.json")

# 3. Update blog_posts.json with 2 new high-traffic blog articles
posts_file = os.path.join(data_dir, "blog_posts.json")
with open(posts_file, "r", encoding="utf-8") as f:
    posts = json.load(f)

if not any(p["id"] == 13 for p in posts):
    post13 = {
        "id": 13,
        "category_id": 1,
        "slug": "desert-safari-vs-dune-buggy-rental-dubai-comparison",
        "title": "Desert Safari vs. Dune Buggy Rental in Dubai: Which Desert Adventure Should You Choose?",
        "excerpt": "Deciding between a passenger desert safari and a self-drive 1000cc dune buggy rental in Dubai? Compare pricing, adrenaline levels, safety, and experiences to pick the right adventure.",
        "content": "<p>When planning your trip to Dubai, choosing the right desert experience is one of the most exciting decisions you will make. Two of the most popular desert adventures are the traditional <strong>Passenger Desert Safari</strong> and the self-drive <strong>Dune Buggy Rental</strong>. While both take place in Dubai's breathtaking Lahbab Red Dunes, they offer entirely different experiences.</p><h3>1. Driving Dynamics: Passenger vs. Self-Drive</h3><p>On a standard Evening Desert Safari, an experienced safari captain drives a 4x4 Toyota Land Cruiser while you relax and enjoy the dune bashing ride. In contrast, a Dune Buggy Rental puts <em>you</em> directly behind the steering wheel of a high-powered 1000cc Can-Am Maverick X3 or Polaris RZR buggy.</p><h3>2. Adrenaline Level & Speed</h3><p>If you want maximum control and high-speed off-road rally excitement, dune buggies offer custom suspension and turbo acceleration capable of tackling 45-degree dune climbs. Standard desert safaris offer a thrilling yet family-friendly dune drive followed by camp dining and live shows.</p><h3>3. Pricing & Group Value</h3><p>Desert safaris start from AED 79 to AED 199 per person and include BBQ dinner, henna, camel rides, and entertainment. Dune buggy rentals range from AED 599 to AED 1,299 per buggy (which can be shared by 2 or 4 passengers), making them ideal for thrill-seekers and couples looking for exclusive drive time.</p><h3>Final Recommendation</h3><p>If you are travelling with family, elderly guests, or want an all-inclusive evening with dinner and shows, book the <a href=\"/evening-desert-safari-dubai\">Evening Desert Safari</a>. If you are an adventure enthusiast who craves driving high-performance off-road vehicles across open red dunes, reserve your <a href=\"/dune-buggy-rental-dubai\">Dune Buggy Rental</a> today!</p>",
        "featured_image": "quad-biking-desert-safari-dubai-dune-discovery-tourism.avif",
        "og_image": "quad-biking-desert-safari-dubai-dune-discovery-tourism.avif",
        "author_name": "Dunes Discovery Team",
        "author_title": "Dubai Tourism Experts",
        "author_bio": "Certified UAE tour operators and desert rally guides.",
        "read_time": 6,
        "is_featured": 1,
        "is_published": 1,
        "status": "published",
        "published_at": "2026-02-08 11:00:00",
        "schema_type": "BlogPosting",
        "canonical_url": "https://dunesdiscoverytourism.com/blog/desert-safari-vs-dune-buggy-rental-dubai-comparison",
        "meta_title": "Desert Safari vs Dune Buggy Rental Dubai | Which is Best?",
        "meta_desc": "Compare Dubai desert safari vs self-drive dune buggy rental. Pricing, safety, speed & group advice to choose the best desert tour.",
        "meta_keywords": "desert safari vs dune buggy dubai, dune buggy or quad biking dubai, self drive dune buggy comparison",
        "created_at": "2026-02-08 11:00:00",
        "updated_at": "2026-02-08 11:00:00"
    }

    post14 = {
        "id": 14,
        "category_id": 1,
        "slug": "is-dune-bashing-safe-for-pregnant-women-kids-seniors",
        "title": "Is Dune Bashing Safe for Pregnant Women, Toddlers & Seniors? Full Safety Breakdown",
        "excerpt": "Everything you need to know about Dubai desert safari safety rules for pregnant guests, young children, and elderly travelers. Learn how to skip dune bashing while enjoying the desert camp.",
        "content": "<p>Planning a Dubai desert safari for a family with diverse ages and health requirements requires clear safety information. One of the most frequent questions guests ask is whether <strong>dune bashing is safe during pregnancy, for toddlers, or for seniors with back concerns</strong>.</p><h3>Official Safety Guidelines for Dune Bashing</h3><p>Dune bashing involves 30 to 45 minutes of dynamic off-road driving over steep sand crests. While exciting, the abrupt movement and G-force create jolts that are not suitable for everyone.</p><ul><li><strong>Pregnant Women:</strong> Dune bashing is strictly not recommended at any stage of pregnancy due to unpredictable vehicle bouncing.</li><li><strong>Infants & Toddlers (Under 3 Years):</strong> Young toddlers lack full neck and spine support required for high-intensity dune driving.</li><li><strong>Guests with Severe Back, Neck, or Heart Issues:</strong> The vigorous motion can aggravate spinal or cardiac conditions.</li></ul><h3>The Solution: Direct Camp Transfer Option</h3><p>At Dunes Discovery Tourism, you do not have to miss out on the desert experience! We provide a <strong>Direct Camp Transfer</strong> in a separate 4x4 vehicle that drives smoothly along flat desert roads directly to our Bedouin camp at normal speeds.</p><p>This allows pregnant women, seniors, and families with young children to enjoy the sunset photography stop, camel riding, Arabic dress dress-up, henna art, lavish BBQ dinner, and live Tanoura, fire, and belly dance shows without taking part in dune bashing.</p>",
        "featured_image": "evening-desert-safari-dubai-dune-discovery-tourism.avif",
        "og_image": "evening-desert-safari-dubai-dune-discovery-tourism.avif",
        "author_name": "Dunes Discovery Team",
        "author_title": "Dubai Safety & Operations Specialists",
        "author_bio": "Government licensed desert safari captains and guest safety advisors.",
        "read_time": 5,
        "is_featured": 1,
        "is_published": 1,
        "status": "published",
        "published_at": "2026-02-08 11:30:00",
        "schema_type": "BlogPosting",
        "canonical_url": "https://dunesdiscoverytourism.com/blog/is-dune-bashing-safe-for-pregnant-women-kids-seniors",
        "meta_title": "Is Dune Bashing Safe for Pregnant Women & Kids? | Dubai Safari Safety",
        "meta_desc": "Is dune bashing safe in pregnancy, for kids, or seniors? Learn Dubai desert safari safety rules and direct camp transfer options.",
        "meta_keywords": "dune bashing pregnant women dubai, is desert safari safe for toddlers, desert safari for seniors dubai",
        "created_at": "2026-02-08 11:30:00",
        "updated_at": "2026-02-08 11:30:00"
    }

    posts.extend([post13, post14])
    with open(posts_file, "w", encoding="utf-8") as f:
        json.dump(posts, f, indent=4)
    print("Post IDs 13 & 14 added to blog_posts.json")
