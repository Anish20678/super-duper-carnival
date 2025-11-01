<?php
namespace AI_Elementor_Addon\Widgets;

use AI_Elementor_Addon\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Widget that helps users craft prompts for image generation.
 */
class AI_Image_Prompt_Widget extends Widget_Base {

    public function get_name() {
        return 'ai_image_prompt';
    }

    public function get_title() {
        return __( 'AI Image Prompt Mentor', 'ai-elementor-addon' );
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return [ 'ai-elementor-addon' ];
    }

    public function get_keywords() {
        return [ 'image', 'prompt', 'generator', 'ai' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Prompt Mentorship', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'mentor_title',
            [
                'label'   => __( 'Title', 'ai-elementor-addon' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Design a striking visual', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'mentor_description',
            [
                'label'   => __( 'Description', 'ai-elementor-addon' ),
                'type'    => Controls_Manager::TEXTAREA,
                'default' => __( 'Describe the scene and we will enhance it with lighting, style and atmosphere suggestions.', 'ai-elementor-addon' ),
            ]
        );

        $repeater = new Repeater();
        $repeater->add_control(
            'label',
            [
                'label'   => __( 'Attribute', 'ai-elementor-addon' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Lighting style', 'ai-elementor-addon' ),
            ]
        );
        $repeater->add_control(
            'placeholder',
            [
                'label'   => __( 'Placeholder', 'ai-elementor-addon' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'e.g., cinematic dusk', 'ai-elementor-addon' ),
            ]
        );

        $this->add_control(
            'attributes',
            [
                'label'       => __( 'Prompt Attributes', 'ai-elementor-addon' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ label }}}',
                'default'     => [
                    [ 'label' => __( 'Lighting style', 'ai-elementor-addon' ), 'placeholder' => __( 'e.g., cinematic dusk', 'ai-elementor-addon' ) ],
                    [ 'label' => __( 'Art direction', 'ai-elementor-addon' ), 'placeholder' => __( 'e.g., watercolor storybook', 'ai-elementor-addon' ) ],
                    [ 'label' => __( 'Mood', 'ai-elementor-addon' ), 'placeholder' => __( 'e.g., hopeful and uplifting', 'ai-elementor-addon' ) ],
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => __( 'Typography', 'ai-elementor-addon' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'mentor_typography',
                'selector' => '{{WRAPPER}} .ai-image-prompt-widget',
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
            'model'      => $configuration['default_model'],
            'attributes' => $settings['attributes'],
        ];
        ?>
        <div class="ai-image-prompt-widget" data-settings='<?php echo esc_attr( wp_json_encode( $data ) ); ?>'>
            <h3><?php echo esc_html( $settings['mentor_title'] ); ?></h3>
            <p><?php echo esc_html( $settings['mentor_description'] ); ?></p>
            <textarea class="ai-image-prompt-base" placeholder="<?php esc_attr_e( 'Describe your scene...', 'ai-elementor-addon' ); ?>"></textarea>
            <div class="ai-image-prompt-attributes">
                <?php foreach ( $settings['attributes'] as $index => $attribute ) : ?>
                    <label for="ai-image-prompt-<?php echo esc_attr( $this->get_id() . '-' . $index ); ?>"><?php echo esc_html( $attribute['label'] ); ?></label>
                    <input type="text" id="ai-image-prompt-<?php echo esc_attr( $this->get_id() . '-' . $index ); ?>" placeholder="<?php echo esc_attr( $attribute['placeholder'] ); ?>">
                <?php endforeach; ?>
            </div>
            <button type="button" class="ai-image-prompt-generate" data-api-key="<?php echo esc_attr( $configuration['api_key'] ? 'set' : '' ); ?>">
                <?php esc_html_e( 'Craft prompt', 'ai-elementor-addon' ); ?>
            </button>
            <div class="ai-image-prompt-output" aria-live="polite"></div>
        </div>
        <?php
    }
}

