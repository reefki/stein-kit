<?php
/**
 * Mailchimp class.
 *
 * @package Stein Kit
 * @since   1.0
 */

defined('ABSPATH') or die('Cheatin\' Uh?');

if (! class_exists('Stein_Kit_Mailchimp'))
{
    class Stein_Kit_Mailchimp
    {
        /**
         * Unique identifier.
         * 
         * @since  1.0
         * @access protected
         * @var    string
         */
        protected $id = 'stein_kit_mailchimp';

        /**
         * Base API url.
         * 
         * @since  1.0
         * @access protected
         * @var    string
         */
        protected $api_url = 'https://%s.api.mailchimp.com/3.0/';

        /**
         * Mailchimp API key.
         * 
         * @since  1.0
         * @access protected
         * @var    string
         */
        protected $options;

        /**
         * The class constructor.
         * 
         * @since  1.0
         * @access public
         * @return void
         */
        public function __construct()
        {
            $this->options = get_option($this->id);

            if (! empty($this->options['api_key'])) {
                $api_key_parts = array_map('trim', explode('-', $this->options['api_key']));

                if (is_array($api_key_parts) && ! empty($api_key_parts)) {
                    $this->api_url = sprintf($this->api_url, end($api_key_parts));
                }
            }

            add_filter('stein_kit_mailchimp_lists', array($this, 'lists'), 10, 2);

            add_action('admin_menu', array($this, 'add_options_page'));
            add_action('admin_init', array($this, 'register_setting'));
        }

        /**
         * Add options page.
         * 
         * @since  1.0
         * @access public
         * @return void
         */
        public function add_options_page()
        {
            add_options_page(
                esc_html__('MailChimp Settings', 'stein-kit'), 
                esc_html__('MailChimp', 'stein-kit'), 
                'manage_options', 
                $this->id, 
                array($this, 'options_page_template')
            );
        }

        /**
         * Register settings.
         * 
         * @since  1.0
         * @access public
         * @return void
         */
        public function register_setting()
        {
            register_setting(
                $this->id,
                $this->id,
                array($this, 'sanitize')
            );

            add_settings_section(
                "{$this->id}_api_settings",
                esc_html__('API Settings', 'stein-kit'),
                false,
                $this->id
            ); 

            add_settings_field(
                "{$this->id}_api_key",
                esc_html__('API Key', 'stein-kit'),
                array($this, 'field_api_key_template'), 
                $this->id, 
                "{$this->id}_api_settings"
            );   
        }

        /**
         * Sanitize form input.
         * 
         * @since  1.0
         * @access public
         * @param array $input
         * @return void
         */
        public function sanitize($input)
        {
            if (isset($_POST['clear'])) {
                $this->cleanup();
            }

            $values = array();
            
            if (isset($input['api_key'])) {
                $values['api_key'] = sanitize_text_field($input['api_key']);
            }

            return $values;
        }

        /**
         * Options page template.
         * 
         * @since  1.0
         * @access public
         * @return void
         */
        public function options_page_template()
        {
            ?>
            <div class="wrap">
                <h1><?php esc_html_e('MailChimp Settings', 'stein-kit'); ?></h1>

                <form method="post" action="options.php">
                    <?php
                    settings_fields($this->id);
                    do_settings_sections($this->id);
                    ?>

                    <p class="submit">
                        <?php
                        submit_button(esc_html__('Save Changes'), 'primary', 'submit', false);
                        submit_button(esc_html__('Clear Cache'), 'delete', 'clear', false, array(
                            'style' => 'margin-left: 1rem;'
                        ));
                        ?>
                    </p>
                </form>
            </div>
            <?php
        }

        /**
         * API Key field template.
         * 
         * @since  1.0
         * @access public
         * @return void
         */
        public function field_api_key_template()
        {
            $value = isset(($this->options['api_key'])) ? ($this->options['api_key']) : null;
            
            printf(
                '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s">',
                'api_key',
                $this->id,
                $value
            );
            
            printf(
                '<p class="help">%1$s <a href="%3$s" target="_blank">%2$s</a></p>',
                esc_html__('The API key for connecting with your Mailchimp account.', 'stein-kit'),
                esc_html__('Get your API key here.', 'stein-kit'),
                esc_url('https://admin.mailchimp.com/account/api')
            );
        }

        /**
         * Make a request
         * 
         * @since  1.0
         * @access public
         * @param  string  $endpoint
         * @param  array   $query
         * @param  boolean $json
         * @return array
         */
        public function request($endpoint, $query = array(), $json = true)
        {
            if (empty($this->options['api_key'])) {
                return;
            }

            $url = add_query_arg($query, $this->api_url . $endpoint);

            $response = wp_safe_remote_get($url, array(
                'headers' => array('Authorization' => 'apikey ' . $this->options['api_key'])
            ));

            if (! is_wp_error($response) && (wp_remote_retrieve_response_code($response) == 200)) {
                if ($json) {
                    return json_decode(wp_remote_retrieve_body($response), true);
                }

                return wp_remote_retrieve_body($response);
            }
        }

        /**
         * Get lists.
         * 
         * @since  1.0
         * @access public
         * @return void
         */
        public function lists($lists = array(), $id = null)
        {
            if ($cached = get_transient("{$this->id}_lists")) {
                $lists = $cached;
            } else {
                $response = $this->request('lists');
    
                if ($response && ! empty($response['lists'])) {
                    $lists = $response['lists'];
                    set_transient("{$this->id}_lists", $lists, 1440 * 60);
                }
            }

            if (! is_null($id) && ! empty($lists)) {
                foreach ($lists as $list) {
                    if ($list['id'] === $id) {
                        return $list;
                    }
                } 
            }

            return $lists;
        }

        /**
         * Clean up transients.
         * 
         * @since  1.0
         * @access public
         * @return void
         */
        public function cleanup()
        {
            delete_transient("{$this->id}_lists");
        }
    }
}

new Stein_Kit_Mailchimp();

/**
 * Get MailChimp lists.
 *
 * @package Incredibbble
 * @since   1.0
 */
if (! function_exists('stein_kit_mailchimp_lists')) {
    function stein_kit_mailchimp_lists($id = null) {
        return apply_filters('stein_kit_mailchimp_lists', array(), $id);
    }
}