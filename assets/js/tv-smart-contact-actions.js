/**
 * Elonix – Toolkit for Elementor Smart Contact Actions JS Engine
 *
 * Implements Vanilla JS layout bindings, dynamic timezone checking,
 * client-side conditional QR canvas generation, GA4 telemetry click triggers,
 * and WCAG 2.1 AA keyboard focus trap rules.
 *
 * @package Elonix_Toolkit
 * @version 1.0.0
 */

"use strict";

class ElonixSmartContactActions {
  /**
   * Constructor.
   *
   * @param {HTMLElement} container Widget container element.
   */
  constructor(container) {
    this.container = container;
    this.trigger = container.querySelector(".tv-sca-trigger-btn");
    this.itemsList = container.querySelector(".tv-sca-item-list");
    this.drawer = container.querySelector(".tv-sca-card-drawer");

    // Parse config
    try {
      this.config = JSON.parse(container.getAttribute("data-config")) || {};
    } catch (e) {
      this.config = {};
    }

    // Accessibility and State trackers
    this.activeFocusTrapHandler = null;
    this.activeModalKeydownHandler = null;
    this.lastFocusedElement = null;
    this.scrollTicking = false;
    this.lastScrollY = window.scrollY;

    this.init();
  }

  /**
   * Initialize widget components, check timezone status, bind events.
   */
  init() {
    // Calculate and render online/offline status tags
    this.updateStatusIndicators();

    // Set up radial alignment values if active
    this.initRadialLayout();

    // Bind events
    this.bindEvents();

    // Set up smart scroll visibility
    this.initScrollVisibility();
  }

  /**
   * Bind all click, keyboard, hover, and scroll event listeners.
   */
  bindEvents() {
    // Toggle trigger buttons
    if (this.trigger) {
      this.trigger.addEventListener("click", (e) => {
        e.preventDefault();
        this.toggleMenu();
      });
    }

    // Close QR code modal triggers
    const qrModal = this.container.querySelector(".tv-sca-qr-modal");
    if (qrModal) {
      const closeBtn = qrModal.querySelector(".tv-sca-qr-close");
      if (closeBtn) {
        closeBtn.addEventListener("click", (e) => {
          e.preventDefault();
          this.closeQrModal();
        });
      }
    }

    // Bind QR triggers to list items (desktop) and GA4 telemetry dispatch
    const actionLinks = this.container.querySelectorAll(".tv-sca-item-btn, .tv-sca-agent-link-btn, .tv-sca-trigger-btn[href]");
    actionLinks.forEach((link) => {
      link.addEventListener("click", (e) => this.handleContactClick(e, link));
    });

    // Close menus if clicking outside
    document.addEventListener("click", (e) => {
      if (!this.container.contains(e.target)) {
        const overlay = document.querySelector(".tv-sca-qr-overlay");
        if (overlay && overlay.contains(e.target)) {
          this.closeQrModal();
        } else {
          this.closeAllMenus();
        }
      }
    });
  }

  /**
   * Check business hours schedules and calculate current status.
   *
   * @return {string} Calculated status ('online', 'offline', 'lunch').
   */
  getCurrentStatus() {
    if (!this.config.enableHours || !this.config.schedule || this.config.schedule.length === 0) {
      return "online";
    }

    let dateInTimezone;
    try {
      dateInTimezone = new Date(new Date().toLocaleString("en-US", { timeZone: this.config.timezone }));
    } catch (e) {
      dateInTimezone = new Date();
    }

    const currentDay = dateInTimezone.toLocaleDateString("en-US", { weekday: "long" }).toLowerCase();
    const currentTime = dateInTimezone.getHours() * 60 + dateInTimezone.getMinutes();

    let status = "offline";

    const timeToMinutes = (timeStr) => {
      if (!timeStr) {
        return null;
      }
      const parts = timeStr.split(":");
      if (parts.length < 2) {
        return null;
      }
      return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
    };

    for (const slot of this.config.schedule) {
      if (slot.day === currentDay) {
        const openMin = timeToMinutes(slot.open);
        const closeMin = timeToMinutes(slot.close);
        if (openMin === null || closeMin === null) {
          continue;
        }

        let isOpen = false;
        if (openMin > closeMin) {
          // Spans midnight, e.g. 22:00 -> 06:00
          isOpen = currentTime >= openMin || currentTime <= closeMin;
        } else {
          isOpen = currentTime >= openMin && currentTime <= closeMin;
        }

        if (isOpen) {
          const lunchStartMin = timeToMinutes(slot.lunchStart);
          const lunchEndMin = timeToMinutes(slot.lunchEnd);
          let isLunch = false;
          if (lunchStartMin !== null && lunchEndMin !== null) {
            if (lunchStartMin > lunchEndMin) {
              isLunch = currentTime >= lunchStartMin || currentTime <= lunchEndMin;
            } else {
              isLunch = currentTime >= lunchStartMin && currentTime <= lunchEndMin;
            }
          }

          if (isLunch) {
            return "lunch";
          }
          status = "online";
        }
      }
    }

    return status;
  }

  /**
   * Update online/offline/lunch badges for all agents dynamically.
   */
  updateStatusIndicators() {
    const status = this.getCurrentStatus();
    const indicators = this.container.querySelectorAll(".tv-sca-status-indicator");

    indicators.forEach((indicator) => {
      indicator.classList.remove("tv-sca-status-online", "tv-sca-status-offline", "tv-sca-status-away", "tv-sca-status-lunch");
      let title = "Offline";
      if (status === "online") {
        indicator.classList.add("tv-sca-status-online");
        title = "Online";
      } else if (status === "lunch") {
        indicator.classList.add("tv-sca-status-lunch");
        title = "Lunch Break / Away";
      } else {
        indicator.classList.add("tv-sca-status-offline");
        title = "Offline";
      }
      indicator.setAttribute("title", title);
    });
  }

  /**
   * Calculate coordinate layouts dynamically for Radial Polar Menu layouts.
   */
  initRadialLayout() {
    if (this.config.layout !== "radial") {
      return;
    }

    const list = this.itemsList;
    const triggerBtn = this.trigger;
    if (!list || !triggerBtn) {
      return;
    }

    const triggerWidth = triggerBtn.offsetWidth || 60;
    const triggerHeight = triggerBtn.offsetHeight || 60;

    list.style.left = `${triggerWidth / 2}px`;
    list.style.top = `${triggerHeight / 2}px`;

    const items = list.querySelectorAll(".tv-sca-item");
    const count = items.length;
    if (count === 0) {
      return;
    }

    let startAngle, endAngle;
    const radius = 100;

    switch (this.config.position) {
      case "top-left":
        startAngle = 0;
        endAngle = Math.PI / 2;
        break;
      case "top-right":
        startAngle = Math.PI / 2;
        endAngle = Math.PI;
        break;
      case "bottom-left":
        startAngle = 1.5 * Math.PI;
        endAngle = 2 * Math.PI;
        break;
      case "bottom-right":
      default:
        startAngle = Math.PI;
        endAngle = 1.5 * Math.PI;
        break;
      case "center-left":
        startAngle = -Math.PI / 2;
        endAngle = Math.PI / 2;
        break;
      case "center-right":
        startAngle = Math.PI / 2;
        endAngle = 1.5 * Math.PI;
        break;
    }

    items.forEach((item, index) => {
      let angle;
      if (count === 1) {
        angle = (startAngle + endAngle) / 2;
      } else {
        angle = startAngle + (index / (count - 1)) * (endAngle - startAngle);
      }

      const x = Math.round(radius * Math.cos(angle));
      const y = Math.round(radius * Math.sin(angle));
      item.style.setProperty("--tv-sca-radial-x", `${x}px`);
      item.style.setProperty("--tv-sca-radial-y", `${y}px`);
      item.style.left = "0";
      item.style.top = "0";
    });
  }

  /**
   * Toggle open/close status of Speed dial and Contact Card drawers.
   */
  toggleMenu() {
    if (this.config.layout === "speed_dial" || this.config.layout === "radial") {
      const isOpen = this.container.classList.contains("tv-sca-dial-open");
      if (isOpen) {
        this.closeAllMenus();
      } else {
        if (this.itemsList) {
          this.itemsList.style.display = "flex";
          this.itemsList.offsetHeight; // trigger reflow
        }
        this.container.classList.add("tv-sca-dial-open");
        this.trigger.setAttribute("aria-expanded", "true");
        this.focusTrap(this.itemsList, this.trigger);
      }
    } else if (this.config.layout === "contact_card") {
      const isOpen = this.container.classList.contains("tv-sca-card-open");
      if (isOpen) {
        this.closeAllMenus();
      } else {
        if (this.drawer) {
          this.drawer.style.display = "flex";
          this.drawer.offsetHeight;
        }
        this.container.classList.add("tv-sca-card-open");
        this.trigger.setAttribute("aria-expanded", "true");
        this.focusTrap(this.drawer, this.trigger);
      }
    }
  }

  /**
   * Close all menus and reset accessibility helpers.
   */
  closeAllMenus() {
    if (this.container.classList.contains("tv-sca-dial-open")) {
      this.container.classList.remove("tv-sca-dial-open");
      if (this.trigger) {
        this.trigger.setAttribute("aria-expanded", "false");
      }
      const list = this.itemsList;
      if (list) {
        setTimeout(() => {
          if (!this.container.classList.contains("tv-sca-dial-open")) {
            list.style.display = "none";
          }
        }, 300);
      }
    }

    if (this.container.classList.contains("tv-sca-card-open")) {
      this.container.classList.remove("tv-sca-card-open");
      if (this.trigger) {
        this.trigger.setAttribute("aria-expanded", "false");
      }
      const dr = this.drawer;
      if (dr) {
        setTimeout(() => {
          if (!this.container.classList.contains("tv-sca-card-open")) {
            dr.style.display = "none";
          }
        }, 300);
      }
    }

    if (this.activeFocusTrapHandler) {
      this.container.removeEventListener("keydown", this.activeFocusTrapHandler);
      this.activeFocusTrapHandler = null;
    }
  }

  /**
   * Handle contact item redirects, telemetries, or conditionally show QR overlays.
   *
   * @param {Event} e Click event object.
   * @param {HTMLElement} itemBtn The clicked button.
   */
  handleContactClick(e, itemBtn) {
    const isMobile = window.innerWidth <= 1024;

    // 1. Google Analytics telemetry track
    this.trackClick(itemBtn);

    // If QR option is disabled or on mobile, let the default link redirection execute
    if (!this.config.enableQr || isMobile) {
      const href = itemBtn.getAttribute("href");
      if (href === "#" || !href) {
        e.preventDefault();
      }
      return;
    }

    // On desktop, intercept and render QR modal code instead
    e.preventDefault();

    const parentItem = itemBtn.closest(".tv-sca-item, .tv-sca-agent-row");
    const href = itemBtn.getAttribute("href") || "#";

    let qrData = href;
    if (this.config.qrType === "custom_qr" && this.config.qrVal) {
      qrData = this.config.qrVal;
    } else if (this.config.qrType === "vcard_qr") {
      const name = this.config.vcardName || "Contact";
      const phone = this.config.vcardPhone || "";
      const email = this.config.vcardEmail || "";
      qrData = `BEGIN:VCARD\nVERSION:3.0\nN:${name}\nFN:${name}\nTEL;TYPE = CELL:${phone}\nEMAIL;TYPE = PREF,INTERNET:${email}\nEND:VCARD`;
    }

    this.openQrModal(qrData);
  }

  /**
   * Push GA4 telemetry events to the GTM / global dataLayer.
   *
   * @param {HTMLElement} itemBtn The clicked button.
   */
  trackClick(itemBtn) {
    if (!this.config.tracking) {
      return;
    }

    const parentItem = itemBtn.closest(".tv-sca-item, .tv-sca-agent-row");
    const actionType = parentItem ? parentItem.getAttribute("data-action") : "custom";

    let label = "Trigger";
    if (parentItem) {
      const h5 = parentItem.querySelector("h5");
      label = h5 ? h5.innerText : itemBtn.getAttribute("aria-label") || "Contact Item";
    } else {
      label = itemBtn.getAttribute("aria-label") || "Trigger Button";
    }

    const dataLayer = window.dataLayer || [];
    dataLayer.push({
      event: "tv_smart_contact_click",
      widget_id: this.config.id,
      action_type: actionType,
      action_label: label,
      device_category: window.innerWidth <= 1024 ? "mobile" : "desktop",
      timestamp: new Date().toISOString(),
    });
  }

  /**
   * Load client QRious JS library dynamically and execute callback.
   *
   * @param {Function} callback Action execution when loaded.
   */
  loadQrLibrary(callback) {
    if (window.QRious) {
      callback();
      return;
    }

    const existing = document.querySelector('script[src*="qrious.min.js"]');
    if (existing) {
      const checker = setInterval(() => {
        if (window.QRious) {
          clearInterval(checker);
          callback();
        }
      }, 50);
      return;
    }

    const script = document.createElement("script");
    const currentScript = document.querySelector('script[src*="tv-smart-contact-actions"]');
    script.src = currentScript ? currentScript.src.replace('tv-smart-contact-actions.js', 'vendor/qrious.min.js') : "/wp-content/plugins/elonix/assets/js/vendor/qrious.min.js";
    script.async = true;
    script.onload = () => {
      callback();
    };
    script.onerror = () => {};
    document.head.appendChild(script);
  }

  /**
   * Open QR modal scan target.
   *
   * @param {string} qrData String data values to render.
   */
  openQrModal(qrData) {
    this.loadQrLibrary(() => {
      const modal = this.container.querySelector(".tv-sca-qr-modal");
      if (!modal) {
        return;
      }
      const canvas = modal.querySelector(".tv-sca-qr-canvas");
      if (!canvas) {
        return;
      }
      try {
        new QRious({
          element: canvas,
          value: qrData,
          size: 200,
          background: "#ffffff",
          foreground: "#333333",
          level: "H",
        });
      } catch (e) {}

      let overlay = document.querySelector(".tv-sca-qr-overlay");
      if (!overlay) {
        overlay = document.createElement("div");
        overlay.className = "tv-sca-qr-overlay";
        document.body.appendChild(overlay);
      }
      overlay.style.display = "block";
      modal.style.display = "flex";
      modal.offsetHeight;
      modal.classList.add("tv-sca-qr-active");
      this.modalFocusTrap(modal, overlay);
    });
  }

  /**
   * Close client QR Modal scan target.
   */
  closeQrModal() {
    const modal = this.container.querySelector(".tv-sca-qr-modal");
    const overlay = document.querySelector(".tv-sca-qr-overlay");
    if (modal) {
      modal.classList.remove("tv-sca-qr-active");
      setTimeout(() => {
        modal.style.display = "none";
      }, 300);
    }
    if (overlay) {
      overlay.style.display = "none";
    }

    if (this.activeModalKeydownHandler) {
      document.removeEventListener("keydown", this.activeModalKeydownHandler);
      this.activeModalKeydownHandler = null;
    }

    if (this.lastFocusedElement) {
      this.lastFocusedElement.focus();
    }
  }

  /**
   * Loop focus flow inside the open speed dial list or contact card.
   *
   * @param {HTMLElement} trapContainer Element to contain focus inside.
   * @param {HTMLElement} triggerBtn Reset focus target.
   */
  focusTrap(trapContainer, triggerBtn) {
    const focusable = trapContainer.querySelectorAll('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (focusable.length === 0) {
      return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (this.activeFocusTrapHandler) {
      this.container.removeEventListener("keydown", this.activeFocusTrapHandler);
    }

    this.activeFocusTrapHandler = (e) => {
      if (e.key === "Tab") {
        if (e.shiftKey) {
          if (document.activeElement === first) {
            last.focus();
            e.preventDefault();
          }
        } else {
          if (document.activeElement === last) {
            first.focus();
            e.preventDefault();
          }
        }
      } else if (e.key === "Escape") {
        e.preventDefault();
        this.closeAllMenus();
        triggerBtn.focus();
      }
    };

    this.container.addEventListener("keydown", this.activeFocusTrapHandler);
  }

  /**
   * Loop focus flow inside the open QR Code Modal box.
   *
   * @param {HTMLElement} modal Modal box container.
   * @param {HTMLElement} overlay Backdrop element.
   */
  modalFocusTrap(modal, overlay) {
    this.lastFocusedElement = document.activeElement;
    const focusable = modal.querySelectorAll('button, canvas, [tabindex]:not([tabindex="-1"])');
    if (focusable.length === 0) {
      return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    first.focus();

    this.activeModalKeydownHandler = (e) => {
      if (e.key === "Tab") {
        if (e.shiftKey) {
          if (document.activeElement === first) {
            last.focus();
            e.preventDefault();
          }
        } else {
          if (document.activeElement === last) {
            first.focus();
            e.preventDefault();
          }
        }
      } else if (e.key === "Escape") {
        e.preventDefault();
        this.closeQrModal();
      }
    };

    document.addEventListener("keydown", this.activeModalKeydownHandler);
  }

  /**
   * Setup event bindings to track scroll offsets and toggle visibility classes.
   */
  initScrollVisibility() {
    if (!this.config.smartVisibility) {
      return;
    }

    window.addEventListener(
      "scroll",
      () => {
        if (!this.scrollTicking) {
          window.requestAnimationFrame(() => {
            const currentScrollY = window.scrollY;
            // If scroll down more than 100px and scrolling down, hide container
            if (currentScrollY > this.lastScrollY && currentScrollY > 100) {
              this.container.classList.add("tv-sca-hidden");
            } else {
              // Scroll up shows
              this.container.classList.remove("tv-sca-hidden");
            }

            this.lastScrollY = currentScrollY;
            this.scrollTicking = false;
          });
          this.scrollTicking = true;
        }
      },
      { passive: true },
    );
  }
}

// Instantiate widgets on page load
const tvInitSmartContactActions = () => {
  const widgets = document.querySelectorAll(".tv-sca-container");
  widgets.forEach((widget) => {
    if (!widget.hasAttribute("data-initialized")) {
      new ElonixSmartContactActions(widget);
      widget.setAttribute("data-initialized", "true");
    }
  });
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", tvInitSmartContactActions);
} else {
  tvInitSmartContactActions();
}

// Elementor Preview registration
const tvRegisterElementorSmartContactActions = () => {
  if (window.elementorFrontend && elementorFrontend.hooks) {
    elementorFrontend.hooks.addAction("frontend/element_ready/tv-smart-contact-actions.default", ($scope) => {
      if (!$scope || !$scope.length) {
        return;
      }
      const target = $scope[0].querySelector(".tv-sca-container");
      if (target && !target.hasAttribute("data-initialized")) {
        new ElonixSmartContactActions(target);
        target.setAttribute("data-initialized", "true");
      }
    });
  }
};

if (window.elementorFrontend) {
  tvRegisterElementorSmartContactActions();
} else {
  window.addEventListener("elementor/frontend/init", tvRegisterElementorSmartContactActions);
}
