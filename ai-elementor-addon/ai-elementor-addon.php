<?php
/**
 * Plugin Name: AI Elementor Addon
 * Description: Adds AI-powered Elementor widgets with OpenAI integration and granular controls.
 * Version: 1.0.0
 * Author: OpenAI Assistant
 * Text Domain: ai-elementor-addon
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'AI_ELEMENTOR_ADDON_PATH' ) ) {
    define( 'AI_ELEMENTOR_ADDON_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'AI_ELEMENTOR_ADDON_URL' ) ) {
    define( 'AI_ELEMENTOR_ADDON_URL', plugin_dir_url( __FILE__ ) );
}

require_once AI_ELEMENTOR_ADDON_PATH . 'includes/class-ai-elementor-settings.php';
require_once AI_ELEMENTOR_ADDON_PATH . 'includes/class-ai-elementor-addon.php';
require_once AI_ELEMENTOR_ADDON_PATH . 'includes/class-ai-elementor-admin.php';
require_once AI_ELEMENTOR_ADDON_PATH . 'includes/class-ai-elementor-ajax.php';

/**
 * Bootstrap plugin after Elementor is loaded.
 */
function ai_elementor_addon_init() {
    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-warning"><p>' . esc_html__( 'AI Elementor Addon requires Elementor to be active.', 'ai-elementor-addon' ) . '</p></div>';
        } );
        return;
    }

    \AI_Elementor_Addon\Plugin::instance();
}
add_action( 'plugins_loaded', 'ai_elementor_addon_init' );

// Initialize admin after plugins loaded to ensure settings exist.
add_action( 'plugins_loaded', function() {
    new \AI_Elementor_Addon\Admin();
} );


register_activation_hook( __FILE__, function() {
    if ( ! get_option( \AI_Elementor_Addon\Settings::OPTION_KEY ) ) {
        \AI_Elementor_Addon\Settings::update_settings( [] );
    }
} );

