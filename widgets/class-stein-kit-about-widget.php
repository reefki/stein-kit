<?php
/**
 * About widget.
 *
 * @package Stein Kit
 * @since   1.0.0
 */

defined('ABSPATH') or die('Cheatin\' Uh?');

if (! class_exists('Stein_Kit_About_Widget') && class_exists('WP_Widget'))
{
    final class Stein_Kit_About_Widget extends WP_Widget
    {
        /**
         * The class constructor.
         * 
         * @since  1.0.0
         * @access protected
         * @return void
         */
        public function __construct()
        {
            add_action('acf/init', array($this, 'acf_init'));

            parent::__construct('stein_kit_about', esc_html__('About', 'stein-kit'), array(
                'classname' => 'widget_stein_kit_about',
                'description' => esc_html__('Displays about.', 'stein-kit'),
            ));
        }

        /**
         * Widget default args.
         * 
         * @since  1.0.0
         * @access protected
         * @return void
         */
        public function acf_init()
        {
            acf_add_local_field_group(
                array(
                    'key' => 'group_widget_about',
                    'fields' => array(
                        array(
                            'key' => 'field_stein_kit_widget_about_logo',
                            'label' => esc_html__('Logo', 'stein-kit'),
                            'name' => 'logo',
                            'type' => 'group',
                            'layout' => 'block',
                            'sub_fields' => array(
                                array(
                                    'key' => 'field_stein_kit_widget_about_logo_image',
                                    'label' => esc_html__('Image', 'stein-kit'),
                                    'name' => 'logo_image',
                                    'type' => 'image',
                                    'preview_size' => 'medium',
                                    'return_format' => 'id',
                                ),
                                array(
                                    'key' => 'field_stein_kit_widget_about_logo_width',
                                    'label' => esc_html__('Width', 'stein-kit'),
                                    'name' => 'logo_width',
                                    'type' => 'text',
                                    'default_value' => 'auto',
                                ),
                                array(
                                    'key' => 'field_stein_kit_widget_about_logo_height',
                                    'label' => esc_html__('Height', 'stein-kit'),
                                    'name' => 'logo_height',
                                    'type' => 'text',
                                    'default_value' => 'auto',
                                ),
                            ),
                        ),
                        array(
                            'key' => 'field_stein_kit_widget_about_text',
                            'label' => esc_html__('Text', 'stein-kit'),
                            'name' => 'text',
                            'type' => 'textarea',
                            'rows' => 4,
                        ),
                        array(
                            'key' => 'field_stein_kit_widget_about_social',
                            'message' => esc_html__('Show social links?', 'stein-kit'),
                            'name' => 'social',
                            'type' => 'true_false',
                        ),
                    ),
                    'location' => array(
                        array(
                            array(
                                'param' => 'widget',
                                'operator' => '==',
                                'value' => 'stein_kit_about',
                            ),
                        ),
                    ),
                )
            );
        }

        /**
         * Update widget backend settings.
         * 
         * @since  1.0.0
         * @access protected
         * @param  array $new
         * @param  array $instance
         * @return array
         */
        public function update($new, $old) {
            $instance = array();
            
            $instance['title'] = isset($new['title']) ? sanitize_text_field($new['title']) : null;

            return $instance;
        }

        /**
         * Outputs the settings update form.
         * 
         * @since  1.0.0
         * @access protected
         * @param  array $instance
         * @return void
         */
        public function form($instance)
        {
            $title = ! empty($instance['title']) ? $instance['title'] : null;
            ?>
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                        <?php esc_html_e('Title:', 'stein-kit'); ?>
                    </label>
                    
                    <input id="<?php echo esc_attr($this->get_field_id('title')); ?>" class="widefat" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
                </p>
            <?php

            if (! function_exists('acf_add_local_field_group')) {
                echo wp_sprintf('<p>%s</p>',
                    esc_html__('Please install Advanced Custom Fields plugin to use this widget.', 'stein-kit')
                );
            }
        }

        /**
         * Echoes the widget content.
         * 
         * @since  1.0.0
         * @access protected
         * @param  array $args
         * @param  array $instance
         * @return void
         */
        public function widget($args, $instance)
        {
            $title = ! empty($instance['title']) ? $instance['title'] : null;
            $logo = $this->get_field('logo');
            $text = $this->get_field('text');
            $social_enabled = $this->get_field('social');
            $social_links = apply_filters('stein_kit_about_widget_social', array());
            
            echo apply_filters('before_widget', $args['before_widget'], $instance, $this->id_base);

            if ($title = apply_filters('widget_title', $title, $instance, $this->id_base)) {
                echo apply_filters('before_title', $args['before_title'], $instance, $this->id_base);
                echo esc_html($title);
                echo apply_filters('after_title', $args['after_title'], $instance, $this->id_base);
            }

            echo '<div class="about">';
            
            if (! empty($logo['logo_image'])) {
                echo '<style type="text/css">';
                    if (! empty($logo['logo_width'])) {
                        echo sprintf('#%s .about-logo img { width: %s; }', $this->id, $logo['logo_width']);
                    }
    
                    if (! empty($logo['logo_height'])) {
                        echo sprintf('#%s .about-logo img { height: %s; }', $this->id, $logo['logo_height']);
                    }
                echo '</style>';

                echo wp_sprintf('<a class="about-logo tw-block tw-mb-4" href="%1$s"><img src="%2$s" alt="%3$s"></a>',
                    esc_url(home_url()),
                    wp_get_attachment_image_url($logo['logo_image'], 'full'),
                    esc_attr(get_bloginfo('name'))
                );
            } else {
                echo wp_sprintf('<h1 class="about-title tw-text-3xl tw-mb-4"><a href="%1$s">%2$s</a></h1>',
                    esc_url(home_url()),
                    esc_attr(get_bloginfo('name'))
                );
            }

            if (! empty($text)) {
                echo wp_sprintf('<div class="about-text">%s</div>', do_shortcode($text));
            }
            
            if (! empty($social_links) && $social_enabled) : ?>
                <ul class="social tw-mt-6 tw--mx-4">
                    <?php foreach ($social_links as $item) : ?>
                        <li class="tw-inline-block tw-px-4">
                            <a href="<?php echo esc_url($item['url']); ?>" target="_blank" title="<?php echo esc_attr($item['name']); ?>" class="tw-block tw-text-base tw-text-strong hover_tw-no-underline">
                                <i class="si si-<?php echo esc_attr($item['icon']); ?>"></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif;

            echo '</div>';

            echo apply_filters('after_widget', $args['after_widget'], $instance, $this->id_base);
        }

        /**
         * Outputs the settings update form.
         * 
         * @since  1.0.0
         * @access protected
         * @param  array $instance
         * @return void
         */
        public function get_field($key, $default = null)
        {
            if (function_exists('get_field')) {
                return get_field($key, 'widget_' . $this->id);
            }

            return $default;
        }
    }
}

/**
 * Register the widget.
 *
 * @package Stein Kit
 * @since   1.0.0
 */
add_action('widgets_init', function () {
    register_widget('Stein_Kit_About_Widget');
});