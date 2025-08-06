<?php
/*
 * Plugin Name:			Swipe Slider
 * Plugin URI:			https://pluginenvision.com/plugins/swipe-slider
 * Description:			Make dynamic slider with solid, gradient, or image background.
 * Version:				0.22
 * Requires at least:	6.5
 * Requires PHP:		7.2
 * Author:				Plugin Envision
 * Author URI:			https://pluginenvision.com
 * License:				GPLv3 or later
 * License URI:			https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:			swipe-slider
 * Domain Path:			/languages
 */

if( !defined( 'ABSPATH' ) ) { exit; }

if( function_exists( 'evss_fs' ) ){
	evss_fs()->set_basename( false, __FILE__ );
}else{
	define( 'EVSS_VERSION', isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '0.19' );
	define( 'EVSS_DIR_URL', plugin_dir_url( __FILE__ ) );
	define( 'EVSS_DIR_PATH', plugin_dir_path( __FILE__ ) );
	define( 'EVSS_HAS_FS', file_exists( EVSS_DIR_PATH . 'vendor/freemius/start.php' ) );

	if( EVSS_HAS_FS ){
		require_once EVSS_DIR_PATH . 'includes/premium.php';
	}

	function evssWusul(){
		if( EVSS_HAS_FS ){
			return evss_fs()->can_use_premium_code();
		}else{
			return false;
		}
	}

	require_once 'includes/DesignLibrary/DesignLibrary.php';

	if( !class_exists( 'EVSSPlugin' ) ){
		class EVSSPlugin{
			public function __construct(){
				add_action( 'init', [ $this, 'onInit' ] );
				add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), [$this, 'pluginActionLinks'] );
				add_action( 'wp_ajax_evssWusulDekho', [$this, 'evssWusulDekho'] );
				add_action( 'wp_ajax_nopriv_evssWusulDekho', [$this, 'evssWusulDekho'] );
			}

			function onInit(){
				register_block_type( __DIR__ . '/build' );
			}

			function pluginActionLinks ( $links ) {
				$acLinks = [];

				if( !evssWusul() ){
					$acLinks[] = "<a href='https://pluginenvision.com/plugins/swipe-slider/#plans' target='_blank' style='font-size: 1.1em; font-weight: bold; color: #5465ff; text-shadow: 1px 1px 1px #eee;'>Get Pro</a>";
				}

				return array_merge( $links, $acLinks );
			}

			function evssWusulDekho(){
				$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) ?? null;

				if( !wp_verify_nonce( $nonce, 'wp_rest' )){
					wp_send_json_error( 'Invalid Request' );
				}

				wp_send_json_success( [ 'wusul' => evssWusul() ] );
			}
		}
		new EVSSPlugin();
	}
}