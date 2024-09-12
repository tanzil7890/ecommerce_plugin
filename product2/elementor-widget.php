<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PCM_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'pcm_elementor_widget';
    }

    public function get_title() {
        return __('Product Collections Manager', 'pcm');
    }

    public function get_icon() {
        return 'eicon-products';
    }

    public function get_categories() {
        return ['product-collections'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'pcm'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Collection Selection
        $collections = get_option('pcm_collections', array());
        $collection_options = ['0' => __('Select a Collection', 'pcm')];
        foreach ($collections as $name => $products) {
            $collection_options[$name] = $name;
        }

        $this->add_control(
            'collection_name',
            [
                'label' => __('Select Collection', 'pcm'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $collection_options,
                'default' => '0',
            ]
        );

        // Layout Options
        $this->add_control(
            'columns',
            [
                'label' => __('Columns', 'pcm'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 6,
                'step' => 1,
                'default' => 3,
            ]
        );

        $this->add_control(
            'rows',
            [
                'label' => __('Rows', 'pcm'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 6,
                'step' => 1,
                'default' => 2,
            ]
        );

        $this->end_controls_section();

        // Style Section for Product Card
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Product Card Style', 'pcm'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_background_color',
            [
                'label' => __('Card Background Color', 'pcm'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pcm-product-card' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'label' => __('Card Border', 'pcm'),
                'selector' => '{{WRAPPER}} .pcm-product-card',
            ]
        );

        $this->add_control(
            'card_border_radius',
            [
                'label' => __('Border Radius', 'pcm'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pcm-product-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'label' => __('Box Shadow', 'pcm'),
                'selector' => '{{WRAPPER}} .pcm-product-card',
            ]
        );

        $this->end_controls_section();

        // Style Section for Product Image
        $this->start_controls_section(
            'image_style_section',
            [
                'label' => __('Product Image Style', 'pcm'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'image_border_radius',
            [
                'label' => __('Image Border Radius', 'pcm'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pcm-product-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section for Product Title
        $this->start_controls_section(
            'title_style_section',
            [
                'label' => __('Product Title Style', 'pcm'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'pcm'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pcm-product-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Title Typography', 'pcm'),
                'selector' => '{{WRAPPER}} .pcm-product-title',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $collection_name = $settings['collection_name'];
        $columns = $settings['columns'];
        $rows = $settings['rows'];

        if ($collection_name === '0') {
            echo __('Please select a collection', 'pcm');
            return;
        }

        $collections = get_option('pcm_collections', array());
        if (!isset($collections[$collection_name])) {
            echo __('Collection not found', 'pcm');
            return;
        }

        $product_ids = $collections[$collection_name];
        $products_to_show = array_slice($product_ids, 0, $columns * $rows);

        echo '<div class="pcm-product-grid" style="display: grid; grid-template-columns: repeat(' . $columns . ', 1fr); gap: 20px;">';
        foreach ($products_to_show as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product) continue;

            echo '<div class="pcm-product-card">';
            echo '<div class="pcm-product-image">' . $product->get_image() . '</div>';
            echo '<h3 class="pcm-product-title">' . $product->get_name() . '</h3>';
            echo '<div class="pcm-product-rating">' . wc_get_rating_html($product->get_average_rating()) . '</div>';
            echo '<div class="pcm-product-price">' . $product->get_price_html() . '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    protected function _content_template() {
        ?>
        <# if ( settings.collection_name === '0' ) { #>
            <?php echo __('Please select a collection', 'pcm'); ?>
        <# } else { #>
            <div class="pcm-product-grid" style="display: grid; grid-template-columns: repeat({{ settings.columns }}, 1fr); gap: 20px;">
                <# for (var i = 0; i < settings.columns * settings.rows; i++) { #>
                    <div class="pcm-product-card">
                        <div class="pcm-product-image">[Product Image]</div>
                        <h3 class="pcm-product-title">[Product Title]</h3>
                        <div class="pcm-product-rating">[Product Rating]</div>
                        <div class="pcm-product-price">[Product Price]</div>
                    </div>
                <# } #>
            </div>
        <# } #>
        <?php
    }
}