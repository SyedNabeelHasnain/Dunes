/**
 * Dunes Discovery Tourism - Embedded Tour Card Widget (v1.0.0)
 * Official syndication widget for partner travel portals, blogs, and directories.
 * Provides real-time tour pricing, accurate pickup/drop-off schedules, and instant booking attribution.
 * License: Proprietary - Dunes Discovery Tourism LLC (DET License #1430583)
 */
(function() {
  'use strict';

  // Prevent multiple initializations of the same script
  if (window.__dunes_widget_loaded) {
    if (typeof window.DunesWidget !== 'undefined' && typeof window.DunesWidget.init === 'function') {
      window.DunesWidget.init();
    }
    return;
  }
  window.__dunes_widget_loaded = true;

  // Detect script source to resolve API endpoint dynamically if hosted on custom domain
  var scriptTag = document.currentScript || (function() {
    var scripts = document.getElementsByTagName('script');
    for (var i = scripts.length - 1; i >= 0; i--) {
      if (scripts[i].src && scripts[i].src.indexOf('safari-card.js') !== -1) {
        return scripts[i];
      }
    }
    return null;
  })();

  var defaultApiBase = 'https://dunesdiscoverytourism.com';
  if (scriptTag && scriptTag.src) {
    try {
      var urlObj = new URL(scriptTag.src);
      defaultApiBase = urlObj.origin;
    } catch (e) {
      // Use fallback
    }
  }

  // Inject required CSS once into the document head
  function injectStyles() {
    var styleId = 'dunes-safari-widget-styles';
    if (document.getElementById(styleId)) return;

    var css = `
      .ddt-widget-card {
        box-sizing: border-box;
        max-width: 380px;
        width: 100%;
        margin: 16px auto;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.08);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        text-align: left;
        line-height: 1.5;
        color: #1e293b;
      }
      .ddt-widget-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.12);
      }
      .ddt-widget-card * {
        box-sizing: border-box;
      }
      .ddt-widget-media {
        position: relative;
        width: 100%;
        height: 190px;
        background: #0f172a;
        overflow: hidden;
      }
      .ddt-widget-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.4s ease;
      }
      .ddt-widget-card:hover .ddt-widget-media img {
        transform: scale(1.04);
      }
      .ddt-widget-badge-det {
        position: absolute;
        top: 12px;
        left: 12px;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(4px);
        color: #f59e0b;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 4px 10px;
        border-radius: 9999px;
        border: 1px solid rgba(245, 158, 11, 0.3);
        text-transform: uppercase;
      }
      .ddt-widget-badge-rating {
        position: absolute;
        bottom: 12px;
        right: 12px;
        background: rgba(255, 255, 255, 0.95);
        color: #0f172a;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      }
      .ddt-widget-badge-rating svg {
        width: 13px;
        height: 13px;
        fill: #f59e0b;
      }
      .ddt-widget-body {
        padding: 16px 20px 20px;
      }
      .ddt-widget-category {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #d97706;
        margin-bottom: 4px;
      }
      .ddt-widget-title {
        margin: 0 0 10px;
        font-size: 17px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.35;
      }
      .ddt-widget-title a {
        color: inherit;
        text-decoration: none;
      }
      .ddt-widget-title a:hover {
        color: #d97706;
      }
      .ddt-widget-timings {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 12px;
        color: #475569;
        margin-bottom: 14px;
        display: flex;
        flex-direction: column;
        gap: 3px;
      }
      .ddt-widget-timing-row {
        display: flex;
        align-items: center;
        gap: 6px;
      }
      .ddt-widget-timing-row strong {
        color: #1e293b;
      }
      .ddt-widget-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
      }
      .ddt-widget-price-wrap {
        display: flex;
        flex-direction: column;
      }
      .ddt-widget-price-label {
        font-size: 10px;
        text-transform: uppercase;
        font-weight: 600;
        color: #64748b;
      }
      .ddt-widget-price {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
      }
      .ddt-widget-price small {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        margin-left: 2px;
      }
      .ddt-widget-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f59e0b;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff !important;
        font-size: 13px;
        font-weight: 700;
        padding: 9px 18px;
        border-radius: 8px;
        text-decoration: none !important;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);
        transition: opacity 0.2s ease, transform 0.2s ease;
      }
      .ddt-widget-btn:hover {
        opacity: 0.95;
        transform: scale(1.02);
      }
      .ddt-widget-attribution {
        margin-top: 10px;
        font-size: 10px;
        color: #94a3b8;
        text-align: center;
      }
      .ddt-widget-attribution a {
        color: #64748b;
        text-decoration: underline;
        font-weight: 600;
      }
      .ddt-widget-attribution a:hover {
        color: #d97706;
      }
    `;

    var style = document.createElement('style');
    style.id = styleId;
    style.type = 'text/css';
    if (style.styleSheet) {
      style.styleSheet.cssText = css;
    } else {
      style.appendChild(document.createTextNode(css));
    }
    document.head.appendChild(style);
  }

  // Render HTML for a single tour card
  function renderCard(tour, partnerSlug) {
    var refParam = partnerSlug ? encodeURIComponent(partnerSlug) : 'embed_widget';
    var trackingUrl = tour.url + (tour.url.indexOf('?') === -1 ? '?' : '&') +
      'utm_source=' + refParam +
      '&utm_medium=syndicated_widget' +
      '&utm_campaign=dubai_safari_syndication';

    var pickupInfo = (tour.timings && tour.timings.pickup) ? tour.timings.pickup : 'Hotel Pickup Included';
    var dropoffInfo = (tour.timings && tour.timings.dropoff) ? tour.timings.dropoff : 'Return Drop-off';

    return `
      <div class="ddt-widget-card" itemscope itemtype="https://schema.org/Product">
        <div class="ddt-widget-media">
          <img src="${escapeHtml(tour.hero_image)}" alt="${escapeHtml(tour.name)}" loading="lazy" itemprop="image">
          <div class="ddt-widget-badge-det">DET Licensed #1430583</div>
          <div class="ddt-widget-badge-rating">
            <svg viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
            <span>4.9 / 5</span>
          </div>
        </div>
        <div class="ddt-widget-body">
          <div class="ddt-widget-category">${escapeHtml(tour.category)}</div>
          <h3 class="ddt-widget-title" itemprop="name">
            <a href="${escapeHtml(trackingUrl)}" target="_blank" rel="noopener noreferrer">${escapeHtml(tour.name)}</a>
          </h3>
          <div class="ddt-widget-timings">
            <div class="ddt-widget-timing-row">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              <span><strong>Pickup:</strong> ${escapeHtml(pickupInfo)}</span>
            </div>
            <div class="ddt-widget-timing-row">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
              <span><strong>Return:</strong> ${escapeHtml(dropoffInfo)}</span>
            </div>
          </div>
          <div class="ddt-widget-footer" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
            <div class="ddt-widget-price-wrap">
              <span class="ddt-widget-price-label">Guaranteed Best Price</span>
              <span class="ddt-widget-price">
                <span itemprop="priceCurrency">AED</span> <span itemprop="price">${tour.starting_price_aed}</span>
                <small>/ person</small>
              </span>
            </div>
            <a href="${escapeHtml(trackingUrl)}" class="ddt-widget-btn" target="_blank" rel="noopener noreferrer">
              Book Direct & Save
            </a>
          </div>
          <div class="ddt-widget-attribution">
            Official Partner of <a href="${escapeHtml(tour.url)}?utm_source=${refParam}" target="_blank" rel="noopener">Dunes Discovery Tourism</a>
          </div>
        </div>
      </div>
    `;
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Scan document for widget containers and populate them
  function initWidgets() {
    injectStyles();

    var containers = document.querySelectorAll('.dunes-safari-widget, [data-dunes-tour], [data-dunes-widget]');
    if (!containers || containers.length === 0) return;

    containers.forEach(function(container) {
      if (container.getAttribute('data-dunes-initialized') === 'true') return;

      var apiBase = container.getAttribute('data-api-base') || defaultApiBase;
      var tourSlug = container.getAttribute('data-tour') || 'evening-desert-safari';
      var partnerRef = container.getAttribute('data-partner') || '';

      var apiUrl = apiBase.replace(/\/+$/, '') + '/api/v1/syndication/tours?tour=' + encodeURIComponent(tourSlug);

      container.innerHTML = '<div style="font-family:sans-serif;font-size:12px;color:#94a3b8;padding:12px;text-align:center;">Loading safari details...</div>';

      fetch(apiUrl, {
        headers: { 'Accept': 'application/json' }
      })
      .then(function(res) {
        if (!res.ok) throw new Error('Network error ' + res.status);
        return res.json();
      })
      .then(function(data) {
        if (data && data.tours && data.tours.length > 0) {
          var tour = data.tours[0];
          container.innerHTML = renderCard(tour, partnerRef);
          container.setAttribute('data-dunes-initialized', 'true');
        } else {
          container.innerHTML = '';
        }
      })
      .catch(function(err) {
        console.error('Dunes Safari Widget Error:', err);
        container.innerHTML = '';
      });
    });
  }

  // Expose global API
  window.DunesWidget = {
    init: initWidgets
  };

  // Run automatically when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWidgets);
  } else {
    initWidgets();
  }
})();
