<?php
namespace AI_Elementor_Addon\Widgets;

use AI_Elementor_Addon\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Conversational AI chat widget.
 */
class AI_Chat_Widget extends Widget_Base {

    public function get_name() {
        return 'ai_chat';
    }

    public function get_title() {
        return __( 'AI Chat Assistant', 'ai-elementor-addon' );
    }

    public function get_icon() {
        return 'eicon-chat';
    }

    public function get_categories() {
        return [ 'ai-elementor-addon' ];
    }

    public function get_keywords() {
        return [ 'ai', 'chat', 'assistant', 'openai' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Chat Content', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'chat_title',
            [
                'label'       => __( 'Widget Title', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Ask our AI assistant', 'ai-elementor-addon' ),
                'placeholder' => __( 'Enter title', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'welcome_message',
            [
                'label'       => __( 'Welcome Message', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXTAREA,
                'default'     => __( 'How can I help you today?', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'prompt_context',
            [
                'label'       => __( 'Prompt Context', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXTAREA,
                'description' => __( 'Optional system instructions to guide the AI assistant.', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'temperature',
            [
                'label'   => __( 'Creativity', 'ai-elementor-addon' ),
                'type'    => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0.6,
                ],
                'range'   => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1,
                        'step' => 0.1,
                    ],
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => __( 'Chat Appearance', 'ai-elementor-addon' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'bubble_radius',
            [
                'label'      => __( 'Bubble Radius', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 30 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-bubble' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'chat_typography',
                'selector' => '{{WRAPPER}} .ai-chat-window',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'chat_background',
                'selector' => '{{WRAPPER}} .ai-chat-window',
                'types'    => [ 'classic', 'gradient' ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'chat_border',
                'selector' => '{{WRAPPER}} .ai-chat-window',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        wp_enqueue_style( 'ai-elementor-addon' );
        wp_enqueue_script( 'ai-elementor-addon' );

        $settings      = $this->get_settings_for_display();
        $configuration = Plugin::get_ai_configuration();

        $data = [
            'model'        => $configuration['default_model'],
            'temperature'  => isset( $settings['temperature']['size'] ) ? (float) $settings['temperature']['size'] : 0.6,
            'prompt'       => $settings['prompt_context'],
        ];
        ?>
        <div class="ai-chat-widget" data-settings='<?php echo esc_attr( wp_json_encode( $data ) ); ?>'>
            <div class="ai-chat-header">
                <h3><?php echo esc_html( $settings['chat_title'] ); ?></h3>
            </div>
            <div class="ai-chat-window">
                <div class="ai-chat-bubble ai-chat-bubble--assistant">
                    <?php echo esc_html( $settings['welcome_message'] ); ?>
                </div>
            </div>
            <div class="ai-chat-input">
                <label class="screen-reader-text" for="ai-chat-prompt-<?php echo esc_attr( $this->get_id() ); ?>"><?php esc_html_e( 'Message', 'ai-elementor-addon' ); ?></label>
                <textarea id="ai-chat-prompt-<?php echo esc_attr( $this->get_id() ); ?>" placeholder="<?php esc_attr_e( 'Ask anything...', 'ai-elementor-addon' ); ?>"></textarea>
                <button type="button" class="ai-chat-send" data-api-key="<?php echo esc_attr( $configuration['api_key'] ? 'set' : '' ); ?>">
                    <?php esc_html_e( 'Send', 'ai-elementor-addon' ); ?>
                </button>
            </div>
        </div>
        <?php
    }
}

