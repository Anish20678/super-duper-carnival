<?php
namespace AI_Elementor_Addon;

use Elementor\Widgets_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Core plugin loader for Elementor widgets.
 */
class Plugin {

    /**
     * Singleton instance.
     *
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * Widgets registered by the addon.
     *
     * @var array
     */
    private $widgets = [
        'ai_chat'         => '\\AI_Elementor_Addon\\Widgets\\AI_Chat_Widget',
        'ai_brainstorm'   => '\\AI_Elementor_Addon\\Widgets\\AI_Brainstorm_Widget',
        'ai_image_prompt' => '\\AI_Elementor_Addon\\Widgets\\AI_Image_Prompt_Widget',
    ];

    /**
     * Retrieve singleton instance.
     *
     * @return Plugin
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Plugin constructor.
     */
    private function __construct() {
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Register custom Elementor category for AI widgets.
     */
    public function register_categories( $elements_manager ) {
        $elements_manager->add_category(
            'ai-elementor-addon',
            [
                'title' => __( 'AI Elementor Addon', 'ai-elementor-addon' ),
                'icon'  => 'fa fa-robot',
            ]
        );
    }

    /**
     * Register widgets that are enabled in settings.
     */
    public function register_widgets( Widgets_Manager $widgets_manager ) {
        foreach ( $this->widgets as $widget_id => $class ) {
            if ( ! Settings::is_widget_enabled( $widget_id ) ) {
                continue;
            }

            if ( ! class_exists( $class ) ) {
                $this->include_widget( $widget_id );
            }

            if ( class_exists( $class ) ) {
                $widgets_manager->register( new $class() );
            }
        }
    }

    /**
     * Include widget file when required.
     *
     * @param string $widget_id
     */
    private function include_widget( $widget_id ) {
        $map = [
            'ai_chat'         => 'class-widget-ai-chat.php',
            'ai_brainstorm'   => 'class-widget-ai-brainstorm.php',
            'ai_image_prompt' => 'class-widget-ai-image-prompt.php',
        ];

        if ( isset( $map[ $widget_id ] ) ) {
            require_once AI_ELEMENTOR_ADDON_PATH . 'includes/widgets/' . $map[ $widget_id ];
        }
    }

    /**
     * Enqueue frontend assets for widgets.
     */
    public function enqueue_assets() {
        wp_register_style(
            'ai-elementor-addon',
            AI_ELEMENTOR_ADDON_URL . 'assets/css/frontend.css',
            [],
            '1.1.0'
        );

        wp_register_script(
            'ai-elementor-addon',
            AI_ELEMENTOR_ADDON_URL . 'assets/js/frontend.js',
            [ 'jquery' ],
            '1.1.0',
            true
        );

        wp_localize_script(
            'ai-elementor-addon',
            'AIElementorAddon',
            [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'ai-elementor-addon' ),
                'timeout' => \AI_Elementor_Addon\Conversation_Store::SESSION_TIMEOUT,
            ]
        );
    }

    /**
     * Helper to fetch OpenAI configuration for widgets.
     *
     * @return array
     */
    public static function get_ai_configuration() {
        $settings = Settings::get_settings();

        return [
            'api_key'       => $settings['api_key'],
            'default_model' => $settings['default_model'],
        ];
    }
}

