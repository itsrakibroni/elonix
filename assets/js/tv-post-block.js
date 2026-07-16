/**
 * Elonix Premium Post Block Frontend script
 *
 * Handles AJAX category/tag filters, live searches with debounce,
 * abort controller HTTP cancels, shimmers, and WCAG accessibility keyboard controls.
 *
 * Zero jQuery dependency.
 *
 * @package Elonix_Toolkit
 */

/* global elementorFrontend */

jQuery(window).on("elementor/frontend/init", () => {
  const initTvPostBlock = ($element) => {
    const wrap = $element[0].querySelector(".tv-post-block-wrap");
    if (!wrap) {
      return;
    }
    const container = wrap.querySelector(".tv-post-block-container");
    if (!container) {
      return;
    }
    // 1. Settings & State Management
    const rawSettings = wrap.getAttribute("data-settings");
    let settings = {};
    try {
      settings = JSON.parse(rawSettings);
    } catch (e) {
      return;
    }

    // Get localization strings from container data attributes
    const rawI18n = wrap.getAttribute("data-i18n");
    let i18n = {
      prev: "Prev",
      next: "Next",
      page: "Go to page %d",
      loading: "Loading posts...",
      loaded: "Posts loaded.",
      no_posts: "No posts found.",
    };
    if (rawI18n) {
      try {
        i18n = JSON.parse(rawI18n);
      } catch (e) {
        // Fail silently
      }
    }
    // Find Screen Reader Announcer
    const srAnnouncer = wrap.querySelector(".tv-sr-announcer");
    const announceToScreenReader = (message) => {
      if (srAnnouncer) {
        srAnnouncer.textContent = message;
      }
    };
    let currentPage = parseInt(settings.paged) || 1;
    let maxPages = parseInt(wrap.getAttribute("data-max-pages")) || 1;
    let activeCategory = 0;
    let activeTag = 0;
    let activeAuthor = 0;
    let searchPhrase = "";
    let isLoading = false;
    let abortController = null;
    let searchDebounceTimer = null;
    const paginationType = settings.pagination_type || "none";
    const layout = settings.layout || "style_1";
    // 2. Main AJAX Retrieval Pipeline
    const fetchPostsData = (isAppend = false) => {
      isLoading = true;
      // Trigger abort controller to stop pending AJAX request
      if (abortController) {
        abortController.abort();
      }
      abortController = new AbortController();
      const signal = abortController.signal;
      // Apply loading state classes and ARIA states
      wrap.classList.add("loading-active");
      container.setAttribute("aria-busy", "true");
      announceToScreenReader(i18n.loading);
      // Set loading spinner DOM if load_more pagination is active
      const loadMoreBtn = wrap.querySelector(".tv-post-block-load-more");
      const loadMoreSpinner = loadMoreBtn ? loadMoreBtn.querySelector(".tv-btn-spinner") : null;
      if (loadMoreBtn && isAppend) {
        loadMoreBtn.setAttribute("disabled", "true");
        if (loadMoreSpinner) {
          loadMoreSpinner.style.display = "inline-block";
        }
      }
      // Create skeleton placeholders matching the layout grid properties
      const skeletons = [];
      const skeletonsCount = isAppend ? 2 : parseInt(settings.limit) || 4;
      if (!isAppend) {
        container.innerHTML = "";
        if (document.activeElement !== searchInput) {
          const yOffset = -100; // Account for sticky headers
          const y = wrap.getBoundingClientRect().top + window.pageYOffset + yOffset;
          window.scrollTo({ top: y, behavior: "smooth" });
        }
      }
      for (let i = 0; i < skeletonsCount; i++) {
        const sk = document.createElement("div");
        sk.className = `tv-post-block-card tv-skeleton-card tv-layout-${layout}`;
        sk.innerHTML = `
						<div class="tv-skeleton-img"></div>
						<div class="tv-post-block-content">
							<div class="tv-skeleton-line tv-skeleton-title"></div>
							<div class="tv-skeleton-line tv-skeleton-meta"></div>
							<div class="tv-skeleton-line tv-skeleton-excerpt"></div>
						</div>
					`;
        container.appendChild(sk);
        skeletons.push(sk);
      }
      // Formulate AJAX Body parameters
      const formData = new FormData();
      formData.append("action", wrap.getAttribute("data-action") || "tv_post_block_fetch_posts");
      formData.append("security", wrap.getAttribute("data-nonce"));
      formData.append("paged", currentPage);
      formData.append("category", activeCategory);
      formData.append("tag", activeTag);
      formData.append("author", activeAuthor);
      formData.append("search", searchPhrase);
      const archiveVars = wrap.getAttribute("data-archive-vars") || "";
      if (archiveVars) {
        formData.append("archive_vars", archiveVars);
      }
      // Construct serialized settings payload recursively
      const appendSettings = (obj, prefix = "") => {
        for (const key in obj) {
          if (Object.prototype.hasOwnProperty.call(obj, key)) {
            const val = obj[key];
            const formKey = prefix ? `${prefix}[${key}]` : `settings[${key}]`;
            if (val !== null && typeof val === "object") {
              appendSettings(val, formKey);
            } else {
              formData.append(formKey, val !== null ? val : "");
            }
          }
        }
      };
      appendSettings(settings);
      // Execute asynchronous HTTP request
      fetch(wrap.getAttribute("data-ajax-url"), {
        method: "POST",
        body: formData,
        signal: signal,
      })
        .then((res) => {
          if (signal.aborted) {
            return;
          }
          return res.json();
        })
        .then((res) => {
          if (signal.aborted || !res) {
            return;
          }
          // Clear out skeleton cards
          skeletons.forEach((sk) => sk.remove());
          wrap.classList.remove("loading-active");
          container.setAttribute("aria-busy", "false");
          if (res.success && res.data) {
            const htmlContent = res.data.html || "";
            maxPages = parseInt(res.data.max_num_pages) || 1;

            if (isAppend) {
              const tempDiv = document.createElement("div");
              tempDiv.innerHTML = htmlContent;
              while (tempDiv.firstChild) {
                container.appendChild(tempDiv.firstChild);
              }
            } else {
              container.innerHTML = htmlContent || ` <div class = "tv-no-posts" > ${i18n.no_posts} </div> `;
            }

            // Render/update numeric pagination wrapper if numeric pages are active
            if (paginationType === "ajax_numeric") {
              updateNumericPaginationMarkup();
            }

            // Update Load More & Infinite scroll display
            updateLoadMoreTriggerStates();
            announceToScreenReader(i18n.loaded);
          } else {
            announceToScreenReader(i18n.no_posts);
          }
        })
        .catch((err) => {
          if (signal.aborted) {
            return;
          }
          skeletons.forEach((sk) => sk.remove());
          wrap.classList.remove("loading-active");
          container.setAttribute("aria-busy", "false");
          announceToScreenReader(i18n.no_posts);
        })
        .finally(() => {
          if (signal.aborted) {
            return;
          }
          isLoading = false;
          if (loadMoreBtn) {
            loadMoreBtn.removeAttribute("disabled");
            if (loadMoreSpinner) {
              loadMoreSpinner.style.display = "none";
            }
          }
        });
    };
    // 3. UI Trigger Update Handlers
    const updateLoadMoreTriggerStates = () => {
      const paginationWrap = wrap.querySelector(".tv-post-block-pagination");
      if (!paginationWrap) {
        return;
      }
      if (maxPages <= 1) {
        paginationWrap.style.display = "none";
      } else if (paginationType === "ajax_numeric") {
        paginationWrap.style.display = "flex";
      } else if (currentPage >= maxPages) {
        paginationWrap.style.display = "none";
      } else {
        paginationWrap.style.display = "flex";
      }
    };
    const updateNumericPaginationMarkup = () => {
      const nav = wrap.querySelector(".tv-post-block-pagination-nav");
      if (!nav) {
        return;
      }
      nav.innerHTML = "";
      // Previous Button
      const prevBtn = document.createElement("button");
      prevBtn.className = `tv-page-navprev ${currentPage <= 1 ? "disabled" : ""}`;
      if (currentPage <= 1) {
        prevBtn.setAttribute("aria-disabled", "true");
        prevBtn.setAttribute("disabled", "true");
      }
      prevBtn.setAttribute("aria-label", i18n.prev);
      prevBtn.innerHTML = ` ${i18n.prev}`;
      prevBtn.addEventListener("click", (e) => {
        e.preventDefault();
        if (currentPage > 1) {
          currentPage--;
          fetchPostsData(false);
        }
      });
      nav.appendChild(prevBtn);
      // Numbers Buttons
      for (let i = 1; i <= maxPages; i++) {
        const btn = document.createElement("button");
        btn.className = `tv-page-num ${i === currentPage ? "active" : ""}`;
        if (i === currentPage) {
          btn.setAttribute("aria-current", "page");
          btn.setAttribute("disabled", "true");
        }
        btn.setAttribute("aria-label", i18n.page.replace("%d", i));
        btn.innerText = i;
        btn.addEventListener("click", (e) => {
          e.preventDefault();
          if (currentPage !== i) {
            currentPage = i;
            fetchPostsData(false);
          }
        });
        nav.appendChild(btn);
      }

      // Next Button
      const nextBtn = document.createElement("button");
      nextBtn.className = `tv-page-navnext ${currentPage >= maxPages ? "disabled" : ""}`;
      if (currentPage >= maxPages) {
        nextBtn.setAttribute("aria-disabled", "true");
        nextBtn.setAttribute("disabled", "true");
      }
      nextBtn.setAttribute("aria-label", i18n.next);
      nextBtn.innerHTML = `${i18n.next}`;
      nextBtn.addEventListener("click", (e) => {
        e.preventDefault();
        if (currentPage < maxPages) {
          currentPage++;
          fetchPostsData(false);
        }
      });
      nav.appendChild(nextBtn);
    };
    // 4. Input Interactions & Tab Swaps
    const tabs = wrap.querySelectorAll(".tv-filter-tab");
    if (tabs.length > 0) {
      tabs.forEach((tab) => {
        tab.addEventListener("click", (e) => {
          e.preventDefault();
          if (tab.classList.contains("active")) {
            return;
          }
          tabs.forEach((t) => {
            t.classList.remove("active");
            t.setAttribute("aria-selected", "false");
          });
          tab.classList.add("active");
          tab.setAttribute("aria-selected", "true");
          // Map dynamically updated tab label to grid description
          const activeTabId = tab.getAttribute("id");
          if (activeTabId && container) {
            container.setAttribute("aria-labelledby", activeTabId);
          }
          const filterId = parseInt(tab.getAttribute("data-filter-id")) || 0;
          if (settings.ajax_tabs === "category") {
            activeCategory = filterId;
          } else if (settings.ajax_tabs === "tag") {
            activeTag = filterId;
          } else if (settings.ajax_tabs === "author") {
            activeAuthor = filterId;
          }

          currentPage = 1;
          fetchPostsData(false);
        });
        // Keyboard interactions (arrows)
        tab.addEventListener("keydown", (e) => {
          const tabList = Array.from(tabs);
          const index = tabList.indexOf(tab);
          let targetTab = null;
          if (e.key === "ArrowRight") {
            targetTab = tabList[index + 1] || tabList[0];
          } else if (e.key === "ArrowLeft") {
            targetTab = tabList[index - 1] || tabList[tabList.length - 1];
          }

          if (targetTab) {
            targetTab.focus();
            targetTab.click();
            e.preventDefault();
          }
        });
      });
    }
    // Live Search Input Handle with debounce
    const searchInput = wrap.querySelector(".tv-post-block-search-input");
    if (searchInput) {
      searchInput.addEventListener("input", (e) => {
        searchPhrase = e.target.value.trim();
        currentPage = 1;
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
          fetchPostsData(false);
        }, 400); // 400ms debounce
      });

      // Native clear button click handler
      searchInput.addEventListener("search", (e) => {
        searchPhrase = e.target.value.trim();
        currentPage = 1;
        clearTimeout(searchDebounceTimer);
        fetchPostsData(false);
      });

      // Keydown Enter listener for instant search
      searchInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          searchPhrase = e.target.value.trim();
          currentPage = 1;
          clearTimeout(searchDebounceTimer);
          fetchPostsData(false);
        }
      });
    }
    // 5. Pagination Actions setup
    if (paginationType === "load_more") {
      const loadMoreBtn = wrap.querySelector(".tv-post-block-load-more");
      if (loadMoreBtn) {
        loadMoreBtn.addEventListener("click", (e) => {
          e.preventDefault();
          if (currentPage < maxPages) {
            currentPage++;
            fetchPostsData(true);
          }
        });
      }
    } else if (paginationType === "infinite_scroll") {
      const scrollTrigger = wrap.querySelector(".tv-post-block-scroll-trigger");
      if (scrollTrigger) {
        const observer = new IntersectionObserver(
          (entries) => {
            if (entries[0].isIntersecting && !isLoading && currentPage < maxPages) {
              currentPage++;
              fetchPostsData(true);
            }
          },
          {
            root: null,
            rootMargin: "120px",
            threshold: 0.1,
          },
        );
        observer.observe(scrollTrigger);
      }
    } else if (paginationType === "ajax_numeric") {
      // Initial numeric bindings
      updateNumericPaginationMarkup();
    }

    // Initialize Load More wrapper checks on load
    updateLoadMoreTriggerStates();
  };

  elementorFrontend.hooks.addAction("frontend/element_ready/tv-post-block.default", initTvPostBlock);
  elementorFrontend.hooks.addAction("frontend/element_ready/tv-search-results.default", initTvPostBlock);
});
