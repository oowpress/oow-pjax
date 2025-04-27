<?php
/**
 * OOW Extensions Class
 *
 * Manages the OOWCODE extensions dashboard, including plugin browsing,
 * installation, and admin interface rendering for both official WordPress.org
 * plugins and beta candidate plugins. Designed to be reusable across multiple plugins.
 *
 * @package OOW_Extensions
 * @since 1.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class OOW_Extensions {
    /**
     * Singleton instance
     *
     * @var OOW_Extensions|null
     */
    private static $instance = null;

    /**
     * Get Singleton Instance
     *
     * Ensures only one instance of OOW_Extensions is created.
     *
     * @return OOW_Extensions
     * @since 1.4
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * Initializes the extensions dashboard by registering hooks.
     *
     * @since 1.4
     */
    private function __construct() {

        add_action('admin_head', array($this, 'add_inline_admin_styles'));
        add_action('admin_menu', array($this, 'admin_menu'), 5);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'), 5);
        add_action('wp_ajax_oow_save_theme', array($this, 'save_theme'));
        add_action('admin_head', array($this, 'add_critical_styles'));
    }

    /**
     * Enqueue Admin Styles
     *
     * Loads CSS and Google Fonts for the extensions dashboard.
     *
     * @param string $hook The current admin page hook.
     * @since 1.4
     */
    public function enqueue_admin_styles($hook) {
        if (strpos($hook, 'oow-extensions') !== false) {
            wp_enqueue_style(
                'oow-extensions-style',
                plugins_url('/assets/css/oow-extensions.css', dirname(__FILE__)),
                array(),
                '1.4',
                'all'
            );
            wp_enqueue_style(
                'oow-google-fonts',
                'https://fonts.googleapis.com/css2?family=Blinker:wght@100;200;300&display=swap',
                array(),
                null
            );
        }
    }

    /**
     * Add Critical Styles
     *
     * Injects minimal CSS to prevent FOUC by hiding content until styles are loaded.
     *
     * @since 1.5
     */
    public function add_critical_styles() {
        if (strpos(get_current_screen()->id, 'oow-extensions') !== false) {
            echo '<style>
                .wrap.oow-loading { opacity: 0; }
                .wrap { transition: opacity 0.2s; }
                </style>';
            echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Blinker:wght@100;200;300&display=swap" as="style" onload="this.rel=\'stylesheet\'">';
        }
    }

    /**
     * Add Inline Admin Styles
     *
     * Injects minimal CSS to ensure proper menu display and button alignment.
     *
     * @since 1.4
     */
    public function add_inline_admin_styles() {
        echo '<style>
        #toplevel_page_oow-extensions ul {
            white-space: nowrap;
        }
        .oow-plugin-status {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .oow-plugin-status .button-primary {
            margin: 0;
        }
        </style>';
    }

    /**
     * Register Admin Menu
     *
     * Adds the OOWCODE top-level menu and the extensions submenu page.
     *
     * @since 1.4
     */
    public function admin_menu() {
        $parent_slug = 'oow-extensions';

        // Add top-level menu
        add_menu_page(
            __('OOWCODE Dashboard', 'oowcode-custom-menu-shortcode'),
            __('OOWCODE', 'oowcode-custom-menu-shortcode'),
            'manage_options',
            $parent_slug,
            array($this, 'render_extensions_page'),
            'dashicons-superhero',
            80
        );

        // Add extensions submenu
        add_submenu_page(
            $parent_slug,
            __('OOW Extensions', 'oowcode-custom-menu-shortcode'),
            __('OOW Extensions', 'oowcode-custom-menu-shortcode'),
            'manage_options',
            $parent_slug,
            array($this, 'render_extensions_page')
        );
    }

    /**
     * Render Extensions Page
     *
     * Displays the extensions dashboard with tabs for official and beta plugins, each containing an iframe.
     *
     * @since 1.4
     */
    public function render_extensions_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $nonce = wp_create_nonce('oow_admin_nonce');
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'official';
        $current_theme = get_option('oowcode_admin_theme', 'dark');

        ?>
        <div class="wrap oow-loading">
            <div class="oow-header">
                <h1 class="oow-title">
                    <span class="text-logo"><?php echo esc_html__('OOW', 'oowcode-custom-menu-shortcode'); ?></span>
                    <?php echo esc_html__('Extensions', 'oowcode-custom-menu-shortcode'); ?>
                    <span class="author"><?php echo esc_html__('By OOWCODE', 'oowcode-custom-menu-shortcode'); ?></span>
                </h1>
                <button id="oow-theme-toggle" class="theme-toggle-btn">
                    <?php echo $current_theme === 'dark' ? esc_html__('Light Mode', 'oowcode-custom-menu-shortcode') : esc_html__('Dark Mode', 'oowcode-custom-menu-shortcode'); ?>
                </button>
            </div>
            <div class="oow-notices"></div>
            <h2 class="nav-tab-wrapper">
                <a href="?page=oow-extensions&tab=official&nonce=<?php echo esc_attr($nonce); ?>" class="nav-tab <?php echo $tab === 'official' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('Official WordPress Plugins', 'oowcode-custom-menu-shortcode'); ?>
                </a>
                <a href="?page=oow-extensions&tab=beta&nonce=<?php echo esc_attr($nonce); ?>" class="nav-tab <?php echo $tab === 'beta' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('OOWCODE Labs & Updates', 'oowcode-custom-menu-shortcode'); ?>
                </a>
            </h2>

            <div class="oow-tab-content">
                <?php if ($tab === 'official') : ?>
                    <iframe src="https://oowcode.com/wp-support/wordpress-plugins/" style="width: 100%; height: 70vh; border: none;"></iframe>
                <?php else : ?>
                    <iframe src="https://oowcode.com/wp-support/oowcode-labs-updates/" style="width: 100%; height: 70vh; border: none;"></iframe>
                <?php endif; ?>
            </div>
        </div>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                const wrap = document.querySelector('.wrap.oow-loading');
                const body = document.body;
                let current_theme = '<?php echo esc_js($current_theme); ?>';

                // Apply theme and remove FOUC
                body.classList.add('oow-theme-' + current_theme);
                if (wrap) {
                    wrap.classList.remove('oow-loading');
                }

                // Theme toggle handler
                const toggleBtn = document.getElementById('oow-theme-toggle');
                if (toggleBtn) {
                    toggleBtn.addEventListener('click', function() {
                        const newTheme = current_theme === 'dark' ? 'light' : 'dark';
                        body.classList.remove('oow-theme-' + current_theme);
                        body.classList.add('oow-theme-' + newTheme);
                        current_theme = newTheme;
                        toggleBtn.textContent = newTheme === 'dark' ? '<?php echo esc_js(__('Light Mode', 'oowcode-custom-menu-shortcode')); ?>' : '<?php echo esc_js(__('Dark Mode', 'oowcode-custom-menu-shortcode')); ?>';
                        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                action: 'oow_save_theme',
                                theme: newTheme,
                                nonce: '<?php echo esc_js(wp_create_nonce('oow_theme_nonce')); ?>'
                            })
                        });
                    });
                }

                // Move notices to container
                setTimeout(function() {
                    const notices = document.querySelectorAll('.notice');
                    const noticeContainer = document.querySelector('.oow-notices');
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
     * Save Theme Preference
     *
     * Updates the admin theme option (light/dark) via AJAX with nonce verification.
     *
     * @since 1.4
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







}