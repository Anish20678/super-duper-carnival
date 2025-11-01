<?php
namespace AI_Elementor_Addon;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shared settings helper.
 */
class Settings {

    const OPTION_KEY = 'ai_elementor_addon_settings';

    /**
     * Get plugin settings with defaults.
     *
     * @return array
     */
    public static function get_settings() {
        $defaults = [
            'api_key'          => '',
            'default_model'    => 'gpt-4o-mini',
            'enabled_widgets'  => [ 'ai_chat', 'ai_brainstorm', 'ai_image_prompt' ],
            'usage_logging'    => true,
            'future_widgets'   => [ 'ai_personal_trainer', 'ai_copy_optimizer' ],
        ];

        $settings = get_option( self::OPTION_KEY, [] );

        return wp_parse_args( $settings, $defaults );
    }

    /**
     * Update settings safely.
     *
     * @param array $values
     */
    public static function update_settings( array $values ) {
        $settings = self::get_settings();

        $settings['api_key']         = isset( $values['api_key'] ) ? sanitize_text_field( $values['api_key'] ) : $settings['api_key'];
        $settings['default_model']   = isset( $values['default_model'] ) ? sanitize_text_field( $values['default_model'] ) : $settings['default_model'];
        $settings['usage_logging']   = isset( $values['usage_logging'] ) ? (bool) $values['usage_logging'] : $settings['usage_logging'];

        if ( isset( $values['enabled_widgets'] ) && is_array( $values['enabled_widgets'] ) ) {
            $settings['enabled_widgets'] = array_values( array_unique( array_map( 'sanitize_key', $values['enabled_widgets'] ) ) );
        }

        if ( isset( $values['future_widgets'] ) && is_array( $values['future_widgets'] ) ) {
            $settings['future_widgets'] = array_map( 'sanitize_text_field', $values['future_widgets'] );
        }

        update_option( self::OPTION_KEY, $settings );
    }

    /**
     * Determine if a widget is active.
     *
     * @param string $widget_id
     *
     * @return bool
     */
    public static function is_widget_enabled( $widget_id ) {
        $settings = self::get_settings();

        return in_array( sanitize_key( $widget_id ), $settings['enabled_widgets'], true );
    }
}

