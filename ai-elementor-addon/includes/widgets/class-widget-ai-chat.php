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
            'assistant_avatar',
            [
                'label'       => __( 'Assistant Avatar', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::MEDIA,
                'description' => __( 'Optional profile image displayed next to the title.', 'ai-elementor-addon' ),
                'condition'   => [
                    'show_header' => 'yes',
                ],
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

        $this->add_control(
            'knowledge_pages',
            [
                'label'        => __( 'Train With Site Pages', 'ai-elementor-addon' ),
                'type'         => Controls_Manager::SELECT2,
                'multiple'     => true,
                'label_block'  => true,
                'options'      => $this->get_available_pages_options(),
                'description'  => __( 'Search and select published pages to feed as additional context for the assistant.', 'ai-elementor-addon' ),
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
            'section_notifications',
            [
                'label' => __( 'Email Notifications', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'notification_enable',
            [
                'label'        => __( 'Send Email On First Message', 'ai-elementor-addon' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'ai-elementor-addon' ),
                'label_off'    => __( 'No', 'ai-elementor-addon' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => __( 'Sends a notification email as soon as the visitor sends their first chat message.', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'notification_to',
            [
                'label'       => __( 'To', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => \get_bloginfo( 'admin_email' ),
                'description' => __( 'Enter one or more email addresses separated by commas.', 'ai-elementor-addon' ),
                'condition'   => [
                    'notification_enable' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'notification_subject',
            [
                'label'       => __( 'Subject', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'New AI chat from {site_name}', 'ai-elementor-addon' ),
                'condition'   => [
                    'notification_enable' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'notification_from_name',
            [
                'label'       => __( 'From Name', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => \get_bloginfo( 'name' ),
                'condition'   => [
                    'notification_enable' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'notification_from_email',
            [
                'label'       => __( 'From Email', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => \get_bloginfo( 'admin_email' ),
                'condition'   => [
                    'notification_enable' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'notification_reply_to',
            [
                'label'     => __( 'Reply To', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::TEXT,
                'condition' => [
                    'notification_enable' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'notification_cc',
            [
                'label'     => __( 'Cc', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::TEXT,
                'condition' => [
                    'notification_enable' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'notification_bcc',
            [
                'label'     => __( 'Bcc', 'ai-elementor-addon' ),
                'type'      => Controls_Manager::TEXT,
                'condition' => [
                    'notification_enable' => 'yes',
                ],
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
            'chat_window_height_mode',
            [
                'label'       => __( 'Height Preset', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::SELECT,
                'options'     => [
                    ''     => __( 'Custom', 'ai-elementor-addon' ),
                    'fit'  => __( 'Fit To Screen', 'ai-elementor-addon' ),
                ],
                'default'     => '',
                'description' => __( 'Select “Fit To Screen” to stretch the chat between the header and the input area.', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'chat_window_height',
            [
                'label'      => __( 'Chat Window Max Height', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'vh', 'em', 'rem' ],
                'range'      => [
                    'px' => [ 'min' => 160, 'max' => 960 ],
                    '%'  => [ 'min' => 10, 'max' => 100 ],
                    'vh' => [ 'min' => 10, 'max' => 100 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-window' => 'max-height: {{SIZE}}{{UNIT}};',
                ],
                'condition'  => [
                    'chat_window_height_mode!' => 'fit',
                ],
            ]
        );

        $this->add_control(
            'chat_window_min_height',
            [
                'label'      => __( 'Chat Window Min Height', 'ai-elementor-addon' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'vh', 'em', 'rem' ],
                'range'      => [
                    'px' => [ 'min' => 120, 'max' => 640 ],
                    '%'  => [ 'min' => 10, 'max' => 100 ],
                    'vh' => [ 'min' => 10, 'max' => 100 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ai-chat-window' => 'min-height: {{SIZE}}{{UNIT}};',
                ],
                'condition'  => [
                    'chat_window_height_mode!' => 'fit',
                ],
            ]
        );

        $this->add_control(
            'chat_window_fixed_height',
            [
                'label'       => __( 'Fixed Chat Height', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::SLIDER,
                'size_units'  => [ 'px', '%', 'vh', 'em', 'rem' ],
                'range'       => [
                    'px' => [ 'min' => 160, 'max' => 800 ],
                    '%'  => [ 'min' => 10, 'max' => 100 ],
                    'vh' => [ 'min' => 10, 'max' => 100 ],
                ],
                'selectors'   => [
                    '{{WRAPPER}} .ai-chat-window' => 'height: {{SIZE}}{{UNIT}};',
                ],
                'description' => __( 'Sets an explicit height for the chat area when required.', 'ai-elementor-addon' ),
                'condition'   => [
                    'chat_window_height_mode!' => 'fit',
                ],
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

        $selected_page_ids = [];

        if ( ! empty( $settings['knowledge_pages'] ) && is_array( $settings['knowledge_pages'] ) ) {
            $selected_page_ids = array_map( 'absint', $settings['knowledge_pages'] );
        }

        $page_context = $this->build_pages_context( $selected_page_ids );

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

        if ( ! empty( $page_context ) ) {
            $compiled_pages = [];

            foreach ( $page_context as $page ) {
                $compiled_pages[] = sprintf(
                    "%s\nURL: %s\nSummary: %s",
                    $page['title'],
                    $page['url'],
                    $page['excerpt']
                );
            }

            $prompt_parts[] = __( 'Site knowledge base:', 'ai-elementor-addon' ) . "\n\n" . implode( "\n\n", $compiled_pages );
            $prompt_parts[] = __( 'When a visitor asks about these pages, include rich HTML in your responses (bold, italics, lists) and finish with a sentence linking to the most relevant page using an anchor tag.', 'ai-elementor-addon' );
        } else {
            $prompt_parts[] = __( 'Respond using helpful HTML formatting (paragraphs, bold, italics, links) whenever it improves clarity.', 'ai-elementor-addon' );
        }

        $compiled_prompt = trim( implode( "\n\n", array_filter( $prompt_parts ) ) );

        $enable_typing_indicator = isset( $settings['enable_typing_indicator'] ) ? 'yes' === $settings['enable_typing_indicator'] : true;
        $persist_session         = isset( $settings['persist_session'] ) ? 'yes' === $settings['persist_session'] : true;

        $notifications = [
            'enabled'  => isset( $settings['notification_enable'] ) ? 'yes' === $settings['notification_enable'] : false,
            'to'       => isset( $settings['notification_to'] ) ? \sanitize_text_field( $settings['notification_to'] ) : '',
            'subject'  => isset( $settings['notification_subject'] ) ? \sanitize_text_field( $settings['notification_subject'] ) : '',
            'fromName' => isset( $settings['notification_from_name'] ) ? \sanitize_text_field( $settings['notification_from_name'] ) : '',
            'from'     => isset( $settings['notification_from_email'] ) ? \sanitize_email( $settings['notification_from_email'] ) : '',
            'replyTo'  => isset( $settings['notification_reply_to'] ) ? \sanitize_email( $settings['notification_reply_to'] ) : '',
            'cc'       => isset( $settings['notification_cc'] ) ? \sanitize_text_field( $settings['notification_cc'] ) : '',
            'bcc'      => isset( $settings['notification_bcc'] ) ? \sanitize_text_field( $settings['notification_bcc'] ) : '',
        ];

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
            'notifications'          => $notifications,
            'knowledgePages'         => array_values( $page_context ),
        ];

        $wrapper_classes = [ 'ai-chat-widget' ];

        if ( empty( $quick_prompts ) ) {
            $wrapper_classes[] = 'ai-chat-widget--no-prompts';
        }

        if ( empty( $settings['show_header'] ) || 'yes' !== $settings['show_header'] ) {
            $wrapper_classes[] = 'ai-chat-widget--no-header';
        }

        if ( ! empty( $settings['chat_window_height_mode'] ) && 'fit' === $settings['chat_window_height_mode'] ) {
            $wrapper_classes[] = 'ai-chat-widget--fit-screen';
        }

        $wrapper_classnames = array_map( 'sanitize_html_class', $wrapper_classes );
        ?>
        <div class="<?php echo esc_attr( implode( ' ', $wrapper_classnames ) ); ?>" data-widget-id="<?php echo esc_attr( $this->get_id() ); ?>" data-settings='<?php echo esc_attr( wp_json_encode( $data ) ); ?>'>
            <?php if ( ! empty( $settings['show_header'] ) && 'yes' === $settings['show_header'] ) : ?>
                <div class="ai-chat-header">
                    <?php if ( ! empty( $settings['assistant_avatar']['url'] ) ) : ?>
                        <span class="ai-chat-avatar">
                            <img src="<?php echo esc_url( $settings['assistant_avatar']['url'] ); ?>" alt="" />
                        </span>
                    <?php endif; ?>
                    <h3>
                        <span class="ai-chat-title-text"><?php echo isset( $settings['chat_title'] ) ? esc_html( $settings['chat_title'] ) : ''; ?></span>
                    </h3>
                </div>
            <?php endif; ?>
            <div class="ai-chat-window">
                <div class="ai-chat-bubble ai-chat-bubble--assistant">
                    <?php echo isset( $settings['welcome_message'] ) ? esc_html( $settings['welcome_message'] ) : ''; ?>
                </div>
            </div>
            <?php if ( ! empty( $quick_prompts ) ) : ?>
                <div class="ai-chat-quick-prompts" role="list">
                    <?php foreach ( $quick_prompts as $prompt ) : ?>
                        <button type="button" class="ai-chat-quick-prompt" role="listitem"><?php echo esc_html( $prompt ); ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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

    /**
     * Retrieve a list of published pages for selection controls.
     *
     * @return array
     */
    private function get_available_pages_options() {
        $pages = \get_pages(
            [
                'sort_column' => 'post_title',
                'post_status' => 'publish',
                'number'      => 200,
            ]
        );

        if ( empty( $pages ) || ! is_array( $pages ) ) {
            return [];
        }

        $options = [];

        foreach ( $pages as $page ) {
            $options[ $page->ID ] = $page->post_title;
        }

        return $options;
    }

    /**
     * Build an array of page context to embed into prompts and JS settings.
     *
     * @param array $page_ids Page identifiers selected in the editor.
     *
     * @return array[]
     */
    private function build_pages_context( array $page_ids ) {
        $context = [];

        foreach ( $page_ids as $page_id ) {
            $post = \get_post( $page_id );

            if ( ! $post || 'publish' !== $post->post_status ) {
                continue;
            }

            $url     = \get_permalink( $post );
            $excerpt = $post->post_excerpt ? $post->post_excerpt : \wp_strip_all_tags( $post->post_content );
            $excerpt = \wp_trim_words( $excerpt, 80, '…' );

            $context[] = [
                'id'      => (int) $post->ID,
                'title'   => $post->post_title,
                'url'     => $url ? \esc_url_raw( $url ) : '',
                'excerpt' => $excerpt,
                'slug'    => $post->post_name,
            ];
        }

        return $context;
    }
}

