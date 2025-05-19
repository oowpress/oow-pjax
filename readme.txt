=== OOW PJAX ===
Contributors: oowpress, long-dotcom
Donate link: https://profiles.wordpress.org/oowpress/
Tags: pjax, ajax navigation, persistent player, page transition, vanilla javascript
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.5
Requires PHP: 5.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Transform your WordPress site into a fast, seamless PJAX (PushState + AJAX) experience without jQuery.

== Description ==

**OOW PJAX**, brought to you by **OOWCODE** and **OOWPRESS**, revolutionizes WordPress navigation with **PJAX (PushState + AJAX)**, delivering lightning-fast page transitions without full page reloads. Built with **pure JavaScript** (no jQuery), this lightweight plugin ensures a modern, fluid user experience while remaining compatible with any WordPress theme. Whether you’re running a portfolio, a blog with a persistent media player, or a dynamic content site, OOW PJAX enhances navigation, boosts engagement, and reduces server load.

### Why OOW PJAX Stands Out

OOW PJAX is designed for WordPress sites that demand **seamless navigation** and **dynamic content updates**. Unlike generic performance plugins, it targets specific use cases where fluid transitions are critical, such as:

- **Sites with Persistent Media Players**: Keep audio or video players (e.g., music, podcasts, live streams) running in the footer or sidebar during navigation, avoiding interruptions.
- **Portfolio Websites**: Showcase projects with smooth, app-like transitions, perfect for photographers, designers, or agencies.
- **Dynamic Content Sites**: Blogs, magazines, or news sites with frequently updated content benefit from fast, cached page loads.
- **E-commerce Stores**: Enhance product browsing with quick transitions, keeping users engaged without reload delays.
- **Single-Page App (SPA) Experiences**: Create a near-SPA feel for membership sites, directories, or dashboards without heavy frameworks.
- **Interactive Landing Pages**: Deliver immersive experiences for marketing campaigns or event sites with uninterrupted navigation.

Version 1.5 introduces a critical fix for handling Unicode characters (e.g., Chinese, emojis) in inline styles, preventing `InvalidCharacterError` issues with `btoa`. This update, contributed by **@long-dotcom**, ensures robust style management for multilingual and emoji-rich sites. It also enhances security with dynamic nonce refreshing, improves asynchronous stylesheet handling, and refines form redirect handling, making OOW PJAX more reliable for complex WordPress sites.

### Key Features

- **Seamless AJAX Navigation**: Loads content via AJAX, updating specific containers without reloading the entire page.
- **Persistent Elements**: Keeps fixed elements (e.g., media players, sticky menus, chat widgets) intact during transitions.
- **Browser History Support**: Syncs URLs with the History API for natural forward/back navigation.
- **Customizable Loader**: Style the loading overlay with CSS to match your brand (e.g., spinner, progress bar).
- **Content Caching**: Stores pages locally for instant repeat visits, with adjustable cache lifetime and user-aware logic.
- **Advanced Form Handling**: Submits forms (e.g., comments, login, contact) via AJAX, with explicit nonce support and redirect handling (301, 302, 303, 307, 308).
- **Dynamic Nonce Refresh**: Automatically refreshes security nonces via AJAX for enhanced security and reliability.
- **Lightweight & jQuery-Free**: Built with vanilla JavaScript for minimal footprint and maximum performance.
- **Flexible Configuration**: Define target containers, exclude links/zones (e.g., `.no-pjax`, `#wpadminbar`), and add custom JS before/after navigation.
- **Debug Mode**: Logs detailed information in the browser console and server logs for easy troubleshooting.
- **Secure Implementation**: Uses dynamic nonces, sanitization, and strict validation for all settings and AJAX requests.
- **Script Priority Control**: Customize the loading order of `oow-pjax.js` in the footer for compatibility.
- **Dynamic Style Management**: Injects and manages page-specific stylesheets and inline styles asynchronously, now with Unicode support.
- **Advanced Script Execution**: Re-executes scripts in updated containers or footer, with control over inline scripts and validation.
- **CodeMirror Integration**: Edit Custom JS with syntax highlighting and a Dracula theme.
- **Unicode Support for Styles**: Safely handles non-Latin1 characters (e.g., Chinese, emojis) in inline styles without errors (new in 1.5).

### Who Needs OOW PJAX?

OOW PJAX is tailored for WordPress users who want to elevate their site’s navigation and user experience. Specific use cases include:

- **Music & Podcast Sites**: Ensure uninterrupted playback of audio players during browsing.
- **Video Platforms**: Maintain video playback (e.g., tutorials, live streams) across navigation.
- **Creative Portfolios**: Deliver smooth transitions between project pages for artists or agencies.
- **Content-Heavy Blogs**: Speed up navigation with caching for frequently visited pages.
- **E-commerce with Sticky Features**: Keep cart widgets or live chat persistent during browsing.
- **Membership Sites**: Create fluid navigation for dashboards or course platforms.
- **Marketing Campaigns**: Build immersive landing pages with fast transitions.

### How It Works

1. **Link Interception**: Captures clicks on internal links, skipping external links, `target="_blank"`, excluded selectors (e.g., `.no-pjax`), or excluded zones (e.g., `#wpadminbar`).
2. **AJAX Content Loading**: Fetches new content via AJAX and updates specified containers (e.g., `#main`, `.content`).
3. **URL Synchronization**: Updates the browser’s URL using the History API for seamless navigation.
4. **Persistent Elements**: Preserves fixed elements (e.g., media players, sticky headers) across transitions.
5. **Customizable Loader**: Displays a styled overlay during content loading, with configurable minimum duration.
6. **Caching**: Caches pages for instant repeat visits (disabled for logged-in users) with adjustable lifetime.
7. **Form Handling**: Submits forms via AJAX, supporting explicit comment nonces and server-side redirects (e.g., 301, 302).
8. **Script Management**: Re-executes scripts in updated containers or footer, with custom JS execution before/after navigation.
9. **Style Injection**: Asynchronously injects page-specific stylesheets and inline styles, now with robust Unicode support.

### Getting Started

Install OOW PJAX, configure it in minutes, and transform your site’s navigation:
1. Install and activate the plugin from the WordPress admin.
2. Go to **OOWCODE > OOW PJAX** in the WordPress admin panel.
3. In the **Settings** tab, enable PJAX and configure:
   - **Target Containers**: CSS selectors for content updates (e.g., `#main`).
   - **Exclude Selectors/Zones**: Links or zones to skip (e.g., `.no-pjax`, `#wpadminbar`).
   - **Loader CSS**: Customize the loading animation.
   - **Cache Settings**: Enable caching with a lifetime (e.g., 300 seconds).
   - **Form Handling**: Enable AJAX for forms and specify refresh containers (e.g., `#comments`).
   - **Script Priority**: Set a high value (e.g., 9999) to load `oow-pjax.js` late.
   - **Custom JS**: Add JavaScript before/after navigation using CodeMirror.
4. Save settings and test navigation on your site.
5. Check the **Overview** tab for tips or the **Support** tab for help.

### Live Demo

See OOW PJAX in action! Visit our [live demo](https://demo.oowcode.com/oow-pjax/) to experience seamless transitions, a persistent media player, and portfolio navigation on a real WordPress site.

### Why Choose OOW PJAX?

- **Targeted Use Cases**: Perfect for sites with persistent media, portfolios, or dynamic content.
- **SEO-Friendly**: Maintains proper URLs and browser history for search engine compatibility.
- **Theme-Agnostic**: Works with any WordPress theme by targeting custom containers.
- **Lightweight Design**: No jQuery, minimal code, and optimized performance.

Discover the power of seamless navigation with OOW PJAX by **OOWCODE** and **OOWPRESS**. Visit [oowcode.com/oow-pjax](https://oowcode.com/oow-pjax) for full documentation.

== Installation ==

1. Upload the `oow-pjax` folder to `/wp-content/plugins/`, or install via the WordPress plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Navigate to **OOWCODE > OOW PJAX** in the admin panel.
4. Configure settings in the **Settings** tab (e.g., target containers, exclusions, loader styles, form handling).
5. Enable PJAX and save changes to start using seamless navigation.
6. (Optional) Read the **Overview** tab for setup tips or contact support via the **Support** tab.

== Frequently Asked Questions ==

= What is PJAX, and why use it? =
PJAX (PushState + AJAX) loads content dynamically via AJAX while updating the browser’s URL. OOW PJAX uses this to create fast, app-like navigation, ideal for sites with persistent media players or dynamic content.

= Can I use OOW PJAX with a persistent media player? =
Yes! OOW PJAX is perfect for audio or video players. Exclude player controls (e.g., `.player-controls`) in **Exclude Selectors** or zones (e.g., `.player`) in **Exclude Selectors Zone** to keep them persistent.

= Will it work with my WordPress theme? =
Yes. Specify your theme’s content container (e.g., `#main`, `.content`) in **Target Containers**. Check your theme’s source code for the correct selector.

= Does it support AJAX form submissions? =
Yes, enable **Enable Form Handling** to submit forms (e.g., comments, login, contact) via AJAX. Version 1.5 enhances this with explicit comment nonce support, dynamic nonce refreshing, and robust redirect handling (e.g., 301, 302).

= How do I style the loading animation? =
Edit **Loader CSS** in the **Settings** tab to customize the loading overlay. Use **Reset to Default** to revert to the default spinner.

= Can I exclude specific links or zones from PJAX? =
Yes, use **Exclude Selectors** (e.g., `.no-pjax`) for links or **Exclude Selectors Zone** (e.g., `.footer`) for entire zones. Enable **Exclude External Links** and **Exclude Links with target="_blank"** for automatic exclusions.

= Is OOW PJAX compatible with caching plugins? =
Yes, it works with WP Rocket, W3 Total Cache, and others. Enable **Cache** and set **Cache Lifetime** to balance speed and freshness.

= How do I troubleshoot issues? =
Enable **Debug Mode** to view detailed console and server logs (F12 or check server logs). Check the **Overview** tab for troubleshooting tips or contact [support@oowpress.com](mailto:support@oowpress.com).

= Does it require jQuery? =
No, OOW PJAX uses vanilla JavaScript for a lightweight, modern approach.

= Can I add custom JavaScript? =
Yes, use the **Custom JS** tab to add JavaScript before or after PJAX navigation with CodeMirror’s syntax highlighting.

= How does version 1.5 improve style handling? =
Version 1.5 adds support for Unicode characters (e.g., Chinese, emojis) in inline styles, fixing `InvalidCharacterError` issues with `btoa`. Thanks to @long-dotcom for the contribution.

= How does OOW PJAX handle page-specific styles? =
Version 1.5 enhances asynchronous stylesheet management, extracting and applying `<link>` and `<style>` tags during PJAX transitions, with full Unicode support for consistent rendering.

= Why are nonces refreshed dynamically? =
Dynamic nonce refreshing prevents errors from expired nonces during long sessions or high-traffic scenarios, enhancing security and reliability for AJAX requests.

== Screenshots ==

1. **Admin Interface**: Explore settings with tabs for Overview, Settings, Custom JS, Support, and About, featuring a light/dark theme toggle.
2. **Settings Configuration**: Customize target containers, exclusions, loader styles, form handling, and script priority.
3. **Custom JS with CodeMirror**: Edit JavaScript with syntax highlighting and a Dracula theme.
4. **Loading Overlay**: Preview the customizable loader during transitions.
5. **Persistent Media Player**: Example of a sticky audio player staying active during navigation.

== Changelog ==

= 1.5 =
* **Fixed**: `InvalidCharacterError` in `btoa` when handling Unicode characters (e.g., Chinese, emojis) in inline styles by adding `safeBase64Encode` function. Credits to @long-dotcom for identifying the issue and suggesting a solution.
* **Improved**: Enhanced `applyStylesheetsAsync` to use `safeBase64Encode` for robust style management with non-Latin1 characters.
* **Fixed**: Minor JSDoc typo in `isCacheValid` for improved documentation clarity.

= 1.4 =
* **Added**: Dynamic nonce refreshing via AJAX (`refreshNonce` and `refresh_nonce`) for enhanced security and reliability.
* **Added**: Asynchronous stylesheet management (`extractStylesheets` and `applyStylesheetsAsync`) for page-specific `<link>` and `<style>` tags.
* **Improved**: Form submission redirect handling with automatic follow-up GET requests for 301, 302, 303, 307, and 308 responses.
* **Improved**: Server-side script validation in `load_content` and `handle_form_submit` to prevent execution of invalid scripts.
* **Improved**: Detailed server-side error logging (`error_log`) for AJAX requests and redirects to facilitate debugging.
* **Improved**: Cache management to include stylesheets, ensuring consistent rendering during cached page loads.
* **Improved**: Admin interface with critical styles (`<link rel="preload">`) for faster font loading.
* **Fixed**: Potential issues with duplicate stylesheets by checking for existing `<link>` and `<style>` tags.

= 1.3 =
* **Added**: Enhanced redirect handling for form submissions, supporting 301, 302, 303, 307, and 308 responses with automatic follow-up GET requests.
* **Added**: Form refresh targets (`oow_pjax_form_refresh_targets`) to update additional containers (e.g., `#comments`) after form submissions.
* **Improved**: Form submission logic with serialized form data and explicit nonce handling for better security and compatibility.
* **Improved**: Redirect detection in `handle_form_submit` with detailed logging for debugging (HTTP status, headers, body).
* **Improved**: Cache management with timestamp validation and user-aware logic (disabled for logged-in users).
* **Improved**: JavaScript code organization with detailed JSDoc comments for better readability and maintainability.
* **Improved**: Error logging in PHP and JavaScript for easier troubleshooting of AJAX requests and script execution.
* **Fixed**: Potential issues with script re-execution by ensuring proper replacement of script nodes.
* **Fixed**: Minor bugs in form submission handling for edge cases with missing nonces or invalid redirects.

= 1.2 =
* **Added**: **Allow Risky Inline Scripts** option to enable execution of inline scripts with `addEventListener` or `window.location` (use with caution).
* **Added**: **CodeMirror** integration for **Custom JS Before** and **Custom JS After** fields with syntax highlighting and Dracula theme.
* **Added**: Maximum cache size limit (`MAX_CACHE_SIZE = 50`) to optimize memory usage.
* **Improved**: Inline script validation with `isValidScriptContent` to prevent execution of non-JavaScript content.
* **Improved**: JavaScript code structure with detailed comments and better organization.
* **Improved**: Error handling for custom JavaScript execution with detailed console logging.
* **Improved**: Admin interface with critical styles to prevent FOUC and enhanced CodeMirror usability.

= 1.1 =
* **Added**: **Script Priority** setting to control `oow-pjax.js` loading order in the footer (default: 9999).
* **Added**: **Page-Specific Styles** option to inject stylesheets and inline styles during PJAX transitions.
* **Added**: **Script Re-execution** options for targets, footer, and inline scripts.
* **Added**: Dynamic notices in the admin interface for improved feedback.
* **Improved**: JavaScript comments standardized to English with `/* */` format.
* **Improved**: JavaScript initialization with `document.readyState` check for late script loading.
* **Improved**: Inline script validation to prevent non-JavaScript content execution.
* **Improved**: Cache management with user-aware logic and validity checks.
* **Improved**: Form handling with support for server-side redirects via `Location` header.
* **Improved**: Security with strict script validation, `wp_unslash`, and `esc_url_raw` in AJAX requests.
* **Improved**: Admin theme toggle with AJAX saving and UI responsiveness.
* **Improved**: Documentation with detailed setting descriptions and internal code comments.

= 1.0 =
* Initial release with seamless PJAX navigation, persistent element support, customizable loader, content caching, AJAX form handling, and debug mode.

== Upgrade Notice ==

= 1.5 =
Upgrade to version 1.5 for robust Unicode support in inline styles, fixing `InvalidCharacterError` with `btoa`. This update, contributed by @long-dotcom, enhances compatibility with multilingual and emoji-rich sites, alongside improved style management. Recommended for all users.

= 1.4 =
Upgrade to version 1.4 for dynamic nonce refreshing, asynchronous stylesheet management, and improved form redirect handling. This update enhances security, compatibility with dynamic styles, and debugging capabilities. Recommended for all users.

= 1.3 =
Upgrade to version 1.3 for enhanced form handling with comment nonce support, improved redirect handling. This update boosts compatibility with comment forms, dynamic grids, and complex form submissions, with better debugging and performance. Recommended for all users.

= 1.2 =
Upgrade to version 1.2 for the **Allow Risky Inline Scripts** option, **CodeMirror** integration for Custom JS, and improved cache management. Enhances script execution and developer experience.

= 1.1 =
Upgrade to version 1.1 for **Script Priority**, **Page-Specific Styles**, and **Advanced Script Execution** options, plus improved security and admin interface. Highly recommended.

= 1.0 =
Initial release with seamless navigation and advanced features for media players, portfolios, and more.

== Support ==

Need help? Visit the **Support** tab in the plugin settings or email [support@oowpress.com](mailto:support@oowpress.com). Full documentation is available at [oowcode.com/oow-pjax](https://oowcode.com/oow-pjax).

== Contribute ==

Contribute to OOW PJAX on [GitHub](https://github.com/oowcode/oow-pjax) or share feedback at [oowcode.com](https://oowcode.com).

== License ==

OOW PJAX is licensed under the GPLv2 or later.