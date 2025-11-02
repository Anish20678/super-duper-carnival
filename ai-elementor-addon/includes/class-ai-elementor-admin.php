<?php
namespace AI_Elementor_Addon;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles WordPress admin integration and settings pages.
 */
class Admin {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menus' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_post_ai_elementor_export_conversation', [ $this, 'export_conversation' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Register admin menu and submenus.
     */
    public function register_menus() {
        add_menu_page(
            __( 'AI Elementor', 'ai-elementor-addon' ),
            __( 'AI Elementor', 'ai-elementor-addon' ),
            'manage_options',
            'ai-elementor-addon',
            [ $this, 'render_dashboard_page' ],
            'dashicons-art',
            58
        );

        add_submenu_page(
            'ai-elementor-addon',
            __( 'Widget Manager', 'ai-elementor-addon' ),
            __( 'Widget Manager', 'ai-elementor-addon' ),
            'manage_options',
            'ai-elementor-addon-widgets',
            [ $this, 'render_widgets_page' ]
        );

        add_submenu_page(
            'ai-elementor-addon',
            __( 'OpenAI Settings', 'ai-elementor-addon' ),
            __( 'OpenAI Settings', 'ai-elementor-addon' ),
            'manage_options',
            'ai-elementor-addon-api',
            [ $this, 'render_api_page' ]
        );

        add_submenu_page(
            'ai-elementor-addon',
            __( 'AI Conversations', 'ai-elementor-addon' ),
            __( 'AI Conversations', 'ai-elementor-addon' ),
            'manage_options',
            'ai-elementor-addon-conversations',
            [ $this, 'render_conversations_page' ]
        );

        add_submenu_page(
            'ai-elementor-addon',
            __( 'AI Roadmap', 'ai-elementor-addon' ),
            __( 'AI Roadmap', 'ai-elementor-addon' ),
            'manage_options',
            'ai-elementor-addon-roadmap',
            [ $this, 'render_roadmap_page' ]
        );
    }

    /**
     * Register plugin settings and fields.
     */
    public function register_settings() {
        register_setting( 'ai_elementor_addon_settings', Settings::OPTION_KEY, [ $this, 'sanitize_settings' ] );

        add_settings_section(
            'ai_elementor_addon_api_section',
            __( 'OpenAI Configuration', 'ai-elementor-addon' ),
            function() {
                echo '<p>' . esc_html__( 'Provide API credentials to unlock AI-powered widgets.', 'ai-elementor-addon' ) . '</p>';
            },
            'ai_elementor_addon_api_settings'
        );

        add_settings_field(
            'ai_elementor_addon_api_key',
            __( 'OpenAI API Key', 'ai-elementor-addon' ),
            [ $this, 'render_api_key_field' ],
            'ai_elementor_addon_api_settings',
            'ai_elementor_addon_api_section'
        );

        add_settings_field(
            'ai_elementor_addon_default_model',
            __( 'Default Model', 'ai-elementor-addon' ),
            [ $this, 'render_default_model_field' ],
            'ai_elementor_addon_api_settings',
            'ai_elementor_addon_api_section'
        );

        add_settings_field(
            'ai_elementor_addon_usage_logging',
            __( 'Usage Logging', 'ai-elementor-addon' ),
            [ $this, 'render_usage_logging_field' ],
            'ai_elementor_addon_api_settings',
            'ai_elementor_addon_api_section'
        );
    }

    /**
     * Sanitize settings input.
     */
    public function sanitize_settings( $input ) {
        if ( ! is_array( $input ) ) {
            $input = [];
        }

        return Settings::sanitize_settings_array( $input );
    }

    /**
     * Render admin dashboard page.
     */
    public function render_dashboard_page() {
        $settings = Settings::get_settings();
        ?>
        <div class="wrap ai-elementor-addon">
            <h1><?php esc_html_e( 'AI Elementor Dashboard', 'ai-elementor-addon' ); ?></h1>
            <p><?php esc_html_e( 'Configure and monitor your AI widgets from a single location.', 'ai-elementor-addon' ); ?></p>
            <div class="card">
                <h2><?php esc_html_e( 'Connection Status', 'ai-elementor-addon' ); ?></h2>
                <?php if ( ! empty( $settings['api_key'] ) ) : ?>
                    <p><?php esc_html_e( 'Your site is connected to OpenAI.', 'ai-elementor-addon' ); ?></p>
                <?php else : ?>
                    <p><?php esc_html_e( 'Add your OpenAI API key to start using AI widgets.', 'ai-elementor-addon' ); ?></p>
                <?php endif; ?>
            </div>
            <div class="card">
                <h2><?php esc_html_e( 'Active Widgets', 'ai-elementor-addon' ); ?></h2>
                <ul>
                    <?php foreach ( $settings['enabled_widgets'] as $widget_id ) : ?>
                        <li><?php echo esc_html( ucwords( str_replace( '_', ' ', $widget_id ) ) ); ?></li>
                    <?php endforeach; ?>
                    <?php if ( empty( $settings['enabled_widgets'] ) ) : ?>
                        <li><?php esc_html_e( 'No widgets enabled yet.', 'ai-elementor-addon' ); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Render widget manager page for enabling/disabling widgets.
     */
    public function render_widgets_page() {
        $settings = Settings::get_settings();
        $all_widgets = [
            'ai_chat'         => __( 'Conversational AI Chat', 'ai-elementor-addon' ),
            'ai_brainstorm'   => __( 'AI Brainstorm Canvas', 'ai-elementor-addon' ),
            'ai_image_prompt' => __( 'AI Image Prompt Helper', 'ai-elementor-addon' ),
        ];
        ?>
        <div class="wrap ai-elementor-addon">
            <h1><?php esc_html_e( 'AI Widget Manager', 'ai-elementor-addon' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'ai_elementor_addon_settings' ); ?>
                <table class="form-table" role="presentation">
                    <tbody>
                    <?php foreach ( $all_widgets as $id => $label ) : ?>
                        <tr>
                            <th scope="row"><?php echo esc_html( $label ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[enabled_widgets][]" value="<?php echo esc_attr( $id ); ?>" <?php checked( in_array( $id, $settings['enabled_widgets'], true ) ); ?>>
                                    <?php esc_html_e( 'Enable widget', 'ai-elementor-addon' ); ?>
                                </label>
                                <p class="description">
                                    <?php
                                    switch ( $id ) {
                                        case 'ai_chat':
                                            esc_html_e( 'Embed a two-way chat powered by OpenAI.', 'ai-elementor-addon' );
                                            break;
                                        case 'ai_brainstorm':
                                            esc_html_e( 'Generate structured ideas, FAQs and outlines.', 'ai-elementor-addon' );
                                            break;
                                        case 'ai_image_prompt':
                                            esc_html_e( 'Assist visitors with crafting descriptive prompts for image models.', 'ai-elementor-addon' );
                                            break;
                                    }
                                    ?>
                                </p>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render API settings page.
     */
    public function render_api_page() {
        ?>
        <div class="wrap ai-elementor-addon">
            <h1><?php esc_html_e( 'OpenAI Configuration', 'ai-elementor-addon' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'ai_elementor_addon_settings' );
                do_settings_sections( 'ai_elementor_addon_api_settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render roadmap page.
     */
    public function render_roadmap_page() {
        $settings = Settings::get_settings();
        ?>
        <div class="wrap ai-elementor-addon">
            <h1><?php esc_html_e( 'AI Roadmap & Ideas', 'ai-elementor-addon' ); ?></h1>
            <p><?php esc_html_e( 'Preview upcoming AI widgets and propose your own enhancements.', 'ai-elementor-addon' ); ?></p>
            <ul class="ul-disc">
                <?php foreach ( $settings['future_widgets'] as $future_widget ) : ?>
                    <li><?php echo esc_html( $future_widget ); ?></li>
                <?php endforeach; ?>
            </ul>
            <p><?php esc_html_e( 'We plan to support hyper-personalized training plans, marketing optimizers, and more unique experiences. Submit your ideas to help shape the roadmap.', 'ai-elementor-addon' ); ?></p>
        </div>
        <?php
    }

    /**
     * Render stored conversation list or a single conversation view.
     */
    public function render_conversations_page() {
        if ( isset( $_GET['conversation'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            $conversation_id = absint( $_GET['conversation'] ); // phpcs:ignore WordPress.Security.NonceVerification
            $this->render_conversation_detail( $conversation_id );

            return;
        }

        $paged        = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
        $per_page     = 20;
        $total_rows   = Conversation_Store::get_conversation_count();
        $conversations = Conversation_Store::get_conversations( $paged, $per_page );
        $total_pages  = $per_page ? (int) ceil( $total_rows / $per_page ) : 1;
        ?>
        <div class="wrap ai-elementor-addon">
            <h1><?php esc_html_e( 'AI Conversations', 'ai-elementor-addon' ); ?></h1>
            <p><?php esc_html_e( 'Review visitor AI chats, their origin, and manage transcripts.', 'ai-elementor-addon' ); ?></p>
            <table class="widefat fixed striped">
                <thead>
                <tr>
                    <th><?php esc_html_e( 'Session ID', 'ai-elementor-addon' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'ai-elementor-addon' ); ?></th>
                    <th><?php esc_html_e( 'Started', 'ai-elementor-addon' ); ?></th>
                    <th><?php esc_html_e( 'Last Activity', 'ai-elementor-addon' ); ?></th>
                    <th><?php esc_html_e( 'Visitor IP', 'ai-elementor-addon' ); ?></th>
                    <th><?php esc_html_e( 'Source', 'ai-elementor-addon' ); ?></th>
                    <th><?php esc_html_e( 'Page', 'ai-elementor-addon' ); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if ( empty( $conversations ) ) : ?>
                    <tr>
                        <td colspan="7"><?php esc_html_e( 'No conversations captured yet.', 'ai-elementor-addon' ); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ( $conversations as $conversation ) : ?>
                        <tr>
                            <td>
                                <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ai-elementor-addon-conversations', 'conversation' => $conversation->id ], admin_url( 'admin.php' ) ) ); ?>">
                                    <?php echo esc_html( $conversation->session_id ); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html( ucfirst( $conversation->status ) ); ?></td>
                            <td><?php echo esc_html( $this->format_datetime( $conversation->created_at ) ); ?></td>
                            <td><?php echo esc_html( $this->format_datetime( $conversation->updated_at ) ); ?></td>
                            <td><?php echo esc_html( $conversation->visitor_ip ); ?></td>
                            <td><?php echo esc_html( $conversation->referer ? $conversation->referer : __( 'Direct', 'ai-elementor-addon' ) ); ?></td>
                            <td>
                                <?php if ( ! empty( $conversation->page_url ) ) : ?>
                                    <a href="<?php echo esc_url( $conversation->page_url ); ?>" target="_blank" rel="noopener"> <?php echo esc_html( $conversation->page_url ); ?> </a>
                                <?php else : ?>
                                    &mdash;
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            <?php if ( $total_pages > 1 ) : ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        echo wp_kses_post(
                            paginate_links(
                                [
                                    'base'      => add_query_arg( 'paged', '%#%' ),
                                    'format'    => '',
                                    'current'   => max( 1, $paged ),
                                    'total'     => $total_pages,
                                    'prev_text' => __( '&laquo; Previous', 'ai-elementor-addon' ),
                                    'next_text' => __( 'Next &raquo;', 'ai-elementor-addon' ),
                                ]
                            )
                        );
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render an individual conversation transcript view.
     *
     * @param int $conversation_id
     */
    private function render_conversation_detail( $conversation_id ) {
        $conversation = Conversation_Store::get_conversation( $conversation_id );

        if ( ! $conversation ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Conversation not found.', 'ai-elementor-addon' ) . '</p></div>';

            return;
        }

        $messages   = Conversation_Store::get_history_for_output( $conversation_id );
        $export_url = wp_nonce_url( admin_url( 'admin-post.php?action=ai_elementor_export_conversation&conversation=' . $conversation_id ), 'ai_elementor_export_' . $conversation_id );
        $back_url   = admin_url( 'admin.php?page=ai-elementor-addon-conversations' );
        ?>
        <div class="wrap ai-elementor-addon ai-elementor-addon__conversation-detail">
            <h1><?php esc_html_e( 'Conversation Transcript', 'ai-elementor-addon' ); ?></h1>
            <p>
                <a class="button" href="<?php echo esc_url( $back_url ); ?>">&larr; <?php esc_html_e( 'Back to list', 'ai-elementor-addon' ); ?></a>
                <a class="button button-primary" href="<?php echo esc_url( $export_url ); ?>"><?php esc_html_e( 'Export Transcript (JSON)', 'ai-elementor-addon' ); ?></a>
            </p>
            <table class="widefat striped">
                <tbody>
                <tr>
                    <th><?php esc_html_e( 'Session ID', 'ai-elementor-addon' ); ?></th>
                    <td><?php echo esc_html( $conversation->session_id ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Widget ID', 'ai-elementor-addon' ); ?></th>
                    <td><?php echo esc_html( $conversation->widget_id ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Started', 'ai-elementor-addon' ); ?></th>
                    <td><?php echo esc_html( $this->format_datetime( $conversation->created_at ) ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Last Activity', 'ai-elementor-addon' ); ?></th>
                    <td><?php echo esc_html( $this->format_datetime( $conversation->updated_at ) ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Visitor IP', 'ai-elementor-addon' ); ?></th>
                    <td><?php echo esc_html( $conversation->visitor_ip ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Referrer', 'ai-elementor-addon' ); ?></th>
                    <td><?php echo $conversation->referer ? '<a href="' . esc_url( $conversation->referer ) . '" target="_blank" rel="noopener">' . esc_html( $conversation->referer ) . '</a>' : '&mdash;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Page URL', 'ai-elementor-addon' ); ?></th>
                    <td><?php echo $conversation->page_url ? '<a href="' . esc_url( $conversation->page_url ) . '" target="_blank" rel="noopener">' . esc_html( $conversation->page_url ) . '</a>' : '&mdash;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Status', 'ai-elementor-addon' ); ?></th>
                    <td><?php echo esc_html( ucfirst( $conversation->status ) ); ?></td>
                </tr>
                <?php if ( ! empty( $conversation->prompt_context ) ) : ?>
                    <tr>
                        <th><?php esc_html_e( 'System Prompt', 'ai-elementor-addon' ); ?></th>
                        <td><pre><?php echo esc_html( $conversation->prompt_context ); ?></pre></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

            <h2><?php esc_html_e( 'Transcript', 'ai-elementor-addon' ); ?></h2>
            <div class="ai-elementor-addon-transcript">
                <?php if ( empty( $messages ) ) : ?>
                    <p><?php esc_html_e( 'No messages recorded for this conversation.', 'ai-elementor-addon' ); ?></p>
                <?php else : ?>
                    <ul>
                        <?php foreach ( $messages as $message ) : ?>
                            <li class="ai-elementor-addon-transcript__item ai-elementor-addon-transcript__item--<?php echo esc_attr( $message['role'] ); ?>">
                                <span class="ai-elementor-addon-transcript__meta">
                                    <?php echo esc_html( ucfirst( $message['role'] ) ); ?>
                                    <em><?php echo esc_html( $this->format_datetime( $message['time'] ) ); ?></em>
                                </span>
                                <div class="ai-elementor-addon-transcript__content">
                                    <?php
                                    if ( 'assistant' === $message['role'] ) {
                                        echo wp_kses_post( wpautop( $message['content'] ) );
                                    } else {
                                        echo '<pre>' . esc_html( $message['content'] ) . '</pre>';
                                    }
                                    ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Export conversation as JSON download.
     */
    public function export_conversation() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to export conversations.', 'ai-elementor-addon' ) );
        }

        $conversation_id = isset( $_GET['conversation'] ) ? absint( $_GET['conversation'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

        if ( ! $conversation_id || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ai_elementor_export_' . $conversation_id ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            wp_die( esc_html__( 'Invalid export request.', 'ai-elementor-addon' ) );
        }

        $conversation = Conversation_Store::get_conversation( $conversation_id );

        if ( ! $conversation ) {
            wp_die( esc_html__( 'Conversation not found.', 'ai-elementor-addon' ) );
        }

        $messages = Conversation_Store::get_history_for_output( $conversation_id );

        $payload = [
            'conversation' => [
                'id'             => $conversation->id,
                'session_id'     => $conversation->session_id,
                'widget_id'      => $conversation->widget_id,
                'status'         => $conversation->status,
                'prompt_context' => $conversation->prompt_context,
                'page_url'       => $conversation->page_url,
                'referer'        => $conversation->referer,
                'visitor_ip'     => $conversation->visitor_ip,
                'user_agent'     => $conversation->user_agent,
                'created_at'     => $conversation->created_at,
                'updated_at'     => $conversation->updated_at,
            ],
            'messages' => $messages,
        ];

        nocache_headers();
        header( 'Content-Type: application/json; charset=' . get_bloginfo( 'charset' ) );
        header( 'Content-Disposition: attachment; filename="ai-conversation-' . $conversation->id . '.json"' );

        echo wp_json_encode( $payload, JSON_PRETTY_PRINT );
        exit;
    }

    /**
     * Format a MySQL datetime string for output.
     *
     * @param string $datetime
     *
     * @return string
     */
    private function format_datetime( $datetime ) {
        $timestamp = strtotime( $datetime );

        if ( ! $timestamp ) {
            return $datetime;
        }

        return wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
    }

    /**
     * Render API key field.
     */
    public function render_api_key_field() {
        $settings = Settings::get_settings();
        ?>
        <input type="password" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[api_key]" value="<?php echo esc_attr( $settings['api_key'] ); ?>" class="regular-text" autocomplete="off">
        <p class="description">
            <?php esc_html_e( 'Store your API key securely. It will be used for server-side OpenAI requests.', 'ai-elementor-addon' ); ?>
        </p>
        <?php
    }

    /**
     * Render default model select field.
     */
    public function render_default_model_field() {
        $settings = Settings::get_settings();
        $models   = [ 'gpt-4o-mini', 'gpt-4.1', 'gpt-4o', 'o4-mini' ];
        ?>
        <select name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[default_model]">
            <?php foreach ( $models as $model ) : ?>
                <option value="<?php echo esc_attr( $model ); ?>" <?php selected( $settings['default_model'], $model ); ?>><?php echo esc_html( strtoupper( $model ) ); ?></option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php esc_html_e( 'Choose the default model used across AI widgets. Individual widgets can override this.', 'ai-elementor-addon' ); ?>
        </p>
        <?php
    }

    /**
     * Render usage logging toggle.
     */
    public function render_usage_logging_field() {
        $settings = Settings::get_settings();
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[usage_logging]" value="1" <?php checked( ! empty( $settings['usage_logging'] ) ); ?>>
            <?php esc_html_e( 'Record anonymized statistics for AI interactions.', 'ai-elementor-addon' ); ?>
        </label>
        <?php
    }

    /**
     * Enqueue admin assets for plugin pages.
     *
     * @param string $hook
     */
    public function enqueue_assets( $hook ) {
        if ( false === strpos( $hook, 'ai-elementor-addon' ) ) {
            return;
        }

        wp_enqueue_style( 'ai-elementor-addon-admin', AI_ELEMENTOR_ADDON_URL . 'assets/css/frontend.css', [], '1.1.0' );
    }
}
