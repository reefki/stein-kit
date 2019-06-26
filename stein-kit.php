<?php
/**
 * Plugin Name: Stein Kit
 * Plugin URI: 
 * Description: The essential plugin for Stein theme.
 * Version: 1.0.0
 * Author: Rifki
 */

if (! defined('ABSPATH')) {
	exit;
}

if (! class_exists('Stein_Kit')) {
    final class Stein_Kit
    {
        /**
         * Self cached instance.
         * 
         * @since  1.0.0
         * @access protected
         * @var    self
         */
        protected static $instance;

        /**
         * Class constructor.
         * 
         * @return void
         */
        public function __construct()
        {
            add_action('init', array($this, 'set_locale'));

            $this->load_dependencies();
        }

        /**
         * Loads plugin's dependecies.
         * 
         * @return void
	     * @access private
         */
        private function load_dependencies()
        {
            $this->load_files_in(plugin_dir_path(__FILE__) . '/classes', array('index.php'));
            $this->load_files_in(plugin_dir_path(__FILE__) . '/widgets', array('index.php'));
        }

        /**
         * Loads plugin's text domain.
         * 
         * @return void
         */
        public function set_locale()
        {
            load_plugin_textdomain('stein-kit', false, basename(__DIR__) . '/languages');
        }

        /**
         * Load the specified file.
         * 
         * @param  string  $file
         * @return boolean
         */
        public function load_file($file)
        {
            if (! file_exists($file)) {
                return false;
            }

            require_once $file;

            return true;
        }

        /**
         * Load all files in the specified directory.
         * 
         * @param  string $directory
         * @param  array $exclude
         * @return integer
         */
        public function load_files_in($directory, $exclude = array())
        {
            $files = glob(trailingslashit($directory) . '*.php');
            $found = 0;

            foreach ($files as $file) {
                if (in_array(basename($file), $exclude)) {
                    continue;
                }

                if ($this->load_file(trailingslashit($directory) . basename($file))) {
                    $found++;
                }
            }

            return $found;
        }

        /**
         * Returns self cached instance or build new instance if undefined.
         * 
         * @since  1.0.0
         * @access public
         * @return self
         */
        public static function instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }

            return self::$instance;
        }
    }
}

/**
 * Get option
 *
 * @since 1.0.0
 */
if (! function_exists('stein_kit_get_option')) {
    function stein_kit_get_option($key, $default = null) {
        $options = get_option('stein_kit', array());

        if (substr($key, 0, 13) != 'stein_kit_') {
            $key = "stein_kit_{$key}";
        }

        if (isset($options[$key]) && ! is_null($options[$key])) {
            return $options[$key];
        }

        return $default;
    }
}

/**
 * Plugin Activation.
 * 
 * @since 1.0.0
 * @return void
 */
function stein_kit_activated() {
	do_action('stein_kit_activated');
}

register_activation_hook(__FILE__, 'stein_kit_activated');

/**
 * Plugin Deactivation.
 * 
 * @since 1.0.0
 * @return void
 */
function stein_kit_deactivated() {
	do_action('stein_kit_deactivated');
}

register_deactivation_hook(__FILE__, 'stein_kit_deactivated');

/**
 * Run the plugin.
 *
 * @since 1.0.0
 */
if (! function_exists('stein_kit')) {
    function stein_kit() {
        return Stein_Kit::instance();
    }
}

stein_kit();