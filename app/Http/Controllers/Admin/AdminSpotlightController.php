<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\LegalPage;
use App\Models\Page;
use App\Models\Review;
use App\Models\Subscriber;
use App\Models\Tour;
use App\Models\WhatsappInquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminSpotlightController extends Controller
{
    /**
     * Perform real-time, dynamic database search across all business entities.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        $categoryFilter = trim((string) $request->input('category', 'all'));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'query' => $q,
                'total' => 0,
                'categories' => [],
            ]);
        }

        $categories = [];
        $totalResults = 0;

        // 1. Bookings & Reservations
        if (in_array($categoryFilter, ['all', 'bookings'])) {
            try {
                $bookings = Booking::where(function ($query) use ($q) {
                    $query->where('reference', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('tour_name', 'like', "%{$q}%");
                })
                ->orderBy('created_at', 'desc')
                ->take(6)
                ->get();

                if ($bookings->isNotEmpty()) {
                    $items = $bookings->map(function ($b) {
                        $badgeColor = match ($b->status) {
                            'confirmed', 'completed' => 'emerald',
                            'pending' => 'amber',
                            'cancelled' => 'rose',
                            default => 'slate',
                        };

                        $dateStr = $b->tour_date ? $b->tour_date->format('d M Y') : 'Date N/A';
                        $priceStr = $b->total ? 'AED ' . number_format($b->total, 0) : 'AED 0';

                        return [
                            'id' => $b->id,
                            'title' => '#' . ($b->reference ?: $b->id) . ' — ' . ($b->name ?: 'Guest'),
                            'subtitle' => ($b->tour_name ?: 'Tour') . ' • ' . $dateStr . ' • ' . $priceStr,
                            'url' => route('admin.bookings.show', $b->id),
                            'badge' => ucfirst($b->status ?: 'Unknown'),
                            'badge_color' => $badgeColor,
                            'icon' => 'bi bi-calendar2-check-fill text-primary',
                            'category' => 'Bookings',
                        ];
                    });

                    $categories[] = [
                        'key' => 'bookings',
                        'label' => 'Bookings & Reservations',
                        'icon' => 'bi bi-calendar2-check-fill text-primary',
                        'items' => $items,
                    ];
                    $totalResults += $bookings->count();
                }
            } catch (\Throwable $e) {
                // Ignore gracefully
            }
        }

        // 2. Tours & Inventory
        if (in_array($categoryFilter, ['all', 'tours'])) {
            try {
                $tours = Tour::with('category')
                    ->where(function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%")
                            ->orWhere('slug', 'like', "%{$q}%");
                    })
                    ->orderBy('priority', 'asc')
                    ->take(6)
                    ->get();

                if ($tours->isNotEmpty()) {
                    $items = $tours->map(function ($t) {
                        $badgeColor = $t->status === 'active' ? 'emerald' : 'slate';
                        $catName = $t->category?->name ?? 'Desert Safari';
                        $dur = $t->duration ? ' • ' . $t->duration : '';

                        return [
                            'id' => $t->id,
                            'title' => $t->name,
                            'subtitle' => $catName . $dur . ' (/' . $t->slug . ')',
                            'url' => route('admin.tours.edit', $t->id),
                            'preview_url' => url('/' . $t->slug),
                            'badge' => ucfirst($t->status ?: 'Draft'),
                            'badge_color' => $badgeColor,
                            'icon' => 'bi bi-compass-fill text-primary',
                            'category' => 'Tours',
                        ];
                    });

                    $categories[] = [
                        'key' => 'tours',
                        'label' => 'Tours Inventory',
                        'icon' => 'bi bi-compass-fill text-primary',
                        'items' => $items,
                    ];
                    $totalResults += $tours->count();
                }
            } catch (\Throwable $e) {
                // Ignore gracefully
            }
        }

        // 3. Customer Inquiries
        if (in_array($categoryFilter, ['all', 'inquiries'])) {
            try {
                $inquiries = Contact::where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('subject', 'like', "%{$q}%")
                        ->orWhere('message', 'like', "%{$q}%");
                })
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

                if ($inquiries->isNotEmpty()) {
                    $items = $inquiries->map(function ($c) {
                        $badgeColor = $c->status === 'new' ? 'amber' : 'emerald';

                        return [
                            'id' => $c->id,
                            'title' => ($c->name ?: 'Guest') . ' — ' . ($c->subject ?: 'General Inquiry'),
                            'subtitle' => ($c->email ?: 'No email') . ($c->phone ? ' • ' . $c->phone : '') . ' • ' . ($c->created_at ? $c->created_at->diffForHumans() : ''),
                            'url' => route('admin.inquiries.show', $c->id),
                            'badge' => ucfirst($c->status ?: 'New'),
                            'badge_color' => $badgeColor,
                            'icon' => 'bi bi-envelope-paper-fill text-amber-500',
                            'category' => 'Inquiries',
                        ];
                    });

                    $categories[] = [
                        'key' => 'inquiries',
                        'label' => 'Customer Inquiries',
                        'icon' => 'bi bi-envelope-paper-fill text-amber-500',
                        'items' => $items,
                    ];
                    $totalResults += $inquiries->count();
                }
            } catch (\Throwable $e) {
                // Ignore gracefully
            }
        }

        // 4. WhatsApp Leads
        if (in_array($categoryFilter, ['all', 'leads', 'inquiries'])) {
            try {
                $leads = WhatsappInquiry::where(function ($query) use ($q) {
                    $query->where('phone', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('tour_name', 'like', "%{$q}%")
                        ->orWhere('message_text', 'like', "%{$q}%");
                })
                ->orderBy('id', 'desc')
                ->take(5)
                ->get();

                if ($leads->isNotEmpty()) {
                    $items = $leads->map(function ($w) {
                        return [
                            'id' => $w->id,
                            'title' => ($w->name ? $w->name . ' (' . $w->phone . ')' : $w->phone),
                            'subtitle' => ($w->tour_name ?: 'Direct WhatsApp') . ($w->message_text ? ' • ' . Str::limit($w->message_text, 45) : ''),
                            'url' => route('admin.whatsapp.leads') . '?search=' . urlencode($w->phone),
                            'badge' => 'Lead',
                            'badge_color' => 'emerald',
                            'icon' => 'bi bi-whatsapp text-emerald-500',
                            'category' => 'WhatsApp Leads',
                        ];
                    });

                    $categories[] = [
                        'key' => 'whatsapp',
                        'label' => 'WhatsApp Leads',
                        'icon' => 'bi bi-whatsapp text-emerald-500',
                        'items' => $items,
                    ];
                    $totalResults += $leads->count();
                }
            } catch (\Throwable $e) {
                // Ignore gracefully
            }
        }

        // 5. Coupons & Promo Codes
        if (in_array($categoryFilter, ['all', 'coupons', 'marketing'])) {
            try {
                $coupons = Coupon::where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                })
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

                if ($coupons->isNotEmpty()) {
                    $items = $coupons->map(function ($cp) {
                        $badgeColor = $cp->status === 'active' ? 'rose' : 'slate';
                        $disc = $cp->discount_type === 'percentage'
                            ? ($cp->discount_value . '% OFF')
                            : ('AED ' . number_format((float) $cp->discount_value, 0) . ' OFF');

                        return [
                            'id' => $cp->id,
                            'title' => 'Coupon: ' . strtoupper($cp->code),
                            'subtitle' => $disc . ($cp->name ? ' • ' . $cp->name : ''),
                            'url' => route('admin.coupons.edit', $cp->id),
                            'badge' => ucfirst($cp->status ?: 'Active'),
                            'badge_color' => $badgeColor,
                            'icon' => 'bi bi-ticket-perforated-fill text-rose-500',
                            'category' => 'Coupons',
                        ];
                    });

                    $categories[] = [
                        'key' => 'coupons',
                        'label' => 'Coupons & Promos',
                        'icon' => 'bi bi-ticket-perforated-fill text-rose-500',
                        'items' => $items,
                    ];
                    $totalResults += $coupons->count();
                }
            } catch (\Throwable $e) {
                // Ignore gracefully
            }
        }

        // 6. Blog Articles
        if (in_array($categoryFilter, ['all', 'content', 'blogs'])) {
            try {
                $posts = BlogPost::where(function ($query) use ($q) {
                    $query->where('title', 'like', "%{$q}%")
                        ->orWhere('slug', 'like', "%{$q}%");
                })
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

                if ($posts->isNotEmpty()) {
                    $items = $posts->map(function ($p) {
                        $badgeColor = $p->status === 'published' ? 'emerald' : 'slate';
                        $pubStr = $p->published_at ? $p->published_at->format('d M Y') : 'Draft';

                        return [
                            'id' => $p->id,
                            'title' => $p->title,
                            'subtitle' => '/blog/' . $p->slug . ' • ' . $pubStr,
                            'url' => route('admin.blogs.edit', $p->id),
                            'preview_url' => url('/blog/' . $p->slug),
                            'badge' => ucfirst($p->status ?: 'Draft'),
                            'badge_color' => $badgeColor,
                            'icon' => 'bi bi-journal-richtext text-emerald-500',
                            'category' => 'Blog Articles',
                        ];
                    });

                    $categories[] = [
                        'key' => 'blogs',
                        'label' => 'Blog Articles',
                        'icon' => 'bi bi-journal-richtext text-emerald-500',
                        'items' => $items,
                    ];
                    $totalResults += $posts->count();
                }
            } catch (\Throwable $e) {
                // Ignore gracefully
            }
        }

        // 7. Customer Reviews
        if (in_array($categoryFilter, ['all', 'reviews', 'content'])) {
            try {
                $reviews = Review::where(function ($query) use ($q) {
                    $query->where('reviewer_name', 'like', "%{$q}%")
                        ->orWhere('review_text', 'like', "%{$q}%")
                        ->orWhere('review_title', 'like', "%{$q}%");
                })
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

                if ($reviews->isNotEmpty()) {
                    $items = $reviews->map(function ($r) {
                        $badgeColor = $r->status === 'approved' ? 'emerald' : 'amber';
                        $ratingStr = str_repeat('★', (int) ($r->rating ?? 5));
                        $snippet = Str::limit($r->review_text ?: $r->review_title ?: 'Review comment', 50);

                        return [
                            'id' => $r->id,
                            'title' => ($r->reviewer_name ?: 'Guest') . ' — ' . $ratingStr,
                            'subtitle' => $snippet . ' (' . ucfirst($r->source ?: 'website') . ')',
                            'url' => route('admin.reviews.index') . '?search=' . urlencode($r->reviewer_name),
                            'badge' => ucfirst($r->status ?: 'Approved'),
                            'badge_color' => $badgeColor,
                            'icon' => 'bi bi-star-half text-amber-400',
                            'category' => 'Customer Reviews',
                        ];
                    });

                    $categories[] = [
                        'key' => 'reviews',
                        'label' => 'Customer Reviews',
                        'icon' => 'bi bi-star-half text-amber-400',
                        'items' => $items,
                    ];
                    $totalResults += $reviews->count();
                }
            } catch (\Throwable $e) {
                // Ignore gracefully
            }
        }

        // 8. Newsletter Subscribers
        if (in_array($categoryFilter, ['all', 'subscribers', 'marketing'])) {
            try {
                $subscribers = Subscriber::where(function ($query) use ($q) {
                    $query->where('email', 'like', "%{$q}%")
                        ->orWhere('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%");
                })
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

                if ($subscribers->isNotEmpty()) {
                    $items = $subscribers->map(function ($s) {
                        $badgeColor = $s->status === 'subscribed' ? 'sky' : 'slate';
                        $fullName = trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''));

                        return [
                            'id' => $s->id,
                            'title' => $s->email,
                            'subtitle' => ($fullName ? $fullName . ' • ' : '') . ucfirst($s->source ?: 'Website Footer'),
                            'url' => route('admin.subscribers.index') . '?search=' . urlencode($s->email),
                            'badge' => ucfirst($s->status ?: 'Subscribed'),
                            'badge_color' => $badgeColor,
                            'icon' => 'bi bi-people-fill text-sky-400',
                            'category' => 'Subscribers',
                        ];
                    });

                    $categories[] = [
                        'key' => 'subscribers',
                        'label' => 'Email Subscribers',
                        'icon' => 'bi bi-people-fill text-sky-400',
                        'items' => $items,
                    ];
                    $totalResults += $subscribers->count();
                }
            } catch (\Throwable $e) {
                // Ignore gracefully
            }
        }

        // 9. Pages & Legal Policies
        if (in_array($categoryFilter, ['all', 'content', 'pages'])) {
            try {
                $legal = LegalPage::where(function ($query) use ($q) {
                    $query->where('title', 'like', "%{$q}%")
                        ->orWhere('slug', 'like', "%{$q}%");
                })->take(4)->get();

                $pages = Page::where(function ($query) use ($q) {
                    $query->where('title', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('slug', 'like', "%{$q}%");
                })->take(4)->get();

                $cmsItems = collect();

                foreach ($legal as $l) {
                    $cmsItems->push([
                        'id' => 'legal_' . $l->id,
                        'title' => 'Legal: ' . ($l->title ?: ucfirst(str_replace('-', ' ', $l->slug))),
                        'subtitle' => '/' . $l->slug . ' • Policy document',
                        'url' => route('admin.legal.edit', $l->id),
                        'preview_url' => url('/' . $l->slug),
                        'badge' => 'Legal',
                        'badge_color' => 'indigo',
                        'icon' => 'bi bi-shield-check text-indigo-500',
                        'category' => 'Legal Policies',
                    ]);
                }

                foreach ($pages as $p) {
                    $cmsItems->push([
                        'id' => 'page_' . $p->id,
                        'title' => 'Page: ' . ($p->title ?: $p->name),
                        'subtitle' => '/' . $p->slug . ' • Dynamic CMS page',
                        'url' => route('admin.pages.edit', $p->id),
                        'preview_url' => url('/' . $p->slug),
                        'badge' => 'Page',
                        'badge_color' => 'amber',
                        'icon' => 'bi bi-file-earmark-richtext-fill text-amber-500',
                        'category' => 'CMS Pages',
                    ]);
                }

                if ($cmsItems->isNotEmpty()) {
                    $categories[] = [
                        'key' => 'pages',
                        'label' => 'Pages & Legal Policies',
                        'icon' => 'bi bi-file-earmark-richtext-fill text-amber-500',
                        'items' => $cmsItems->values()->all(),
                    ];
                    $totalResults += $cmsItems->count();
                }
            } catch (\Throwable $e) {
                // Ignore gracefully
            }
        }

        return response()->json([
            'query' => $q,
            'total' => $totalResults,
            'categories' => $categories,
        ]);
    }
}
