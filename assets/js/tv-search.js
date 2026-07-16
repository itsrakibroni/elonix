/**
 * Elonix Search Widget Frontend Script
 *
 * Premium, performance-optimized, vanilla ES6 script for Elonix Search.
 * Completely independent of jQuery.
 */

/* global elementorFrontend, ajaxurl */

jQuery(window).on("elementor/frontend/init", () => {
  elementorFrontend.hooks.addAction("frontend/element_ready/tv-search.default", ($element) => {
    const container = $element[0].querySelector(".tv-search-container");
    if (!container) {
      return;
    }

    // Parse attributes
    const widgetId = container.getAttribute("data-widget-id");
    const nonce = container.getAttribute("data-nonce");
    const limit = parseInt(container.getAttribute("data-limit") || 5, 10);
    const postTypes = JSON.parse(container.getAttribute("data-post-types") || '["post", "page"]');
    const layout = container.getAttribute("data-layout") || "classic";
    const showFields = JSON.parse(container.getAttribute("data-show-fields") || "{}");
    const ajaxUrl = container.getAttribute("data-ajax-url") || (typeof ajaxurl !== "undefined" ? ajaxurl : "");
    const resultsLayout = container.getAttribute("data-results-layout") || "list";

    // DOM selectors
    const input = container.querySelector(".tv-search-input");
    const formWrapper = container.querySelector(".tv-search-form-wrapper");
    const resultsContainer = container.querySelector(".tv-search-results-container");
    const triggerBtn = container.querySelector(".tv-search-trigger-btn");
    const closeBtn = container.querySelector(".tv-search-close-btn");
    const backdrop = container.querySelector(".tv-search-backdrop");
    const srStatus = container.querySelector(".tv-search-sr-status");

    // Cache, Debounce and Abort Controllers
    const searchCache = new Map();
    let abortController = null;
    let debounceTimer = null;
    let selectedIndex = -1;

    // 1. Setup Trigger Toggles (for overlay, fullscreen, offcanvas layouts)
    if (["overlay", "fullscreen", "offcanvas"].includes(layout)) {
      if (triggerBtn) {
        triggerBtn.addEventListener("click", (e) => {
          e.preventDefault();
          openPanel();
        });
      }

      if (closeBtn) {
        closeBtn.addEventListener("click", (e) => {
          e.preventDefault();
          closePanel();
        });
      }

      if (backdrop) {
        backdrop.addEventListener("click", (e) => {
          e.preventDefault();
          closePanel();
        });
      }

      // Focus trap logic inside modal
      formWrapper.addEventListener("keydown", (e) => {
        if (e.key !== "Tab") {
          return;
        }

        const focusables = formWrapper.querySelectorAll("input, button, a[href]");
        if (!focusables.length) {
          return;
        }

        const firstFocusable = focusables[0];
        const lastFocusable = focusables[focusables.length - 1];

        if (e.shiftKey) {
          // Shift + Tab
          if (document.activeElement === firstFocusable) {
            e.preventDefault();
            lastFocusable.focus();
          }
        } else {
          // Tab
          if (document.activeElement === lastFocusable) {
            e.preventDefault();
            firstFocusable.focus();
          }
        }
      });
    }

    // 2. Input Change / Type Handler
    input.addEventListener("input", () => {
      const value = input.value.trim();

      if (debounceTimer) {
        clearTimeout(debounceTimer);
      }

      if (value.length < 3) {
        clearResults();
        return;
      }

      debounceTimer = setTimeout(() => {
        performSearch(value);
      }, 300);
    });

    // 3. Keyboard Navigation handler on input field
    input.addEventListener("keydown", (e) => {
      const listItems = resultsContainer.querySelectorAll(".tv-search-result-item");

      if (e.key === "Escape") {
        e.preventDefault();
        clearResults();
        if (["overlay", "fullscreen", "offcanvas"].includes(layout) && formWrapper.classList.contains("tv-search-panel-visible")) {
          closePanel();
        }
        return;
      }

      if (!listItems.length) {
        return;
      }

      if (e.key === "ArrowDown") {
        e.preventDefault();
        selectedIndex++;
        if (selectedIndex >= listItems.length) {
          selectedIndex = 0;
        }
        highlightItem(listItems);
      } else if (e.key === "ArrowUp") {
        e.preventDefault();
        selectedIndex--;
        if (selectedIndex < 0) {
          selectedIndex = listItems.length - 1;
        }
        highlightItem(listItems);
      } else if (e.key === "Enter") {
        if (selectedIndex >= 0 && selectedIndex < listItems.length) {
          e.preventDefault();
          const selectedLink = listItems[selectedIndex].querySelector("a");
          if (selectedLink) {
            window.location.href = selectedLink.getAttribute("href");
          }
        }
      }
    });

    // Close dropdown if clicked outside of container
    document.addEventListener("click", (e) => {
      if (!container.contains(e.target)) {
        clearResults();
      }
    });

    // Core Search Execution
    function performSearch(term) {
      const cacheKey = term.toLowerCase();

      // Local cache check
      if (searchCache.has(cacheKey)) {
        renderResults(searchCache.get(cacheKey));
        return;
      }

      // Abort previous pending query request
      if (abortController) {
        abortController.abort();
      }
      abortController = new AbortController();

      // Show Loading state (Skeleton lines placeholder animation)
      let skeletonHtml = '<div class="tv-search-skeleton-wrapper">';
      for (let i = 0; i < 3; i++) {
        skeletonHtml += `
					<div class="tv-search-skeleton-item">
						<div class="tv-search-skeleton-thumbnail"></div>
						<div class="tv-search-skeleton-content">
							<div class="tv-search-skeleton-title"></div>
							<div class="tv-search-skeleton-excerpt"></div>
							<div class="tv-search-skeleton-excerpt short"></div>
						</div>
					</div>
				`;
      }
      skeletonHtml += "</div>";
      resultsContainer.innerHTML = skeletonHtml;
      resultsContainer.classList.add("tv-has-results");
      input.setAttribute("aria-expanded", "true");
      if (srStatus) {
        srStatus.textContent = "Searching...";
      }

      const formData = new FormData();
      formData.append("action", "tv_live_search");
      formData.append("nonce", nonce);
      formData.append("term", term);
      formData.append("limit", limit);
      postTypes.forEach((type) => formData.append("post_types[]", type));

      fetch(ajaxUrl, {
        method: "POST",
        body: formData,
        signal: abortController.signal,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Network error");
          }
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            const results = data.data.results || [];
            searchCache.set(cacheKey, results);
            renderResults(results);
          } else {
            showError(data.data.message || "Error occurred");
          }
        })
        .catch((error) => {
          if (error.name === "AbortError") {
            return; // Ignored as request was deliberately aborted
          }
          showError("Search query failed.");
        });
    }

    // Render Dropdown Results HTML markup
    function renderResults(results) {
      resultsContainer.innerHTML = "";
      selectedIndex = -1;
      input.removeAttribute("aria-activedescendant");

      if (!results.length) {
        resultsContainer.innerHTML = '<div class="tv-search-no-results">No matching results found</div>';
        if (srStatus) {
          srStatus.textContent = "No results found.";
        }
        return;
      }

      const ul = document.createElement("ul");
      ul.className = `tv-search-results-list tv-results-layout-${resultsLayout}`;
      ul.setAttribute("role", "presentation");

      results.forEach((item, index) => {
        const li = document.createElement("li");
        li.className = "tv-search-result-item";
        li.setAttribute("role", "option");
        li.setAttribute("id", `tv-search-item-${widgetId}-${index}`);
        li.setAttribute("aria-selected", "false");

        let itemHtml = `<a href="${item.url}" class="tv-search-result-link" tabindex="-1">`;

        // Render Image if enabled
        if (showFields.image && item.image) {
          itemHtml += `
						<div class="tv-search-result-image">
							<img src="${item.image}" alt="${item.title}" />
						</div>
					`;
        }

        itemHtml += '<div class="tv-search-result-content">';

        // Render Category if enabled
        if (showFields.category && item.category) {
          itemHtml += `<span class="tv-search-result-category">${item.category}</span>`;
        }

        // Render Title if enabled
        if (showFields.title) {
          itemHtml += `<h4 class="tv-search-result-title">${item.title}</h4>`;
        }

        // Render Excerpt if enabled
        if (showFields.excerpt && item.excerpt) {
          itemHtml += `<p class="tv-search-result-excerpt">${item.excerpt}</p>`;
        }

        // Metadata check (Author, Date)
        let metaHtml = "";
        if (showFields.author && item.author) {
          metaHtml += `<span class="tv-search-result-author">By ${item.author}</span>`;
        }
        if (showFields.date && item.date) {
          if (metaHtml) {
            metaHtml += " &bull; ";
          }
          metaHtml += `<span class="tv-search-result-date">${item.date}</span>`;
        }

        if (metaHtml) {
          itemHtml += `<div class="tv-search-result-meta">${metaHtml}</div>`;
        }

        itemHtml += "</div></a>";
        li.innerHTML = itemHtml;

        ul.appendChild(li);
      });

      resultsContainer.appendChild(ul);
      if (srStatus) {
        srStatus.textContent = `${results.length} search results found. Use up and down arrow keys to navigate.`;
      }
    }

    // Helper: Highlight Selected Keyboard Item
    function highlightItem(items) {
      items.forEach((item, index) => {
        if (index === selectedIndex) {
          item.classList.add("tv-selected");
          item.setAttribute("aria-selected", "true");
          input.setAttribute("aria-activedescendant", item.id);
          item.scrollIntoView({ block: "nearest" });
        } else {
          item.classList.remove("tv-selected");
          item.setAttribute("aria-selected", "false");
        }
      });
    }

    // Helper: Clear/Reset Live Dropdown Display
    function clearResults() {
      resultsContainer.innerHTML = "";
      resultsContainer.classList.remove("tv-has-results");
      input.setAttribute("aria-expanded", "false");
      input.removeAttribute("aria-activedescendant");
      selectedIndex = -1;
      if (abortController) {
        abortController.abort();
        abortController = null;
      }
    }

    // Helper: Output Error state messages
    function showError(message) {
      resultsContainer.innerHTML = `<div class="tv-search-error">${message}</div>`;
      resultsContainer.classList.add("tv-has-results");
      if (srStatus) {
        srStatus.textContent = message;
      }
    }

    // Helper: Open Modal Overlay Panel
    function openPanel() {
      formWrapper.classList.remove("tv-search-panel-hidden");
      formWrapper.classList.add("tv-search-panel-visible");
      if (triggerBtn) {
        triggerBtn.setAttribute("aria-expanded", "true");
      }
      if (["fullscreen", "offcanvas"].includes(layout)) {
        document.body.classList.add("tv-search-lock-scroll");
      }
      setTimeout(() => {
        input.focus();
      }, 100);
    }

    // Helper: Close Modal Overlay Panel
    function closePanel() {
      formWrapper.classList.remove("tv-search-panel-visible");
      formWrapper.classList.add("tv-search-panel-hidden");
      if (triggerBtn) {
        triggerBtn.setAttribute("aria-expanded", "false");
        triggerBtn.focus();
      }
      document.body.classList.remove("tv-search-lock-scroll");
      clearResults();
    }
  });
});
