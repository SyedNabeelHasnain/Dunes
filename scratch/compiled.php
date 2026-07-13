<?php $__env->startSection('content'); ?>
<?php
    $notAllowed = $tour->contentItems->where('type', 'not_allowed')->sortBy('priority');
    $minPrice = $tour->tiers->min('pivot.price') ?? 0;
    
    // Prepare Tabs Array
    $tabs = [];
    if($highlights->count()) $tabs['highlights'] = 'Highlights';
    if($tour->itineraries->count()) $tabs['itinerary'] = 'Itinerary';
    if($inclusions->count() || $exclusions->count()) $tabs['inex'] = 'Inclusion & Exclusion';
    if($notAllowed->count()) $tabs['info'] = 'Important Information';
    if($faqs->count()) $tabs['faqs'] = 'FAQ';
?>

<!-- Ecommerce GTM View Item DataLayer -->
<script>
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({ ecommerce: null });
window.dataLayer.push({
  event: "view_item",
  ecommerce: {
    currency: "AED",
    value: <?php echo e($minPrice); ?>,
    items: [{
      item_id: "<?php echo e($tour->id); ?>",
      item_name: "<?php echo e($tour->name); ?>",
      item_category: "<?php echo e($tour->category ? $tour->category->slug : ''); ?>",
      price: <?php echo e($minPrice); ?>

    }]
  }
});
</script>

<!-- Meta Pixel ViewContent Event -->
<?php if(\App\Models\Setting::where('setting_key', 'meta_active')->value('setting_value') === '1'): ?>
<?php $metaPixelId = \App\Models\Setting::where('setting_key', 'meta_pixel_id')->value('setting_value'); ?>
<script>
if(window.fbq){
    fbq('track', 'ViewContent', {
        content_ids: ['TOUR-<?php echo e($tour->id); ?>'],
        content_type: 'product',
        value: <?php echo e($minPrice); ?>,
        currency: 'AED'
    });
}
</script>
<?php endif; ?>

<!-- Schema.org metadata -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "<?php echo e($tour->name); ?>",
  "image": "<?php echo e(asset('images/' . $tour->hero_image)); ?>",
  "description": "<?php echo e($tour->short_desc); ?>",
  "offers": {
    "@type": "Offer",
    "priceCurrency": "AED",
    "price": "<?php echo e($minPrice); ?>",
    "availability": "https://schema.org/InStock"
  }
}
</script>

<!-- Tour Hero Section -->
<section class="tour-hero-modern position-relative d-flex align-items-end" style="min-height: 50vh; background: url('<?php echo e(asset('images/' . $tour->hero_image)); ?>') center/cover no-repeat; margin-top: -var(--header-h);">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.85) 100%);"></div>
    <div class="container position-relative z-1 pb-5 text-white">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>" class="text-white text-opacity-75 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo e(route('tours.index')); ?>" class="text-white text-opacity-75 text-decoration-none">Tours</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?php echo e($tour->name); ?></li>
            </ol>
        </nav>
        <div>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge rounded-pill glass px-3 py-2 fw-semibold">
                    <i class="bi bi-tag-fill me-1 text-primary"></i><?php echo e($tour->category ? $tour->category->name : 'Tour'); ?>

                </span>
                <?php if($tour->is_bestseller): ?>
                <span class="badge rounded-pill bg-primary px-3 py-2 shadow-sm border-0">
                    <i class="bi bi-fire me-1"></i>Best Seller
                </span>
                <?php endif; ?>
            </div>
            <h1 class="display-3 fw-bold mb-4 text-white text-shadow"><?php echo e($tour->name); ?></h1>
            <div class="d-flex flex-wrap gap-4 align-items-center opacity-90">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-box-sm bg-primary rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-clock-fill text-white small"></i>
                    </div>
                    <span class="fw-medium"><?php echo e($tour->duration); ?></span>
                </div>
                <div class="d-flex align-items-center gap-2 border-start border-white border-opacity-25 ps-4">
                    <div class="text-warning">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <span class="fw-medium"><?php echo e($tour->rating); ?> <small class="opacity-75">(<?php echo e(number_format($tour->review_count)); ?> reviews)</small></span>
                </div>
                <div class="d-flex align-items-center gap-2 border-start border-white border-opacity-25 ps-4 d-none d-md-flex">
                    <i class="bi bi-geo-alt-fill text-primary"></i>
                    <span class="fw-medium">Hotel Pickup Included</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Content and Sidebar Section -->
<section class="section py-5 bg-white">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <!-- About This Tour -->
                <div class="content-card mb-5 border-0 shadow-none p-0">
                    <h2 class="h3 fw-bold mb-4 d-flex align-items-center gap-3">
                        <span class="bg-soft-primary p-2 rounded-3 text-primary"><i class="bi bi-info-circle"></i></span>
                        About This Tour
                    </h2>
                    <div class="text-muted lead-sm fs-5 position-relative" style="line-height: 1.8;">
                        <div id="tourDescriptionText" class="line-clamp-3">
                            <?php echo nl2br(e($tour->full_desc)); ?>

                        </div>
                        <button class="btn btn-sm border-animated rounded-pill px-3 py-1 mt-3 shadow-sm fw-bold d-none text-primary" id="readMoreBtn" onclick="toggleDescription()">
                            Read More <i class="bi bi-chevron-down ms-1"></i>
                        </button>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <?php if(count($tabs)): ?>
                <div class="mb-4">
                    <ul class="nav nav-pills tour-tabs" id="tourTabs" role="tablist">
                        <?php $first = true; ?>
                        <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill fw-bold <?php echo e($first ? 'active' : ''); ?>" data-bs-toggle="tab" data-bs-target="#tab-<?php echo e($key); ?>" type="button" role="tab"><?php echo e($label); ?></button>
                        </li>
                        <?php $first = false; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>

                <!-- Tabs Content -->
                <div class="tab-content mb-5">
                    <?php $first = true; ?>
                    
                    <!-- Highlights -->
                    <?php if(isset($tabs['highlights'])): ?>
                    <div class="tab-pane fade <?php echo e($first ? 'show active' : ''); ?>" id="tab-highlights" role="tabpanel">
                        <div class="row g-4">
                            <?php $__currentLoopData = $highlights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-6">
                                <div class="d-flex gap-3 p-3 rounded-4 bg-light hover-shadow-sm transition-all border border-transparent hover-border-primary">
                                    <div class="bg-primary text-white rounded-circle p-2 flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-check2 fw-bold"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo e($h->title); ?></div>
                                        <?php if($h->description): ?>
                                        <small class="text-muted d-block mt-1"><?php echo e($h->description); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php $first = false; ?>
                    <?php endif; ?>

                    <!-- Itinerary -->
                    <?php if(isset($tabs['itinerary'])): ?>
                    <div class="tab-pane fade <?php echo e($first ? 'show active' : ''); ?>" id="tab-itinerary" role="tabpanel">
                        <div class="itinerary-modern ps-4 border-start border-primary border-opacity-25 position-relative ms-2">
                            <?php $__currentLoopData = $tour->itineraries->sortBy('priority'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="itinerary-item position-relative pb-4 mb-4">
                                <div class="position-absolute start-0 top-0 translate-middle-x bg-primary rounded-circle border border-4 border-white shadow-sm" style="width: 22px; height: 22px; margin-left: -24px;"></div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="badge bg-soft-primary text-primary rounded-pill fw-bold px-3"><?php echo e($it->time); ?></div>
                                    <?php if($it->duration): ?>
                                    <small class="text-muted fw-semibold"><i class="bi bi-clock me-1 text-primary"></i><?php echo e($it->duration); ?></small>
                                    <?php endif; ?>
                                </div>
                                <h5 class="fw-bold text-dark mb-2"><?php echo e($it->title); ?></h5>
                                <p class="text-muted mb-0 small opacity-75"><?php echo e($it->description); ?></p>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php $first = false; ?>
                    <?php endif; ?>

                    <!-- Inclusion & Exclusion -->
                    <?php if(isset($tabs['inex'])): ?>
                    <div class="tab-pane fade <?php echo e($first ? 'show active' : ''); ?>" id="tab-inex" role="tabpanel">
                        <div class="row g-4">
                            <?php if($inclusions->count()): ?>
                            <div class="col-md-6">
                                <div class="card border-0 bg-soft-success p-4 rounded-4 border-start border-4 border-success h-100">
                                    <h3 class="h6 fw-bold mb-4 d-flex align-items-center gap-2 text-success">
                                        <i class="bi bi-check-circle-fill"></i>What's Included
                                    </h3>
                                    <ul class="list-unstyled mb-0">
                                        <?php $__currentLoopData = $inclusions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="d-flex gap-2 mb-3 align-items-start">
                                            <i class="bi bi-check-lg text-success fw-bold mt-1"></i>
                                            <span class="text-dark fw-medium opacity-90"><?php echo e($inc->title); ?></span>
                                        </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if($exclusions->count()): ?>
                            <div class="col-md-6">
                                <div class="card border-0 bg-soft-danger p-4 rounded-4 border-start border-4 border-danger h-100">
                                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2 text-danger">
                                        <i class="bi bi-x-circle-fill"></i>Not Included
                                    </h5>
                                    <ul class="list-unstyled mb-0">
                                        <?php $__currentLoopData = $exclusions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="d-flex gap-2 mb-3 align-items-start">
                                            <i class="bi bi-x-lg text-danger fw-bold mt-1"></i>
                                            <span class="text-dark fw-medium opacity-90"><?php echo e($exc->title); ?></span>
                                        </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php $first = false; ?>
                    <?php endif; ?>

                    <!-- Important Information -->
                    <?php if(isset($tabs['info'])): ?>
                    <div class="tab-pane fade <?php echo e($first ? 'show active' : ''); ?>" id="tab-info" role="tabpanel">
                        <div class="card border-0 bg-soft-warning p-4 rounded-4 border-start border-4 border-warning">
                            <h3 class="h6 fw-bold mb-3 d-flex align-items-center gap-2 text-warning">
                                <i class="bi bi-exclamation-triangle-fill"></i>Important Information
                            </h3>
                            <ul class="list-unstyled mb-0 row g-3">
                                <?php $__currentLoopData = $notAllowed; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $na): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="col-md-6 text-dark opacity-90 small d-flex gap-2 align-items-start fw-medium">
                                    <i class="bi bi-info-circle-fill text-warning"></i>
                                    <span><?php echo e($na->title); ?><?php if($na->description): ?> - <span class="fw-normal opacity-75"><?php echo e($na->description); ?></span><?php endif; ?></span>
                                </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                    <?php $first = false; ?>
                    <?php endif; ?>

                    <!-- FAQs -->
                    <?php if(isset($tabs['faqs'])): ?>
                    <div class="tab-pane fade <?php echo e($first ? 'show active' : ''); ?>" id="tab-faqs" role="tabpanel">
                        <div class="accordion accordion-flush" id="tourFaqAccordion">
                            <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="accordion-item mb-3 border-animated rounded-4 shadow-sm overflow-hidden bg-white">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold py-4 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#tour-faq-<?php echo e($index); ?>">
                                        <i class="bi bi-question-circle-fill me-3 text-primary"></i>
                                        <?php echo e($f->question); ?>

                                    </button>
                                </h2>
                                <div id="tour-faq-<?php echo e($index); ?>" class="accordion-collapse collapse" data-bs-parent="#tourFaqAccordion">
                                    <div class="accordion-body py-4 px-4 text-muted leading-relaxed">
                                        <?php echo e($f->answer); ?>

                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php $first = false; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar-sticky" style="top: 100px;">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4 bg-white">
                        <div class="card-body p-4">
                            <div class="text-center mb-4 pb-4 border-bottom">
                                <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 11px;">Starting from</small>
                                <div class="d-flex align-items-center justify-content-center gap-2 mt-1">
                                    <span class="h2 fw-bold text-primary mb-0">AED <?php echo e(number_format($minPrice)); ?></span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-box-seam text-primary"></i>Select Package
                                </h6>
                                <div class="d-grid gap-3">
                                    <?php $__currentLoopData = $tour->tiers->sortBy('priority'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $tPrice = $tier->pivot->price;
                                        $tOldPrice = $tier->pivot->old_price;
                                        $save = ($tOldPrice > 0 && $tOldPrice > $tPrice) ? ($tOldPrice - $tPrice) : 0;
                                    ?>
                                    <div class="package-option p-3 border rounded-4 position-relative cursor-pointer transition-all hover-shadow-sm <?php echo e($tier->is_popular ? 'border-primary bg-soft-primary' : 'bg-white'); ?>" data-action="open-booking" data-tour="<?php echo e($tour->id); ?>" data-tier="<?php echo e($tier->id); ?>">
                                        <?php if($tier->is_popular): ?>
                                        <span class="badge bg-primary position-absolute top-0 end-0 m-2 rounded-pill px-2 py-1" style="font-size: 9px; letter-spacing: 0.5px;">POPULAR</span>
                                        <?php endif; ?>
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="pe-3">
                                                <div class="fw-bold text-dark mb-1"><?php echo e($tier->name); ?></div>
                                                <?php if($tier->description): ?>
                                                <small class="text-muted d-block line-clamp-1 opacity-75" style="font-size: 11px;"><?php echo e($tier->description); ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end flex-shrink-0 <?php echo e($tier->is_popular ? 'mt-4' : ''); ?>">
                                                <div class="d-flex flex-column align-items-end">
                                                    <?php if($save): ?>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <small class="text-secondary text-decoration-line-through fw-semibold" style="font-size: 11px;">AED <?php echo e(number_format($tOldPrice)); ?></small>
                                                        <div class="fw-bold text-primary">AED <?php echo e(number_format($tPrice)); ?></div>
                                                    </div>
                                                    <?php else: ?>
                                                    <div class="fw-bold text-primary">AED <?php echo e(number_format($tPrice)); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>

                            <div class="d-grid gap-3">
                                <button class="btn btn-desert-animated btn-lg rounded-pill py-3 shadow-primary fw-bold border-0 transition-all hover-translate-up" data-bs-toggle="modal" data-bs-target="#bookingModal">
                                    <i class="bi bi-calendar-check-fill me-2"></i>Book Online Now
                                </button>
                                <a href="https://wa.me/<?php echo e(preg_replace('/[^0-9]/','',\App\Models\Setting::where('setting_key', 'site_whatsapp')->value('setting_value') ?? '971502456056')); ?>?text=<?php echo e(urlencode('Hi! I want to book ' . $tour->name)); ?>" class="btn btn-whatsapp-animated btn-lg rounded-pill py-3 fw-bold border-0 transition-all hover-translate-up" target="_blank" rel="noopener">
                                    <i class="bi bi-whatsapp me-2"></i>Inquire via WhatsApp
                                </a>
                            </div>

                            <div class="row g-3 mt-4 text-center">
                                <div class="col-6">
                                    <div class="bg-light p-3 rounded-4 border border-transparent hover-border-primary transition-all">
                                        <i class="bi bi-clock-history text-primary d-block mb-2 fs-5"></i>
                                        <small class="text-muted d-block ls-1 mb-1" style="font-size: 9px; font-weight: 700;">DURATION</small>
                                        <div class="fw-bold small text-dark"><?php echo e($tour->duration); ?></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light p-3 rounded-4 border border-transparent hover-border-primary transition-all">
                                        <i class="bi bi-translate text-primary d-block mb-2 fs-5"></i>
                                        <small class="text-muted d-block ls-1 mb-1" style="font-size: 9px; font-weight: 700;">LANGUAGES</small>
                                        <div class="fw-bold small text-dark"><?php echo e($tour->languages); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-dark text-white rounded-4 overflow-hidden shadow-lg">
                        <div class="card-body p-4 position-relative z-1">
                            <h5 class="fw-bold text-light mb-3">Need Help?</h5>
                            <p class="small opacity-75 mb-4">Our travel experts are available 24/7 to help you with your booking.</p>
                            <?php
                                $phoneVal = \App\Models\Setting::where('setting_key', 'site_phone')->value('setting_value') ?? '+971 50 245 6056';
                            ?>
                            <a href="tel:<?php echo e(preg_replace('/[^0-9+]/','',$phoneVal)); ?>" class="d-flex align-items-center gap-3 text-white text-decoration-none mb-3 group">
                                <div class="bg-primary text-white rounded-circle p-2 group-hover-scale transition-all" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <div>
                                    <small class="d-block opacity-75">Call Us</small>
                                    <div class="fw-bold"><?php echo e($phoneVal); ?></div>
                                </div>
                            </a>
                        </div>
                        <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25" style="background: radial-gradient(circle at 80% 20%, rgba(246, 144, 68, 0.2) 0%, transparent 60%);"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- You Might Also Like Section -->
<?php if($relatedTours->count()): ?>
<section class="section py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
             <h3 class="fw-bold mb-0 d-flex align-items-center gap-3">
                <span class="bg-soft-primary p-2 rounded-3 text-primary"><i class="bi bi-compass"></i></span>
                You Might Also Like
            </h3>
            <a href="<?php echo e(route('tours.index')); ?>" class="btn btn-link text-decoration-none fw-bold">View All Tours <i class="bi bi-arrow-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            <?php $__currentLoopData = $relatedTours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $minPriceRel = \DB::table('tour_tiers')->where('tour_id', $t->id)->min('price') ?? 0; ?>
            <div class="col-12 col-md-6 col-lg-3">
                <article class="card card-modern h-100 border-0 shadow-sm bg-white">
                    <a href="<?php echo e(route('tours.show', $t->slug)); ?>" class="text-decoration-none text-dark d-flex flex-column h-100">
                        <div class="card-img-wrapper position-relative overflow-hidden" style="aspect-ratio: 16/10;">
                            <img src="<?php echo e(asset('images/' . $t->thumb_image)); ?>" class="card-img-top w-100 h-100" alt="<?php echo e($t->name); ?>" loading="lazy" style="object-fit: cover;">
                            <?php if($t->is_bestseller): ?>
                            <span class="badge bg-primary position-absolute top-0 start-0 m-3 rounded-pill shadow-sm">
                                <i class="bi bi-fire me-1"></i>Best Seller
                            </span>
                            <?php endif; ?>
                            <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(0deg, rgba(0,0,0,0.6) 0%, transparent 100%);">
                                <span class="badge glass text-white fw-semibold">
                                    <i class="bi bi-tag-fill me-1"></i><?php echo e($t->category ? $t->category->name : 'Tour'); ?>

                                </span>
                            </div>
                        </div>
                        <div class="card-body p-4 d-flex flex-column flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="text-muted small">
                                    <i class="bi bi-clock me-1"></i><?php echo e($t->duration); ?>

                                </div>
                                <div class="text-warning small">
                                    <i class="bi bi-star-fill me-1"></i><?php echo e($t->rating); ?>

                                </div>
                            </div>
                            <h3 class="h5 fw-bold mb-3 line-clamp-2"><?php echo e($t->name); ?></h3>
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 10px; text-transform: uppercase; font-weight: 700;">Starting from</small>
                                    <span class="h5 fw-bold text-primary mb-0">AED <?php echo e(number_format($minPriceRel)); ?></span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn-circle-whatsapp fab-whatsapp" data-tour-name="<?php echo e($t->name); ?>">
                                        <i class="bi bi-whatsapp"></i>
                                    </button>
                                    <div class="btn-circle-desert d-flex align-items-center justify-content-center">
                                        <i class="bi bi-arrow-right"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </article>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Mobile Book Bar Sticky bottom -->
<div class="mobile-bookbar position-fixed bottom-0 start-0 w-100 glass p-3 border-top d-md-none d-flex align-items-center justify-content-between z-3 safe-area-bottom">
    <div>
        <small class="text-muted d-block opacity-75 fw-bold" style="font-size: 9px; letter-spacing: 1px; text-transform: uppercase;">Starting From</small>
        <div class="h4 fw-bold text-primary mb-0">AED <?php echo e(number_format($minPrice)); ?></div>
    </div>
    <button class="btn btn-primary rounded-pill px-5 py-3 shadow-sm fw-bold border-0" data-action="open-booking" data-tour="<?php echo e($tour->id); ?>">
        Book Now
    </button>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function toggleDescription() {
    const text = document.getElementById('tourDescriptionText');
    const btn = document.getElementById('readMoreBtn');
    if (text.classList.contains('line-clamp-3')) {
        text.classList.remove('line-clamp-3');
        btn.innerHTML = 'Read Less <i class="bi bi-chevron-up ms-1"></i>';
    } else {
        text.classList.add('line-clamp-3');
        btn.innerHTML = 'Read More <i class="bi bi-chevron-down ms-1"></i>';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const text = document.getElementById('tourDescriptionText');
    if(text && text.scrollHeight > text.clientHeight) {
        document.getElementById('readMoreBtn').classList.remove('d-none');
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>