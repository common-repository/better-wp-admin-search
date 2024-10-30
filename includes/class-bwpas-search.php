<?php
/**
 * BWPAS_Search  Class
 * Handles the search
 *
 * @package  Better_WP_Admin_Search
 */
namespace BWPAS; 

defined( 'ABSPATH' ) || exit;

use BWPAS\BWPAS_Result; 
/**
 * BWPAS_Search Class
 */
class BWPAS_Search {
  
  /**
	 * Post types list
   * to perform search in.
	 * 
   * @since 0.0.1
   * 
	 * @access   private
	 * @var      arr    key(post type id) 
   * value(array of post-type name && _builtin).
	 */ 
  private $post_types_list = array();
  /**
	 * List of the 
   * WP built-in(native) post types.
	 * 
   * @since 0.0.1
   * 
	 * @access   private
	 * @var      arr    key(post type id) 
   * value(array of post-type name && _builtin).
	 */ 
  private $wp_native_post_types = array();
  /**
	 * List of the 
   * custom(plugin) post types.
	 * 
   * @since 0.0.1
   * 
	 * @access   private
	 * @var      arr    key(post type id) 
   * value(array of post-type name && _builtin).
	 */ 
  private $plugin_post_types = array();
  /**
	 * Results to display
   * per page
	 *
	 * @since 0.0.1
   * 
	 * @var int
	 */
	protected $results_limit = 20;  
  /**
   * Search error message
   * 
   * @since 0.0.1
   * 
   * @var arr
   */
  protected $search_errors = array();

	public function __construct() { 
    $this->post_types_list    = $this->bwpas_get_post_types();   
    $pagination_config = include BWPAS_DIR . 'includes/config/pagination.php';
		$this->results_limit = $pagination_config['limit'];
  } 
  /**
   * Loads main page
   * content in WP admin.
   *
   * @since 0.0.1
   * 
   * @return void
   */
	public function bwpas_callback(){  

		if ( ! current_user_can( 'edit_posts' ) )
			die( "Access Denied." );
    // default values.
    $defaulut_post_types_search = array_keys( $this->post_types_list );
    $input = array(
      "is_rest" => false,
      "casesensitive" => "",
      "post_types_search" => $defaulut_post_types_search,
      "string_find" => "",
    ); 
    if ( isset( $_POST ) && isset( $_POST['search'] ) ) :
      add_action( 'admin_notices', array( $this, 'admin_warning' ) );
      if ( isset( $_POST['_bwpas_search_nonce'] ) &&
        wp_verify_nonce(
          sanitize_text_field( wp_unslash( $_POST['_bwpas_search_nonce'] ) ),
          'bwpas_search_nonce'
        ) ) :
        $input["post_types_search"] = [];// reset default value. 
        if ( isset( $_POST['search']['post_type'] ) ) : // narrow the search in these post types, min 1|all.
          $post_types_search_sanitized = array_map( 'sanitize_text_field', wp_unslash( $_POST['search']['post_type'] ) );
          foreach ( $post_types_search_sanitized as $key => $value ) :
            if ( in_array( $key, $defaulut_post_types_search ) ) :
              array_push( $input["post_types_search"], $key );
            endif;
          endforeach;
        endif;
        if ( isset( $_POST['search']['string_find'] ) ) :
          $input["string_find"] = trim( sanitize_text_field( wp_unslash( $_POST['search']['string_find'] ) ) );
        endif;
        if ( isset( $_POST['search']['casesensitive'] ) ) : 
          $input["casesensitive"] = 'BINARY';
        endif; 
        // validate required fields.
        if ( $this->bwpas_is_valid_input( $input ) ) :  

          $search_result = $this->bwpas_search( $input ); // arr
        endif;
      endif; 
    endif;  
    $this->bwpas_filter_post_types();
    include BWPAS_DIR . '/includes/templates/search-form.php'; // landing content. 
    if ( isset( $search_result ) ) : 
      $result_list = new BWPAS_Result();
      $result_list->bwpas_result_list_init( $search_result, $input["string_find"], $input["post_types_search"], $this->post_types_list ); 
    endif;
	}
  /**
   * Validates the inout
   *
   * @since 0.0.1
   * 
   * @param str $search_input
   * @return boolean
   */
  public function bwpas_is_valid_input( $search_input ){
    if ( empty( $search_input["string_find"] ) ) : 
      $this->search_errors["string_find"] = __( 'Please, enter a search string!', 'better-wp-admin-search' );
      return false;
    endif;
    if ( empty( $search_input["post_types_search"] ) ) :
      $this->search_errors["post_types_search"] = __( 'Please, select at least one post type!', 'better-wp-admin-search' ); 
      return false;
    endif;
    return true;
  }
 
  /**
   * Search entry point.
   * @since 0.0.1
   * 
   * @param arr $input 
   * @param int $offset
   * @return arr
   */
  public function bwpas_search( $input, $offset = 0 ){ 
    $limit = $this->results_limit;
    $raw_result = $this->bwpas_query_the_posts( $input, $offset, $limit ); // false | array with found posts.
    if ( $input["is_rest"] ) : 
      return $raw_result;
    else :
      $results_by_post_type_count = $this->bwpas_query_count_posts_by_post_type( $input );
      $raw_result['posts_by_type_count'] = $results_by_post_type_count;
      return $raw_result; 
    endif;
  }
  /**
   * Queries the database
   * for posts by post_type
   * casesensitive/caseinsitive
   * search string.
   *
   * @since 0.0.1
   * 
   * @param arr $input
   * @param int $offset
   * @param int $limit
   * @return arr|bool
   */
  public function bwpas_query_the_posts( $input, $offset, $limit ){  

    global $wpdb;

    $casesensitive  = $input["casesensitive"];
    $string_find    = $input["string_find"];
    $post_types_search = $input["post_types_search"];

    $post_types_search_placeholders = implode( ',', array_fill( 0, count( $post_types_search ), '%s' ) );
     
    $query = " SELECT SQL_CALC_FOUND_ROWS ";
    $query .= "p.ID as post_id, ";
    $query .= "p.post_title, ";
    $query .= "p.post_type, ";
    $query .= "p.post_status, ";
    $query .= "p.post_date_gmt, ";
    $query .= "p.post_author, ";
    $query .= "p.post_mime_type, "; 
    $query .= "u.ID as user_id, ";
    $query .= "(SELECT pm.meta_value FROM " . $wpdb->prefix . "postmeta AS pm ";
    $query .= "WHERE pm.meta_key=\"_wp_attachment_metadata\" AND pm.post_id=p.ID) as attachement_meta, ";
    $query .= "(SELECT pm.meta_value FROM " . $wpdb->prefix . "postmeta AS pm ";
    $query .= "WHERE pm.meta_key=\"_wp_attached_file\" AND pm.post_id=p.ID) as attachement_file, ";
    $query .= "u.display_name AS post_author_display_name ";
    $query .= "FROM ";
    $query .= $wpdb->prefix . "posts AS p "; // set table-short-name.
    $query .= "LEFT JOIN ";  
    $query .= $wpdb->prefix . "postmeta AS pm ON ("; // set table-short-name.
    $query .= "p.ID=";
    $query .= "pm.post_id) "; 
    $query .= "LEFT JOIN ";
    $query .= $wpdb->prefix . "users AS u ON ("; // set table-short-name.
    $query .= "p.post_author=";
    $query .= "u.ID) "; 
    $query .= "WHERE  ( ("; 
    $query .= "p.post_title like %1s %s) OR (";  
    $query .= "p.post_name like %1s %s) OR (";
    $query .= "p.post_content like %1s %s) OR (";
    $query .= "p.post_excerpt like %1s %s) OR (";
    $query .= "pm.meta_value like %1s %s) ) AND "; 
    $query .= "p.post_type IN ( $post_types_search_placeholders ) "; // $post_types_search_placeholders is safe.
    $query .= "GROUP BY p.ID, u.ID ";
    $query .= "ORDER BY p.post_type ASC ";
    $query .= "LIMIT %d ";
    $query .= "OFFSET %d";  

    $args1 = array(
      $casesensitive,
      "%$string_find%", 
      $casesensitive,
      "%$string_find%", 
      $casesensitive,
      "%$string_find%", 
      $casesensitive,
      "%$string_find%", 
      $casesensitive,
      "%$string_find%", 
    );
    $args2 = array_merge( $args1, $post_types_search ); // add post types array to args array.
    $args_final = array_merge( $args2, array( $limit, $offset ) );
    
    $result = $wpdb->get_results( $wpdb->prepare( $query, $args_final ) ); // phpcs:ignore WordPress.DB 
    if ( isset( $result ) ) :
      $total_posts = $wpdb->get_var( "SELECT FOUND_ROWS();" ); // phpcs:ignore WordPress.DB
      $total_pages = ceil( $total_posts / $limit );
      return [
              'result_list' => $result, 
              'total_posts' => $total_posts, 
              'total_pages' => $total_pages,
            ];
    endif;

    return false;
  }
  /**
   * Queries the database
   * for posts by post_type
   * casesensitive/caseinsitive
   * search string.
   *
   * @since 0.0.1
   * 
   * @param str $casesensitive
   * @param str $string_find
   * @param arr $post_types_search
   * @return array|object|null
   */
  public function bwpas_query_count_posts_by_post_type( $input ){  

    global $wpdb;

    $casesensitive = $input["casesensitive"];
    $string_find = $input["string_find"];
    $post_types_search = $input["post_types_search"];

    $post_types_search_placeholders = implode( ',', array_fill( 0, count( $post_types_search ), '%s' ) );

    // searches by string in the post_types found.
    $query = " SELECT SQL_CALC_FOUND_ROWS ";
    $query .= "COUNT(DISTINCT p.ID) as count_posts, "; 
    $query .= "p.post_type ";
    $query .= "FROM ";
    $query .= $wpdb->prefix . "posts AS p "; // set table-short-name.
    $query .= "LEFT JOIN ";  
    $query .= $wpdb->prefix . "postmeta AS pm ON ("; // set table-short-name.
    $query .= "p.ID=";
    $query .= "pm.post_id) ";
    $query .= "WHERE  ( ("; 
    $query .= "p.post_title like %1s %s) OR (";
    $query .= "p.post_name like %1s %s) OR (";
    $query .= "p.post_content like %1s %s) OR (";
    $query .= "p.post_excerpt like %1s %s) OR (";
    $query .= "pm.meta_value like %1s %s) ) AND ";
    $query .= "p.post_type IN ( $post_types_search_placeholders ) "; // $post_types_search_placeholders is safe.
    $query .= "GROUP BY p.post_type ";
    $query .= "ORDER BY p.post_type ASC";

    $args1 = array(
      $casesensitive,
      "%$string_find%",
      $casesensitive,
      "%$string_find%",
      $casesensitive,
      "%$string_find%",
      $casesensitive,
      "%$string_find%",
      $casesensitive,
      "%$string_find%", 
    );
    $args_final = array_merge( $args1, $post_types_search ); // add post types array to args array. 

    $results = $wpdb->get_results( $wpdb->prepare( $query, $args_final ) ); // phpcs:ignore WordPress.DB 
    // add Post type name to the result
    if ( count( $results ) > 0 ) :
      foreach ( $results as $result ) {
        $result->name = $this->post_types_list[ $result->post_type ]['name'];
      }
    endif;
    
    return $results;
  }
  /**
   * Gets all the post types
   * to search in.
   * 
   * @since 0.0.1
   * 
   * @return arr key(post type id) - value(post type name)
   */
  public function bwpas_get_post_types() { 
    
    $postypes_data = array();   
    $post_types_remove = array(
                          "revision", 
                          "user_request", 
                          "oembed_cache", 
                          "wp_template", 
                          "wp_template_part", 
                          "wp_navigation",
                          "wp_block",
                          "customize_changeset",
                        ); 
    $post_types = get_post_types( [], 'objects' ); 
    foreach ( $post_types as $type_key => $type_data ) :
      if ( ! in_array( $type_key, $post_types_remove ) ) :
        $postypes_data[ $type_key ]   = array( 
                                              'name' => $type_data->labels->name,
                                              '_builtin' => $type_data->_builtin, 
                                            );
      endif;
    endforeach;
    return $postypes_data; 
  } 
  /**
   * Separates wp core from 
   * plugin registered post types
   *
   * @return void
   */
  public function bwpas_filter_post_types(){
    if ( count( $this->post_types_list ) > 0 ) :
      foreach ($this->post_types_list as $key => $post_type ) {
        if ( $post_type['_builtin'] ) :
          $this->wp_native_post_types[ $key ] = $post_type;
        else :
          $this->plugin_post_types[ $key ] = $post_type;
        endif;
      }
    endif;
  } 
  /**
   * Rest API endpoint
   * callback method
   * that handles the paginated results.
   * 
   * @since 0.0.1
   * 
   * @param \WP_REST_Request $request
   * @return void
   */
  public function bwpas_rest_get_paginated_results( \WP_REST_Request $request ) {  
		$body = json_decode( $request->get_body() ); 
     // default values.
     $defaulut_post_types_search = array_keys( $this->post_types_list );
     $input = array(
      "is_rest" => true,
      "casesensitive" => "",
      "post_types_search" => $defaulut_post_types_search,
      "string_find" => "",
    );  
    if ( "1" === $body->casesensitive ) :
      $input["casesensitive"] = 'BINARY';
    endif;
    // get search string
    $input["string_find"] = sanitize_text_field( trim( $body->search_str ) );
    // get post types
    $input["post_types_search"] = [];// reset default value. 
    if ( isset( $body->post_types ) ) : // narrow the search in these post types, min 1|all.
      foreach ( $body->post_types as $value ) :
        if ( in_array( $value, $defaulut_post_types_search ) ) :
          array_push( $input["post_types_search"], $value );
        endif;
      endforeach;
    endif; 
    $page = sanitize_text_field( $body->page );
    $offset = ( $page - 1 ) * $this->results_limit; 
    // get results limit - put it as costants. 
    $paginated_search_result = $this->bwpas_search( $input, $offset );  
    // prepare search result for output.
    $result = new BWPAS_Result();
    $escaped_search_result = $result->bwpas_prepare_search_result_items( $paginated_search_result, $this->post_types_list ); // prepare for output.
    return wp_json_encode( $paginated_search_result );
  }
}
