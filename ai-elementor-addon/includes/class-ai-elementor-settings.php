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
        $settings = get_option( self::OPTION_KEY, [] );

        return wp_parse_args( $settings, self::get_default_settings() );
    }

    /**
     * Retrieve default plugin settings.
     *
     * @return array
     */
    private static function get_default_settings() {
        return [
            'api_key'          => '',
            'default_model'    => 'gpt-4o-mini',
            'enabled_widgets'  => [ 'ai_chat', 'ai_brainstorm', 'ai_image_prompt' ],
            'usage_logging'    => true,
            'future_widgets'   => [ 'ai_personal_trainer', 'ai_copy_optimizer' ],
        ];
    }

    /**
     * Merge incoming values with stored settings and defaults.
     *
     * @param array $values
     * @param array $base
     *
     * @return array
     */
    private static function merge_settings( array $values, array $base ) {
        $settings = wp_parse_args( $base, self::get_default_settings() );

        if ( isset( $values['api_key'] ) ) {
            $settings['api_key'] = sanitize_text_field( $values['api_key'] );
        }

        if ( isset( $values['default_model'] ) ) {
            $settings['default_model'] = sanitize_text_field( $values['default_model'] );
        }

        if ( isset( $values['usage_logging'] ) ) {
            $settings['usage_logging'] = (bool) $values['usage_logging'];
        }

        if ( isset( $values['enabled_widgets'] ) && is_array( $values['enabled_widgets'] ) ) {
            $settings['enabled_widgets'] = array_values( array_unique( array_map( 'sanitize_key', $values['enabled_widgets'] ) ) );
        }

        if ( isset( $values['future_widgets'] ) && is_array( $values['future_widgets'] ) ) {
            $settings['future_widgets'] = array_map( 'sanitize_text_field', $values['future_widgets'] );
        }

        return $settings;
    }

    /**
     * Update settings safely.
     *
     * @param array $values
     */
    public static function update_settings( array $values ) {
        $settings = self::merge_settings( $values, self::get_settings() );

        update_option( self::OPTION_KEY, $settings );
    }

    /**
     * Sanitize settings array without persisting to the database.
     *
     * @param array $values
     *
     * @return array
     */
    public static function sanitize_settings_array( array $values ) {
        return self::merge_settings( $values, self::get_settings() );
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

