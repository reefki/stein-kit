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
		protected $api_url = 'https://api.instagram.com/v1/';

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
			$this->options = get_option( $this->id );

			add_action( 'admin_init', array( $this, 'register_setting' ) );
			add_action( 'admin_menu', array( $this, 'add_options_page' ) );

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
				"{$this->id}_api_settings",
				esc_html__( 'API Settings', 'stein-kit' ),
				false,
				$this->id
			);

			add_settings_field(
				"{$this->id}_access_token",
				esc_html__( 'Access Token', 'stein-kit' ),
				array( $this, 'field_access_token_template' ),
				$this->id,
				"{$this->id}_api_settings"
			);
		}

		/**
		 * Sanitize form input.
		 *
		 * @since  1.0
		 * @access public
		 * @param  array $input Imput data to sanitize.
		 * @return array
		 */
		public function sanitize( $input ) {
			if ( isset( $_POST['clear'] ) ) {
				$this->cleanup();

				add_settings_error( $this->id, $this->id, esc_html__( 'Cache cleared.', 'stein-kit' ), 'updated' );
			}

			$values = array();

			if ( isset( $input['access_token'] ) ) {
				$values['access_token'] = sanitize_text_field( $input['access_token'] );

				if ( $values['access_token'] !== $this->options['access_token'] ) {
					$this->cleanup();
				}
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

					<p class="submit">
						<?php
						submit_button( esc_html__( 'Save Changes', 'stein-kit' ), 'primary', 'submit', false );
						submit_button(
							esc_html__( 'Clear Cache', 'stein-kit' ),
							'delete',
							'clear',
							false,
							array(
								'style' => 'margin-left: 1rem;',
							)
						);
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
		public function field_access_token_template() {
			$value = isset( $this->options['access_token'] ) ? $this->options['access_token'] : null;

			printf(
				'<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s">',
				'access_token',
				esc_attr( $this->id ),
				esc_attr( $value )
			);

			printf(
				'<p class="help">%1$s <a href="%3$s" target="_blank">%2$s</a></p>',
				esc_html__( 'The access token for connecting with your Instagram account.', 'stein-kit' ),
				esc_html__( 'Generate your access token here.', 'stein-kit' ),
				esc_url( 'https://instagram.pixelunion.net' )
			);

			printf(
				'<p class="help"><strong>%1$s:</strong> %2$s</p>',
				esc_html__( 'Please note', 'stein-kit' ),
				esc_html__( 'If you are using access token, you may only display the Instagram feed of your own Instagram profile.', 'stein-kit' ),
				esc_url( 'https://instagram.pixelunion.net' )
			);
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
			if ( array_key_exists( $key, $this->options ) ) {
				return $this->options[ $key ];
			}

			return $default;
		}

		/**
		 * Make an API request.
		 *
		 * @since  1.0
		 * @access public
		 * @param  string $endpoint The API endpoint.
		 * @return array
		 */
		public function request( $endpoint ) {
			if ( ! empty( $this->options['access_token'] ) ) {
				$response = wp_safe_remote_get(
					add_query_arg(
						array(
							'access_token' => $this->options['access_token'],
						),
						$this->api_url . $endpoint
					)
				);

				if ( ! is_wp_error( $response ) && ( 200 === wp_remote_retrieve_response_code( $response ) ) ) {
					$body = json_decode( wp_remote_retrieve_body( $response ), true );

					return is_array( $body ) && ! empty( $body['data'] ) ? $body['data'] : array();
				}
			}

			return array();
		}

		/**
		 * Scrap account's profile page.
		 *
		 * @since  1.0
		 * @access public
		 * @param  string $username Instagram account username.
		 * @return array
		 */
		public function scrap( $username ) {
			if ( ! empty( $username ) ) {
				$response = wp_safe_remote_get(
					sprintf( 'https://www.instagram.com/%s', $username ),
					array(
						'httpversion' => '1.1',
						'timeout'     => 120,
					)
				);

				if ( is_array( $response ) && ! is_wp_error( $response ) ) {
					$body = wp_remote_retrieve_body( $response );

					if ( preg_match( '/window\._sharedData = (.*);<\/script>/', $body, $matches ) ) {
						$json = json_decode( end( $matches ), true );

						if ( $json && JSON_ERROR_NONE === json_last_error() ) {
							return $json;
						}
					}
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
		 * @param  string $username Instagram account username.
		 * @return array
		 */
		public function get_profile( $profile = array(), $username = null ) {
			if ( ! empty( $this->options['access_token'] ) ) {
				return $this->get_profile_from_api();
			}

			return $this->get_profile_from_scrapper();
		}

		/**
		 * Get account's profile data from API request.
		 *
		 * @since  1.0
		 * @access public
		 * @return array
		 */
		public function get_profile_from_api() {
			$cache   = $this->id . '_profile';
			$profile = get_transient( $cache );

			if ( empty( $profile ) ) {
				$profile = (array) $this->request( 'users/self' );

				if ( ! empty( $profile ) ) {
					set_transient( $cache, $profile, 1440 * 60 );
				}
			}

			return $profile;
		}

		/**
		 * Get account's profile data by scrapping profile page.
		 *
		 * @since  1.0
		 * @access public
		 * @param  string $username Instagram account username.
		 * @return array
		 */
		public function get_profile_from_scrapper( $username = null ) {
			$profile = array();

			if ( ! empty( $username ) ) {
				$cache   = $this->id . '_profile_' . md5( $username );
				$profile = get_transient( $cache );

				if ( empty( $profile ) ) {
					$data = $this->scrap( $username );

					if ( ! empty( $data['entry_data']['ProfilePage'][0]['graphql']['user'] ) ) {
						$user = $data['entry_data']['ProfilePage'][0]['graphql']['user'];

						$profile = array(
							'id'              => $user['id'],
							'username'        => $user['username'],
							'profile_picture' => $user['profile_pic_url'],
							'full_name'       => $user['full_name'],
							'bio'             => $user['biography'],
							'website'         => $user['external_url'],
							'is_business'     => (bool) $user['is_business_account'],
							'counts'          => array(
								'media'       => $user['edge_owner_to_timeline_media']['count'],
								'follows'     => $user['edge_follow']['count'],
								'followed_by' => $user['edge_followed_by']['count'],
							),
						);
					}

					if ( ! empty( $profile ) ) {
						set_transient( $cache, $profile, 1440 * 60 );
					}
				}
			}

			return $profile;
		}

		/**
		 * Get account's recent media.
		 *
		 * @since  1.0
		 * @access public
		 * @param  string $media Default media.
		 * @param  string $username Instagram account username.
		 * @return array
		 */
		public function get_media( $media = array(), $username = null ) {
			if ( ! empty( $this->options['access_token'] ) ) {
				return $this->get_media_from_api();
			}

			return $this->get_media_from_scrapper();
		}

		/**
		 * Get account's recent media.
		 *
		 * @since  1.0
		 * @access public
		 * @return array
		 */
		public function get_media_from_api() {
			$cache = $this->id . '_media';
			$media = get_transient( $cache );

			if ( empty( $media ) ) {
				$media = (array) $this->request( 'users/self/media/recent' );

				if ( ! empty( $media ) ) {
					set_transient( $cache, $media, 1440 * 60 );
				}
			}

			return $media;
		}

		/**
		 * Get account's recent media by scrapping profile page.
		 *
		 * @since  1.0
		 * @access public
		 * @param  string $username Instagram account username.
		 * @return array
		 */
		public function get_media_from_scrapper( $username = null ) {
			$media = array();

			if ( ! empty( $username ) ) {
				$cache = $this->id . '_media_' . md5( $username );
				$media = get_transient( $cache );

				if ( empty( $media ) ) {
					$data = $this->scrap( $username );

					if ( ! empty( $data['entry_data']['ProfilePage'][0]['graphql']['user'] ) ) {
						$user = $data['entry_data']['ProfilePage'][0]['graphql']['user'];

						if ( ! empty( $user['edge_owner_to_timeline_media']['edges'] ) ) {
							$edges = $user['edge_owner_to_timeline_media']['edges'];

							foreach ( $edges as $edge ) {
								$item = array(
									'id'           => $edge['node']['id'],
									'user'         => array(
										'id'              => $user['id'],
										'full_name'       => $user['full_name'],
										'profile_picture' => $user['profile_pic_url'],
										'username'        => $user['username'],
									),
									'images'       => array(),
									'created_time' => $edge['node']['taken_at_timestamp'],
									'likes'        => array(
										'count' => $edge['node']['edge_liked_by']['count'],
									),
									'comments'     => array(
										'count' => $edge['node']['edge_media_to_comment']['count'],
									),
									'link'         => sprintf( 'https://www.instagram.com/p/%s', $edge['node']['shortcode'] ),
								);

								foreach ( $edge['node']['thumbnail_resources'] as $image ) {
									if ( empty( $image['src'] ) ) {
										continue;
									}

									$image_set = array(
										'thumbnail'      => 150,
										'low_resolution' => 320,
										'standard_resolution' => 640,
									);

									foreach ( $image_set as $key => $value ) {
										if ( isset( $image['config_width'] ) && ( $value === absint( $image['config_width'] ) ) ) {
											$item['images'][ $key ] = array(
												'width'  => absint( $image['config_width'] ),
												'height' => absint( $image['config_height'] ),
												'url'    => esc_url( $image['src'] ),
											);
										}
									}
								}

								$media[] = $item;
							}
						}
					}

					if ( ! empty( $media ) ) {
						set_transient( $cache, $media, 1440 * 60 );
					}
				}
			}

			return $media;
		}

		/**
		 * Clean up transients.
		 *
		 * @since  1.0
		 * @access public
		 * @return void
		 */
		public function cleanup() {
			global $wpdb;

			$transients = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->options . ' WHERE option_name = "_transient_' . $this->id . '" OR option_name LIKE "_transient_' . $this->id . '_%"' );

			if ( ! empty( $transients ) ) {
				foreach ( $transients as $transient ) {
					$prefix    = '_transient_';
					$transient = substr( $transient->option_name, strlen( $prefix ) );

					delete_transient( $transient );
				}
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
	 * @param  string $username Instagram account username.
	 * @return array
	 */
	function stein_kit_instagram_get_profile( $username = null ) {
		return apply_filters( 'stein_kit_instagram_get_profile', array(), $username );
	}
}

if ( ! function_exists( 'stein_kit_instagram_get_media' ) ) {
	/**
	 * Get Instagram media.
	 *
	 * @since  1.0
	 * @param  string $username Instagram account username.
	 * @return array
	 */
	function stein_kit_instagram_get_media( $username = null ) {
		return apply_filters( 'stein_kit_instagram_get_media', array(), $username );
	}
}
