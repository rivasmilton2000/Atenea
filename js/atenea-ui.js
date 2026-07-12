(function () {
  "use strict";

  var loaderId = "ateneaPageLoader";
  var minVisibleMs = 320;
  var initialRevealDelayMs = 140;
  var initialFailSafeMs = 2200;
  var shownAt = 0;
  var initialLoaderTimer = 0;
  var failSafeHideTimer = 0;
  var hideTimer = 0;
  var pageSettled = document.readyState === "complete";

  function currentLoaderLogo() {
    return window.ATENEA_LOADER_LOGO || "../img/Atenea Logo.png";
  }

  function findLoader() {
    return document.getElementById(loaderId);
  }

  function clearTimer(timerId) {
    if (timerId) {
      window.clearTimeout(timerId);
    }

    return 0;
  }

  function ensureLoader() {
    if (!document.body) {
      return null;
    }

    var loader = findLoader();
    if (loader) {
      return loader;
    }

    loader = document.createElement("div");
    loader.id = loaderId;
    loader.className = "atenea-page-loader is-hidden";
    loader.setAttribute("aria-live", "polite");
    loader.setAttribute("aria-hidden", "true");
    loader.innerHTML =
      '<div class="atenea-page-loader__panel">' +
      '<div class="atenea-page-loader__logo-wrap">' +
      '<img class="atenea-page-loader__logo" src="' + currentLoaderLogo() + '" alt="Atenea">' +
      "</div>" +
      '<div class="atenea-page-loader__spinner" aria-hidden="true"></div>' +
      '<p class="atenea-page-loader__text">Cargando Atenea...</p>' +
      "</div>";

    document.body.appendChild(loader);

    return loader;
  }

  function setLoaderMessage(message) {
    var loader = findLoader() || ensureLoader();
    if (!loader) {
      return;
    }

    var messageNode = loader.querySelector(".atenea-page-loader__text");
    if (messageNode && message) {
      messageNode.textContent = message;
    }
  }

  function scheduleFailSafeHide(timeoutMs) {
    failSafeHideTimer = clearTimer(failSafeHideTimer);

    if (!timeoutMs || timeoutMs <= 0) {
      return;
    }

    failSafeHideTimer = window.setTimeout(function () {
      hideLoader(true);
    }, timeoutMs);
  }

  function showLoader(message, options) {
    var loader = ensureLoader();
    if (!loader) {
      return;
    }

    if (message) {
      setLoaderMessage(message);
    }

    options = options || {};
    shownAt = Date.now();
    hideTimer = clearTimer(hideTimer);
    loader.classList.remove("is-hidden");
    loader.setAttribute("aria-hidden", "false");
    document.body.classList.add("atenea-loading");
    document.body.classList.remove("atenea-loaded");

    if (options.failSafeMs) {
      scheduleFailSafeHide(options.failSafeMs);
    }
  }

  function hideLoader(force) {
    var loader = findLoader();

    failSafeHideTimer = clearTimer(failSafeHideTimer);
    hideTimer = clearTimer(hideTimer);

    if (!loader) {
      shownAt = 0;
      if (document.body) {
        document.body.classList.remove("atenea-loading");
        document.body.classList.add("atenea-loaded");
      }
      return;
    }

    var elapsed = shownAt > 0 ? Date.now() - shownAt : minVisibleMs;
    var delay = force ? 0 : Math.max(minVisibleMs - elapsed, 0);

    hideTimer = window.setTimeout(function () {
      loader.classList.add("is-hidden");
      loader.setAttribute("aria-hidden", "true");
      document.body.classList.remove("atenea-loading");
      document.body.classList.add("atenea-loaded");
      shownAt = 0;
      hideTimer = 0;
    }, delay);
  }

  function completeInitialLoad(force) {
    pageSettled = true;
    initialLoaderTimer = clearTimer(initialLoaderTimer);
    hideLoader(force);
  }

  function queueInitialLoader() {
    if (document.body && document.body.dataset.disablePreloader === "true") {
      return;
    }

    initialLoaderTimer = clearTimer(initialLoaderTimer);

    if (pageSettled) {
      hideLoader(true);
      return;
    }

    // Avoid flashing the overlay on fast pages and do not wait forever on slow assets.
    initialLoaderTimer = window.setTimeout(function () {
      if (pageSettled) {
        return;
      }

      showLoader((document.body && document.body.getAttribute("data-loader-text")) || "Cargando Atenea...", {
        failSafeMs: initialFailSafeMs
      });
      initialLoaderTimer = 0;
    }, initialRevealDelayMs);
  }

  function isLocalNavigation(link, event) {
    if (!link || event.defaultPrevented) {
      return false;
    }

    if (link.dataset.ateneaNoLoader === "true") {
      return false;
    }

    if (link.target && link.target !== "" && link.target !== "_self") {
      return false;
    }

    var href = link.getAttribute("href") || "";
    if (
      href === "" ||
      href.charAt(0) === "#" ||
      href.indexOf("javascript:") === 0 ||
      href.indexOf("mailto:") === 0 ||
      href.indexOf("tel:") === 0
    ) {
      return false;
    }

    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
      return false;
    }

    try {
      var destination = new URL(link.href, window.location.href);
      return destination.origin === window.location.origin;
    } catch (error) {
      return false;
    }
  }

  function fireAlert(options) {
    if (window.Swal && typeof window.Swal.fire === "function") {
      var baseOptions = {
        buttonsStyling: false,
        reverseButtons: true,
        customClass: {
          popup: "atenea-swal-popup",
          title: "atenea-swal-title",
          htmlContainer: "atenea-swal-text",
          confirmButton: "atenea-swal-confirm",
          cancelButton: "atenea-swal-cancel",
          denyButton: "atenea-swal-deny"
        }
      };

      var mergedOptions = Object.assign({}, baseOptions, options || {});
      mergedOptions.customClass = Object.assign({}, baseOptions.customClass, (options && options.customClass) || {});

      return window.Swal.fire(mergedOptions);
    }

    if (options && options.title) {
      window.alert(options.title + (options.text ? "\n\n" + options.text : ""));
    }

    return Promise.resolve({ isConfirmed: true });
  }

  function initUserMenus() {
    var roots = Array.prototype.slice.call(document.querySelectorAll("[data-atenea-user-menu]"));
    var closeDelayMs = 160;

    if (!roots.length) {
      return;
    }

    var menus = roots
      .map(function (root) {
        var trigger = root.querySelector(".atenea-user-menu-trigger");
        var menu = root.querySelector(".atenea-user-dropdown");

        if (!trigger || !menu) {
          return null;
        }

        return {
          root: root,
          trigger: trigger,
          menu: menu
        };
      })
      .filter(Boolean);

    if (!menus.length) {
      return;
    }

    function menuItems(entry) {
      return Array.prototype.slice.call(entry.menu.querySelectorAll('[role="menuitem"]'));
    }

    function closeMenu(entry, focusTrigger) {
      if (!entry) {
        return;
      }

      entry.root.classList.remove("is-open");
      entry.trigger.setAttribute("aria-expanded", "false");

      window.setTimeout(function () {
        if (!entry.root.classList.contains("is-open")) {
          entry.menu.hidden = true;
        }
      }, closeDelayMs);

      if (focusTrigger) {
        entry.trigger.focus();
      }
    }

    function closeAllMenus(exceptEntry) {
      menus.forEach(function (entry) {
        if (entry !== exceptEntry) {
          closeMenu(entry, false);
        }
      });
    }

    function openMenu(entry, focusIndex) {
      if (!entry) {
        return;
      }

      closeAllMenus(entry);
      entry.menu.hidden = false;

      window.requestAnimationFrame(function () {
        entry.root.classList.add("is-open");
        entry.trigger.setAttribute("aria-expanded", "true");

        if (typeof focusIndex === "number") {
          var items = menuItems(entry);
          if (items[focusIndex]) {
            items[focusIndex].focus();
          }
        }
      });
    }

    function toggleMenu(entry) {
      if (entry.root.classList.contains("is-open")) {
        closeMenu(entry, false);
        return;
      }

      openMenu(entry);
    }

    menus.forEach(function (entry) {
      entry.trigger.addEventListener("click", function (event) {
        event.preventDefault();
        toggleMenu(entry);
      });

      entry.trigger.addEventListener("keydown", function (event) {
        if (event.key === "ArrowDown" || event.key === "Enter" || event.key === " ") {
          event.preventDefault();
          openMenu(entry, 0);
        }

        if (event.key === "ArrowUp") {
          var items = menuItems(entry);
          event.preventDefault();
          openMenu(entry, items.length ? items.length - 1 : 0);
        }
      });

      menuItems(entry).forEach(function (item) {
        item.addEventListener("click", function () {
          closeMenu(entry, false);
        });

        item.addEventListener("keydown", function (event) {
          var items = menuItems(entry);
          var currentIndex = items.indexOf(event.currentTarget);

          if (event.key === "Escape") {
            event.preventDefault();
            closeMenu(entry, true);
            return;
          }

          if (event.key === "ArrowDown") {
            event.preventDefault();
            items[(currentIndex + 1) % items.length].focus();
            return;
          }

          if (event.key === "ArrowUp") {
            event.preventDefault();
            items[(currentIndex - 1 + items.length) % items.length].focus();
            return;
          }

          if (event.key === "Home") {
            event.preventDefault();
            items[0].focus();
            return;
          }

          if (event.key === "End") {
            event.preventDefault();
            items[items.length - 1].focus();
            return;
          }

          if (event.key === "Tab") {
            window.setTimeout(function () {
              if (!entry.root.contains(document.activeElement)) {
                closeMenu(entry, false);
              }
            }, 0);
          }
        });
      });
    });

    document.addEventListener("click", function (event) {
      menus.forEach(function (entry) {
        if (!entry.root.contains(event.target)) {
          closeMenu(entry, false);
        }
      });
    });

    document.addEventListener("keydown", function (event) {
      if (event.key !== "Escape") {
        return;
      }

      menus.forEach(function (entry) {
        closeMenu(entry, false);
      });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    queueInitialLoader();
    initUserMenus();
  });

  window.addEventListener("load", function () {
    completeInitialLoad(false);
  });

  window.addEventListener("pageshow", function () {
    completeInitialLoad(true);
  });

  document.addEventListener("readystatechange", function () {
    if (document.readyState === "complete") {
      completeInitialLoad(true);
    }
  });

  document.addEventListener("click", function (event) {
    var link = event.target.closest("a[href]");
    if (!isLocalNavigation(link, event)) {
      return;
    }

    showLoader(link.dataset.loaderText || "Abriendo...");
  });

  document.addEventListener("submit", function (event) {
    var form = event.target;
    if (!(form instanceof HTMLFormElement)) {
      return;
    }

    if (!form.hasAttribute("data-atenea-loading-form")) {
      return;
    }

    showLoader(form.getAttribute("data-loader-text") || "Procesando...");
  });

  window.AteneaUI = {
    showLoader: showLoader,
    hideLoader: hideLoader,
    setLoaderMessage: setLoaderMessage
  };

  window.AteneaAlerts = {
    fire: fireAlert,
    success: function (title, text, extra) {
      return fireAlert(Object.assign({ icon: "success", title: title, text: text }, extra || {}));
    },
    error: function (title, text, extra) {
      return fireAlert(Object.assign({ icon: "error", title: title, text: text }, extra || {}));
    },
    warning: function (title, text, extra) {
      return fireAlert(Object.assign({ icon: "warning", title: title, text: text }, extra || {}));
    },
    info: function (title, text, extra) {
      return fireAlert(Object.assign({ icon: "info", title: title, text: text }, extra || {}));
    }
  };
}());
