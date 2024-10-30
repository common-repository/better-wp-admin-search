<?php
/**
 * BWPAS_Main Class
 * Handles plugin functionality after activation
 *
 * @package  Better_WP_Admin_Search
 */
namespace BWPAS; 

defined( 'ABSPATH' ) || exit;

use BWPAS\BWPAS_Api;
use BWPAS\BWPAS_Search;

/**
 * BWPAS_Main Class
 */
class BWPAS_Main {

	public function __construct() {
		
		$api = new BWPAS_Api();
		add_action( 'rest_api_init', array( $api, 'bwpas_rest_init' ) );

		$this->bwpas_init();
	}

	/**
	 * Register class hooks.
	 *
	 * @return void
	 */
	public function bwpas_init(){
		add_action( 'admin_menu', array( $this, 'bwpas_menu_init' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'bwpas_admin_scripts' ) );
	}

	/**
	 * Menu Init.
	 *
	 * @return void
	 */
	public function bwpas_menu_init() {

		$search = new BWPAS_Search();
		
		add_menu_page(
			'Better WP-Admin Search',
			'Better WP-Admin Search',
			'manage_options',
			'better-wp-admin-search',
			array( $search, 'bwpas_callback' ),
			'dashicons-search',
			13
		);
	}
	/**
	 * Load admin scripts.
	 *
	 * @return void
	 */
	public function bwpas_admin_scripts(){
		
		wp_enqueue_script(
			'bwpas-admin-assets',
			BWPAS_URI . 'dist/admin/admin.bundle.js',
			'',
			BWPAS_VERSION,
			false
		);
		wp_localize_script(
			'bwpas-admin-assets',
			'bwpasApiSettings',
			array(
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'api_root' => get_rest_url(),
				'bwpas_version' => BWPAS_VERSION,
			)
		);
	}
}
