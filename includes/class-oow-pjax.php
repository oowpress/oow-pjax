<?php
/**
 * OOW PJAX Class
 *
 * Manages the core functionality of the PJAX plugin, including script registration,
 * admin interface handling, and AJAX content loading.
 *
 * @package OOW_PJAX
 * @since 1.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class OOW_PJAX {
    /**
     * Constructor
     */
    public function __construct() {
        $script_priority = absint(get_option('oow_pjax_script_priority', 9999));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), $script_priority);
        add_action('wp_footer', array($this, 'add_loader_html'));
        add_action('admin_menu', array($this, 'admin_menu'), 15);
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'), 5);
        add_action('wp_ajax_oow_pjax_load', array($this, 'load_content'));
        add_action('wp_ajax_nopriv_oow_pjax_load', array($this, 'load_content'));
        add_action('wp_ajax_oow_save_theme', array($this, 'save_theme'));
        add_action('wp_ajax_oow_pjax_form_submit', array($this, 'handle_form_submit'));
        add_action('wp_ajax_nopriv_oow_pjax_form_submit', array($this, 'handle_form_submit'));
        add_action('wp_ajax_oow_pjax_refresh_nonce', array($this, 'refresh_nonce'));
        add_action('wp_ajax_nopriv_oow_pjax_refresh_nonce', array($this, 'refresh_nonce'));
        add_action('admin_head', array($this, 'add_critical_styles'));
    }

    /**
     * Provides a new nonce via AJAX.
     */
    public function refresh_nonce() {
        wp_send_json_success(array(
            'nonce' => wp_create_nonce('oow_pjax_nonce'),
        ));
    }

    /**
     * Register front-end scripts and styles
     */
    public function enqueue_scripts() {
        if (get_option('oow_pjax_enabled', '0') !== '1') {
            return;
        }

        wp_enqueue_script(
            'oow-pjax-script',
            plugins_url('/assets/js/oow-pjax.js', dirname(__FILE__)),
            array(),
            time(),
            true
        );

        $settings = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('oow_pjax_nonce'),
            'targets' => get_option('oow_pjax_targets', '#main'),
            'excludeSelectors' => get_option('oow_pjax_exclude_selectors', ''),
            'excludeZoneSelectors' => get_option('oow_pjax_exclude_zone_selectors', ''),
            'excludeExternal' => get_option('oow_pjax_exclude_external', '1'),
            'excludeTargetBlank' => get_option('oow_pjax_exclude_target_blank', '1'),
            'enableCache' => get_option('oow_pjax_enable_cache', '0'),
            'cacheLifetime' => get_option('oow_pjax_cache_lifetime', '300'),
            'debugMode' => get_option('oow_pjax_debug_mode', '0'),
            'enableLoader' => get_option('oow_pjax_enable_loader', '1'),
            'minLoaderDuration' => get_option('oow_pjax_min_loader_duration', '200'),
            'errorMessage' => __('An error occurred while loading the page. Please try again.', 'oow-pjax'),
            'enableForms' => get_option('oow_pjax_enable_forms', '0'),
            'isLoggedIn' => is_user_logged_in() ? '1' : '0',
            'customJSBefore' => get_option('oow_pjax_custom_js_before', ''),
            'customJSAfter' => get_option('oow_pjax_custom_js_after', ''),
            'formRefreshTargets' => get_option('oow_pjax_form_refresh_targets', '')
        );
        wp_localize_script('oow-pjax-script', 'oowPJAXConfig', $settings);

        if (get_option('oow_pjax_enable_loader', '1') === '1') {
            wp_enqueue_style(
                'oow-pjax-style',
                plugins_url('/assets/css/oow-pjax.css', dirname(__FILE__)),
                array(),
                OOW_PJAX_VERSION
            );
            $custom_loader_css = get_option('oow_pjax_loader_css', $this->default_loader_css());
            wp_add_inline_style('oow-pjax-style', $custom_loader_css);
        }
    }

    /**
     * Register admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'oow-pjax-settings') === false) {
            return;
        }

        wp_enqueue_style(
            'oow-pjax-admin-style',
            plugins_url('/assets/css/oow-pjax-admin.css', dirname(__FILE__)),
            array(),
            time()
        );
        wp_enqueue_style(
            'oow-google-fonts',
            'https://fonts.googleapis.com/css2?family=Blinker:wght@100;200;300&display=swap',
            array(),
            null
        );
        wp_enqueue_script(
            'codemirror',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.12/codemirror.min.js',
            array(),
            '5.65.12',
            true
        );
        wp_enqueue_style(
            'codemirror',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.12/codemirror.min.css',
            array(),
            '5.65.12'
        );
        wp_enqueue_script(
            'codemirror-javascript',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.12/mode/javascript/javascript.min.js',
            array('codemirror'),
            '5.65.12',
            true
        );
        wp_enqueue_style(
            'codemirror-dracula',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.12/theme/dracula.min.css',
            array('codemirror'),
            '5.65.12'
        );
        wp_enqueue_script(
            'oow-pjax-admin',
            plugins_url('/assets/js/oow-pjax-admin.js', dirname(__FILE__)),
            array('codemirror', 'codemirror-javascript'),
            time(),
            true
        );
    }

    /**
     * Add critical styles
     */
    public function add_critical_styles() {
        if (strpos(get_current_screen()->id, 'oow-pjax-settings') !== false) {
            ?>
            <style>
                .wrap.oow-loading { opacity: 0; }
                .wrap { transition: opacity 0.2s; }
            </style>
            <link rel="preload" href="https://fonts.googleapis.com/css2?family=Blinker:wght@100;200;300&display=swap" as="style" onload="this.rel='stylesheet'">
            <?php
        }
    }

    /**
     * Add loader HTML
     */
    public function add_loader_html() {
        if (get_option('oow_pjax_enabled', '0') !== '1' || get_option('oow_pjax_enable_loader', '1') !== '1') {
            return;
        }

        ?>
        <div id="oow-pjax-loader" class="oow-pjax-loader" style="display: none;"></div>
        <div id="oow-pjax-error" class="oow-pjax-error" style="display: none;"></div>
        <?php
    }

    /**
     * Load content via AJAX
     */
    public function load_content() {
        if (!check_ajax_referer('oow_pjax_nonce', 'nonce', false)) {
            wp_send_json_error(__('Security check failed. Invalid nonce.', 'oow-pjax'));
        }

        $url = isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : '';
        if (empty($url)) {
            wp_send_json_error(__('No URL provided.', 'oow-pjax'));
        }

        $cookies = array();
        foreach ($_COOKIE as $name => $value) {
            $cookies[$name] = is_scalar($value) ? $value : serialize($value);
        }

        $response = wp_remote_get($url, array(
            'cookies' => $cookies,
            'timeout' => 15,
        ));

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            $doc = new DOMDocument();
            @$doc->loadHTML('<?xml encoding="UTF-8">' . $body);

            $scripts = '';
            $script_nodes = $doc->getElementsByTagName('script');
            foreach ($script_nodes as $script) {
                if ($script->getAttribute('src')) {
                    $scripts .= '<script src="' . esc_url($script->getAttribute('src')) . '"></script>';
                } else {
                    $scriptContent = trim($script->nodeValue);
                    if ($scriptContent && !preg_match('/^</', $scriptContent)) {
                        $scripts .= '<script>' . $scriptContent . '</script>';
                    } else {
                        error_log("[OOW PJAX] Invalid script content skipped in load_content: " . substr($scriptContent, 0, 50));
                    }
                }
            }

            $head = $doc->getElementsByTagName('head')->item(0);
            $head_content = $head ? $doc->saveHTML($head) : '';

            $footer = $doc->getElementsByTagName('footer')->item(0);
            $footer_content = $footer ? $doc->saveHTML($footer) : '';

            wp_send_json_success(array(
                'html' => $body,
                'head' => $head_content,
                'footer' => $footer_content,
                'scripts' => $scripts
            ));
        } else {
            $error_message = is_wp_error($response) ? $response->get_error_message() : __('Error loading content.', 'oow-pjax');
            error_log("[OOW PJAX] Error loading URL {$url}: {$error_message}");
            wp_send_json_error($error_message);
        }
    }

    /**
     * Handle form submissions via AJAX
     */
    public function handle_form_submit() {
        if (!check_ajax_referer('oow_pjax_nonce', 'nonce', false)) {
            wp_send_json_error(__('Security check failed. Invalid nonce.', 'oow-pjax'));
        }

        $form_data = isset($_POST['formData']) ? wp_unslash($_POST['formData']) : '';
        $url = isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : '';

        if (empty($url) || empty($form_data)) {
            wp_send_json_error(__('Invalid form submission.', 'oow-pjax'));
        }

        $cookies = array();
        foreach ($_COOKIE as $name => $value) {
            $cookies[$name] = is_scalar($value) ? $value : serialize($value);
        }

        $response = wp_remote_post($url, array(
            'body' => $form_data,
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
            'cookies' => $cookies,
            'redirection' => 0,
            'timeout' => 15,
        ));

        $response_code = wp_remote_retrieve_response_code($response);
        $response_headers = wp_remote_retrieve_headers($response);
        $response_body = wp_remote_retrieve_body($response);
        error_log("[OOW PJAX] Form submission to {$url}: HTTP {$response_code}, Headers: " . print_r($response_headers, true));

        if (in_array($response_code, [301, 302, 303, 307, 308]) && isset($response_headers['location'])) {
            $redirect_url = esc_url_raw($response_headers['location']);
            error_log("[OOW PJAX] Redirect detected to: {$redirect_url}");

            $redirect_response = wp_remote_get($redirect_url, array(
                'cookies' => $cookies,
                'timeout' => 15,
            ));

            $redirect_code = wp_remote_retrieve_response_code($redirect_response);
            $redirect_body = wp_remote_retrieve_body($redirect_response);
            error_log("[OOW PJAX] Redirect response: HTTP {$redirect_code}");

            if (!is_wp_error($redirect_response) && $redirect_code === 200) {
                $doc = new DOMDocument();
                @$doc->loadHTML('<?xml encoding="UTF-8">' . $redirect_body);

                $scripts = '';
                $script_nodes = $doc->getElementsByTagName('script');
                foreach ($script_nodes as $script) {
                    if ($script->getAttribute('src')) {
                        $scripts .= '<script src="' . esc_url($script->getAttribute('src')) . '"></script>';
                    } else {
                        $scriptContent = trim($script->nodeValue);
                        if ($scriptContent && !preg_match('/^</', $scriptContent)) {
                            $scripts .= '<script>' . $scriptContent . '</script>';
                        }
                    }
                }

                $head = $doc->getElementsByTagName('head')->item(0);
                $head_content = $head ? $doc->saveHTML($head) : '';
                $footer = $doc->getElementsByTagName('footer')->item(0);
                $footer_content = $footer ? $doc->saveHTML($footer) : '';

                wp_send_json_success(array(
                    'html' => $redirect_body,
                    'head' => $head_content,
                    'footer' => $footer_content,
                    'scripts' => $scripts,
                    'redirect_url' => $redirect_url
                ));
            } else {
                $error_message = is_wp_error($redirect_response) ? $redirect_response->get_error_message() : __('Error loading redirected page.', 'oow-pjax');
                error_log("[OOW PJAX] Redirect error for {$redirect_url}: {$error_message}");
                wp_send_json_error($error_message);
            }
        } elseif (!is_wp_error($response) && $response_code === 200) {
            $body = wp_remote_retrieve_body($response);
            $doc = new DOMDocument();
            @$doc->loadHTML('<?xml encoding="UTF-8">' . $body);

            $scripts = '';
            $script_nodes = $doc->getElementsByTagName('script');
            foreach ($script_nodes as $script) {
                if ($script->getAttribute('src')) {
                    $scripts .= '<script src="' . esc_url($script->getAttribute('src')) . '"></script>';
                } else {
                    $scriptContent = trim($script->nodeValue);
                    if ($scriptContent && !preg_match('/^</', $scriptContent)) {
                        $scripts .= '<script>' . $scriptContent . '</script>';
                    }
                }
            }

            $head = $doc->getElementsByTagName('head')->item(0);
            $head_content = $head ? $doc->saveHTML($head) : '';
            $footer = $doc->getElementsByTagName('footer')->item(0);
            $footer_content = $footer ? $doc->saveHTML($footer) : '';

            wp_send_json_success(array(
                'html' => $body,
                'head' => $head_content,
                'footer' => $footer_content,
                'scripts' => $scripts,
                'redirect_url' => $url
            ));
        } else {
            $error_message = is_wp_error($response) ? $response->get_error_message() : __('Error submitting form.', 'oow-pjax');
            error_log("[OOW PJAX] Form submission error for {$url}: {$error_message}, Response Code: {$response_code}, Body: " . substr($response_body, 0, 200));
            wp_send_json_error($error_message);
        }
    }

    /**
     * Register admin menu
     */
    public function admin_menu() {
        add_submenu_page(
            'oow-extensions',
            __('OOW PJAX Settings', 'oow-pjax'),
            __('OOW PJAX', 'oow-pjax'),
            'manage_options',
            'oow-pjax-settings',
            array($this, 'settings_page')
        );
    }

    /**
     * Display the settings page
     */
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $nonce = wp_create_nonce('oow_admin_nonce');
        $tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'overview';
        $get_nonce = isset($_GET['nonce']) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : '';
        $current_theme = get_option('oowcode_admin_theme', 'dark');

        if (empty($get_nonce) || !wp_verify_nonce($get_nonce, 'oow_admin_nonce')) {
            $tab = 'overview';
        }

        ?>
        <div class="wrap oow-loading">
            <div class="oow-pjax-header">
                <h1 class="oow-pjax-title">
                    <span class="text-logo"><?php echo esc_html__('OOW', 'oow-pjax'); ?></span>
                    <?php echo esc_html__('PJAX', 'oow-pjax'); ?>
                    <span class="version"><?php echo esc_html(OOW_PJAX_VERSION); ?></span>
                    <span class="author"><?php echo esc_html__('By OOWCODE', 'oow-pjax'); ?></span>
                </h1>
                <button id="oow-pjax-theme-toggle" class="theme-toggle-btn">
                    <?php echo $current_theme === 'dark' ? esc_html__('Light Mode', 'oow-pjax') : esc_html__('Dark Mode', 'oow-pjax'); ?>
                </button>
            </div>
            <div class="oow-pjax-notices"></div>
            <h2 class="nav-tab-wrapper">
                <a href="?page=oow-pjax-settings&tab=overview&nonce=<?php echo esc_attr($nonce); ?>" class="nav-tab <?php echo $tab === 'overview' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('Overview', 'oow-pjax'); ?>
                </a>
                <a href="?page=oow-pjax-settings&tab=settings&nonce=<?php echo esc_attr($nonce); ?>" class="nav-tab <?php echo $tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('Settings', 'oow-pjax'); ?>
                </a>
                <a href="?page=oow-pjax-settings&tab=custom-js&nonce=<?php echo esc_attr($nonce); ?>" class="nav-tab <?php echo $tab === 'custom-js' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('Custom JS', 'oow-pjax'); ?>
                </a>
                <a href="?page=oow-pjax-settings&tab=support&nonce=<?php echo esc_attr($nonce); ?>" class="nav-tab <?php echo $tab === 'support' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('Support', 'oow-pjax'); ?>
                </a>
                <a href="?page=oow-pjax-settings&tab=about&nonce=<?php echo esc_attr($nonce); ?>" class="nav-tab <?php echo $tab === 'about' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('About', 'oow-pjax'); ?>
                </a>
            </h2>

            <div class="oow-pjax-tab-content">
                <?php if ($tab === 'overview') : ?>
                    <h2><?php echo esc_html__('Plugin Overview', 'oow-pjax'); ?></h2>
                    <p><?php echo esc_html__('OOW PJAX enhances your WordPress site by enabling fast, seamless navigation using PushState and AJAX (PJAX). This plugin transforms traditional page reloads into smooth, app-like transitions, ideal for sites requiring persistent elements or dynamic content updates.', 'oow-pjax'); ?></p>
                    <h3><?php echo esc_html__('Key Features', 'oow-pjax'); ?></h3>
                    <ul class="oow-pjax-list">
                        <li><?php echo esc_html__('Seamless Navigation: Intercepts internal link clicks to load content via AJAX, preventing full page reloads.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Persistent Elements: Preserves fixed elements like media players, sticky menus, or chat widgets during navigation.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Browser History Support: Updates URLs using the History API for natural back/forward navigation.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Customizable Loader: Displays a styled loading overlay during transitions, configurable via CSS.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Content Caching: Stores pages locally for instant repeat visits, with adjustable cache lifetime.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Advanced Form Handling: Submits forms via AJAX, supporting comment nonces and server-side redirects.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Dynamic Nonce Refreshing: Automatically refreshes security nonces for reliable AJAX requests.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Asynchronous Stylesheet Management: Loads page-specific stylesheets and inline styles without duplicates.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Custom JavaScript: Execute custom JS before or after navigation to integrate with other scripts.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Lightweight & jQuery-Free: Built with vanilla JavaScript for optimal performance.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Debug Mode: Provides detailed console and server logs for troubleshooting.', 'oow-pjax'); ?></li>
                    </ul>
                    <h3><?php echo esc_html__('Who Should Use OOW PJAX?', 'oow-pjax'); ?></h3>
                    <ul class="oow-pjax-list">
                        <li><?php echo esc_html__('Music & Podcast Sites: Maintain uninterrupted audio playback during navigation.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Video Platforms: Keep video players active across page transitions.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Creative Portfolios: Deliver smooth transitions for project showcases.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Content-Heavy Blogs: Speed up navigation with caching for frequent visitors.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('E-commerce Stores: Enhance browsing with persistent cart widgets or live chat.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Membership Sites: Create fluid navigation for dashboards or courses.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Marketing Campaigns: Build immersive landing pages with fast transitions.', 'oow-pjax'); ?></li>
                    </ul>
                    <h3><?php echo esc_html__('How It Works', 'oow-pjax'); ?></h3>
                    <ol class="oow-pjax-list">
                        <li><?php echo esc_html__('Link Interception: Captures internal link clicks, excluding specified selectors or zones (e.g., .no-pjax, #wpadminbar).', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('AJAX Content Loading: Fetches new content and updates target containers (e.g., #main, header).', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('URL Synchronization: Updates the browser URL using the History API.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Persistent Elements: Preserves fixed elements across transitions.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Caching: Stores pages for instant repeat visits (disabled for logged-in users).', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Form Handling: Submits forms via AJAX, supporting redirects and nonce refreshing.', 'oow-pjax'); ?></li>
                        <li><?php echo esc_html__('Style & Script Management: Applies page-specific styles and re-executes scripts dynamically.', 'oow-pjax'); ?></li>
                    </ol>
                    <h3><?php echo esc_html__('Getting Started', 'oow-pjax'); ?></h3>
                    <p><?php echo esc_html__('Configure the plugin in the "Settings" tab to define target containers, exclusions, loader styles, and more. Use the "Custom JS" tab to add JavaScript for advanced integrations. For detailed guidance, visit the "Support" tab or explore our documentation.', 'oow-pjax'); ?></p>
                    <h3><?php echo esc_html__('View the Complete Documentation', 'oow-pjax'); ?></h3>
                    <p><?php echo esc_html__('Visit our documentation for tutorials, advanced tips, and troubleshooting.', 'oow-pjax'); ?> <a href="https://oowcode.com" target="_blank"><?php echo esc_html__('Explore now', 'oow-pjax'); ?></a>.</p>
                    <h3><?php echo esc_html__('Live Demo', 'oow-pjax'); ?></h3>
                    <p><?php echo esc_html__('See OOW PJAX in action! Visit our live demo to experience seamless transitions and persistent elements.', 'oow-pjax'); ?> <a href="https://demo.oowcode.com/oow-pjax/" target="_blank"><?php echo esc_html__('View demo', 'oow-pjax'); ?></a>.</p>
                <?php elseif ($tab === 'settings') : ?>
                    <h2><?php echo esc_html__('Settings', 'oow-pjax'); ?></h2>
                    <p class="description"><?php echo esc_html__('Customize PJAX behavior by defining target containers, exclusions, loader styles, and more. Selectors can include CSS selectors (e.g., #masthead .post-wrapper) or HTML tag names (e.g., header, footer).', 'oow-pjax'); ?></p>
                    <form method="post" action="options.php">
                        <?php settings_fields('oow_pjax_settings_group'); ?>
                        <div class="oow-pjax-section" id="oow-pjax-settings-section">
                            <?php do_settings_sections('oow-pjax-settings'); ?>
                        </div>
                        <?php submit_button(); ?>
                    </form>
                    <script type="text/javascript">
                        document.addEventListener('DOMContentLoaded', function() {
                            const resetLink = document.getElementById('oow-pjax-reset-loader-css');
                            const loaderCssField = document.getElementById('oow-pjax-loader-css');
                            if (resetLink && loaderCssField) {
                                resetLink.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const defaultCss = <?php echo json_encode($this->default_loader_css()); ?>;
                                    loaderCssField.value = defaultCss;
                                });
                            }
                        });
                    </script>
                <?php elseif ($tab === 'custom-js') : ?>
                    <h2><?php echo esc_html__('Custom JS', 'oow-pjax'); ?></h2>
                    <p class="description"><?php echo esc_html__('Add custom JavaScript to execute before or after PJAX navigation. Enter raw JavaScript code without <script> tags. Use the CodeMirror editor for syntax highlighting and a Dracula theme.', 'oow-pjax'); ?></p>
                        <form method="post" action="options.php">
                            <?php settings_fields('oow_pjax_custom_js_group'); ?>
                            <div class="oow-pjax-section" id="oow-pjax-custom-js-section">
                                <?php do_settings_sections('oow-pjax-custom-js'); ?>
                            </div>
                            <?php submit_button(); ?>
                        </form>
                    <?php elseif ($tab === 'support') : ?>
                        <?php
                        $current_user = wp_get_current_user();
                        $email = $current_user->user_email ? esc_attr($current_user->user_email) : '';
                        $wp_version = get_bloginfo('version') ? esc_attr(get_bloginfo('version')) : '';
                        $wp_url = get_bloginfo('url') ? esc_attr(get_bloginfo('url')) : '';
                        $plugin_name = esc_attr(OOW_PJAX_NAME);
                        $plugin_version = esc_attr(OOW_PJAX_VERSION);
                        $iframe_url = add_query_arg(
                            array(
                                'your-email' => $email,
                                'wp-url' => $wp_url,
                                'wp-version' => $wp_version,
                                'plugin-name' => $plugin_name,
                                'plugin-version' => $plugin_version,
                            ),
                            'https://oowcode.com/wp-support/support/'
                        );
                        ?>
                        <iframe src="<?php echo esc_url($iframe_url); ?>" style="width: 100%; height: 70vh; border: none;"></iframe>
                    <?php elseif ($tab === 'about') : ?>
                        <iframe src="https://oowcode.com/wp-support/about/" style="width: 100%; height: 70vh; border: none;"></iframe>
                    <?php endif; ?>
                </div>
            </div>
            <style>
                .oow-pjax-section.hidden { display: none; }
            </style>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    const wrap = document.querySelector('.wrap.oow-loading');
                    const body = document.body;
                    let currentTheme = '<?php echo esc_js($current_theme); ?>';

                    body.classList.add('oow-pjax-theme-' + currentTheme);
                    if (wrap) {
                        wrap.classList.remove('oow-loading');
                    }

                    const toggleBtn = document.getElementById('oow-pjax-theme-toggle');
                    if (toggleBtn) {
                        toggleBtn.addEventListener('click', function() {
                            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                            body.classList.remove('oow-pjax-theme-' + currentTheme);
                            body.classList.add('oow-pjax-theme-' + newTheme);
                            currentTheme = newTheme;
                            toggleBtn.textContent = newTheme === 'dark' ? '<?php echo esc_js(__('Light Mode', 'oow-pjax')); ?>' : '<?php echo esc_js(__('Dark Mode', 'oow-pjax')); ?>';
                            jQuery.post(ajaxurl, {
                                action: 'oow_save_theme',
                                theme: newTheme,
                                nonce: '<?php echo esc_js(wp_create_nonce('oow_theme_nonce')); ?>'
                            }, function(response) {
                                if (!response.success) {
                                    console.error('Failed to save theme:', response.data);
                                }
                            });
                        });
                    }

                    setTimeout(function() {
                        const notices = document.querySelectorAll('.notice');
                        const noticeContainer = document.querySelector('.oow-pjax-notices');
                        if (noticeContainer) {
                            notices.forEach(notice => {
                                noticeContainer.appendChild(notice);
                            });
                        }
                    }, 50);
                });
            </script>
            <?php
        }

    /**
     * Save theme preference
     */
    public function save_theme() {
        check_ajax_referer('oow_theme_nonce', 'nonce');

        if (isset($_POST['theme']) && in_array($_POST['theme'], ['light', 'dark'])) {
            $theme = sanitize_text_field(wp_unslash($_POST['theme']));
            update_option('oowcode_admin_theme', $theme);
            wp_send_json_success();
        } else {
            wp_send_json_error('Invalid theme');
        }
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('oow_pjax_settings_group', 'oow_pjax_enabled', array($this, 'sanitize_checkbox'));
        register_setting('oow_pjax_settings_group', 'oow_pjax_targets', array($this, 'sanitize_text'));
        register_setting('oow_pjax_settings_group', 'oow_pjax_exclude_selectors', array($this, 'sanitize_text'));
        register_setting('oow_pjax_settings_group', 'oow_pjax_exclude_zone_selectors', array($this, 'sanitize_text'));
        register_setting('oow_pjax_settings_group', 'oow_pjax_exclude_external', array($this, 'sanitize_checkbox'));
        register_setting('oow_pjax_settings_group', 'oow_pjax_exclude_target_blank', array($this, 'sanitize_checkbox'));
        register_setting('oow_pjax_settings_group', 'oow_pjax_enable_cache', array($this, 'sanitize_checkbox'));
        register_setting('oow_pjax_settings_group', 'oow_pjax_cache_lifetime', array($this, 'sanitize_cache_lifetime'));
        register_setting('oow_pjax_settings_group', 'oow_pjax_debug_mode', array($this, 'sanitize_checkbox'));
        register_setting('oow_pjax_settings_group', 'oow_pjax_enable_loader', array($this, 'sanitize_checkbox'));
        register_setting('oow_pjax_settings_group', 'oow_pjax_loader_css', array($this, 'sanitize_loader_css'));
        register_setting('oow_pjax_settings_group', 'oow_pjax_min_loader_duration', array($this, 'sanitize_min_loader_duration'));
        register_setting('oow_pjax_settings_group', 'oow_pjax_enable_forms', array($this, 'sanitize_checkbox'));
        register_setting('oow_pjax_settings_group', 'oow_pjax_form_refresh_targets', array($this, 'sanitize_text'));
        register_setting('oow_pjax_settings_group', 'oow_pjax_script_priority', array($this, 'sanitize_script_priority'));

        register_setting('oow_pjax_custom_js_group', 'oow_pjax_custom_js_before', array($this, 'sanitize_js'));
        register_setting('oow_pjax_custom_js_group', 'oow_pjax_custom_js_after', array($this, 'sanitize_js'));

        add_settings_section(
            'oow_pjax_main_section',
            null,
            null,
            'oow-pjax-settings'
        );

        add_settings_section(
            'oow_pjax_custom_js_section',
            null,
            null,
            'oow-pjax-custom-js'
        );

        add_settings_field('oow_pjax_enabled', __('Enable PJAX', 'oow-pjax'), array($this, 'enable_pjax_field'), 'oow-pjax-settings', 'oow_pjax_main_section');
        add_settings_field('oow_pjax_targets', __('Target Containers (space-separated)', 'oow-pjax'), array($this, 'targets_field'), 'oow-pjax-settings', 'oow_pjax_main_section');
        add_settings_field('oow_pjax_exclude_selectors', __('Exclude Selectors (space-separated)', 'oow-pjax'), array($this, 'exclude_selectors_field'), 'oow-pjax-settings', 'oow_pjax_main_section');
        add_settings_field('oow_pjax_exclude_zone_selectors', __('Exclude Selectors Zone (space-separated)', 'oow-pjax'), array($this, 'exclude_zone_selectors_field'), 'oow-pjax-settings', 'oow_pjax_main_section');
        add_settings_field('oow_pjax_exclude_external', __('Exclude External Links', 'oow-pjax'), array($this, 'exclude_external_field'), 'oow-pjax-settings', 'oow_pjax_main_section');
        add_settings_field('oow_pjax_exclude_target_blank', __('Exclude Links with target="_blank"', 'oow-pjax'), array($this, 'exclude_target_blank_field'), 'oow-pjax-settings', 'oow_pjax_main_section');
        add_settings_field('oow_pjax_enable_cache', __('Enable Cache', 'oow-pjax'), array($this, 'enable_cache_field'), 'oow-pjax-settings', 'oow_pjax_main_section');
        add_settings_field('oow_pjax_cache_lifetime', __('Cache Lifetime (seconds)', 'oow-pjax'), array($this, 'cache_lifetime_field'), 'oow-pjax-settings', 'oow_pjax_main_section');
        add_settings_field('oow_pjax_debug_mode', __('Enable Debug Mode', 'oow-pjax'), array($this, 'debug_mode_field'), 'oow-pjax-settings', 'oow_pjax_main_section');
        add_settings_field('oow_pjax_enable_loader', __('Enable Loader', 'oow-pjax'), array($this, 'enable_loader_field'), 'oow-pjax-settings', 'oow_pjax_main_section');
        add_settings_field('oow_pjax_loader_css', __('Loader CSS', 'oow-pjax'), array($this, 'loader_css_field'), 'oow-pjax-settings', 'oow_pjax_main_section');
        add_settings_field('oow_pjax_min_loader_duration', __('Minimum Loader Duration (ms)', 'oow-pjax'), array($this, 'min_loader_duration_field'), 'oow-pjax-settings', 'oow_pjax_main_section');
        add_settings_field('oow_pjax_enable_forms', __('Enable Form Handling', 'oow-pjax'), array($this, 'enable_forms_field'), 'oow-pjax-settings', 'oow_pjax_main_section');
        add_settings_field('oow_pjax_form_refresh_targets', __('Target Refresh Containers (space-separated)', 'oow-pjax'), array($this, 'form_refresh_targets_field'), 'oow-pjax-settings', 'oow_pjax_main_section');
        add_settings_field('oow_pjax_script_priority', __('Script Priority', 'oow-pjax'), array($this, 'script_priority_field'), 'oow-pjax-settings', 'oow_pjax_main_section');

        add_settings_field('oow_pjax_custom_js_before', __('Before PJAX Execution', 'oow-pjax'), array($this, 'custom_js_before_field'), 'oow-pjax-custom-js', 'oow_pjax_custom_js_section');
        add_settings_field('oow_pjax_custom_js_after', __('After PJAX Execution', 'oow-pjax'), array($this, 'custom_js_after_field'), 'oow-pjax-custom-js', 'oow_pjax_custom_js_section');
    }

    /**
     * Sanitize settings fields
     */
    public function sanitize_text($input) {
        return is_string($input) ? sanitize_text_field($input) : '';
    }

    public function sanitize_checkbox($input) {
        return ($input === '1') ? '1' : '0';
    }

    public function sanitize_cache_lifetime($input) {
        return is_numeric($input) ? absint($input) : 300;
    }

    public function sanitize_loader_css($input) {
        return is_string($input) ? wp_strip_all_tags($input) : $this->default_loader_css();
    }

    public function sanitize_min_loader_duration($input) {
        return is_numeric($input) ? absint($input) : 200;
    }

    public function sanitize_script_priority($input) {
        return is_numeric($input) ? absint($input) : 9999;
    }

    public function sanitize_js($input) {
        return is_string($input) ? $input : '';
    }

    /**
     * Form fields for settings
     */
    public function enable_pjax_field() {
        $value = get_option('oow_pjax_enabled', '0');
        ?>
        <input type="checkbox" name="oow_pjax_enabled" value="1" <?php checked('1', $value); ?> />
        <p class="description"><?php esc_html_e('Enable PJAX navigation on the site.', 'oow-pjax'); ?></p>
        <?php
    }

    public function targets_field() {
        $value = get_option('oow_pjax_targets', '#main');
        $selectors = !empty($value) ? explode(' ', $value) : [];
        ?>
        <div class="oow-pjax-tags-input" data-name="oow_pjax_targets">
            <div class="oow-pjax-tags-container">
                <?php foreach ($selectors as $selector) : 
                    if (!empty(trim($selector))) : ?>
                        <span class="oow-pjax-tag" data-value="<?php echo esc_attr($selector); ?>">
                            <?php echo esc_html($selector); ?>
                            <span class="oow-pjax-tag-remove">×</span>
                        </span>
                    <?php endif; 
                endforeach; ?>
                <input type="text" class="oow-pjax-tag-input" placeholder="<?php esc_attr_e('Add selector...', 'oow-pjax'); ?>" />
            </div>
            <input type="hidden" name="oow_pjax_targets" class="oow-pjax-tags-hidden" value="<?php echo esc_attr($value); ?>" />
        </div>
        <p class="description"><?php esc_html_e('Example: #masthead .post-wrapper (press Enter to add)', 'oow-pjax'); ?></p>
        <?php
    }

    public function exclude_selectors_field() {
        $value = get_option('oow_pjax_exclude_selectors', '');
        $selectors = !empty($value) ? explode(' ', $value) : [];
        ?>
        <div class="oow-pjax-tags-input" data-name="oow_pjax_exclude_selectors">
            <div class="oow-pjax-tags-container">
                <?php foreach ($selectors as $selector) : 
                    if (!empty(trim($selector))) : ?>
                        <span class="oow-pjax-tag" data-value="<?php echo esc_attr($selector); ?>">
                            <?php echo esc_html($selector); ?>
                            <span class="oow-pjax-tag-remove">×</span>
                        </span>
                    <?php endif; 
                endforeach; ?>
                <input type="text" class="oow-pjax-tag-input" placeholder="<?php esc_attr_e('Add selector...', 'oow-pjax'); ?>" />
            </div>
            <input type="hidden" name="oow_pjax_exclude_selectors" class="oow-pjax-tags-hidden" value="<?php echo esc_attr($value); ?>" />
        </div>
        <p class="description"><?php esc_html_e('Example: .no-pjax #skip-link (press Enter to add)', 'oow-pjax'); ?></p>
        <?php
    }

    public function exclude_zone_selectors_field() {
        $value = get_option('oow_pjax_exclude_zone_selectors', '');
        $selectors = !empty($value) ? explode(' ', $value) : [];
        ?>
        <div class="oow-pjax-tags-input" data-name="oow_pjax_exclude_zone_selectors">
            <div class="oow-pjax-tags-container">
                <?php foreach ($selectors as $selector) : 
                    if (!empty(trim($selector))) : ?>
                        <span class="oow-pjax-tag" data-value="<?php echo esc_attr($selector); ?>">
                            <?php echo esc_html($selector); ?>
                            <span class="oow-pjax-tag-remove">×</span>
                        </span>
                    <?php endif; 
                endforeach; ?>
                <input type="text" class="oow-pjax-tag-input" placeholder="<?php esc_attr_e('Add selector...', 'oow-pjax'); ?>" />
            </div>
            <input type="hidden" name="oow_pjax_exclude_zone_selectors" class="oow-pjax-tags-hidden" value="<?php echo esc_attr($value); ?>" />
        </div>
        <p class="description"><?php esc_html_e('Example: .footer .sidebar (press Enter to add; all links and forms inside these zones will be ignored)', 'oow-pjax'); ?></p>
        <?php
    }

    public function exclude_external_field() {
        $value = get_option('oow_pjax_exclude_external', '1');
        ?>
        <input type="checkbox" name="oow_pjax_exclude_external" value="1" <?php checked('1', $value); ?> />
        <?php
    }

    public function exclude_target_blank_field() {
        $value = get_option('oow_pjax_exclude_target_blank', '1');
        ?>
        <input type="checkbox" name="oow_pjax_exclude_target_blank" value="1" <?php checked('1', $value); ?> />
        <?php
    }

    public function enable_cache_field() {
        $value = get_option('oow_pjax_enable_cache', '0');
        ?>
        <input type="checkbox" name="oow_pjax_enable_cache" value="1" <?php checked('1', $value); ?> />
        <p class="description"><?php esc_html_e('Enable caching for visited pages.', 'oow-pjax'); ?></p>
        <?php
    }

    public function cache_lifetime_field() {
        $value = get_option('oow_pjax_cache_lifetime', '300');
        ?>
        <input type="number" name="oow_pjax_cache_lifetime" value="<?php echo esc_attr($value); ?>" min="0" step="10" class="small-text" /> seconds
        <p class="description"><?php esc_html_e('Time in seconds before cached content expires (0 to disable expiration).', 'oow-pjax'); ?></p>
        <?php
    }

    public function debug_mode_field() {
        $value = get_option('oow_pjax_debug_mode', '0');
        ?>
        <input type="checkbox" name="oow_pjax_debug_mode" value="1" <?php checked('1', $value); ?> />
        <p class="description"><?php esc_html_e('Display logs in the console.', 'oow-pjax'); ?></p>
        <?php
    }

    public function enable_loader_field() {
        $value = get_option('oow_pjax_enable_loader', '1');
        ?>
        <input type="checkbox" name="oow_pjax_enable_loader" value="1" <?php checked('1', $value); ?> />
        <p class="description"><?php esc_html_e('Show a loading overlay during content loading.', 'oow-pjax'); ?></p>
        <?php
    }

    public function loader_css_field() {
        $value = get_option('oow_pjax_loader_css', $this->default_loader_css());
        ?>
        <textarea name="oow_pjax_loader_css" id="oow-pjax-loader-css" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($value); ?></textarea>
        <p class="description"><?php esc_html_e('Customize the loader appearance with CSS.', 'oow-pjax'); ?> <a href="#" id="oow-pjax-reset-loader-css"><?php esc_html_e('Reset to Default', 'oow-pjax'); ?></a></p>
        <?php
    }

    public function min_loader_duration_field() {
        $value = get_option('oow_pjax_min_loader_duration', '200');
        ?>
        <input type="number" name="oow_pjax_min_loader_duration" value="<?php echo esc_attr($value); ?>" min="0" step="50" class="small-text" /> ms
        <p class="description"><?php esc_html_e('Minimum time the loader is visible (0 to disable).', 'oow-pjax'); ?></p>
        <?php
    }

    public function enable_forms_field() {
        $value = get_option('oow_pjax_enable_forms', '0');
        ?>
        <input type="checkbox" name="oow_pjax_enable_forms" value="1" <?php checked('1', $value); ?> />
        <p class="description"><?php esc_html_e('Enable PJAX handling for form submissions.', 'oow-pjax'); ?></p>
        <?php
    }

    public function form_refresh_targets_field() {
        $value = get_option('oow_pjax_form_refresh_targets', '');
        $selectors = !empty($value) ? explode(' ', $value) : [];
        ?>
        <div class="oow-pjax-tags-input" data-name="oow_pjax_form_refresh_targets">
            <div class="oow-pjax-tags-container">
                <?php foreach ($selectors as $selector) : 
                    if (!empty(trim($selector))) : ?>
                        <span class="oow-pjax-tag" data-value="<?php echo esc_attr($selector); ?>">
                            <?php echo esc_html($selector); ?>
                            <span class="oow-pjax-tag-remove">×</span>
                        </span>
                    <?php endif; 
                endforeach; ?>
                <input type="text" class="oow-pjax-tag-input" placeholder="<?php esc_attr_e('Add selector...', 'oow-pjax'); ?>" />
            </div>
            <input type="hidden" name="oow_pjax_form_refresh_targets" class="oow-pjax-tags-hidden" value="<?php echo esc_attr($value); ?>" />
        </div>
        <p class="description"><?php esc_html_e('Additional containers to refresh after form submission (e.g., #comments .comment-form). Press Enter to add.', 'oow-pjax'); ?></p>
        <?php
    }

    public function script_priority_field() {
        $value = get_option('oow_pjax_script_priority', '9999');
        ?>
        <input type="number" name="oow_pjax_script_priority" value="<?php echo esc_attr($value); ?>" min="0" step="1" class="small-text" />
        <p class="description"><?php esc_html_e('Set the priority for loading oow-pjax.js in the footer.', 'oow-pjax'); ?></p>
        <?php
    }

    public function custom_js_before_field() {
        $value = get_option('oow_pjax_custom_js_before', '');
        ?>
        <textarea name="oow_pjax_custom_js_before" class="codemirror-js large-text" rows="10"><?php echo esc_textarea($value); ?></textarea>
        <p class="description"><?php esc_html_e('JavaScript to execute before PJAX navigation starts.', 'oow-pjax'); ?></p>
        <?php
    }

    public function custom_js_after_field() {
        $value = get_option('oow_pjax_custom_js_after', '');
        ?>
        <textarea name="oow_pjax_custom_js_after" class="codemirror-js large-text" rows="10"><?php echo esc_textarea($value); ?></textarea>
        <p class="description"><?php esc_html_e('JavaScript to execute after PJAX navigation completes.', 'oow-pjax'); ?></p>
        <?php
    }

    /**
     * Default loader CSS
     */
    private function default_loader_css() {
        return "#oow-pjax-loader {\n" .
               "    position: fixed !important;\n" .
               "    top: 0 !important;\n" .
               "    left: 0 !important;\n" .
               "    width: 100vw !important;\n" .
               "    height: 100vh !important;\n" .
               "    background: rgba(0, 0, 0, 0.7) !important;\n" .
               "    justify-content: center !important;\n" .
               "    align-items: center !important;\n" .
               "    z-index: 999999 !important;\n" .
               "}\n" .
               "#oow-pjax-loader:after {\n" .
               "    content: '';\n" .
               "    width: 50px !important;\n" .
               "    height: 50px !important;\n" .
               "    border: 5px solid #fff !important;\n" .
               "    border-top: 5px solid transparent !important;\n" .
               "    border-radius: 50% !important;\n" .
               "    animation: spin 1s linear infinite !important;\n" .
               "}\n" .
               "#oow-pjax-error {\n" .
               "    position: fixed !important;\n" .
               "    top: 20px !important;\n" .
               "    left: 50% !important;\n" .
               "    transform: translateX(-50%) !important;\n" .
               "    background: #ff4d4d !important;\n" .
               "    color: white !important;\n" .
               "    padding: 10px 20px !important;\n" .
               "    border-radius: 5px !important;\n" .
               "    z-index: 1000000 !important;\n" .
               "    box-shadow: 0 2px 10px rgba(0,0,0,0.3) !important;\n" .
               "}\n" .
               "@keyframes spin {\n" .
               "    0% { transform: rotate(0deg); }\n" .
               "    100% { transform: rotate(360deg); }\n" .
               "}";
    }
}