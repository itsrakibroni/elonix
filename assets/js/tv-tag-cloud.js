/**
 * Elonix Smart Taxonomy Filter Widget Frontend Script
 *
 * Coordinates AJAX filtering triggers, active states, batch selection,
 * WCAG spacebar/enter keyboard listeners, and target widget events.
 *
 * @package Elonix_Toolkit
 */

/* global elementorFrontend */

jQuery(window).on("elementor/frontend/init", () => {
  elementorFrontend.hooks.addAction("frontend/element_ready/tv-tag-cloud.default", ($element) => {
    const wrap = $element[0].querySelector(".tv-tag-cloud-wrap");
    if (!wrap) {
      return;
    }

    const interaction = wrap.getAttribute("data-interaction") || "link";
    if ("link" === interaction) {
      return; // Static links navigate natively using browser default actions
    }

    const targetWidgetId = wrap.getAttribute("data-target-widget");
    if (!targetWidgetId) {
      return;
    }

    const tags = wrap.querySelectorAll(".tv-tag-item");
    let selectedTerms = [];

    const clearBtnWrap = wrap.querySelector(".tv-tag-clear-filter-wrap");
    const clearBtn = clearBtnWrap ? clearBtnWrap.querySelector(".tv-tag-clear-filter-btn") : null;

    // Fetch the target tv-post-list wrapper element dynamically
    const getTargetPostListWrap = () => {
      const targetEl = document.getElementById(targetWidgetId);
      if (targetEl) {
        return targetEl.querySelector(".tv-post-list-wrap");
      }
      const targetClassEl = document.querySelector("." + targetWidgetId);
      if (targetClassEl) {
        return targetClassEl.querySelector(".tv-post-list-wrap");
      }
      return null;
    };

    // Broadcast selected taxonomy terms list to target Post List widget via custom DOM events
    const updateTargetFilter = () => {
      const targetWrap = getTargetPostListWrap();
      if (targetWrap) {
        targetWrap.dispatchEvent(
          new CustomEvent("elonix/post_list/filter", {
            detail: { selectedTerms: selectedTerms },
          }),
        );
      }
    };

    tags.forEach((tag) => {
      // WCAG 2.1 Compliance configurations
      tag.setAttribute("role", "button");
      tag.setAttribute("tabindex", "0");
      tag.setAttribute("aria-pressed", "false");

      const handleTagSelection = (e) => {
        e.preventDefault();
        const termId = parseInt(tag.getAttribute("data-term-id"));
        if (isNaN(termId)) {
          return;
        }

        if ("ajax_filter" === interaction) {
          // Single-select live query mode
          if (tag.classList.contains("tv-active")) {
            tag.classList.remove("tv-active");
            tag.setAttribute("aria-pressed", "false");
            selectedTerms = [];
          } else {
            // Clean active classes on sibling elements
            tags.forEach((t) => {
              t.classList.remove("tv-active");
              t.setAttribute("aria-pressed", "false");
            });
            tag.classList.add("tv-active");
            tag.setAttribute("aria-pressed", "true");
            selectedTerms = [termId];
          }
          updateTargetFilter();
        } else if ("ajax_multi_select" === interaction) {
          // Multi-select live query mode
          if (tag.classList.contains("tv-active")) {
            tag.classList.remove("tv-active");
            tag.setAttribute("aria-pressed", "false");
            selectedTerms = selectedTerms.filter((id) => id !== termId);
          } else {
            tag.classList.add("tv-active");
            tag.setAttribute("aria-pressed", "true");
            selectedTerms.push(termId);
          }

          // Toggle visibility of clear filter indicator
          if (clearBtnWrap) {
            clearBtnWrap.style.display = selectedTerms.length > 0 ? "block" : "none";
          }

          updateTargetFilter();
        }
      };

      tag.addEventListener("click", handleTagSelection);

      // Keyboard event triggers for Accessibility compliance (Space & Enter keys)
      tag.addEventListener("keydown", (e) => {
        if (13 === e.keyCode || 32 === e.keyCode) {
          e.preventDefault();
          handleTagSelection(e);
        }
      });
    });

    // Clear all tags action listener
    if (clearBtn && clearBtnWrap) {
      clearBtn.addEventListener("click", (e) => {
        e.preventDefault();
        tags.forEach((tag) => {
          tag.classList.remove("tv-active");
          tag.setAttribute("aria-pressed", "false");
        });
        selectedTerms = [];
        clearBtnWrap.style.display = "none";
        updateTargetFilter();
      });
    }
  });
});
