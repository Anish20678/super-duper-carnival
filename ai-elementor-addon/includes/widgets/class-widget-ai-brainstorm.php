<?php
namespace AI_Elementor_Addon\Widgets;

use AI_Elementor_Addon\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Widget for brainstorming ideas and structured outlines.
 */
class AI_Brainstorm_Widget extends Widget_Base {

    public function get_name() {
        return 'ai_brainstorm';
    }

    public function get_title() {
        return __( 'AI Brainstorm Canvas', 'ai-elementor-addon' );
    }

    public function get_icon() {
        return 'eicon-lightbulb-o';
    }

    public function get_categories() {
        return [ 'ai-elementor-addon' ];
    }

    public function get_keywords() {
        return [ 'ideas', 'brainstorm', 'ai', 'outline' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Prompt', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'canvas_title',
            [
                'label'       => __( 'Canvas Title', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Instant brainstorming', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'prompt_template',
            [
                'label'       => __( 'Prompt Template', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::TEXTAREA,
                'default'     => __( 'Generate 5 innovative ideas about {{topic}} and outline the first steps for each.', 'ai-elementor-addon' ),
                'description' => __( 'Use {{topic}} placeholder to dynamically insert visitor input.', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'cta_label',
            [
                'label'   => __( 'Button Label', 'ai-elementor-addon' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Ideate now', 'ai-elementor-addon' ),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => __( 'Style', 'ai-elementor-addon' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'typography',
                'selector' => '{{WRAPPER}} .ai-brainstorm-widget',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'background',
                'selector' => '{{WRAPPER}} .ai-brainstorm-widget',
                'types'    => [ 'classic', 'gradient' ],
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
            'model'    => $configuration['default_model'],
            'template' => $settings['prompt_template'],
        ];
        ?>
        <div class="ai-brainstorm-widget" data-settings='<?php echo esc_attr( wp_json_encode( $data ) ); ?>'>
            <h3><?php echo esc_html( $settings['canvas_title'] ); ?></h3>
            <label for="ai-brainstorm-topic-<?php echo esc_attr( $this->get_id() ); ?>" class="screen-reader-text"><?php esc_html_e( 'Topic', 'ai-elementor-addon' ); ?></label>
            <input type="text" id="ai-brainstorm-topic-<?php echo esc_attr( $this->get_id() ); ?>" placeholder="<?php esc_attr_e( 'Enter a topic (e.g., community event)', 'ai-elementor-addon' ); ?>">
            <button type="button" class="ai-brainstorm-generate" data-api-key="<?php echo esc_attr( $configuration['api_key'] ? 'set' : '' ); ?>">
                <?php echo esc_html( $settings['cta_label'] ); ?>
            </button>
            <div class="ai-brainstorm-output" aria-live="polite"></div>
        </div>
        <?php
    }
}

