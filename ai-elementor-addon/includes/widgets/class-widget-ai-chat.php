<?php
namespace AI_Elementor_Addon\Widgets;

use AI_Elementor_Addon\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

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

        $this->add_control(
            'chat_icon',
            [
                'label'       => __( 'Assistant Icon', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '🤖',
                'description' => __( 'Shown before the title to add personality.', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'placeholder_text',
            [
                'label'       => __( 'Input Placeholder', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Ask anything…', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'send_button_text',
            [
                'label'       => __( 'Send Button Label', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Send', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'typing_indicator',
            [
                'label'       => __( 'Typing Indicator', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Assistant is thinking…', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'inactivity_message',
            [
                'label'       => __( 'Inactivity Message', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => __( 'The chat session expired due to inactivity. Starting a new conversation.', 'ai-elementor-addon' ),
                'description' => __( 'Displayed when the visitor returns after a session timeout (7 minutes of inactivity).', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'enter_to_send',
            [
                'label'        => __( 'Send On Enter', 'ai-elementor-addon' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'ai-elementor-addon' ),
                'label_off'    => __( 'No', 'ai-elementor-addon' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'quick_prompts',
            [
                'label'       => __( 'Suggested Prompts', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 5,
                'description' => __( 'Enter one suggestion per line to show quick prompt chips.', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'auto_send_quick_prompt',
            [
                'label'        => __( 'Auto Send Quick Prompts', 'ai-elementor-addon' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'ai-elementor-addon' ),
                'label_off'    => __( 'No', 'ai-elementor-addon' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => __( 'Automatically send the suggestion when clicked. When disabled the prompt only fills the input.', 'ai-elementor-addon' ),
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
            'widget_padding',
            [
                'label'      => __( 'Widget Padding', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
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

        $this->add_control(
            'chat_window_height',
            [
                'label' => __( 'Chat Window Max Height', 'ai-elementor-addon' ),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [ 'min' => 160, 'max' => 640 ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-window' => 'max-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'chat_window_min_height',
            [
                'label' => __( 'Chat Window Min Height', 'ai-elementor-addon' ),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [ 'min' => 120, 'max' => 640 ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-window' => 'min-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'chat_window_fixed_height',
            [
                'label'       => __( 'Fixed Chat Height', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::SLIDER,
                'range'       => [
                    'px' => [ 'min' => 160, 'max' => 800 ],
                ],
                'selectors'   => [
                    '{{WRAPPER}} .ai-chat-window' => 'height: {{SIZE}}{{UNIT}};',
                ],
                'description' => __( 'Sets an explicit height for the chat area when required.', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'chat_window_padding',
            [
                'label'      => __( 'Chat Window Padding', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-window' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'chat_typography',
                'selector' => '{{WRAPPER}} .ai-chat-window, {{WRAPPER}} .ai-chat-bubble',
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

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'chat_shadow',
                'selector' => '{{WRAPPER}} .ai-chat-widget',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_header_style',
            [
                'label' => __( 'Header', 'ai-elementor-addon' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'header_alignment',
            [
                'label'   => __( 'Alignment', 'ai-elementor-addon' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => __( 'Left', 'ai-elementor-addon' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center'     => [
                        'title' => __( 'Center', 'ai-elementor-addon' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end'   => [
                        'title' => __( 'Right', 'ai-elementor-addon' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default'  => 'flex-start',
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-header' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'header_background',
            [
                'label'     => __( 'Background Color', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-header' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'header_text_color',
            [
                'label'     => __( 'Text Color', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-header, {{WRAPPER}} .ai-chat-header h3' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'header_typography',
                'selector' => '{{WRAPPER}} .ai-chat-header h3',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_bubble_style',
            [
                'label' => __( 'Messages', 'ai-elementor-addon' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'assistant_bubble_color',
            [
                'label'     => __( 'Assistant Bubble', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-bubble--assistant' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'assistant_text_color',
            [
                'label'     => __( 'Assistant Text', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-bubble--assistant' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'user_bubble_color',
            [
                'label'     => __( 'User Bubble', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-bubble--user' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'user_text_color',
            [
                'label'     => __( 'User Text', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-bubble--user' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_input_style',
            [
                'label' => __( 'Input & Actions', 'ai-elementor-addon' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'input_background',
            [
                'label'     => __( 'Input Background', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-input textarea' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_text_color',
            [
                'label'     => __( 'Input Text', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-input textarea' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background',
            [
                'label'     => __( 'Button Background', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-send' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label'     => __( 'Button Text', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-send' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        wp_enqueue_style( 'ai-elementor-addon' );
        wp_enqueue_script( 'ai-elementor-addon' );

        $settings      = $this->get_settings_for_display();
        $configuration = Plugin::get_ai_configuration();

        $quick_prompts = [];

        if ( ! empty( $settings['quick_prompts'] ) ) {
            $quick_prompts = array_filter( array_map( 'trim', explode( "\n", $settings['quick_prompts'] ) ) );
        }

        $data = [
            'model'             => $configuration['default_model'],
            'temperature'       => isset( $settings['temperature']['size'] ) ? (float) $settings['temperature']['size'] : 0.6,
            'prompt'            => $settings['prompt_context'],
            'enterToSend'       => isset( $settings['enter_to_send'] ) && 'yes' === $settings['enter_to_send'],
            'quickPrompts'      => array_values( $quick_prompts ),
            'autoSend'          => isset( $settings['auto_send_quick_prompt'] ) && 'yes' === $settings['auto_send_quick_prompt'],
            'typingText'        => $settings['typing_indicator'],
            'widgetId'          => $this->get_id(),
            'welcomeMessage'    => $settings['welcome_message'],
            'sessionTimeout'    => \AI_Elementor_Addon\Conversation_Store::SESSION_TIMEOUT,
            'sessionExpiredText'=> $settings['inactivity_message'],
        ];
        ?>
        <div class="ai-chat-widget" data-widget-id="<?php echo esc_attr( $this->get_id() ); ?>" data-settings='<?php echo esc_attr( wp_json_encode( $data ) ); ?>'>
            <div class="ai-chat-header">
                <h3>
                    <?php if ( ! empty( $settings['chat_icon'] ) ) : ?>
                        <span class="ai-chat-icon" aria-hidden="true"><?php echo esc_html( $settings['chat_icon'] ); ?></span>
                    <?php endif; ?>
                    <span class="ai-chat-title-text"><?php echo esc_html( $settings['chat_title'] ); ?></span>
                </h3>
            </div>
            <?php if ( ! empty( $quick_prompts ) ) : ?>
                <div class="ai-chat-quick-prompts" role="list">
                    <?php foreach ( $quick_prompts as $prompt ) : ?>
                        <button type="button" class="ai-chat-quick-prompt" role="listitem"><?php echo esc_html( $prompt ); ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="ai-chat-window">
                <div class="ai-chat-bubble ai-chat-bubble--assistant">
                    <?php echo esc_html( $settings['welcome_message'] ); ?>
                </div>
            </div>
            <div class="ai-chat-input">
                <label class="screen-reader-text" for="ai-chat-prompt-<?php echo esc_attr( $this->get_id() ); ?>"><?php esc_html_e( 'Message', 'ai-elementor-addon' ); ?></label>
                <textarea id="ai-chat-prompt-<?php echo esc_attr( $this->get_id() ); ?>" placeholder="<?php echo esc_attr( $settings['placeholder_text'] ); ?>"></textarea>
                <button type="button" class="ai-chat-send" data-api-key="<?php echo esc_attr( $configuration['api_key'] ? 'set' : '' ); ?>">
                    <span class="ai-chat-send__label"><?php echo esc_html( $settings['send_button_text'] ); ?></span>
                </button>
            </div>
        </div>
        <?php
    }
}

