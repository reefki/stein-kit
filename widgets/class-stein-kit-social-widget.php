<?php
/**
 * Social links widget.
 *
 * @package Stein Kit
 * @since   1.0.0
 */

defined('ABSPATH') or die('Cheatin\' Uh?');

if (! class_exists('Stein_Toolkit_Social_Widget') && class_exists('WP_Widget'))
{
    final class Stein_Toolkit_Social_Widget extends WP_Widget
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
            parent::__construct('stein_toolkit_social', esc_html__('Social Links', 'stein-kit'), array(
                'classname' => 'widget_stein_toolkit_social',
                'description' => esc_html__('Displays links to your social accounts.', 'stein-kit'),
            ));
        }

        /**
         * Widget default args.
         * 
         * @since  1.0.0
         * @access protected
         * @return void
         */
        public function defaults()
        {
            return apply_filters('stein_toolkit_social_widget_defaults', array(
                'title' => null,
                'style' => null,
                'limit' => 10,
            ));
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
            $instance = (array) $old;
            $new = wp_parse_args((array) $new, $this->defaults());
            
            $instance['title'] = sanitize_text_field($new['title']);
            $instance['limit'] = absint($new['limit']);
            $instance['style'] = sanitize_text_field($new['style']);

            if ($instance['limit'] < 1) {
                $instance['limit'] = 1;
            }

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
            $instance = wp_parse_args((array) $instance, $this->defaults());
            $columns = array_fill(1, 5, null);
            $styles = array(
                '' => esc_html__('Default', 'stein-kit'),
                'colored' => esc_html__('Colored', 'stein-kit'),
                'bg-colored' => esc_html__('Colored Background', 'stein-kit'),
            );

            foreach ($columns as $key => $value) {
                $columns[$key] = wp_sprintf(_n('%s column', '%s columns', $key, 'stein-kit'), $key);
            }
            ?>
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                        <?php esc_html_e('Title:', 'stein-kit'); ?>
                    </label>

                    <input id="<?php echo esc_attr($this->get_field_id('title')); ?>" class="widefat" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>">
                </p>

                <?php if (! empty($styles)) : ?>
                    <p>
                        <label for="<?php echo esc_attr($this->get_field_id('style')); ?>">
                            <?php esc_html_e('Style:', 'stein-kit'); ?>
                        </label>

                        <select id="<?php echo esc_attr($this->get_field_id('style')); ?>" class="widefat" name="<?php echo esc_attr($this->get_field_name('style')); ?>">
                            <?php foreach($styles as $key => $label) : ?>
                                <option value="<?php echo esc_attr($key); ?>"<?php selected($instance['style'] == $key) ;?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                <?php endif; ?>

                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>">
                        <?php esc_html_e('Limit:', 'stein-kit'); ?>
                    </label>
                    
                    <input id="<?php echo esc_attr($this->get_field_id('limit')); ?>" class="tiny-text" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" min="1" value="<?php echo esc_attr($instance['limit']); ?>">
                </p>
            <?php
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
            $instance = wp_parse_args((array) $instance, $this->defaults());

            echo apply_filters('before_widget', $args['before_widget'], $instance, $this->id_base);

            if ($title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base)) {
                echo apply_filters('before_title', $args['before_title'], $instance, $this->id_base);
                echo esc_html($title);
                echo apply_filters('after_title', $args['after_title'], $instance, $this->id_base);
            }
    
            $social = apply_filters('stein_toolkit_social_widget_links', array());
            $social = array_slice($social, 0, $instance['limit']);

            $link_classes = array(
                'social-link',
                'tw-block',
                'tw-h-10',
                'tw-w-10',
                'tw-rounded-full',
                'tw-flex',
                'tw-items-center',
                'tw-justify-center',
                'tw-text-lg',
                'lg_tw-h-12',
                'lg_tw-w-12',
                'hover_tw-no-underline',
                'hover_tw-opacity-75',
            );

            switch($instance['style']) {
                case 'colored';
                    $link_classes[] = 'tw-bg-alt tw-text-%1$s hover_tw-text-%1$s';
                    break;

                case 'bg-colored';
                    $link_classes[] = 'tw-bg-%s';
                    break;

                default;
                    $link_classes[] = 'tw-bg-alt tw-text-strong hover_tw-text-strong';
                    break;
            }
            
            if (! empty($social)) {
                ?>
                <ul class="social tw-flex tw-flex-wrap tw--mr-3 tw--mb-3">
                    <?php foreach ($social as $item) : ?>
                        <li class="social-item tw-w-1/5 tw-mb-3">
                            <a href="<?php echo esc_url($item['url']); ?>" title="<?php echo esc_attr($item['name']); ?>" class="<?php echo esc_attr(wp_sprintf(implode(' ', $link_classes), $item['icon'])); ?>" target="_blank"><i class="si si-<?php echo esc_attr($item['icon']); ?>"></i></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php
            } else {
                echo wp_sprintf(
                    '<p class="tw-mb-0">%s</p>',
                    esc_html__('No social links available.', 'stein-kit')
                );
            }
            
            echo apply_filters('after_widget', $args['after_widget'], $instance, $this->id_base);
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
    register_widget('Stein_Toolkit_Social_Widget');
});