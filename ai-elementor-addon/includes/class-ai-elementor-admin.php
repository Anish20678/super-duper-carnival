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

        Settings::update_settings( $input );

        return Settings::get_settings();
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
}

