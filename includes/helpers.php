<?php
/**
 * Helpers functions.
 *
 * @package Stein Kit
 * @since   1.1.3
 */

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
