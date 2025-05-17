<?php
namespace EVSS\DesignLibrary;

if ( !defined( 'ABSPATH' ) ) { exit; }

if( !class_exists( 'ImageImport' ) ){
	class ImageImport {
		private $alreadyImportedIDs = [];

		public function maybeImportImages( $content ) {
			// Extract all links.
			preg_match_all( '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $content, $match );
	
			$all_links = array_unique( $match[0] );
	
			// Not have any link.
			if ( empty( $all_links ) ) {
				return $content;
			}
	
			$link_mapping = [];
			$image_links = [];
			$other_links = [];
	
			// Extract normal and image links.
			foreach ( $all_links as $key => $link ) {
				if ( $this->isValidImage( $link ) ) {
					// Avoid *-150x, *-300x and *-1024x images.
					if (
						false === strpos( $link, '-150x' ) &&
						false === strpos( $link, '-300x' ) &&
						false === strpos( $link, '-1024x' )
					) {
						$image_links[] = $link;
					}
				} else {
					// Collect other links.
					$other_links[] = $link;
				}
			}
	
			// Step 1: Download images.
			if ( !empty( $image_links ) ) {
				foreach ( $image_links as $key => $image_url ) {
					// Download remote image.
					$image = [
						'url' => $image_url,
						'id' => 0
					];
					$downloaded_image = $this->import( $image );
	
					// Old and New image mapping links.
					$link_mapping[ $image_url ] = $downloaded_image['url'];
				}
			}
	
			// Step 3: Replace mapping links.
			foreach ( $link_mapping as $old_url => $new_url ) {
				$old_url = (string) $old_url;
				$content = str_replace( $old_url, $new_url, $content );
	
				// Replace the slashed URLs if any exist.
				$old_url = str_replace( '/', '/\\', $old_url );
				$new_url = str_replace( '/', '/\\', $new_url );
				$content = str_replace( $old_url, $new_url, $content );
			}
	
			return $content;
		}

		private function getSavedImage( $attachment ) {
			global $wpdb;
	
			// 1. Is already imported in Batch Import Process?
			$post_id = $wpdb->get_var( $wpdb->prepare( 'SELECT `post_id` FROM `' . $wpdb->postmeta . '` WHERE `meta_key` = \'_pev_templates_img\' AND `meta_value` = %s;', $this->getHashImage( $attachment['url'] ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	
			// 2. Is image already imported though XML?
			if ( empty( $post_id ) ) {
	
				// Get file name without extension.
				// To check it exist in attachment.
				$filename = basename( $attachment['url'] );
	
				$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s", '%/' . $filename . '%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			}
	
			if ( $post_id ) {
				$new_attachment = [
					'id' => $post_id,
					'url' => wp_get_attachment_url( $post_id ),
				];
				$this->alreadyImportedIDs[] = $post_id;
	
				return [
					'status' => true,
					'attachment' => $new_attachment
				];
			}
	
			return [
				'status' => false,
				'attachment' => $attachment
			];
		}

		public function import( $attachment ) {
			$saved_image = $this->getSavedImage( $attachment );
	
			if ( $saved_image['status'] ) {
				return $saved_image['attachment'];
			}
	
			// Extract the file name and extension from the URL.
			$filename = basename( $attachment['url'] );
	
			if ( isset( $attachment['engine'] ) && 'unsplash' === $attachment['engine'] ) {
				$filename = 'unsplash-photo-' . $attachment['id'] . '.jpg';
			}
	
			$file_content = wp_remote_retrieve_body(
				wp_safe_remote_get( $attachment['url'], [ 'timeout' => '60' ] )
			);
	
			// Empty file content?
			if ( empty( $file_content ) ) {
				return $attachment;
			}
	
			$upload = wp_upload_bits( $filename, null, $file_content );
	
			$post = [
				'post_title' => $filename,
				'guid' => $upload['url'],
			];
	
			$info = wp_check_filetype( $upload['file'] );
			if ( $info ) {
				$post['post_mime_type'] = $info['type'];
			} else {
				// For now just return the origin attachment.
				return $attachment;
			}
	
			if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
				include ABSPATH . 'wp-admin/includes/image.php';
			}
	
			$post_id = wp_insert_attachment( $post, $upload['file'] );
			wp_update_attachment_metadata(
				$post_id,
				wp_generate_attachment_metadata( $post_id, $upload['file'] )
			);
			update_post_meta( $post_id, '_pev_templates_img', $this->getHashImage( $attachment['url'] ) );
	
			$new_attachment = [
				'id' => $post_id,
				'url' => $upload['url'],
			];
	
			$this->alreadyImportedIDs[] = $post_id;
	
			return $new_attachment;
		}

		public function getHashImage( $attachment_url ) {
			return sha1( $attachment_url );
		}

		public function isValidImage( $link = '' ) {
			return preg_match( '/^((https?:\/\/)|(www\.))([a-z0-9-].?)+(:[0-9]+)?\/[\w\-]+\.(jpg|png|gif|jpeg)\/?$/i', $link );
		}
	}
}