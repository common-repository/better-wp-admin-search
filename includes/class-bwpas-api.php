<?php
/**
 * BWPAS_Api Class
 * handles plugin 
 * rest routes
 *
 * @package  Better_WP_Admin_Search
 */
namespace BWPAS; 

defined( 'ABSPATH' ) || exit;

/**
 * BWPAS_Api Class
 */
class BWPAS_Api { 
  
   /**
	 * Rest Init
	 *
	 * @return void
	 */
	public function bwpas_rest_init() {
    $search = new BWPAS_Search();
		register_rest_route(
			'bwpas/v1',
			'/bwpa-search',
			array(
				'methods'  => array( 'POST' ),
				'callback' => array( $search, 'bwpas_rest_get_paginated_results' ), 
				'permission_callback' => '__return_true',
			)			
		);
	}
}
