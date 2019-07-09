<?php
/**
 * Instagram.
 *
 * @package Stein Kit
 * @since   1.0
 */

defined( 'ABSPATH' ) || die( 'Cheatin\' Uh?' );

if ( ! class_exists( 'Stein_Kit_Instagram' ) ) {
	/**
	 * Instagram class.
	 *
	 * @since 1.0
	 */
	final class Stein_Kit_Instagram {

		/**
		 * Username.
		 *
		 * @since  1.0
		 * @access protected
		 * @var    string
		 */
		protected $username;

		/**
		 * Cache expires.
		 *
		 * @since  1.0
		 * @access protected
		 * @var    integer
		 */
		protected $expires = 0;

		/**
		 * Data instance.
		 *
		 * @since  1.0
		 * @access protected
		 * @var    array
		 */
		protected $data = array();

		/**
		 * Class constructor.
		 *
		 * @since  1.0
		 * @param  string  $username Instagram username.
		 * @param  integer $expires  Cache expires.
		 * @return void
		 */
		public function __construct( $username, $expires = 720 ) {
			$this->username = $username;
			$this->expires  = $expires;

			$this->fetch();
		}

		/**
		 * Gets data from Instagram.
		 *
		 * @since  1.0
		 * @return void
		 */
		public function fetch() {
			$cache_key = 'stein_kit_instagram_' . md5( $this->username );
			$cached    = get_transient( $cache_key );

			if ( $cached ) {
				$this->data = $cached;
			} else {
				$response = wp_safe_remote_get(
					sprintf( 'https://www.instagram.com/%s', $this->username ),
					array(
						'httpversion' => '1.1',
						'timeout'     => 120,
					)
				);

				if ( is_array( $response ) && ! is_wp_error( $response ) ) {
					$response = wp_remote_retrieve_body( $response );

					if ( preg_match( '/window\._sharedData = (.*);<\/script>/', $response, $matches ) ) {
						$json = json_decode( end( $matches ), true );

						if ( $json && JSON_ERROR_NONE === json_last_error() ) {
							$this->data = $json;
							set_transient( $cache_key, $json, 60 * $this->expires );
						}
					}
				}
			}
		}

		/**
		 * Gets profile data.
		 *
		 * @since  1.0
		 * @return array
		 */
		public function profile() {
			$profile = array();

			if ( isset( $this->data['entry_data']['ProfilePage'][0]['graphql']['user'] ) ) {
				$user = $this->data['entry_data']['ProfilePage'][0]['graphql']['user'];

				if ( isset( $user['username'] ) ) {
					$profile['username'] = $user['username'];
				}

				if ( isset( $user['full_name'] ) ) {
					$profile['name'] = $user['full_name'];
				}

				if ( isset( $user['edge_follow']['count'] ) ) {
					$profile['following'] = absint( $user['edge_follow']['count'] );
				}

				if ( isset( $user['edge_followed_by']['count'] ) ) {
					$profile['followers'] = absint( $user['edge_followed_by']['count'] );
				}

				if ( isset( $user['profile_pic_url'] ) ) {
					$profile['avatar'] = $user['profile_pic_url'];
				}

				if ( isset( $user['profile_pic_url_hd'] ) ) {
					$profile['avatar_hd'] = $user['profile_pic_url_hd'];
				}
			}

			return $profile;
		}

		/**
		 * Gets Instagram posts.
		 *
		 * @since  1.0
		 * @return array
		 */
		public function posts() {
			$posts = array();

			if ( isset( $this->data['entry_data']['ProfilePage'][0]['graphql']['user'] ) ) {
				$user = $this->data['entry_data']['ProfilePage'][0]['graphql']['user'];

				foreach ( $user['edge_owner_to_timeline_media']['edges'] as $edge ) {
					if ( empty( $edge['node'] ) ) {
						continue;
					}

					if ( empty( $edge['node']['thumbnail_resources'] ) ) {
						continue;
					}

					$post = array(
						'id'         => null,
						'url'        => null,
						'image'      => null,
						'width'      => 0,
						'height'     => 0,
						'caption'    => null,
						'comments'   => 0,
						'likes'      => 0,
						'thumbnails' => array(),
					);

					if ( ! empty( $edge['node']['id'] ) ) {
						$post['id'] = $edge['node']['id'];
					}

					if ( ! empty( $edge['node']['display_url'] ) ) {
						$post['image'] = $edge['node']['display_url'];
					}

					if ( ! empty( $edge['node']['dimensions']['width'] ) ) {
						$post['width'] = $edge['node']['dimensions']['width'];
					}

					if ( ! empty( $edge['node']['dimensions']['height'] ) ) {
						$post['height'] = $edge['node']['dimensions']['height'];
					}

					if ( ! empty( $edge['node']['shortcode'] ) ) {
						$post['url'] = sprintf( 'https://www.instagram.com/p/%s', $edge['node']['shortcode'] );
					}

					if ( ! empty( $edge['node']['edge_media_to_caption']['edges'][0]['node']['text'] ) ) {
						$post['caption'] = strtok( $edge['node']['edge_media_to_caption']['edges'][0]['node']['text'], "\n" );
					}

					if ( ! empty( $edge['node']['edge_media_to_comment']['count'] ) ) {
						$post['comments'] = absint( $edge['node']['edge_media_to_comment']['count'] );
					}

					if ( ! empty( $edge['node']['edge_liked_by']['count'] ) ) {
						$post['likes'] = absint( $edge['node']['edge_liked_by']['count'] );
					}

					if ( ! empty( $edge['node']['taken_at_timestamp'] ) ) {
						$post['timestamp'] = absint( $edge['node']['taken_at_timestamp'] );
					}

					foreach ( $edge['node']['thumbnail_resources'] as $thumbnail ) {
						if ( empty( $thumbnail['src'] ) ) {
							continue;
						}

						if ( isset( $thumbnail['config_width'] ) && ( 150 === $thumbnail['config_width'] ) ) {
							$post['thumbnails']['150'] = $thumbnail['src'];
						}

						if ( isset( $thumbnail['config_width'] ) && ( 240 === $thumbnail['config_width'] ) ) {
							$post['thumbnails']['240'] = $thumbnail['src'];
						}

						if ( isset( $thumbnail['config_width'] ) && ( 480 === $thumbnail['config_width'] ) ) {
							$post['thumbnails']['480'] = $thumbnail['src'];
						}

						if ( isset( $thumbnail['config_width'] ) && ( 640 === $thumbnail['config_width'] ) ) {
							$post['thumbnails']['640'] = $thumbnail['src'];
						}
					}

					$posts[] = $post;
				}
			}

			return $posts;
		}
	}
}
