<?php
namespace AI_Elementor_Addon;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles AJAX bridge between Elementor widgets and OpenAI.
 */
class Ajax {

    /**
     * Register AJAX actions.
     */
    public static function init() {
        add_action( 'wp_ajax_ai_elementor_generate', [ __CLASS__, 'handle_generate' ] );
        add_action( 'wp_ajax_nopriv_ai_elementor_generate', [ __CLASS__, 'handle_generate' ] );
    }

    /**
     * Handle generation requests for widgets.
     */
    public static function handle_generate() {
        check_ajax_referer( 'ai-elementor-addon', 'nonce' );

        $payload     = isset( $_POST['payload'] ) ? wp_unslash( $_POST['payload'] ) : '';
        $mode        = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'chat';
        $model       = isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( $_POST['model'] ) ) : '';
        $temperature = isset( $_POST['temperature'] ) ? floatval( wp_unslash( $_POST['temperature'] ) ) : null;

        if ( empty( $payload ) ) {
            wp_send_json_error( [ 'message' => __( 'No prompt provided.', 'ai-elementor-addon' ) ] );
        }

        $config = Plugin::get_ai_configuration();

        if ( empty( $config['api_key'] ) ) {
            wp_send_json_error( [ 'message' => __( 'OpenAI API key missing.', 'ai-elementor-addon' ) ] );
        }

        $model = $model ?: $config['default_model'];

        $prompt      = self::build_prompt( $mode, $payload );
        $temperature = null !== $temperature ? max( 0, min( 2, $temperature ) ) : null;

        $response = wp_remote_post(
            'https://api.openai.com/v1/responses',
            [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $config['api_key'],
                ],
                'body'    => wp_json_encode(
                    [
                        'model'   => $model,
                        'input'   => $prompt,
                        'max_output_tokens' => 800,
                        'temperature' => null !== $temperature ? $temperature : 0.7,
                    ]
                ),
                'timeout' => 45,
            ]
        );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( [ 'message' => $response->get_error_message() ] );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $body['output'][0]['content'][0]['text'] ) && empty( $body['output_text'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Unexpected response from OpenAI.', 'ai-elementor-addon' ) ] );
        }

        $text = '';

        if ( ! empty( $body['output'][0]['content'][0]['text'] ) ) {
            $text = $body['output'][0]['content'][0]['text'];
        } elseif ( ! empty( $body['output_text'] ) ) {
            $text = $body['output_text'];
        }

        wp_send_json_success( [ 'message' => wp_kses_post( $text ) ] );
    }

    /**
     * Build context sensitive prompt string.
     *
     * @param string $mode
     * @param array|string $payload
     *
     * @return string
     */
    private static function build_prompt( $mode, $payload ) {
        if ( is_array( $payload ) ) {
            $payload = wp_json_encode( $payload );
        }

        switch ( $mode ) {
            case 'brainstorm':
                return sprintf( 'You are an imaginative strategist. Provide structured bullet lists for: %s', $payload );
            case 'image_prompt':
                return sprintf( 'Craft a single detailed image prompt. Consider attributes: %s', $payload );
            default:
                return $payload;
        }
    }
}

Ajax::init();

