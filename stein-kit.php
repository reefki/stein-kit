<?php
/**
 * Plugin Name: Stein Kit
 * Plugin URI: https://github.com/reefki/stein-kit
 * Description: The essential plugin for Stein theme.
 * Version: 1.1.2
 * Author: Rifki
 * Author URI: https://rifki.net
 * Text Domain: stein-kit
 *
 * @package Stein Kit
 * @since   1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stein_Kit' ) ) {
	/**
	 * Stein kit Class.
	 *
	 * @package Stein Kit
	 * @since   1.0
	 */
	final class Stein_Kit {

		/**
		 * Self cached instance.
		 *
		 * @since  1.0
		 * @access protected
		 * @var    self
		 */
		protected static $instance;

		/**
		 * Class constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'set_locale' ) );

			$this->load_dependencies();
		}

		/**
		 * Loads plugin's dependecies.
		 *
		 * @return void
		 * @access private
		 */
		private function load_dependencies() {
			$this->load_files_in( plugin_dir_path( __FILE__ ) . '/classes', array( 'index.php' ) );
			$this->load_files_in( plugin_dir_path( __FILE__ ) . '/widgets', array( 'index.php' ) );
		}

		/**
		 * Loads plugin's text domain.
		 *
		 * @return void
		 */
		public function set_locale() {
			load_plugin_textdomain( 'stein-kit', false, basename( __DIR__ ) . '/languages' );
		}

		/**
		 * Load the specified file.
		 *
		 * @param  string $file The file path.
		 * @return boolean
		 */
		public function load_file( $file ) {
			if ( ! file_exists( $file ) ) {
				return false;
			}

			require_once $file;

			return true;
		}

		/**
		 * Load all files in the specified directory.
		 *
		 * @param  string $directory Target irectory.
		 * @param  array  $exclude Files to be excluded.
		 * @return integer
		 */
		public function load_files_in( $directory, $exclude = array() ) {
			$files = glob( trailingslashit( $directory ) . '*.php' );
			$found = 0;

			foreach ( $files as $file ) {
				if ( in_array( basename( $file ), $exclude, true ) ) {
					continue;
				}

				if ( $this->load_file( trailingslashit( $directory ) . basename( $file ) ) ) {
					$found++;
				}
			}

			return $found;
		}

		/**
		 * Returns self cached instance or build new instance if undefined.
		 *
		 * @since  1.0
		 * @access public
		 * @return self
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}

if ( ! function_exists( 'stein_kit_get_option' ) ) {
	/**
	 * Get option
	 *
	 * @since 1.0
	 * @param  string $key The option key.
	 * @param  mixed  $default The option default value.
	 * @return mixed
	 */
	function stein_kit_get_option( $key, $default = null ) {
		$options = get_option( 'stein_kit', array() );

		if ( 'stein_kit_' !== substr( $key, 0, 13 ) ) {
			$key = "stein_kit_{$key}";
		}

		if ( isset( $options[ $key ] ) && ! is_null( $options[ $key ] ) ) {
			return $options[ $key ];
		}

		return $default;
	}
}

if ( ! function_exists( 'stein_kit_share_links' ) ) {
	/**
	 * Post share links.
	 *
	 * @since  1.0
	 * @param  array $args Post share links arguments.
	 * @return string
	 */
	function stein_kit_share_links( $args ) {
		$links = array();

		$args = wp_parse_args(
			$args,
			array(
				'url'   => '',
				'text'  => '',
				'media' => '',
			)
		);

		$config = array(
			'facebook' => array(
				'label' => 'Facebook',
				'icon'  => '<i class="si si-facebook"></i>',
				'url'   => 'https://www.facebook.com/sharer/sharer.php',
				'query' => array(
					'u' => $args['url'],
					't' => $args['text'],
				),
			),
			'twitter'  => array(
				'label' => 'Twitter',
				'icon'  => '<i class="si si-twitter"></i>',
				'url'   => 'https://twitter.com/intent/tweet',
				'query' => array(
					'url'  => $args['url'],
					'text' => $args['text'],
				),
			),
			'linkedin' => array(
				'label' => 'Linkedin',
				'icon'  => '<i class="si si-linkedin"></i>',
				'url'   => 'https://www.linkedin.com/shareArticle',
				'query' => array(
					'mini'  => true,
					'url'   => $args['url'],
					'title' => $args['text'],
				),
			),
			'email'    => array(
				'label' => 'Email',
				'icon'  => '<i class="si si-envelope"></i>',
				'url'   => 'mailto:',
				'query' => array(
					'subject' => $args['text'],
					'body'    => wp_sprintf(
						'%1$s: %2$s',
						esc_html__( 'Check out this article', 'stein-kit' ),
						esc_url( $args['url'] )
					),
				),
			),
		);

		foreach ( $config as $key => $value ) {
			$links[ $key ] = array(
				'label' => $value['label'],
				'icon'  => $value['icon'],
				'url'   => $value['url'] . '?' . http_build_query( $value['query'] ),
			);
		}

		return $links;
	}
}

if ( ! function_exists( 'stein_kit_activated' ) ) {
	/**
	 * Plugin Activation.
	 *
	 * @since 1.0
	 * @return void
	 */
	function stein_kit_activated() {
		do_action( 'stein_kit_activated' );
	}
}

register_activation_hook( __FILE__, 'stein_kit_activated' );

if ( ! function_exists( 'stein_kit_deactivated' ) ) {
	/**
	 * Plugin Deactivation.
	 *
	 * @since 1.0
	 * @return void
	 */
	function stein_kit_deactivated() {
		do_action( 'stein_kit_deactivated' );
	}
}

register_deactivation_hook( __FILE__, 'stein_kit_deactivated' );

if ( ! function_exists( 'stein_kit' ) ) {
	/**
	 * Run the plugin.
	 *
	 * @since 1.0
	 */
	function stein_kit() {
		return Stein_Kit::instance();
	}
}

stein_kit();
