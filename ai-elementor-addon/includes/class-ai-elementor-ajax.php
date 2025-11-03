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
        $notification_raw = isset( $_POST['notification'] ) ? wp_unslash( $_POST['notification'] ) : '';
        $notification_settings = self::parse_notification_settings( $notification_raw );

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

        $existing_user_messages = Conversation_Store::count_messages_by_role( (int) $conversation->id, 'user' );

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

        if ( $notification_settings['enabled'] && 0 === $existing_user_messages ) {
            self::dispatch_notification( $conversation, $message, $notification_settings );
        }

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

    /**
     * Normalise notification data posted from the widget.
     *
     * @param string|array $raw_settings Raw notification settings from the request.
     *
     * @return array
     */
    private static function parse_notification_settings( $raw_settings ) {
        $defaults = [
            'enabled'  => false,
            'to'       => '',
            'subject'  => '',
            'fromName' => '',
            'from'     => '',
            'replyTo'  => '',
            'cc'       => '',
            'bcc'      => '',
        ];

        if ( empty( $raw_settings ) ) {
            return $defaults;
        }

        $decoded = is_array( $raw_settings ) ? $raw_settings : json_decode( $raw_settings, true );

        if ( ! is_array( $decoded ) ) {
            return $defaults;
        }

        $settings = array_merge( $defaults, $decoded );

        $settings['enabled']  = ! empty( $settings['enabled'] );
        $settings['to']       = isset( $settings['to'] ) ? \sanitize_text_field( $settings['to'] ) : '';
        $settings['subject']  = isset( $settings['subject'] ) ? \sanitize_text_field( $settings['subject'] ) : '';
        $settings['fromName'] = isset( $settings['fromName'] ) ? \sanitize_text_field( $settings['fromName'] ) : '';
        $settings['from']     = isset( $settings['from'] ) ? \sanitize_email( $settings['from'] ) : '';
        $settings['replyTo']  = isset( $settings['replyTo'] ) ? \sanitize_email( $settings['replyTo'] ) : '';
        $settings['cc']       = isset( $settings['cc'] ) ? \sanitize_text_field( $settings['cc'] ) : '';
        $settings['bcc']      = isset( $settings['bcc'] ) ? \sanitize_text_field( $settings['bcc'] ) : '';

        return $settings;
    }

    /**
     * Send the configured notification when the first message arrives.
     *
     * @param object $conversation Conversation model.
     * @param string $message      First visitor message.
     * @param array  $settings     Parsed notification settings.
     */
    private static function dispatch_notification( $conversation, $message, array $settings ) {
        if ( empty( $settings['enabled'] ) ) {
            return;
        }

        $recipients = self::parse_recipient_list( $settings['to'] );

        if ( empty( $recipients ) ) {
            return;
        }

        $site_name = \get_bloginfo( 'name' );
        $page_title = '';

        if ( ! empty( $conversation->page_url ) ) {
            $page_id = \url_to_postid( $conversation->page_url );

            if ( $page_id ) {
                $page = \get_post( $page_id );

                if ( $page ) {
                    $page_title = $page->post_title;
                }
            }
        }

        $subject_template = ! empty( $settings['subject'] ) ? $settings['subject'] : __( 'New AI chat from {site_name}', 'ai-elementor-addon' );
        $replacements      = [
            '{site_name}'  => $site_name,
            '{session_id}' => $conversation ? $conversation->session_id : '',
            '{page_title}' => $page_title,
        ];

        $subject = strtr( $subject_template, $replacements );

        $lines = [];
        $lines[] = sprintf( __( 'A new AI chat conversation has started on %s.', 'ai-elementor-addon' ), $site_name );
        $lines[] = '';
        $lines[] = __( 'First visitor message:', 'ai-elementor-addon' );
        $lines[] = trim( \wp_strip_all_tags( $message ) );
        $lines[] = '';

        if ( ! empty( $conversation->page_url ) ) {
            $lines[] = sprintf( __( 'Page: %s', 'ai-elementor-addon' ), $conversation->page_url );
        }

        if ( ! empty( $page_title ) ) {
            $lines[] = sprintf( __( 'Page title: %s', 'ai-elementor-addon' ), $page_title );
        }

        if ( ! empty( $conversation->visitor_ip ) ) {
            $lines[] = sprintf( __( 'Visitor IP: %s', 'ai-elementor-addon' ), $conversation->visitor_ip );
        }

        if ( ! empty( $conversation->user_agent ) ) {
            $lines[] = sprintf( __( 'User agent: %s', 'ai-elementor-addon' ), $conversation->user_agent );
        }

        if ( ! empty( $conversation->referer ) ) {
            $lines[] = sprintf( __( 'Referrer: %s', 'ai-elementor-addon' ), $conversation->referer );
        }

        $lines[] = sprintf( __( 'Session ID: %s', 'ai-elementor-addon' ), $conversation ? $conversation->session_id : '' );
        $lines[] = sprintf( __( 'Started at: %s', 'ai-elementor-addon' ), $conversation ? $conversation->created_at : current_time( 'mysql' ) );

        $body = implode( "\n", array_filter( $lines ) );

        $headers = [ 'Content-Type: text/plain; charset=UTF-8' ];

        if ( ! empty( $settings['from'] ) ) {
            $from_name = $settings['fromName'] ? $settings['fromName'] : $site_name;
            $headers[] = sprintf( 'From: %s <%s>', $from_name, $settings['from'] );
        }

        if ( ! empty( $settings['replyTo'] ) && \is_email( $settings['replyTo'] ) ) {
            $headers[] = 'Reply-To: ' . $settings['replyTo'];
        }

        $cc  = self::parse_recipient_list( $settings['cc'] );
        $bcc = self::parse_recipient_list( $settings['bcc'] );

        if ( ! empty( $cc ) ) {
            $headers[] = 'Cc: ' . implode( ', ', $cc );
        }

        if ( ! empty( $bcc ) ) {
            $headers[] = 'Bcc: ' . implode( ', ', $bcc );
        }

        \wp_mail( $recipients, $subject, $body, $headers );
    }

    /**
     * Parse a comma separated list of email addresses.
     *
     * @param string|array $list Email addresses.
     *
     * @return array
     */
    private static function parse_recipient_list( $list ) {
        if ( empty( $list ) ) {
            return [];
        }

        $items = is_array( $list ) ? $list : explode( ',', $list );
        $emails = [];

        foreach ( $items as $item ) {
            $email = trim( $item );

            if ( $email && \is_email( $email ) ) {
                $emails[] = $email;
            }
        }

        return array_values( array_unique( $emails ) );
    }
}

Ajax::init();

