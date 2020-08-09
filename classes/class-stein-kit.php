<?php
/**
 * Stein Kit Core class.
 *
 * @package Stein Kit
 * @since   1.1.3
 */

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
			$this->load_files_in( plugin_dir_path( __FILE__ ) . '../includes', array( 'index.php' ) );
			$this->load_files_in( plugin_dir_path( __FILE__ ) . '../classes', array( 'index.php' ) );
			$this->load_files_in( plugin_dir_path( __FILE__ ) . '../widgets', array( 'index.php' ) );
		}

		/**
		 * Loads plugin's text domain.
		 *
		 * @return void
		 */
		public function set_locale() {
			load_plugin_textdomain( 'stein-kit', false, basename( __DIR__ ) . '../languages' );
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
