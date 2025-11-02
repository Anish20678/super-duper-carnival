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
            'section_content_setup',
            [
                'label' => __( 'Assistant Setup', 'ai-elementor-addon' ),
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
            'chat_icon',
            [
                'label'       => __( 'Assistant Icon', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '🤖',
                'description' => __( 'Shown before the title to add personality.', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'show_header',
            [
                'label'        => __( 'Display Header', 'ai-elementor-addon' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Show', 'ai-elementor-addon' ),
                'label_off'    => __( 'Hide', 'ai-elementor-addon' ),
                'return_value' => 'yes',
                'default'      => 'yes',
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

        $this->end_controls_section();

        $this->start_controls_section(
            'section_content_training',
            [
                'label' => __( 'Training', 'ai-elementor-addon' ),
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
            'assistant_persona',
            [
                'label'       => __( 'Assistant Persona', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXTAREA,
                'description' => __( 'Describe tone and personality. Added to the system prompt.', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'knowledge_base_links',
            [
                'label'       => __( 'Knowledge Sources', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXTAREA,
                'description' => __( 'Provide URLs or references the assistant can cite.', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'response_guidelines',
            [
                'label'       => __( 'Response Guidelines', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXTAREA,
                'description' => __( 'Clarify how the AI should answer (formatting, length, tone).', 'ai-elementor-addon' ),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_content_behavior',
            [
                'label' => __( 'Responses & Behaviour', 'ai-elementor-addon' ),
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
            'persist_session',
            [
                'label'        => __( 'Remember Conversation', 'ai-elementor-addon' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'ai-elementor-addon' ),
                'label_off'    => __( 'No', 'ai-elementor-addon' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => __( 'Stores the session locally so visitors can resume later.', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'enable_typing_indicator',
            [
                'label'        => __( 'Show Typing Indicator', 'ai-elementor-addon' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Show', 'ai-elementor-addon' ),
                'label_off'    => __( 'Hide', 'ai-elementor-addon' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'typing_indicator',
            [
                'label'       => __( 'Typing Indicator', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Assistant is thinking…', 'ai-elementor-addon' ),
                'condition'   => [
                    'enable_typing_indicator' => 'yes',
                ],
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
            'empty_input_message',
            [
                'label'       => __( 'Empty Message Notice', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Please enter a message before sending.', 'ai-elementor-addon' ),
                'description' => __( 'Shown when the visitor tries to send a blank message.', 'ai-elementor-addon' ),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_content_prompts',
            [
                'label' => __( 'Quick Responses', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'display_quick_prompts',
            [
                'label'        => __( 'Show Quick Prompts', 'ai-elementor-addon' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Show', 'ai-elementor-addon' ),
                'label_off'    => __( 'Hide', 'ai-elementor-addon' ),
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
                'condition'   => [
                    'display_quick_prompts' => 'yes',
                ],
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
                'condition'    => [
                    'display_quick_prompts' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_container',
            [
                'label' => __( 'Chat Container', 'ai-elementor-addon' ),
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
            'widget_gap',
            [
                'label'      => __( 'Section Gap', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 48 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-widget' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'container_background',
                'selector' => '{{WRAPPER}} .ai-chat-widget',
                'types'    => [ 'classic', 'gradient' ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'container_border',
                'selector' => '{{WRAPPER}} .ai-chat-widget',
            ]
        );

        $this->add_control(
            'container_radius',
            [
                'label'      => __( 'Container Radius', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 60 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-widget' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'container_shadow',
                'selector' => '{{WRAPPER}} .ai-chat-widget',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_header_style',
            [
                'label'     => __( 'Header', 'ai-elementor-addon' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_header' => 'yes',
                ],
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
            'header_padding',
            [
                'label'      => __( 'Header Padding', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'header_border',
                'selector' => '{{WRAPPER}} .ai-chat-header',
            ]
        );

        $this->add_control(
            'header_radius',
            [
                'label'      => __( 'Header Radius', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 40 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-header' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'header_shadow',
                'selector' => '{{WRAPPER}} .ai-chat-header',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_chat_window_style',
            [
                'label' => __( 'Chat Window', 'ai-elementor-addon' ),
                'tab'   => Controls_Manager::TAB_STYLE,
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

        $this->add_control(
            'chat_window_gap',
            [
                'label'      => __( 'Message Spacing', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 40 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-window' => 'gap: {{SIZE}}{{UNIT}};',
                ],
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

        $this->add_control(
            'chat_window_radius',
            [
                'label'      => __( 'Window Radius', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 40 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-window' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'chat_shadow',
                'selector' => '{{WRAPPER}} .ai-chat-window',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'chat_typography',
                'selector' => '{{WRAPPER}} .ai-chat-window, {{WRAPPER}} .ai-chat-bubble',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_assistant_messages_style',
            [
                'label' => __( 'Assistant Messages', 'ai-elementor-addon' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'assistant_bubble_color',
            [
                'label'     => __( 'Bubble Color', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-bubble--assistant' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'assistant_bubble_background',
                'selector' => '{{WRAPPER}} .ai-chat-bubble--assistant',
                'types'    => [ 'classic', 'gradient' ],
            ]
        );

        $this->add_control(
            'assistant_text_color',
            [
                'label'     => __( 'Text Color', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-bubble--assistant' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'assistant_text_alignment',
            [
                'label'   => __( 'Text Alignment', 'ai-elementor-addon' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left'   => [ 'title' => __( 'Left', 'ai-elementor-addon' ), 'icon' => 'eicon-text-align-left' ],
                    'center' => [ 'title' => __( 'Center', 'ai-elementor-addon' ), 'icon' => 'eicon-text-align-center' ],
                    'right'  => [ 'title' => __( 'Right', 'ai-elementor-addon' ), 'icon' => 'eicon-text-align-right' ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-bubble--assistant' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'assistant_typography',
                'selector' => '{{WRAPPER}} .ai-chat-bubble--assistant',
            ]
        );

        $this->add_control(
            'assistant_bubble_padding',
            [
                'label'      => __( 'Bubble Padding', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-bubble--assistant' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'assistant_bubble_radius',
            [
                'label'      => __( 'Bubble Radius', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 50 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-bubble--assistant' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'assistant_bubble_border',
                'selector' => '{{WRAPPER}} .ai-chat-bubble--assistant',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'assistant_bubble_shadow',
                'selector' => '{{WRAPPER}} .ai-chat-bubble--assistant',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_user_messages_style',
            [
                'label' => __( 'User Messages', 'ai-elementor-addon' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'user_bubble_color',
            [
                'label'     => __( 'Bubble Color', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-bubble--user' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'user_bubble_background',
                'selector' => '{{WRAPPER}} .ai-chat-bubble--user',
                'types'    => [ 'classic', 'gradient' ],
            ]
        );

        $this->add_control(
            'user_text_color',
            [
                'label'     => __( 'Text Color', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-bubble--user' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'user_text_alignment',
            [
                'label'   => __( 'Text Alignment', 'ai-elementor-addon' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left'   => [ 'title' => __( 'Left', 'ai-elementor-addon' ), 'icon' => 'eicon-text-align-left' ],
                    'center' => [ 'title' => __( 'Center', 'ai-elementor-addon' ), 'icon' => 'eicon-text-align-center' ],
                    'right'  => [ 'title' => __( 'Right', 'ai-elementor-addon' ), 'icon' => 'eicon-text-align-right' ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-bubble--user' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'user_typography',
                'selector' => '{{WRAPPER}} .ai-chat-bubble--user',
            ]
        );

        $this->add_control(
            'user_bubble_padding',
            [
                'label'      => __( 'Bubble Padding', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-bubble--user' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'user_bubble_radius',
            [
                'label'      => __( 'Bubble Radius', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 50 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-bubble--user' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'user_bubble_border',
                'selector' => '{{WRAPPER}} .ai-chat-bubble--user',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'user_bubble_shadow',
                'selector' => '{{WRAPPER}} .ai-chat-bubble--user',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_quick_prompts_style',
            [
                'label'     => __( 'Quick Prompts', 'ai-elementor-addon' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'display_quick_prompts' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'quick_prompt_alignment',
            [
                'label'   => __( 'Alignment', 'ai-elementor-addon' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [ 'title' => __( 'Left', 'ai-elementor-addon' ), 'icon' => 'eicon-text-align-left' ],
                    'center'     => [ 'title' => __( 'Center', 'ai-elementor-addon' ), 'icon' => 'eicon-text-align-center' ],
                    'flex-end'   => [ 'title' => __( 'Right', 'ai-elementor-addon' ), 'icon' => 'eicon-text-align-right' ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-quick-prompts' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quick_prompt_gap',
            [
                'label'      => __( 'Chip Spacing', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 32 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-quick-prompts' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'quick_prompt_typography',
                'selector' => '{{WRAPPER}} .ai-chat-quick-prompt',
            ]
        );

        $this->add_control(
            'quick_prompt_padding',
            [
                'label'      => __( 'Chip Padding', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-quick-prompt' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'quick_prompt_background',
            [
                'label'     => __( 'Background', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-quick-prompt' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quick_prompt_text_color',
            [
                'label'     => __( 'Text Color', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-quick-prompt' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quick_prompt_border_radius',
            [
                'label'      => __( 'Chip Radius', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 60 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-quick-prompt' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'quick_prompt_border',
                'selector' => '{{WRAPPER}} .ai-chat-quick-prompt',
            ]
        );

        $this->add_control(
            'quick_prompt_hover_background',
            [
                'label'     => __( 'Hover Background', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-quick-prompt:hover, {{WRAPPER}} .ai-chat-quick-prompt:focus' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quick_prompt_hover_text_color',
            [
                'label'     => __( 'Hover Text', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-quick-prompt:hover, {{WRAPPER}} .ai-chat-quick-prompt:focus' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_input_style',
            [
                'label' => __( 'Message Input', 'ai-elementor-addon' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'input_background',
            [
                'label'     => __( 'Input Background', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-input textarea' => 'background: {{VALUE}};',
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
            'input_placeholder_color',
            [
                'label'     => __( 'Placeholder Text', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-input textarea::placeholder' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'input_typography',
                'selector' => '{{WRAPPER}} .ai-chat-input textarea',
            ]
        );

        $this->add_control(
            'input_padding',
            [
                'label'      => __( 'Input Padding', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-input textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'input_border',
                'selector' => '{{WRAPPER}} .ai-chat-input textarea',
            ]
        );

        $this->add_control(
            'input_border_radius',
            [
                'label'      => __( 'Input Radius', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 40 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-input textarea' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'input_min_height',
            [
                'label'      => __( 'Minimum Height', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [
                    'px' => [ 'min' => 40, 'max' => 200 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-input textarea' => 'min-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_button_style',
            [
                'label' => __( 'Send Button', 'ai-elementor-addon' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'button_background',
            [
                'label'     => __( 'Background', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-send' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_background',
            [
                'label'     => __( 'Hover Background', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-send:hover, {{WRAPPER}} .ai-chat-send:focus' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label'     => __( 'Text Color', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-send' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_text_color',
            [
                'label'     => __( 'Hover Text', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-send:hover, {{WRAPPER}} .ai-chat-send:focus' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .ai-chat-send',
            ]
        );

        $this->add_control(
            'button_padding',
            [
                'label'      => __( 'Button Padding', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-send' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'button_border',
                'selector' => '{{WRAPPER}} .ai-chat-send',
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label'      => __( 'Button Radius', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 60 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-send' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'button_shadow',
                'selector' => '{{WRAPPER}} .ai-chat-send',
            ]
        );

        $this->add_control(
            'button_full_width',
            [
                'label'        => __( 'Full Width Button', 'ai-elementor-addon' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'ai-elementor-addon' ),
                'label_off'    => __( 'No', 'ai-elementor-addon' ),
                'return_value' => 'yes',
                'selectors'    => [
                    '{{WRAPPER}} .ai-chat-send' => 'width: 100%;',
                ],
            ]
        );

        $this->add_control(
            'button_alignment',
            [
                'label'   => __( 'Button Alignment', 'ai-elementor-addon' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [ 'title' => __( 'Left', 'ai-elementor-addon' ), 'icon' => 'eicon-text-align-left' ],
                    'center'     => [ 'title' => __( 'Center', 'ai-elementor-addon' ), 'icon' => 'eicon-text-align-center' ],
                    'flex-end'   => [ 'title' => __( 'Right', 'ai-elementor-addon' ), 'icon' => 'eicon-text-align-right' ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ai-chat-send' => 'align-self: {{VALUE}};',
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

        $should_display_prompts = isset( $settings['display_quick_prompts'] ) ? 'yes' === $settings['display_quick_prompts'] : true;

        $quick_prompts = [];

        if ( ! empty( $settings['quick_prompts'] ) ) {
            $quick_prompts = array_filter( array_map( 'trim', explode( "\n", $settings['quick_prompts'] ) ) );
        }

        if ( ! $should_display_prompts ) {
            $quick_prompts = [];
        }

        $prompt_parts = [];

        if ( ! empty( $settings['prompt_context'] ) ) {
            $prompt_parts[] = trim( $settings['prompt_context'] );
        }

        if ( ! empty( $settings['assistant_persona'] ) ) {
            $prompt_parts[] = __( 'Assistant persona:', 'ai-elementor-addon' ) . "\n" . trim( $settings['assistant_persona'] );
        }

        if ( ! empty( $settings['knowledge_base_links'] ) ) {
            $knowledge_lines = array_filter( array_map( 'trim', preg_split( '/\r?\n/', $settings['knowledge_base_links'] ) ) );

            if ( ! empty( $knowledge_lines ) ) {
                $prompt_parts[] = __( 'Reference materials:', 'ai-elementor-addon' ) . "\n- " . implode( "\n- ", $knowledge_lines );
            }
        }

        if ( ! empty( $settings['response_guidelines'] ) ) {
            $guideline_lines = array_filter( array_map( 'trim', preg_split( '/\r?\n/', $settings['response_guidelines'] ) ) );

            if ( ! empty( $guideline_lines ) ) {
                $prompt_parts[] = __( 'Response guidelines:', 'ai-elementor-addon' ) . "\n- " . implode( "\n- ", $guideline_lines );
            }
        }

        $compiled_prompt = trim( implode( "\n\n", array_filter( $prompt_parts ) ) );

        $enable_typing_indicator = isset( $settings['enable_typing_indicator'] ) ? 'yes' === $settings['enable_typing_indicator'] : true;
        $persist_session         = isset( $settings['persist_session'] ) ? 'yes' === $settings['persist_session'] : true;

        $data = [
            'model'                  => $configuration['default_model'],
            'temperature'            => isset( $settings['temperature']['size'] ) ? (float) $settings['temperature']['size'] : 0.6,
            'prompt'                 => $compiled_prompt,
            'enterToSend'            => isset( $settings['enter_to_send'] ) && 'yes' === $settings['enter_to_send'],
            'quickPrompts'           => array_values( $quick_prompts ),
            'autoSend'               => $should_display_prompts && isset( $settings['auto_send_quick_prompt'] ) && 'yes' === $settings['auto_send_quick_prompt'],
            'typingText'             => ! empty( $settings['typing_indicator'] ) ? $settings['typing_indicator'] : '',
            'enableTypingIndicator'  => $enable_typing_indicator,
            'persistSession'         => $persist_session,
            'emptyInputMessage'      => isset( $settings['empty_input_message'] ) ? $settings['empty_input_message'] : '',
            'widgetId'               => $this->get_id(),
            'welcomeMessage'         => isset( $settings['welcome_message'] ) ? $settings['welcome_message'] : '',
            'sessionTimeout'         => \AI_Elementor_Addon\Conversation_Store::SESSION_TIMEOUT,
            'sessionExpiredText'     => isset( $settings['inactivity_message'] ) ? $settings['inactivity_message'] : '',
            'displayQuickPrompts'    => $should_display_prompts,
        ];

        $wrapper_classes = [ 'ai-chat-widget' ];

        if ( empty( $quick_prompts ) ) {
            $wrapper_classes[] = 'ai-chat-widget--no-prompts';
        }

        if ( empty( $settings['show_header'] ) || 'yes' !== $settings['show_header'] ) {
            $wrapper_classes[] = 'ai-chat-widget--no-header';
        }

        $wrapper_classnames = array_map( 'sanitize_html_class', $wrapper_classes );
        ?>
        <div class="<?php echo esc_attr( implode( ' ', $wrapper_classnames ) ); ?>" data-widget-id="<?php echo esc_attr( $this->get_id() ); ?>" data-settings='<?php echo esc_attr( wp_json_encode( $data ) ); ?>'>
            <?php if ( ! empty( $settings['show_header'] ) && 'yes' === $settings['show_header'] ) : ?>
                <div class="ai-chat-header">
                    <h3>
                        <?php if ( ! empty( $settings['chat_icon'] ) ) : ?>
                            <span class="ai-chat-icon" aria-hidden="true"><?php echo esc_html( $settings['chat_icon'] ); ?></span>
                        <?php endif; ?>
                        <span class="ai-chat-title-text"><?php echo isset( $settings['chat_title'] ) ? esc_html( $settings['chat_title'] ) : ''; ?></span>
                    </h3>
                </div>
            <?php endif; ?>
            <?php if ( ! empty( $quick_prompts ) ) : ?>
                <div class="ai-chat-quick-prompts" role="list">
                    <?php foreach ( $quick_prompts as $prompt ) : ?>
                        <button type="button" class="ai-chat-quick-prompt" role="listitem"><?php echo esc_html( $prompt ); ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="ai-chat-window">
                <div class="ai-chat-bubble ai-chat-bubble--assistant">
                    <?php echo isset( $settings['welcome_message'] ) ? esc_html( $settings['welcome_message'] ) : ''; ?>
                </div>
            </div>
            <div class="ai-chat-input">
                <label class="screen-reader-text" for="ai-chat-prompt-<?php echo esc_attr( $this->get_id() ); ?>"><?php esc_html_e( 'Message', 'ai-elementor-addon' ); ?></label>
                <textarea id="ai-chat-prompt-<?php echo esc_attr( $this->get_id() ); ?>" placeholder="<?php echo isset( $settings['placeholder_text'] ) ? esc_attr( $settings['placeholder_text'] ) : ''; ?>"></textarea>
                <button type="button" class="ai-chat-send" data-api-key="<?php echo esc_attr( $configuration['api_key'] ? 'set' : '' ); ?>">
                    <span class="ai-chat-send__label"><?php echo isset( $settings['send_button_text'] ) ? esc_html( $settings['send_button_text'] ) : ''; ?></span>
                </button>
            </div>
        </div>
        <?php
    }
}

