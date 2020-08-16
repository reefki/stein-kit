<?php
/**
 * Instagram.
 *
 * @package Stein Kit
 * @since   1.0
 */

defined( 'ABSPATH' ) || wp_die( 'Cheatin\' Uh?' );

if ( ! class_exists( 'Stein_Kit_Instagram' ) ) {
	/**
	 * Instagram class.
	 *
	 * @since 1.0
	 */
	final class Stein_Kit_Instagram {

		/**
		 * Unique identifier.
		 *
		 * @since  1.0
		 * @access protected
		 * @var    string
		 */
		protected $id = 'stein_kit_instagram';

		/**
		 * Base API url.
		 *
		 * @since  1.0
		 * @access protected
		 * @var    string
		 */
		protected $api_url = 'https://graph.instagram.com/';

		/**
		 * Options
		 *
		 * @since  1.0
		 * @access protected
		 * @var    array
		 */
		protected $options = array();

		/**
		 * Class constructor.
		 *
		 * @since  1.0
		 * @return void
		 */
		public function __construct() {
			$this->options = (array) get_option( $this->id );

			add_action( 'stein_kit_activated', array( $this, 'activation' ) );
			add_action( 'stein_kit_deactivated', array( $this, 'deactivation' ) );

			add_action( 'admin_init', array( $this, 'register_setting' ) );
			add_action( 'admin_menu', array( $this, 'add_options_page' ) );
			add_action( $this->id . '_refresh_access_token', array( $this, 'refresh_access_token' ) );

			add_filter( $this->id . '_get_option', array( $this, 'get_option' ), 10, 2 );
			add_filter( $this->id . '_get_profile', array( $this, 'get_profile' ), 10, 2 );
			add_filter( $this->id . '_get_media', array( $this, 'get_media' ), 10, 2 );
		}

		/**
		 * Register settings.
		 *
		 * @since  1.0
		 * @access public
		 * @return void
		 */
		public function register_setting() {
			register_setting(
				$this->id,
				$this->id,
				array( $this, 'sanitize' )
			);

			add_settings_section(
				"{$this->id}_settings",
				'',
				false,
				$this->id
			);

			add_settings_field(
				"{$this->id}_access_token",
				esc_html__( 'Access Token ', 'stein-kit' ),
				array( $this, 'field_access_token_template' ),
				$this->id,
				"{$this->id}_settings"
			);

			add_settings_field(
				"{$this->id}_cache_expiry_time",
				esc_html__( 'Cache ', 'stein-kit' ),
				array( $this, 'field_cache_expiry_time_template' ),
				$this->id,
				"{$this->id}_settings"
			);
		}

		/**
		 * Sanitize form input.
		 *
		 * @since  1.0
		 * @access public
		 * @param  array $input The form input.
		 * @return array
		 */
		public function sanitize( $input ) {
			if ( isset( $_POST['clear_cache'] ) ) {
				$this->cleanup();

				add_settings_error( $this->id, $this->id, esc_html__( 'Cache cleared.', 'stein-kit' ), 'updated' );

				return $this->options;
			}

			$values = array();

			$map = array(
				'access_token'      => 'string',
				'cache_expiry_time' => 'integer',
			);

			foreach ( $map as $key => $type ) {
				if ( ! isset( $input[ $key ] ) ) {
					continue;
				}

				switch ( $type ) {
					case 'integer':
						$value = intval( $input[ $key ] );
						break;

					default:
						$value = sanitize_text_field( $input[ $key ] );
						break;
				}

				$values[ $key ] = $value;
			}

			return $values;
		}

		/**
		 * Add options page.
		 *
		 * @since  1.0
		 * @access public
		 * @return void
		 */
		public function add_options_page() {
			add_options_page(
				esc_html__( 'Instagram Settings', 'stein-kit' ),
				esc_html__( 'Instagram', 'stein-kit' ),
				'manage_options',
				$this->id,
				array( $this, 'options_page_template' )
			);
		}

		/**
		 * Options page template.
		 *
		 * @since  1.0
		 * @access public
		 * @return void
		 */
		public function options_page_template() {
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Instagram Settings', 'stein-kit' ); ?></h1>

				<form method="post" action="options.php">
					<?php
						settings_fields( $this->id );
						do_settings_sections( $this->id );
					?>

					<hr>

					<p class="submit">
						<?php submit_button( esc_html__( 'Save Changes', 'stein-kit' ), 'primary', 'submit', false ); ?>
						<?php submit_button( esc_html__( 'Clear Cache', 'stein-kit' ), 'delete', 'clear_cache', false, array( 'style' => 'margin-left: 0.5rem;' ) ); ?>
					</p>
				</form>
			</div>
			<?php
		}

		/**
		 * Access token field template.
		 *
		 * @since  1.2.4
		 * @access public
		 * @return void
		 */
		public function field_access_token_template() {
			$value = $this->get_option( '', 'access_token' );

			printf(
				'<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s">',
				'access_token',
				esc_attr( $this->id ),
				esc_attr( $value )
			);

			printf(
				'<p class="help">%1$s <a href="%3$s" target="_blank">%2$s</a>.</p>',
				esc_html__( 'Learn how to create a Instagram app and obtain the access token', 'stein-kit' ),
				esc_html__( 'here', 'stein-kit' ),
				esc_url( 'https://docs.rifki.net/stein/#setup-instagram' )
			);
		}

		/**
		 * Cache expiry time field template.
		 *
		 * @since  1.2.2
		 * @access public
		 * @return void
		 */
		public function field_cache_expiry_time_template() {
			$value = $this->get_option( DAY_IN_SECONDS / 60, 'cache_expiry_time' );
			?>
				<input type="text" id="<?php echo esc_attr( $this->id . '_cache_expiry_time' ); ?>" name="<?php echo esc_attr( $this->id . '[cache_expiry_time]' ); ?>" value="<?php echo esc_attr( $value ); ?>">

				<p class="help"><?php esc_html_e( 'The cache expiry time in minutes. Default is 1440, equivalent to 24 hours.', 'stein-kit' ); ?></p>
			<?php
		}

		/**
		 * Get option.
		 *
		 * @since  1.0
		 * @access public
		 * @param  mixed $default The option default value.
		 * @param  mixed $key The option key.
		 * @return mixed
		 */
		public function get_option( $default, $key ) {
			$options = (array) get_option( $this->id );

			if ( array_key_exists( $key, $options ) ) {
				return $options[ $key ];
			}

			return $default;
		}

		/**
		 * Make an API request.
		 *
		 * @since  1.0
		 * @access private
		 * @param  string $endpoint The API endpoint.
		 * @param  string $fields The API fields.
		 * @return array
		 */
		private function request( $endpoint, $fields = null ) {
			if ( ! empty( $this->options['access_token'] ) ) {
				$response = wp_safe_remote_get(
					add_query_arg(
						array(
							'access_token' => $this->options['access_token'],
							'fields'       => $fields,
						),
						$this->api_url . $endpoint
					)
				);

				if ( ! is_wp_error( $response ) && ( 200 === wp_remote_retrieve_response_code( $response ) ) ) {
					return json_decode( wp_remote_retrieve_body( $response ), true );
				}
			}

			return array();
		}

		/**
		 * Get account's profile data.
		 *
		 * @since  1.0
		 * @access public
		 * @param  string $profile Default profile.
		 * @return array
		 */
		public function get_profile( $profile = array() ) {
			$cache   = $this->id . '_profile';
			$profile = get_transient( $cache );

			if ( empty( $profile ) ) {
				$profile = (array) $this->request( 'me', 'id,account_type,username,media_count' );

				if ( ! empty( $profile ) ) {
					set_transient( $cache, $profile, intval( $this->get_option( DAY_IN_SECONDS / 60, 'cache_expiry_time' ) ) * 60 );
				}
			}

			return $profile;
		}

		/**
		 * Get media.
		 *
		 * @since  1.0
		 * @access public
		 * @param  string $media Default media.
		 * @return array
		 */
		public function get_media( $media = array() ) {
			$cache = $this->id . '_media';
			$media = get_transient( $cache );

			if ( empty( $media ) ) {
				$media = (array) $this->request( 'me/media', 'id,media_type,media_url,permalink,thumbnail_url,caption,timestamp' );

				if ( ! empty( $media ) ) {
					set_transient( $cache, $media, intval( $this->get_option( DAY_IN_SECONDS / 60, 'cache_expiry_time' ) ) * 60 );
				}
			}

			return $media;
		}

		/**
		 * Refresh access token
		 *
		 * @since  1.2.2
		 * @access public
		 * @return void
		 */
		public function refresh_access_token() {
			$response = wp_safe_remote_get(
				add_query_arg(
					array(
						'grant_type'   => 'ig_refresh_token',
						'access_token' => $this->options['access_token'],
					),
					$this->api_url . 'refresh_access_token'
				)
			);

			if ( ! is_wp_error( $response ) && ( 200 === wp_remote_retrieve_response_code( $response ) ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );

				$this->options['access_token'] = $body['access_token'];

				update_option( $this->id, $this->options );

				return $body['access_token'];
			}
		}

		/**
		 * Hook when plugin activated.
		 *
		 * @since  1.2.2
		 * @access public
		 * @return void
		 */
		public function activation() {
			if ( ! wp_next_scheduled( $this->id . '_refresh_access_token' ) ) {
				wp_schedule_event( time(), 'weekly', $this->id . '_refresh_access_token' );
			}
		}

		/**
		 * Hook when plugin deactivated.
		 *
		 * @since  1.2.2
		 * @access public
		 * @return void
		 */
		public function deactivation() {
			$this->cleanup();

			wp_clear_scheduled_hook( $this->id . '_refresh_access_token' );
		}

		/**
		 * Clean up transients.
		 *
		 * @since  1.0
		 * @access private
		 * @return void
		 */
		private function cleanup() {
			global $wpdb;

			$transients = $wpdb->get_results(
				$wpdb->prepare( 'SELECT * FROM ' . $wpdb->options . ' WHERE `option_name` = %s OR `option_name` LIKE %s', '_transient_' . $this->id, '_transient_' . $this->id . '_%' )
			);

			foreach ( (array) $transients as $transient ) {
				delete_transient( substr( $transient->option_name, strlen( '_transient_' ) ) );
			}
		}
	}
}

new Stein_Kit_Instagram();

if ( ! function_exists( 'stein_kit_instagram_get_option' ) ) {
	/**
	 * Get Instagram option.
	 *
	 * @since  1.0
	 * @param  mixed $key The option key.
	 * @param  mixed $default The option default value.
	 * @return mixed
	 */
	function stein_kit_instagram_get_option( $key, $default = null ) {
		return apply_filters( 'stein_kit_instagram_get_option', $default, $key );
	}
}

if ( ! function_exists( 'stein_kit_instagram_get_profile' ) ) {
	/**
	 * Get Instagram profile.
	 *
	 * @since  1.0
	 * @return array
	 */
	function stein_kit_instagram_get_profile() {
		return apply_filters( 'stein_kit_instagram_get_profile', array() );
	}
}

if ( ! function_exists( 'stein_kit_instagram_get_media' ) ) {
	/**
	 * Get Instagram media.
	 *
	 * @since  1.0
	 * @return array
	 */
	function stein_kit_instagram_get_media() {
		return apply_filters( 'stein_kit_instagram_get_media', array() );
	}
}
