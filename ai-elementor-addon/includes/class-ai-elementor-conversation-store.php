<?php
namespace AI_Elementor_Addon;

use wpdb;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Lightweight persistence layer for AI chat conversations.
 */
class Conversation_Store {

    const CONVERSATIONS_TABLE = 'ai_elementor_conversations';
    const MESSAGES_TABLE      = 'ai_elementor_messages';
    const SESSION_TIMEOUT     = 7 * MINUTE_IN_SECONDS;

    /**
     * Create or upgrade database tables.
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $conversations   = self::get_table_name( self::CONVERSATIONS_TABLE );
        $messages        = self::get_table_name( self::MESSAGES_TABLE );

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql_conversations = "CREATE TABLE {$conversations} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id VARCHAR(64) NOT NULL,
            widget_id VARCHAR(120) NOT NULL,
            prompt_context LONGTEXT NULL,
            page_url TEXT NULL,
            referer TEXT NULL,
            user_agent TEXT NULL,
            visitor_ip VARCHAR(100) DEFAULT '',
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY session_id (session_id)
        ) {$charset_collate};";

        $sql_messages = "CREATE TABLE {$messages} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            conversation_id BIGINT UNSIGNED NOT NULL,
            role VARCHAR(20) NOT NULL,
            message LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY conversation_id (conversation_id)
        ) {$charset_collate};";

        dbDelta( $sql_conversations );
        dbDelta( $sql_messages );
    }

    /**
     * Ensure tables exist on runtime (failsafe for upgrades).
     */
    public static function ensure_tables_exist() {
        global $wpdb;
        $conversations = self::get_table_name( self::CONVERSATIONS_TABLE );
        $messages      = self::get_table_name( self::MESSAGES_TABLE );

        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $conversations ) ) !== $conversations ) {
            self::create_tables();
        }

        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $messages ) ) !== $messages ) {
            self::create_tables();
        }
    }

    /**
     * Start or resume a chat session.
     *
     * @param string $session_id
     * @param array  $meta
     *
     * @return array
     */
    public static function start_session( $session_id, array $meta = [] ) {
        global $wpdb;

        self::ensure_tables_exist();

        $session_id   = sanitize_text_field( $session_id );
        $conversation = null;

        if ( $session_id ) {
            $conversation = self::get_conversation_by_session( $session_id );

            if ( $conversation && self::is_expired( $conversation ) ) {
                self::close_conversation( (int) $conversation->id );
                $conversation = null;
            } elseif ( $conversation ) {
                $updates = [];

                if ( ! empty( $meta['widget_id'] ) ) {
                    $widget_id = sanitize_key( $meta['widget_id'] );

                    if ( $widget_id && $widget_id !== $conversation->widget_id ) {
                        $updates['widget_id'] = $widget_id;
                    }
                }

                if ( ! empty( $meta['prompt_context'] ) ) {
                    $prompt_context = self::sanitize_prompt_context( $meta['prompt_context'] );

                    if ( $prompt_context !== $conversation->prompt_context ) {
                        $updates['prompt_context'] = $prompt_context;
                    }
                }

                if ( ! empty( $meta['page_url'] ) && empty( $conversation->page_url ) ) {
                    $updates['page_url'] = esc_url_raw( $meta['page_url'] );
                }

                if ( ! empty( $meta['referrer'] ) && empty( $conversation->referer ) ) {
                    $updates['referer'] = esc_url_raw( $meta['referrer'] );
                }

                if ( $updates ) {
                    global $wpdb;

                    $wpdb->update(
                        self::get_table_name( self::CONVERSATIONS_TABLE ),
                        $updates,
                        [ 'id' => $conversation->id ],
                        array_fill( 0, count( $updates ), '%s' ),
                        [ '%d' ]
                    );

                    $conversation = self::get_conversation_by_session( $session_id );
                }
            }
        }

        if ( $conversation ) {
            return [
                'session_id'    => $conversation->session_id,
                'conversation'  => $conversation,
                'history'       => self::get_history_for_output( (int) $conversation->id ),
                'session_state' => 'continued',
            ];
        }

        $new_session_id = $session_id ? $session_id : self::generate_session_id();
        $now            = current_time( 'mysql' );

        $data = [
            'session_id'     => $new_session_id,
            'widget_id'      => isset( $meta['widget_id'] ) ? sanitize_key( $meta['widget_id'] ) : '',
            'prompt_context' => isset( $meta['prompt_context'] ) ? self::sanitize_prompt_context( $meta['prompt_context'] ) : '',
            'page_url'       => isset( $meta['page_url'] ) ? esc_url_raw( $meta['page_url'] ) : '',
            'referer'        => isset( $meta['referrer'] ) ? esc_url_raw( $meta['referrer'] ) : '',
            'user_agent'     => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
            'visitor_ip'     => self::detect_ip_address(),
            'status'         => 'active',
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        $wpdb->insert( self::get_table_name( self::CONVERSATIONS_TABLE ), $data, [ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ] );
        $conversation_id = (int) $wpdb->insert_id;

        $conversation = self::get_conversation( $conversation_id );

        return [
            'session_id'    => $conversation ? $conversation->session_id : $new_session_id,
            'conversation'  => $conversation,
            'history'       => [],
            'session_state' => 'new',
        ];
    }

    /**
     * Retrieve a conversation row.
     *
     * @param int $id
     *
     * @return object|null
     */
    public static function get_conversation( $id ) {
        global $wpdb;

        self::ensure_tables_exist();

        $table = self::get_table_name( self::CONVERSATIONS_TABLE );

        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
    }

    /**
     * Retrieve conversation by session identifier.
     *
     * @param string $session_id
     *
     * @return object|null
     */
    public static function get_conversation_by_session( $session_id ) {
        global $wpdb;

        self::ensure_tables_exist();

        $session_id = sanitize_text_field( $session_id );

        if ( ! $session_id ) {
            return null;
        }

        $table = self::get_table_name( self::CONVERSATIONS_TABLE );

        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE session_id = %s", $session_id ) );
    }

    /**
     * Return a paginated list of conversations.
     *
     * @param int $paged
     * @param int $per_page
     *
     * @return array
     */
    public static function get_conversations( $paged = 1, $per_page = 20 ) {
        global $wpdb;

        self::ensure_tables_exist();

        $table = self::get_table_name( self::CONVERSATIONS_TABLE );
        $paged = max( 1, (int) $paged );
        $limit = max( 1, (int) $per_page );
        $offset = ( $paged - 1 ) * $limit;

        $rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d", $limit, $offset ) );

        return is_array( $rows ) ? $rows : [];
    }

    /**
     * Count total stored conversations.
     *
     * @return int
     */
    public static function get_conversation_count() {
        global $wpdb;

        self::ensure_tables_exist();

        $table = self::get_table_name( self::CONVERSATIONS_TABLE );

        $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

        return $count ? (int) $count : 0;
    }

    /**
     * Add a message to a conversation.
     *
     * @param int    $conversation_id
     * @param string $role
     * @param string $message
     */
    public static function add_message( $conversation_id, $role, $message ) {
        global $wpdb;

        self::ensure_tables_exist();

        $conversation_id = (int) $conversation_id;

        if ( ! $conversation_id ) {
            return;
        }

        $role    = sanitize_key( $role );
        $message = self::sanitize_message_for_role( $message, $role );

        $wpdb->insert(
            self::get_table_name( self::MESSAGES_TABLE ),
            [
                'conversation_id' => $conversation_id,
                'role'            => $role,
                'message'         => $message,
                'created_at'      => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%s', '%s' ]
        );

        self::touch_conversation( $conversation_id );
    }

    /**
     * Fetch messages for prompt construction.
     *
     * @param int $conversation_id
     *
     * @return array
     */
    public static function get_messages( $conversation_id ) {
        global $wpdb;

        self::ensure_tables_exist();

        $conversation_id = (int) $conversation_id;

        if ( ! $conversation_id ) {
            return [];
        }

        $table = self::get_table_name( self::MESSAGES_TABLE );

        $rows = $wpdb->get_results( $wpdb->prepare( "SELECT role, message, created_at FROM {$table} WHERE conversation_id = %d ORDER BY id ASC", $conversation_id ), ARRAY_A );

        return is_array( $rows ) ? $rows : [];
    }

    /**
     * Convert message history into simplified array for output.
     *
     * @param int $conversation_id
     *
     * @return array
     */
    public static function get_history_for_output( $conversation_id ) {
        $messages = self::get_messages( $conversation_id );
        $history  = [];

        foreach ( $messages as $message ) {
            $history[] = [
                'role'    => $message['role'],
                'content' => self::prepare_message_for_output( $message['message'], $message['role'] ),
                'time'    => $message['created_at'],
            ];
        }

        return $history;
    }

    /**
     * Count messages for a given role within a conversation.
     *
     * @param int    $conversation_id Conversation identifier.
     * @param string $role            Message role.
     *
     * @return int
     */
    public static function count_messages_by_role( $conversation_id, $role ) {
        global $wpdb;

        self::ensure_tables_exist();

        $conversation_id = (int) $conversation_id;

        if ( ! $conversation_id ) {
            return 0;
        }

        $table = self::get_table_name( self::MESSAGES_TABLE );
        $role  = \sanitize_key( $role );

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE conversation_id = %d AND role = %s",
                $conversation_id,
                $role
            )
        );
    }

    /**
     * Determine if conversation is expired.
     *
     * @param object $conversation
     *
     * @return bool
     */
    public static function is_expired( $conversation ) {
        if ( ! $conversation ) {
            return true;
        }

        $last_activity = strtotime( $conversation->updated_at );

        if ( ! $last_activity ) {
            return false;
        }

        return ( time() - $last_activity ) > self::SESSION_TIMEOUT;
    }

    /**
     * Mark conversation as closed.
     *
     * @param int $conversation_id
     */
    public static function close_conversation( $conversation_id ) {
        global $wpdb;

        self::ensure_tables_exist();

        $conversation_id = (int) $conversation_id;

        if ( ! $conversation_id ) {
            return;
        }

        $wpdb->update(
            self::get_table_name( self::CONVERSATIONS_TABLE ),
            [
                'status'     => 'closed',
                'updated_at' => current_time( 'mysql' ),
            ],
            [ 'id' => $conversation_id ],
            [ '%s', '%s' ],
            [ '%d' ]
        );
    }

    /**
     * Update the timestamp for a conversation.
     *
     * @param int $conversation_id
     */
    public static function touch_conversation( $conversation_id ) {
        global $wpdb;

        self::ensure_tables_exist();

        $conversation_id = (int) $conversation_id;

        if ( ! $conversation_id ) {
            return;
        }

        $wpdb->update(
            self::get_table_name( self::CONVERSATIONS_TABLE ),
            [
                'updated_at' => current_time( 'mysql' ),
                'status'     => 'active',
            ],
            [ 'id' => $conversation_id ],
            [ '%s', '%s' ],
            [ '%d' ]
        );
    }

    /**
     * Build prompt text for OpenAI request.
     *
     * @param object $conversation
     * @param array  $messages
     *
     * @return string
     */
    public static function build_prompt_from_history( $conversation, array $messages ) {
        $lines = [];

        if ( $conversation && ! empty( $conversation->prompt_context ) ) {
            $lines[] = 'System: ' . self::prepare_message_for_prompt( $conversation->prompt_context );
        }

        foreach ( $messages as $message ) {
            $role = ucfirst( $message['role'] );
            $lines[] = $role . ': ' . self::prepare_message_for_prompt( $message['message'] );
        }

        return implode( "\n", $lines );
    }

    /**
     * Helper to prepare message for textual prompt.
     *
     * @param string $message
     *
     * @return string
     */
    private static function prepare_message_for_prompt( $message ) {
        $message = wp_strip_all_tags( $message );
        $message = html_entity_decode( $message, ENT_QUOTES, get_bloginfo( 'charset' ) );

        return trim( $message );
    }

    /**
     * Prepare message content for front-end output.
     *
     * @param string $message
     * @param string $role
     *
     * @return string
     */
    private static function prepare_message_for_output( $message, $role ) {
        if ( 'assistant' === $role ) {
            $allowed = wp_kses_allowed_html( 'post' );
            $filtered = wp_kses( $message, $allowed );

            return $filtered;
        }

        return sanitize_textarea_field( $message );
    }

    /**
     * Sanitize stored message based on role.
     *
     * @param string $message
     * @param string $role
     *
     * @return string
     */
    private static function sanitize_message_for_role( $message, $role ) {
        if ( 'assistant' === $role ) {
            return wp_kses_post( $message );
        }

        return sanitize_textarea_field( $message );
    }

    /**
     * Sanitize prompt context.
     *
     * @param string $prompt
     *
     * @return string
     */
    private static function sanitize_prompt_context( $prompt ) {
        return sanitize_textarea_field( $prompt );
    }

    /**
     * Generate unique session identifier.
     *
     * @return string
     */
    private static function generate_session_id() {
        return wp_generate_uuid4();
    }

    /**
     * Helper to get full table name.
     *
     * @param string $table
     *
     * @return string
     */
    private static function get_table_name( $table ) {
        global $wpdb;

        return $wpdb->prefix . $table;
    }

    /**
     * Detect visitor IP address.
     *
     * @return string
     */
    private static function detect_ip_address() {
        $keys = [ 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ];

        foreach ( $keys as $key ) {
            if ( empty( $_SERVER[ $key ] ) ) {
                continue;
            }

            $ip_list = explode( ',', wp_unslash( $_SERVER[ $key ] ) );
            $ip      = trim( $ip_list[0] );

            if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                return $ip;
            }
        }

        return '';
    }
}
