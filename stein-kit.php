<?php
/**
 * Plugin Name: Stein Kit
 * Plugin URI: https://github.com/reefki/stein-kit
 * Description: The essential plugin for Stein theme.
 * Version: 1.2.4
 * Author: Rifki Aria Gumelar
 * Author URI: https://rifki.net
 * Text Domain: stein-kit
 *
 * @package Stein Kit
 * @since   1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include the core plugin class.
 */
require plugin_dir_path( __FILE__ ) . 'classes/class-stein-kit.php';

if ( ! function_exists( 'stein_kit_activated' ) ) {
	/**
	 * Plugin Activation.
	 *
	 * @since 1.0
	 * @param bool $networkwide The networkwide.
	 * @return void
	 */
	function stein_kit_activated( $networkwide ) {
		do_action( 'stein_kit_activated', $networkwide );
	}

	register_activation_hook( __FILE__, 'stein_kit_activated' );
}

if ( ! function_exists( 'stein_kit_deactivated' ) ) {
	/**
	 * Plugin Deactivation.
	 *
	 * @since 1.0
	 * @param bool $networkwide The networkwide.
	 * @return void
	 */
	function stein_kit_deactivated( $networkwide ) {
		do_action( 'stein_kit_deactivated', $networkwide );
	}

	register_deactivation_hook( __FILE__, 'stein_kit_deactivated' );
}

if ( ! function_exists( 'stein_kit' ) ) {
	/**
	 * Run the plugin.
	 *
	 * @since 1.0
	 */
	function stein_kit() {
		return Stein_Kit::instance();
	}

	stein_kit();
}
