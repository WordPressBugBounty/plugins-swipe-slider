<?php
/*
 * Plugin Name:			Swipe Slider
 * Plugin URI:			https://pluginenvision.com/plugins/swipe-slider
 * Description:			Make dynamic slider with solid, gradient, or image background.
 * Version:				0.16
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

define( 'EVSS_FREE_SLUG', 'swipe-slider/swipe-slider.php' );
define( 'EVSS_PRO_SLUG', 'swipe-slider-pro/swipe-slider.php' );

if( function_exists( 'evss_fs' ) ){
	register_activation_hook( __FILE__, function(){
		if( is_plugin_active( EVSS_FREE_SLUG ) ){
			deactivate_plugins( EVSS_FREE_SLUG );
		}
		if( is_plugin_active( EVSS_PRO_SLUG ) ){
			deactivate_plugins( EVSS_PRO_SLUG );
		}
	} );
}else{
	define( 'EVSS_VERSION', isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '0.16' );
	define( 'EVSS_DIR_URL', plugin_dir_url( __FILE__ ) );
	define( 'EVSS_DIR_PATH', plugin_dir_path( __FILE__ ) );
	define( 'EVSS_WP_PLUG', EVSS_FREE_SLUG === plugin_basename( __FILE__ ) );
	define( 'EVSS_FS_PLUG', EVSS_PRO_SLUG === plugin_basename( __FILE__ ) );

	if( EVSS_FS_PLUG ){
		require_once EVSS_DIR_PATH . 'includes/premium.php';

		if( function_exists( 'evss_fs' ) ){
			evss_fs()->set_basename( false, __FILE__ );
		}
	}

	function evssWusul(){
		if( EVSS_FS_PLUG ){
			return evss_fs()->can_use_premium_code();
		}else{
			return false;
		}
	}

	if( !class_exists( 'EVSSPlugin' ) ){
		class EVSSPlugin{
			public function __construct(){
				add_action( 'init', [ $this, 'onInit' ] );
				add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), [$this, 'pluginActionLinks'] );
				add_action( 'wp_ajax_evssWusulDekho', [$this, 'evssWusulDekho'] );
				add_action( 'wp_ajax_nopriv_evssWusulDekho', [$this, 'evssWusulDekho'] );
				add_action( 'admin_init', [$this, 'registerSettings'] );
				add_action( 'rest_api_init', [$this, 'registerSettings']);
				add_action( 'wp_ajax_renderBlocks', [$this, 'renderBlocks'] );
			}

			function onInit(){
				register_block_type( __DIR__ . '/build' );
			}

			function pluginActionLinks ( $links ) {
				$acLinks = [];

				if( EVSS_WP_PLUG ){
					$acLinks[] = "<a href='https://pluginenvision.com/plugins/swipe-slider/#plans' target='_blank' style='font-size: 1.1em; font-weight: bold; color: #5465ff; text-shadow: 1px 1px 1px #eee;'>Get Pro</a>";
				}

				return array_merge( $links, $acLinks );
			}

			function registerSettings(){
				register_setting( 'evssUtils', 'evssUtils', [
					'show_in_rest'		=> [
						'name'			=> 'evssUtils',
						'schema'		=> [ 'type' => 'string' ]
					],
					'type'				=> 'string',
					'default'			=> wp_json_encode( [ 'nonce' => wp_create_nonce( 'wp_ajax' ) ] ),
					'sanitize_callback'	=> 'sanitize_text_field'
				] );
			}

			function evssWusulDekho(){
				$nonce = sanitize_text_field( $_POST['_wpnonce'] ) ?? null;
				
				if( !wp_verify_nonce( $nonce, 'wp_ajax' )){
					wp_send_json_error( 'Invalid Request' );
				}

				wp_send_json_success( [ 'wusul' => evssWusul() ] );
			}

			function renderBlocks(){
				$nonce = sanitize_text_field( $_POST['_wpnonce'] ) ?? null;
				
				if( !wp_verify_nonce( $nonce, 'wp_ajax' )){
					wp_send_json_error( 'Invalid Request' );
				}

				if( !$_POST['_content'] ){
					wp_send_json_success( '' );
				}

				$blocks = parse_blocks( $_POST['_content'] );
				$slideContent = '';

				foreach ( $blocks as $block ) {
					$slideContent .= render_block( $block );
				}

				wp_send_json_success( $slideContent );
			}
		}
		new EVSSPlugin();
	}
}