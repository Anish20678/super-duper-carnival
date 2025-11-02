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
        add_action( 'wp_ajax_ai_elementor_start_session', [ __CLASS__, 'handle_start_session' ] );
        add_action( 'wp_ajax_nopriv_ai_elementor_start_session', [ __CLASS__, 'handle_start_session' ] );
    }

    /**
     * Handle generation requests for widgets.
     */
    public static function handle_generate() {
        check_ajax_referer( 'ai-elementor-addon', 'nonce' );

        $mode        = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'chat';
        $session_id  = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
        $message     = isset( $_POST['message'] ) ? wp_unslash( $_POST['message'] ) : '';
        $model       = isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( $_POST['model'] ) ) : '';
        $temperature = isset( $_POST['temperature'] ) ? floatval( wp_unslash( $_POST['temperature'] ) ) : null;

        $config = Plugin::get_ai_configuration();

        if ( empty( $config['api_key'] ) ) {
            wp_send_json_error( [ 'message' => __( 'OpenAI API key missing.', 'ai-elementor-addon' ) ] );
        }

        $model = $model ?: $config['default_model'];

        if ( 'chat' !== $mode ) {
            $payload = isset( $_POST['payload'] ) ? wp_unslash( $_POST['payload'] ) : '';

            if ( empty( $payload ) ) {
                wp_send_json_error( [ 'message' => __( 'No prompt provided.', 'ai-elementor-addon' ) ] );
            }

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
                            'model'             => $model,
                            'input'             => $prompt,
                            'max_output_tokens' => 800,
                            'temperature'       => null !== $temperature ? $temperature : 0.7,
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

        if ( empty( $session_id ) ) {
            wp_send_json_error( [ 'message' => __( 'Chat session missing. Please refresh the page.', 'ai-elementor-addon' ) ] );
        }

        if ( '' === trim( $message ) ) {
            wp_send_json_error( [ 'message' => __( 'No message provided.', 'ai-elementor-addon' ) ] );
        }

        $conversation = Conversation_Store::get_conversation_by_session( $session_id );

        if ( ! $conversation ) {
            wp_send_json_error(
                [
                    'message' => __( 'Chat session could not be located. Please start a new conversation.', 'ai-elementor-addon' ),
                    'code'    => 'session_missing',
                ]
            );
        }

        if ( Conversation_Store::is_expired( $conversation ) ) {
            Conversation_Store::close_conversation( (int) $conversation->id );

            wp_send_json_error(
                [
                    'message' => __( 'This chat session has expired. Please start a new conversation.', 'ai-elementor-addon' ),
                    'code'    => 'session_expired',
                ]
            );
        }

        Conversation_Store::add_message( (int) $conversation->id, 'user', $message );

        $history      = Conversation_Store::get_messages( (int) $conversation->id );
        $prompt       = Conversation_Store::build_prompt_from_history( $conversation, $history );
        $temperature  = null !== $temperature ? max( 0, min( 2, $temperature ) ) : null;

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

        Conversation_Store::add_message( (int) $conversation->id, 'assistant', $text );

        $history_output = Conversation_Store::get_history_for_output( (int) $conversation->id );
        $conversation   = Conversation_Store::get_conversation_by_session( $session_id );

        wp_send_json_success(
            [
                'message'     => wp_kses_post( $text ),
                'sessionId'   => $session_id,
                'history'     => $history_output,
                'sessionMeta' => [
                    'updatedAt' => $conversation ? $conversation->updated_at : current_time( 'mysql' ),
                ],
            ]
        );
    }

    /**
     * Handle chat session bootstrap and history retrieval.
     *
     * @return void
     */
    public static function handle_start_session() {
        check_ajax_referer( 'ai-elementor-addon', 'nonce' );

        $session_id     = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
        $widget_id      = isset( $_POST['widget_id'] ) ? sanitize_key( wp_unslash( $_POST['widget_id'] ) ) : '';
        $prompt_context = isset( $_POST['prompt_context'] ) ? wp_unslash( $_POST['prompt_context'] ) : '';
        $page_url       = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '';
        $referrer       = isset( $_POST['referrer'] ) ? esc_url_raw( wp_unslash( $_POST['referrer'] ) ) : '';

        $result = Conversation_Store::start_session(
            $session_id,
            [
                'widget_id'      => $widget_id,
                'prompt_context' => $prompt_context,
                'page_url'       => $page_url,
                'referrer'       => $referrer,
            ]
        );

        $history = array_map(
            function( $entry ) {
                return [
                    'role'    => $entry['role'],
                    'content' => $entry['content'],
                    'time'    => $entry['time'],
                ];
            },
            $result['history']
        );

        wp_send_json_success(
            [
                'sessionId'    => $result['session_id'],
                'history'      => $history,
                'sessionState' => $result['session_state'],
                'timeout'      => Conversation_Store::SESSION_TIMEOUT,
            ]
        );
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

