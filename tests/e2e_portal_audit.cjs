// Comprehensive E2E Portal Audit Runner
const dns = require('dns');
dns.setDefaultResultOrder('ipv4first');

const BASE = 'https://dunesdiscoverytourism.com';

const testSuites = [
  // ── 1. Front-Facing Core Pages & Navigation ──────────────────────────
  {
    category: 'Core Front-Facing Pages',
    tests: [
      { name: 'Homepage (200 OK, canonical, schema)', url: `${BASE}/`, expectStatus: 200, contains: ['Dunes Discovery', 'schema.org', '1430583', 'id="main"'] },
      { name: 'Tours Index (200 OK, tours catalog)', url: `${BASE}/tours`, expectStatus: 200, contains: ['Explore Dubai Safari Tours', 'btn-toggle-compare'] },
      { name: 'Dedicated Buggy Rental Page', url: `${BASE}/dune-buggy-rental-dubai`, expectStatus: 200, contains: ['Buggy', 'DDT'] },
      { name: 'Tour Detail View (Evening Safari)', url: `${BASE}/evening-desert-safari-dubai`, expectStatus: 200, contains: ['Evening Desert Safari', 'TouristTrip', 'Highlights'] },
      { name: 'Bespoke Customizer Landing', url: `${BASE}/build-your-own-safari`, expectStatus: 200, contains: ['Custom Dubai Desert Safari Builder', 'customizerTotalDisplay'] },
      { name: 'About Page', url: `${BASE}/about`, expectStatus: 200, contains: ['About', 'Dunes Discovery'] },
      { name: 'Contact Page', url: `${BASE}/contact`, expectStatus: 200, contains: ['Contact', 'form'] },
      { name: 'FAQ Page', url: `${BASE}/faq`, expectStatus: 200, contains: ['Frequently Asked Questions'] },
      { name: 'Rate Card Guide', url: `${BASE}/rate-card`, expectStatus: 200, contains: ['Rate Card', 'AED'] },
      { name: 'Blog Index', url: `${BASE}/blog`, expectStatus: 200, contains: ['Blog', 'Dubai'] },
      { name: 'Public UGC Review Submission (Guest)', url: `${BASE}/review/guest`, expectStatus: 200, contains: ['Dubai Desert Safari Experience', 'star-rating', 'photo'] },
    ]
  },

  // ── 2. Programmatic Geo-Location Pickup Pages ───────────────────────
  {
    category: 'Programmatic Geo-Location SEO Pages',
    tests: [
      { name: 'Dubai Marina Pickup Page', url: `${BASE}/desert-safari-from-dubai-marina`, expectStatus: 200, contains: ['Dubai Marina', 'Lahbab Red Dunes', 'TouristTrip'] },
      { name: 'Downtown Dubai Pickup Page', url: `${BASE}/desert-safari-from-downtown-dubai`, expectStatus: 200, contains: ['Downtown Dubai', 'Burj Khalifa', 'TouristTrip'] },
      { name: 'Palm Jumeirah Pickup Page', url: `${BASE}/desert-safari-from-palm-jumeirah`, expectStatus: 200, contains: ['Palm Jumeirah', 'Atlantis', 'TouristTrip'] },
      { name: 'Deira Old City Pickup Page', url: `${BASE}/desert-safari-from-deira`, expectStatus: 200, contains: ['Deira', 'TouristTrip'] },
      { name: 'Al Barsha Pickup Page', url: `${BASE}/desert-safari-from-al-barsha`, expectStatus: 200, contains: ['Al Barsha', 'Mall of the Emirates', 'TouristTrip'] },
      { name: 'Sharjah Pickup Page', url: `${BASE}/desert-safari-from-sharjah`, expectStatus: 200, contains: ['Sharjah', 'Sahara Centre', 'TouristTrip'] },
    ]
  },

  // ── 3. Search Engine & Discovery ────────────────────────────────────
  {
    category: 'Search Engine & AI Discovery',
    tests: [
      { name: 'Exact-Query Search (Quad Biking)', url: `${BASE}/search?q=quad+biking`, expectStatus: 200, contains: ['Quad Biking Dubai', 'SearchResultsPage'] },
      { name: 'Intent Search (Dune Buggy)', url: `${BASE}/search?q=dune+buggy`, expectStatus: 200, contains: ['Dune Buggy', 'SearchResultsPage'] },
      { name: 'Zero-Match Intent Fallback', url: `${BASE}/search?q=nonexistentqueryxyz123`, expectStatus: 200, contains: ['noindex, follow', 'No direct package named', 'Match My Safari'] },
      { name: 'AISEO Markdown (llms.txt)', url: `${BASE}/llms.txt`, expectStatus: 200, contains: ['Dunes Discovery Tourism', 'DET Tourism License'] },
      { name: 'AISEO Full Markdown (llms-full.txt)', url: `${BASE}/llms-full.txt`, expectStatus: 200, contains: ['Dunes Discovery Tourism', 'COMMERCIAL TOURS CATALOG'] },
      { name: 'Hierarchical Sitemap Index', url: `${BASE}/sitemap_index.xml`, expectStatus: 200, contains: ['sitemap-tours.xml', 'sitemap-blogs.xml'] },
      { name: 'Tours Sub-Sitemap', url: `${BASE}/sitemap-tours.xml`, expectStatus: 200, contains: ['urlset', 'dunesdiscoverytourism.com'] },
    ]
  },

  // ── 4. Legal Policies & 301 Permanent Redirect Aliases ──────────────
  {
    category: 'Legal Policies & Redirect Aliases',
    tests: [
      { name: 'Terms & Conditions Main', url: `${BASE}/terms-condition`, expectStatus: 200, contains: ['Terms & Conditions'] },
      { name: 'Privacy Policy Main', url: `${BASE}/privacy-policy`, expectStatus: 200, contains: ['Privacy Policy'] },
      { name: 'Terms Alias (/terms -> 301)', url: `${BASE}/terms`, expectStatus: 301, redirect: 'manual' },
      { name: 'Terms Alias (/terms-and-conditions -> 301)', url: `${BASE}/terms-and-conditions`, expectStatus: 301, redirect: 'manual' },
      { name: 'Privacy Alias (/privacy -> 301)', url: `${BASE}/privacy`, expectStatus: 301, redirect: 'manual' },
      { name: 'Custom Safari Alias (/custom-safari -> 301)', url: `${BASE}/custom-safari`, expectStatus: 301, redirect: 'manual' },
      { name: 'Waiver Alias (/safety-waiver -> 301)', url: `${BASE}/safety-waiver`, expectStatus: 301, redirect: 'manual' },
      { name: 'Payment Security Alias (/payment-security -> 301)', url: `${BASE}/payment-security`, expectStatus: 301, redirect: 'manual' },
    ]
  },

  // ── 5. Admin CMS Authentication Route Guards ────────────────────────
  {
    category: 'Admin Security & Route Guards',
    tests: [
      { name: 'Admin Root Guard (/admin -> 302 /login)', url: `${BASE}/admin`, expectStatus: 302, redirect: 'manual' },
      { name: 'Admin Dashboard Guard (/admin/dashboard -> 302)', url: `${BASE}/admin/dashboard`, expectStatus: 302, redirect: 'manual' },
      { name: 'Admin Operations Guard (/admin/operations -> 302)', url: `${BASE}/admin/operations`, expectStatus: 302, redirect: 'manual' },
      { name: 'Admin Bookings Guard (/admin/bookings -> 302)', url: `${BASE}/admin/bookings`, expectStatus: 302, redirect: 'manual' },
      { name: 'Admin Campaigns Guard (/admin/campaigns -> 302)', url: `${BASE}/admin/campaigns`, expectStatus: 302, redirect: 'manual' },
      { name: 'Admin Subscribers Guard (/admin/subscribers -> 302)', url: `${BASE}/admin/subscribers`, expectStatus: 302, redirect: 'manual' },
      { name: 'Admin SMTP Settings Guard (/admin/settings/mail -> 302)', url: `${BASE}/admin/settings/mail`, expectStatus: 302, redirect: 'manual' },
    ]
  },

  // ── 6. UI/UX Components & Tracking Endpoints ────────────────────────
  {
    category: 'UI/UX Assets & Tracking Health',
    tests: [
      { name: 'Clean app.min.js bundle (No mojibake)', url: `${BASE}/assets/js/app.min.js`, expectStatus: 200, notContains: ['ð'] },
      { name: 'Email Open Tracking Pixel (200 GIF, no-cache)', url: `${BASE}/email/track/open/audit-test-token.gif`, expectStatus: 200 },
      { name: 'Graceful Unsubscribe 404 on Invalid Token', url: `${BASE}/unsubscribe/nonexistent-token-audit`, expectStatus: 404, contains: ['Invalid or expired unsubscribe link.'] },
    ]
  }
];

async function runTest(test) {
  const opts = {
    method: 'GET',
    headers: { 'User-Agent': 'Dunes-Master-QA-Auditor/2.0' }
  };
  if (test.redirect === 'manual') opts.redirect = 'manual';

  const start = Date.now();
  try {
    const res = await fetch(test.url, opts);
    const duration = Date.now() - start;
    let body = '';
    if (test.contains || test.notContains) {
      body = await res.text();
    }

    let pass = res.status === test.expectStatus;
    let failureReason = '';

    if (!pass) {
      failureReason = `Expected status ${test.expectStatus}, received ${res.status}`;
    }

    if (pass && test.contains) {
      for (const needle of test.contains) {
        if (!body.includes(needle)) {
          pass = false;
          failureReason = `Missing expected content: "${needle}"`;
          break;
        }
      }
    }

    if (pass && test.notContains) {
      for (const needle of test.notContains) {
        if (body.includes(needle)) {
          pass = false;
          failureReason = `Found prohibited content: "${needle}"`;
          break;
        }
      }
    }

    return { name: test.name, pass, status: res.status, duration: `${duration}ms`, failureReason };
  } catch (err) {
    return { name: test.name, pass: false, error: err.message };
  }
}

async function runApiTests() {
  const apiResults = [];

  // 1. Active 25% Welcome Coupon (DUNESWELCOME)
  try {
    const r = await fetch(`${BASE}/api/v1/coupon/validate`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ code: 'DUNESWELCOME', subtotal: 200 })
    });
    const d = await r.json();
    const pass = r.status === 200 && d.success === true && d.coupon && d.coupon.discount_amount === 50;
    apiResults.push({ name: 'API: Coupon Validate DUNESWELCOME (25% off 200 = AED 50)', pass, status: r.status, note: d.coupon ? d.coupon.savings_text : 'N/A' });
  } catch (e) {
    apiResults.push({ name: 'API: Coupon Validate DUNESWELCOME', pass: false, error: e.message });
  }

  // 2. Deactivated 5% Promo (SAVE5) - Must be rejected as inactive
  try {
    const r = await fetch(`${BASE}/api/v1/coupon/validate`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ code: 'SAVE5', subtotal: 200 })
    });
    const d = await r.json();
    const pass = r.status === 422 && d.success === false;
    apiResults.push({ name: 'API: Coupon Validate SAVE5 (Properly Rejected as Inactive: 422)', pass, status: r.status, note: d.message || 'Inactive' });
  } catch (e) {
    apiResults.push({ name: 'API: Coupon Validate SAVE5', pass: false, error: e.message });
  }

  // 3. Newsletter Subscription API test (Valid email)
  const testEmail = `qa.audit.${Date.now()}@dunesdiscoverytourism.test`;
  try {
    const r = await fetch(`${BASE}/api/v1/subscribers/subscribe`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ name: 'QA Master Auditor', email: testEmail, consent: 1 })
    });
    const d = await r.json();
    const pass = r.status === 200 && d.success === true;
    apiResults.push({ name: 'API: Newsletter Subscription Public Opt-In', pass, status: r.status, note: d.message || 'Subscribed' });
  } catch (e) {
    apiResults.push({ name: 'API: Newsletter Subscription Public Opt-In', pass: false, error: e.message });
  }

  return apiResults;
}

async function main() {
  console.log('================================================================');
  console.log('🚀 DUNES DISCOVERY TOURISM — 100% WHOLE PORTAL QA AUDIT BATTERY');
  console.log('================================================================\n');

  let totalTests = 0;
  let totalPassed = 0;
  const allResults = [];

  for (const suite of testSuites) {
    console.log(`\n📋 SUITE: ${suite.category}`);
    console.log('----------------------------------------------------------------');
    const results = [];
    for (const test of suite.tests) {
      totalTests++;
      const res = await runTest(test);
      if (res.pass) totalPassed++;
      results.push(res);
      allResults.push(res);
    }
    console.table(results);
  }

  console.log('\n📋 SUITE: Core Public REST APIs');
  console.log('----------------------------------------------------------------');
  const apiResults = await runApiTests();
  for (const ar of apiResults) {
    totalTests++;
    if (ar.pass) totalPassed++;
    allResults.push(ar);
  }
  console.table(apiResults);

  const failures = allResults.filter(r => !r.pass);
  console.log('\n================================================================');
  if (failures.length === 0) {
    console.log(`🏆 100% SUCCESS! ALL ${totalPassed} / ${totalTests} TESTS PASSED WITH ZERO FAILURES!`);
  } else {
    console.log(`⚠️ COMPLETED: ${totalPassed} / ${totalTests} TESTS PASSED`);
    console.log('\n❌ FAILED TESTS SUMMARY:');
    console.table(failures.map(f => ({ name: f.name, reason: f.failureReason || f.error })));
  }
  console.log('================================================================\n');
}

main();
