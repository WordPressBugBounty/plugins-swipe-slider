<?php
namespace EVSS\DesignLibrary;

if ( !defined( 'ABSPATH' ) ) { exit; }

require_once EVSS_DIR_PATH . 'includes/DesignLibrary/ImageImport.php';

if( !class_exists('DesignLibrary') ){
	class DesignLibrary{
		// private $url = 'http://localhost/dev';
		private $url = 'https://demo.pluginenvision.com';

		public function __construct(){
			add_action( 'wp_ajax_evssDesignLibraryTaxonomies', [$this, 'evssDesignLibraryTaxonomies'] );
			add_action( 'wp_ajax_evssDesignLibraryTemplates', [$this, 'evssDesignLibraryTemplates'] );
			add_action( 'wp_ajax_evssDesignLibraryTemplateImport', [$this, 'evssDesignLibraryTemplateImport'] );
		}

		function evssDesignLibraryTaxonomies(){
			$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) ?? null;

			if( !wp_verify_nonce( $nonce, 'evss_ajax' )){
				wp_send_json_error( 'Invalid Request' );
			}

			$response = wp_remote_get( $this->url . "/wp-json/wp/v1/templates/blocks/taxonomies/templates-plugin,block-name,block-category?plugin=swipe-slider" );
			$data = json_decode( wp_remote_retrieve_body( $response ) );
			wp_send_json_success( $data );
		}

		function evssDesignLibraryTemplates(){
			$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? null ) );

			if( !wp_verify_nonce( $nonce, 'evss_ajax' )){
				wp_send_json_error( 'Invalid Request' );
			}

			$category = sanitize_text_field( wp_unslash( $_POST['category'] ) ) ?? '';
			$pageNumber = absint( wp_unslash( $_POST['pageNumber'] ?? 1 ) );
			$start = $pageNumber - 1;
			$search = sanitize_text_field( wp_unslash( $_POST['search'] ?? '' ) );

			try {
				$response = wp_remote_get( $this->url . "/wp-json/wp/v1/templates/blocks/?plugin=swipe-slider&block=swipe-slider&category=$category&start=$start&end=$pageNumber&search=$search" );

				$data = json_decode( wp_remote_retrieve_body( $response ) );
				wp_send_json_success( $data );
			} catch (\Throwable $th) {
				wp_send_json_error( $th->getMessage() );
			}
		}

		function evssDesignLibraryTemplateImport(){
			$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? null ) );

			if( !wp_verify_nonce( $nonce, 'evss_ajax' )){
				wp_send_json_error( 'Invalid Request' );
			}

			try {
				$data = ( new \EVSS\DesignLibrary\ImageImport())->maybeImportImages( wp_unslash( $_POST['original_content'] ?? '' ) );
				wp_send_json_success( $data );
			} catch (\Throwable $th) {
				wp_send_json_error( $th->getMessage() );
			}
		}
	}
	new DesignLibrary();
}
