<?php
/*
Plugin Name: OOW PJAX
Description: Transforms a WordPress site into a PJAX (PushState + AJAX) experience without jQuery.
Version: 1.4
Author: oowpress
Author URI: https://oowcode.com
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: oow-pjax
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit; // Prevent direct access to this file
}

/**
 * Define plugin constants
 */
define('OOW_PJAX_VERSION', get_file_data(__FILE__, array('Version' => 'Version'))['Version']);
define('OOW_PJAX_NAME', get_file_data(__FILE__, array('PluginName' => 'Plugin Name'))['PluginName']);
define('OOW_PJAX_SLUG', 'oow-pjax/oow-pjax.php');
define('OOW_PJAX_API_URL', 'https://oowcode.com/plugins/?q=oow-pjax');
define('OOW_PJAX_PAGE_URL', 'https://oowcode.com/oow-pjax');
define('OOW_PJAX_DIR', plugin_dir_path(__FILE__));
define('OOW_PJAX_URL', plugin_dir_url(__FILE__));

/**
 * Include main plugin classes
 */
if (!class_exists('OOW_Extensions')) {
    require_once OOW_PJAX_DIR . 'includes/class-oow-extensions.php'; // Load OOW_Extensions class if not already loaded
}
require_once OOW_PJAX_DIR . 'includes/class-oow-pjax.php'; // Load OOW_PJAX class

// Instantiate OOW_Extensions using singleton pattern
OOW_Extensions::get_instance();

// Instantiate OOW_PJAX
new OOW_PJAX();