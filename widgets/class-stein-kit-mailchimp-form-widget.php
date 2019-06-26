<?php
/**
 * Social links widget.
 *
 * @package Stein Kit
 * @since   1.0
 */

defined('ABSPATH') or die('Cheatin\' Uh?');

if (! class_exists('Stein_Kit_Mailchimp_Form_Widget') && class_exists('WP_Widget'))
{
    final class Stein_Kit_Mailchimp_Form_Widget extends WP_Widget
    {
        /**
         * The class constructor.
         * 
         * @since  1.0
         * @access protected
         * @return void
         */
        public function __construct()
        {
            parent::__construct('stein_kit_mailchimp_form', esc_html__('Mailchimp Form', 'stein-kit'), array(
                'classname' => 'widget_stein_kit_mailchimp_form',
                'description' => esc_html__('Displays Mailchimp sign-up form.', 'stein-kit'),
            ));
        }

        /**
         * Widget default args.
         * 
         * @since  1.0
         * @access protected
         * @return void
         */
        public function defaults()
        {
            return apply_filters('stein_kit_mailchimp_form_widget_defaults', array(
                'title' => null,
                'list' => null,
                'text' => null,
                'terms_url' => null,
                'terms_label' => esc_html__('I agree to the terms & conditions', 'stein-kit')
            ));
        }

        /**
         * Update widget backend settings.
         * 
         * @since  1.0
         * @access protected
         * @param  array $new
         * @param  array $instance
         * @return array
         */
        public function update($new, $old) {
            $instance = (array) $old;
            $new = wp_parse_args((array) $new, $this->defaults());
            
            $instance['title'] = sanitize_text_field($new['title']);
            $instance['list']  = sanitize_text_field($new['list']);
            $instance['text']  = sanitize_textarea_field($new['text']);
            $instance['terms_url']  = esc_url_raw($new['terms_url']);
            $instance['terms_label']  = sanitize_text_field($new['terms_label']);

            return $instance;
        }

        /**
         * Outputs the settings update form.
         * 
         * @since  1.0
         * @access protected
         * @param  array $instance
         * @return void
         */
        public function form($instance)
        {
            $instance = wp_parse_args((array) $instance, $this->defaults());
            $lists = stein_kit_mailchimp_lists();
            ?>
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                        <?php esc_html_e('Title:', 'stein-kit'); ?>
                    </label>

                    <input id="<?php echo esc_attr($this->get_field_id('title')); ?>" class="widefat" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>">
                </p>

                <?php if (! empty($lists)) : ?>
                    <p>
                        <label for="<?php echo esc_attr($this->get_field_id('list')); ?>">
                            <?php esc_html_e('List:', 'stein-kit'); ?>
                        </label>

                        <select id="<?php echo esc_attr($this->get_field_id('list')); ?>" class="widefat" name="<?php echo esc_attr($this->get_field_name('list')); ?>">
                            <option value=""<?php selected($instance['list'] == '') ;?>><?php echo esc_html__('Select list', 'stein-kit'); ?></option>
                            <?php foreach($lists as $list) : ?>
                                <option value="<?php echo esc_attr($list['id']); ?>"<?php selected($instance['list'] == $list['id']) ;?>><?php echo esc_html($list['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                <?php else: ?>
                    
                <?php endif; ?>

                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('text')); ?>"><?php esc_html_e('Text:', 'stein-kit'); ?></label>
                    <textarea id="<?php echo esc_attr($this->get_field_id('text')); ?>" class="widefat" name="<?php echo esc_attr($this->get_field_name('text')); ?>" rows="5"><?php echo esc_html($instance['text']); ?></textarea>
                </p>
                
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('terms_url')); ?>">
                        <?php esc_html_e('Terms URL:', 'stein-kit'); ?>
                    </label>

                    <input id="<?php echo esc_attr($this->get_field_id('terms_url')); ?>" class="widefat" name="<?php echo esc_attr($this->get_field_name('terms_url')); ?>" type="url" value="<?php echo esc_attr($instance['terms_url']); ?>">
                </p>
                
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('terms_label')); ?>">
                        <?php esc_html_e('Terms Label:', 'stein-kit'); ?>
                    </label>

                    <input id="<?php echo esc_attr($this->get_field_id('terms_label')); ?>" class="widefat" name="<?php echo esc_attr($this->get_field_name('terms_label')); ?>" type="text" value="<?php echo esc_attr($instance['terms_label']); ?>">
                </p>
            <?php
        }

        /**
         * Echoes the widget content.
         * 
         * @since  1.0
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
            
            if (! empty($instance['text'])) {
                echo wp_sprintf('<p class="tw-mb-4">%s</p>', do_shortcode($instance['text']));
            }

            if (! empty($instance['list'])) {
                $list = stein_kit_mailchimp_lists($instance['list']);
                ?>
                <form method="POST" action="<?php echo esc_url(set_url_scheme($list['subscribe_url_short'], 'https')); ?>">
                    <p class="tw-mb-4">
                        <input type="email" class="input" name="EMAIL" placeholder="Your email address" required>
                    </p>

                    <?php if (! empty($instance['terms_url'])) : ?>
                        <p class="tw-mb-4">
                            <label>
                                <input name="AGREE_TO_TERMS" type="checkbox" value="1" required>
                                <a href="<?php echo esc_url($instance['terms_url']); ?>" target="_blank" class="tw-text-sm tw-text-inherit"><?php echo esc_html($instance['terms_label']); ?></a>
                            </label>
                        </p>
                    <?php endif; ?>

                    <input type="submit" class="btn btn-accent tw-w-full" value="Subscribe">
                </form>
                <?php
            }
            
            echo apply_filters('after_widget', $args['after_widget'], $instance, $this->id_base);
        }
    }
}

/**
 * Register the widget.
 *
 * @package Stein Kit
 * @since   1.0
 */
add_action('widgets_init', function () {
    register_widget('Stein_Kit_Mailchimp_Form_Widget');
});