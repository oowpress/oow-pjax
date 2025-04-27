/**
 * OOW PJAX JavaScript - Handles PJAX (PushState + AJAX) navigation with space-separated selectors and custom JS support.
 * @module OOWPJAX
 */

/**
 * Initializes PJAX functionality on DOM content load.
 * @function
 * @listens DOMContentLoaded
 */
document.addEventListener('DOMContentLoaded', function () {
  /**
   * Configuration object for PJAX, derived from window.oowPJAXConfig.
   * @typedef {Object} PJAXConfig
   * @property {string} [targets='#main'] - Space-separated CSS selectors for content targets.
   * @property {string} [excludeSelectors=''] - Space-separated CSS selectors for excluded links.
   * @property {string} [excludeZoneSelectors=''] - Space-separated CSS selectors for excluded zones.
   * @property {string} [excludeExternal='0'] - Flag to exclude external links ('1' to enable).
   * @property {string} [excludeTargetBlank='0'] - Flag to exclude links with target="_blank" ('1' to enable).
   * @property {string} [enableCache='0'] - Flag to enable caching ('1' to enable).
   * @property {string} [cacheLifetime='0'] - Cache lifetime in seconds.
   * @property {string} [debugMode='0'] - Flag to enable debug logging ('1' to enable).
   * @property {string} [minLoaderDuration='0'] - Minimum loader display duration in milliseconds.
   * @property {string} [enableForms='0'] - Flag to enable form handling ('1' to enable).
   * @property {string} [isLoggedIn='0'] - Flag indicating user login status ('1' for logged in).
   * @property {string} [customJSBefore=''] - Custom JS to execute before page load.
   * @property {string} [customJSAfter=''] - Custom JS to execute after page load.
   * @property {string} [formRefreshTargets=''] - Space-separated CSS selectors for additional containers to refresh after form submission.
   * @property {string} ajaxUrl - URL for AJAX requests.
   * @property {string} nonce - Security nonce for AJAX requests.
   * @property {string} errorMessage - Default error message for display.
   */
  const config = window.oowPJAXConfig || {};

  /** @type {string[]} */
  const targets = config.targets
    ? config.targets.split(' ').map((s) => s.trim())
    : ['#main'];

  /** @type {string[]} */
  const excludeSelectors = config.excludeSelectors
    ? config.excludeSelectors.split(' ').map((s) => s.trim())
    : [];

  /** @type {string[]} */
  const excludeZoneSelectors = config.excludeZoneSelectors
    ? config.excludeZoneSelectors.split(' ').map((s) => s.trim()).concat('#wpadminbar')
    : ['#wpadminbar'];

  /** @type {boolean} */
  const excludeExternal = config.excludeExternal === '1';

  /** @type {boolean} */
  const excludeTargetBlank = config.excludeTargetBlank === '1';

  /** @type {boolean} */
  const enableCache = config.enableCache === '1';

  /** @type {number} */
  const cacheLifetime = parseInt(config.cacheLifetime, 10) * 1000 || 0;

  /** @type {boolean} */
  const debugMode = config.debugMode === '1';

  /** @type {number} */
  const minLoaderDuration = parseInt(config.minLoaderDuration, 10) || 0;

  /** @type {boolean} */
  const enableForms = config.enableForms === '1';

  /** @type {boolean} */
  const isLoggedIn = config.isLoggedIn === '1';

  /** @type {string} */
  const customJSBefore = config.customJSBefore || '';

  /** @type {string} */
  const customJSAfter = config.customJSAfter || '';

  /** @type {string[]} */
  const formRefreshTargets = config.formRefreshTargets
    ? config.formRefreshTargets.split(' ').map((s) => s.trim())
    : [];

  /** @type {Map<string, {content: Object, scripts: string, stylesheets: Array, timestamp: number}>} */
  const cache = new Map();

  /** @type {HTMLElement|null} */
  const loader = document.getElementById('oow-pjax-loader');

  /** @type {HTMLElement|null} */
  const errorDiv = document.getElementById('oow-pjax-error');

  /** @type {boolean} */
  let isInitialLoad = true;

  /**
   * Logs messages to console if debug mode is enabled.
   * @param {...any} args - Arguments to log.
   */
  function log(...args) {
    if (debugMode) console.log('[OOW PJAX]', ...args);
  }

  /**
   * Executes custom JavaScript code safely.
   * @param {string} code - JavaScript code to execute.
   * @param {string} context - Context of execution ('Before' or 'After').
   */
  function executeCustomJS(code, context) {
    if (!code) {
      log(`No ${context} custom JS to execute`);
      return;
    }
    try {
      eval(code);
      log(`${context} custom JS executed successfully`);
    } catch (error) {
      console.error(`Custom JS Error (${context}):`, error);
      log(`Error executing ${context} custom JS:`, error.message);
    }
  }

  /**
   * Displays the PJAX loader.
   */
  function showLoader() {
    if (loader && !isInitialLoad) {
      loader.style.display = 'flex';
      log('Loader shown at:', new Date().toISOString());
    } else {
      log('Loader not shown: not found or initial load');
    }
  }

  /**
   * Hides the PJAX loader, respecting minimum duration.
   * @param {number} [minDurationStart] - Start time of loader display.
   */
  function hideLoader(minDurationStart) {
    if (!loader) {
      log('hideLoader skipped: loader not found');
      return;
    }

    const elapsed = minDurationStart ? Date.now() - minDurationStart : 0;
    const remaining = minLoaderDuration - elapsed;

    if (remaining > 0) {
      setTimeout(() => {
        loader.style.display = 'none';
        log('Loader hidden after delay at:', new Date().toISOString());
      }, remaining);
    } else {
      loader.style.display = 'none';
      log('Loader hidden immediately at:', new Date().toISOString());
    }
  }

  /**
   * Displays an error message.
   * @param {string} [message] - Error message to display.
   */
  function showError(message) {
    if (errorDiv) {
      errorDiv.textContent = message || config.errorMessage;
      errorDiv.style.display = 'block';
      setTimeout(() => {
        errorDiv.style.display = 'none';
      }, 5000);
      log('Error displayed:', message);
    }
  }

  /**
   * Re-executes scripts within a target element.
   * @param {string} target - CSS selector of the target element.
   */
  function reexecuteScripts(target) {
    const scripts = document.querySelector(target)?.querySelectorAll('script') || [];
    scripts.forEach((script) => {
      const newScript = document.createElement('script');
      if (script.src) {
        newScript.src = script.src;
      } else {
        newScript.textContent = script.textContent;
      }
      script.parentNode.replaceChild(newScript, script);
      log('Script re-executed in target:', target);
    });
  }

  /**
   * Executes footer scripts from provided HTML.
   * @param {string} scriptsHtml - HTML containing scripts to execute.
   */
  function executeFooterScripts(scriptsHtml) {
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = scriptsHtml;
    const scripts = tempDiv.querySelectorAll('script');
    scripts.forEach((script) => {
      const newScript = document.createElement('script');
      if (script.src) {
        newScript.src = script.src;
        newScript.async = false;
      } else {
        newScript.textContent = script.textContent;
      }
      document.body.appendChild(newScript);
      log('Footer script executed:', script.src || 'inline');
    });
  }

  /**
   * Checks if cached content is still valid.
   * @param {number} timestamp - Cache entry timestamp.
   * @returns {boolean} True if cache is valid.
   */
  function isCacheValid(timestamp) {
    return cacheLifetime === 0 || Date.now() - timestamp < cacheLifetime;
  }


  /**
   * Extracts stylesheets (<link> and <style>) from an HTML document.
   * @param {Document} doc - HTML document to parse.
   * @returns {Array<{tag: string, content: string}>} List of stylesheets.
   */
  function extractStylesheets(doc) {
    const stylesheets = [];
    
    // Récupérer les balises <link rel="stylesheet">
    const linkElements = doc.querySelectorAll('link[rel="stylesheet"]');
    linkElements.forEach((link) => {
      const href = link.getAttribute('href');
      if (href) {
        stylesheets.push({ tag: 'link', content: href });
      }
    });

    // Récupérer les balises <style>
    const styleElements = doc.querySelectorAll('style');
    styleElements.forEach((style) => {
      const css = style.textContent.trim();
      if (css) {
        stylesheets.push({ tag: 'style', content: css });
      }
    });

    log('Stylesheets extracted:', stylesheets);
    return stylesheets;
  }

  /**
   * Applies stylesheets asynchronously, waiting for <link> tags to load.
   * @param {Array<{tag: string, content: string}>} stylesheets - List of stylesheets.
   * @returns {Promise} Resolves when all styles are applied.
   */
  function applyStylesheetsAsync(stylesheets) {
    return new Promise((resolve) => {
      let loadedCount = 0;
      const totalStyles = stylesheets.length;

      if (totalStyles === 0) {
        resolve();
        return;
      }

      stylesheets.forEach((stylesheet) => {
        if (stylesheet.tag === 'link') {
          if (!document.querySelector(`link[href="${stylesheet.content}"]`)) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = stylesheet.content;
            link.onload = () => {
              loadedCount++;
              log('Stylesheet loaded:', stylesheet.content);
              if (loadedCount === totalStyles) resolve();
            };
            link.onerror = () => {
              loadedCount++;
              log('Error loading stylesheet:', stylesheet.content);
              if (loadedCount === totalStyles) resolve();
            };
            document.head.appendChild(link);
            log('Stylesheet link added:', stylesheet.content);
          } else {
            loadedCount++;
            log('Stylesheet link already exists:', stylesheet.content);
            if (loadedCount === totalStyles) resolve();
          }
        } else if (stylesheet.tag === 'style') {
          if (!document.querySelector(`style[data-content="${btoa(stylesheet.content)}"]`)) {
            const style = document.createElement('style');
            style.textContent = stylesheet.content;
            style.setAttribute('data-content', btoa(stylesheet.content));
            document.head.appendChild(style);
            log('Inline style added');
          } else {
            log('Inline style already exists');
          }
          loadedCount++;
          if (loadedCount === totalStyles) resolve();
        }
      });
    });
  }

  /**
   * Refreshes the nonce via an AJAX request.
   * @returns {Promise<string>} New nonce value.
   */
  function refreshNonce() {
    return fetch(config.ajaxUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'oow_pjax_refresh_nonce',
      }),
      credentials: 'same-origin',
    })
      .then((response) => {
        if (!response.ok) throw new Error('Network error: ' + response.status);
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          log('Nonce refreshed:', data.data.nonce);
          return data.data.nonce;
        }
        throw new Error('Failed to refresh nonce: ' + (data.data || 'Unknown error'));
      })
      .catch((error) => {
        console.error('Nonce refresh error:', error);
        showError('Failed to refresh security token. Please try again.');
        throw error;
      });
  }

  /**
   * Loads a page via PJAX.
   * @param {string} href - URL to load.
   * @param {boolean} [fromPopstate=false] - Indicates if triggered by popstate event.
   */
  function loadPage(href, fromPopstate = false) {
    const startTime = Date.now();
    log('loadPage started for:', href, 'fromPopstate:', fromPopstate);
    log('UNCODE defined before update:', typeof window.UNCODE !== 'undefined');
    log('Custom JS Before available:', !!customJSBefore);
    executeCustomJS(customJSBefore, 'Before');
    showLoader();

    if (
      enableCache &&
      !isLoggedIn &&
      cache.has(href) &&
      !fromPopstate &&
      isCacheValid(cache.get(href).timestamp)
    ) {
      log('Loading from cache:', href);
      applyStylesheetsAsync(cache.get(href).stylesheets).then(() => {
        updateContent(cache.get(href).content);
        setTimeout(() => {
          executeFooterScripts(cache.get(href).scripts);
          log('Custom JS After available:', !!customJSAfter);
          executeCustomJS(customJSAfter, 'After');
        }, 0);
        window.history.pushState({ href }, '', href);
        hideLoader(startTime);
      });
      return;
    }

    // Refresh nonce before making the AJAX request
    refreshNonce()
      .then((newNonce) => {
        config.nonce = newNonce;
        return fetch(config.ajaxUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            action: 'oow_pjax_load',
            url: href,
            nonce: config.nonce,
          }),
          credentials: 'same-origin',
        });
      })
      .then((response) => {
        if (!response.ok) throw new Error('Network error: ' + response.status);
        log('Fetch response received:', href);
        return response.json();
      })
      .then((data) => {
        if (!data.success) throw new Error(data.data);
        log('HTML parsed start:', href);
        const parser = new DOMParser();
        const doc = parser.parseFromString(data.data.html, 'text/html');
        const content = {};

        const stylesheets = extractStylesheets(doc);
        applyStylesheetsAsync(stylesheets).then(() => {
          targets.forEach((target) => {
            const newContent = doc.querySelector(target);
            if (newContent) content[target] = newContent.innerHTML;
          });
          updateContent(content);

          setTimeout(() => {
            executeFooterScripts(data.data.scripts);
            log('Custom JS After available:', !!customJSAfter);
            executeCustomJS(customJSAfter, 'After');
          }, 0);

          if (enableCache && !isLoggedIn) {
            cache.set(href, {
              content,
              scripts: data.data.scripts,
              stylesheets,
              timestamp: Date.now(),
            });
          }
          if (!fromPopstate) window.history.pushState({ href }, '', href);
          document.title = doc.querySelector('title').textContent;

          hideLoader(startTime);
          log('Page fully loaded:', href);
          log('UNCODE defined after update:', typeof window.UNCODE !== 'undefined');
        });
      })
      .catch((error) => {
        console.error('PJAX Error:', error);
        hideLoader(startTime);
        showError(error.message);
      });
  }

  /**
   * Handles form submission via PJAX.
   * @param {HTMLFormElement} form - Form element.
   * @param {string} href - Form action URL.
   */
  function handleFormSubmit(form, href) {
    const startTime = Date.now();
    const originalUrl = window.location.href;
    log('Form submission started for:', href);
    log('Custom JS Before available:', !!customJSBefore);
    executeCustomJS(customJSBefore, 'Before');
    showLoader();

    const formData = new FormData(form);
    const commentNonce = form.querySelector('input[name="_wpnonce"]');
    if (commentNonce) {
      formData.append('_wpnonce', commentNonce.value);
    }
    const serializedData = new URLSearchParams(formData).toString();

    // Refresh nonce before making the AJAX request
    refreshNonce()
      .then((newNonce) => {
        config.nonce = newNonce;
        return fetch(config.ajaxUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            action: 'oow_pjax_form_submit',
            url: href,
            formData: serializedData,
            nonce: config.nonce,
          }),
          credentials: 'same-origin',
        });
      })
      .then((response) => {
        if (!response.ok) throw new Error('Network error: ' + response.status);
        log('Form response received:', href);
        return response.json();
      })
      .then((data) => {
        if (!data.success) throw new Error(data.data);
        log('Form HTML parsed start:', href);
        const parser = new DOMParser();
        const doc = parser.parseFromString(data.data.html, 'text/html');
        const content = {};
        const newUrl = data.data.redirect_url || originalUrl;

        const stylesheets = extractStylesheets(doc);
        applyStylesheetsAsync(stylesheets).then(() => {
          targets.forEach((target) => {
            const newContent = doc.querySelector(target);
            if (newContent) content[target] = newContent.innerHTML;
          });

          formRefreshTargets.forEach((target) => {
            const newContent = doc.querySelector(target);
            if (newContent) content[target] = newContent.innerHTML;
          });

          updateContent(content);
          setTimeout(() => {
            executeFooterScripts(data.data.scripts);
            log('Custom JS After available:', !!customJSAfter);
            executeCustomJS(customJSAfter, 'After');
          }, 0);
          if (enableCache && !isLoggedIn) {
            cache.set(newUrl, {
              content,
              scripts: data.data.scripts,
              stylesheets,
              timestamp: Date.now(),
            });
          }
          window.history.pushState({ href: newUrl }, '', newUrl);
          document.title = doc.querySelector('title').textContent;

          hideLoader(startTime);
          log('Form submission completed:', newUrl);
        });
      })
      .catch((error) => {
        console.error('PJAX Form Error:', error);
        hideLoader(startTime);
        showError(error.message);
      });
  }

  /**
   * Updates page content with new HTML.
   * @param {Object} content - Object mapping target selectors to new HTML.
   */
  function updateContent(content) {
    Object.keys(content).forEach((target) => {
      const element = document.querySelector(target);
      if (element) {
        element.innerHTML = content[target];
        reexecuteScripts(target);
      }
    });
  }

  /**
   * Handles click events for PJAX navigation.
   * @listens click
   */
  document.addEventListener('click', function (e) {
    const link = e.target.closest('a');
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href) return;

    if (href.startsWith('#')) {
      log('Anchor link ignored:', href);
      return;
    }

    const isExternal = !href.startsWith(window.location.origin);
    const isTargetBlank = link.getAttribute('target') === '_blank';
    const isExcluded = excludeSelectors.some((selector) => link.matches(selector));
    const isInExcludedZone = excludeZoneSelectors.some((selector) =>
      link.closest(selector)
    );

    if (
      isExcluded ||
      isInExcludedZone ||
      (excludeExternal && isExternal) ||
      (excludeTargetBlank && isTargetBlank)
    ) {
      log('Link excluded:', href);
      return;
    }

    if (href.startsWith(window.location.origin)) {
      e.preventDefault();
      loadPage(href);
    }
  });

  if (enableForms) {
    /**
     * Handles form submission events for PJAX.
     * @listens submit
     */
    document.addEventListener('submit', function (e) {
      const form = e.target.closest('form');
      if (!form) return;

      const href = form.getAttribute('action') || window.location.href;
      if (!href.startsWith(window.location.origin)) {
        log('External form submission ignored:', href);
        return;
      }

      const isInExcludedZone = excludeZoneSelectors.some((selector) =>
        form.closest(selector)
      );
      if (isInExcludedZone) {
        log('Form submission excluded:', href);
        return;
      }

      e.preventDefault();
      handleFormSubmit(form, href);
    });
  }

  /**
   * Handles browser history navigation.
   * @listens popstate
   */
  window.addEventListener('popstate', function (event) {
    const href = event.state?.href || window.location.href;
    log('Popstate triggered for:', href);
    if (
      enableCache &&
      !isLoggedIn &&
      cache.has(href) &&
      isCacheValid(cache.get(href).timestamp)
    ) {
      const startTime = Date.now();
      showLoader();
      applyStylesheetsAsync(cache.get(href).stylesheets).then(() => {
        updateContent(cache.get(href).content);
        setTimeout(() => {
          executeFooterScripts(cache.get(href).scripts);
          log('Custom JS After available:', !!customJSAfter);
          executeCustomJS(customJSAfter, 'After');
        }, 0);
        hideLoader(startTime);
      });
    } else {
      loadPage(href, true);
    }
  });

  // Cache initial page content
  if (enableCache && !isLoggedIn) {
    const initialContent = {};
    targets.forEach((target) => {
      const element = document.querySelector(target);
      if (element) initialContent[target] = element.innerHTML;
    });
    const initialStylesheets = extractStylesheets(document);
    cache.set(window.location.href, {
      content: initialContent,
      scripts: '',
      stylesheets: initialStylesheets,
      timestamp: Date.now(),
    });
  }

  log('oowPJAXConfig:', config);
  setTimeout(() => {
    isInitialLoad = false;
    log('Initial load complete');
  }, 0);
});