<?php
/**
 * BWPAS_Result Class
 * Handles the results of the search
 *
 * @package  Better_WP_Admin_Search
 */
namespace BWPAS; 

defined( 'ABSPATH' ) || exit;

use BWPAS\Utils\BWPAS_Helper;
/**
 * BWPAS_Result Class
 */
class BWPAS_Result { 

  /**
	 * Data to display
   * paginated search results
	 *
	 * @since 0.0.1
	 * @var arr
	 */
	private $result_pagination_args = array();

  /**
	 * Cached pagination output.
	 *
	 * @since 0.0.1
	 * @var str
	 */
	private $pagination = '';

	/**
	 * Results to display
   * per page.
	 *
	 * @since 0.0.1
	 * @var int
	 */
	protected $per_page = 20;

	/**
	 * Results' Filter
	 * output.
	 *
	 * @var str
	 */
	protected $filter;
	/**
	 * Data to format 
	 * the results filter
	 *
	 * @var arr
	 */
	protected $filter_args;

	public function __construct(){
		$pagination_config = include BWPAS_DIR . 'includes/config/pagination.php';
		$this->per_page = $pagination_config['limit'];
	}

  /**
   * Display the list of results.
   * 
	 * @since 0.0.1
	 * 
   * @param arr $search_result
   * @param str $search_string
	 * @param arr $post_types_search shrinked post types list
	 * @param arr $post_types_list full post types data
	 * 
   * @return void
   */
	public function bwpas_result_list_init( $search_result, $search_string, $post_types_search, $post_types_list ){  
    $results_count = $search_result['total_posts'];
		$results_by_post_type = $search_result['posts_by_type_count'];
    $result_list   = $this->bwpas_prepare_search_result_items( $search_result, $post_types_list ); // prepare for output.

		// pagination init.
    $per_page = $this->per_page;
    $result_pagination_args = array(
      'total_items' => $results_count,
      'total_pages' => ceil( $results_count / $per_page ),  
      'per_page'    => $per_page, 
      'search_string' => $search_string,
      'post_types'  => $post_types_search,
    );
    $this->bwpas_set_pagination_args( $result_pagination_args ); 
		// filter init.
		$this->bwpas_set_filter_args( $results_by_post_type, $results_count );
		// result display init.
    $this->bwpas_display_results_list( $results_count, $search_string, $result_list['result_list'] );
  } 
	 /**
   * Prepare search results data output
   * escaping and inserting 
   * html data wrapping elements.
   * 
   * @since 0.0.1
   * 
   * @param arr $search_results
	 * @param arr $post_types_list
   * @return arr
   */
  public function bwpas_prepare_search_result_items( $search_results, $post_types_list ){ 
		if ( ! empty( $search_results ) ) :
			foreach ( $search_results['result_list'] as $post ) :
				$is_publicly_viewable = is_post_publicly_viewable( $post->post_id );
				$link = get_permalink( $post->post_id ); 

				$post->title_output = $this->bwpas_prepare_search_result_title_output( $post, $is_publicly_viewable, $link );  
				$post->actions_output = $this->bwpas_prepare_search_result_action_output( $post, $is_publicly_viewable, $link );
				$post->post_name_output = $this->bwpas_prepare_search_result_post_name_output( $post, $post_types_list );
				$post->post_author_display_name_output = $this->bwpas_prepare_search_result_author_name( $post );
				$post->post_categories_output = $this->bwpas_prepare_search_result_categories_output( $post );
				$post->post_status_output = $this->bwpas_prepare_search_result_status_output( $post );
				$post->post_date_created_output = $this->bwpas_prepare_search_result_date_created_output( $post );
				$post->post_attachment_details = $this->bwpas_prepare_post_attachment_details( $post );
			endforeach;
		endif;
    return $search_results;
  }
	 /**
   * Adds post type name
   * to posts data array.
   *
   * @since 0.0.1
   * 
   * @param obj $post
	 * @param arr $post_types_list
   * @return obj
   */
  public function bwpas_add_post_type_name( $post, $post_types_list ){   
		$post_name = $post_types_list[ $post->post_type ]['name'];  
		return $post_name;
  }
  /**
	 * Sets all the necessary pagination arguments.
	 * @since 0.0.1
	 * 
	 * @param array|string $args Array or string of arguments with information about the pagination.
	 */
	public function bwpas_set_pagination_args( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'total_items'   => 0,
				'total_pages'   => 0,
				'per_page'      => 0,
        'search_string' => '',
        'post_types'    => '',
			)
		);

		if ( ! $args['total_pages'] && $args['per_page'] > 0 ) {
			$args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );
		}

		$this->result_pagination_args = $args;
	}
	/**
	 * Sets all the necessary filter arguments.
	 * 
	 * @since 0.0.1
	 * 
	 * @param array $results_by_post_type 
	 * @param int 	$results_count 
	 */
	public function bwpas_set_filter_args( $results_by_post_type, $results_count ) {
		$this->filter_args['by_type'] = $results_by_post_type;
		$this->filter_args['all']     = $results_count;
	}

  /**
	 * Displays the pagination.
	 * 
	 * @since 0.0.1
	 * 
	 * @param string $which
	 */
	protected function bwpas_pagination( $which ) {
		if ( empty( $this->result_pagination_args ) ) :
			return;
		endif;

		$total_items  = $this->result_pagination_args['total_items'];
		$total_pages  = $this->result_pagination_args['total_pages']; 
    $search_str 	= $this->result_pagination_args['search_string']; 
		$current = 1; // on page load.

		if ( 2 > $total_pages ) :
			return;
		endif;
		// all items found num display.
		$output = '<span class="displaying-num">';
		$output .= sprintf(
									/* translators: %s: Number of items. */
									_n( '<span class="num">%s</span> item', '<span class="num">%s</span> items', $total_items, 'better-wp-admin-search' ),
									number_format_i18n( $total_items )
								);
		$output .= '</span>';

		$page_links = array();
		// html displayed before total num of pages.
		$total_pages_before = '<span class="paging-input">';
		// html to display after total number of pages.
		$total_pages_after  = '</span></span>';

		$disable_first = false;
		$disable_last  = false;
		$disable_prev  = false;
		$disable_next  = false;

    
		if ( 1 == $current ) {
			$disable_first = true;
			$disable_prev  = true;
		}
		
    // 1st page.
		if ( $disable_first ) :
			$page_links[] = sprintf(
				"<a class='first-page button disabled-page-nav pagination-handler' href='#' data-page='1'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
				'</a>',
				/* translators: Hidden accessibility text. */
				__( 'First page', 'better-wp-admin-search' ),
				'&laquo;'
			);
		// }
		endif;
    // prev page.
		if ( $disable_prev ) :
			$page_links[] = sprintf(
				"<a class='prev-page button disabled-page-nav pagination-handler' href='#' data-page='" . $current . "' >" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
				'</a>',
				/* translators: Hidden accessibility text. */
				__( 'Previous page', 'better-wp-admin-search' ),
				'&lsaquo;'
			);
		endif;
    // display current page num.
		if ( 'bottom' === $which ) :
			$html_current_page  = "<span id='current-page-text'>$current</span>";
			$total_pages_before = sprintf(
				'<span class="screen-reader-text">%s</span>' .
				'<span id="table-paging" class="paging-input">' .
				'<span class="tablenav-paging-text">',
				/* translators: Hidden accessibility text. */
				__( 'Current Page', 'better-wp-admin-search' )
			);
		else :
			// top navigation.
			$html_current_page = sprintf(
				'<label for="current-page-selector" class="screen-reader-text">%s</label>' .
				"<input class='current-page input-page' id='current-page-input' type='text'
					name='paged' value='%s' size='%d' aria-describedby='table-paging' data-total='" . $total_pages . "'/>" .
				"<span class='tablenav-paging-text'></span>",
				/* translators: Hidden accessibility text. */
				__( 'Current Page', 'better-wp-admin-search' ),
				$current,
				strlen( $total_pages )
			);
		endif;

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );

		$page_links[] = $total_pages_before . sprintf(
			/* translators: 1: Current page, 2: Total pages. */
			__( '<span class="current-page">%1$s</span> of <span class="of-total-pages">%2$s</span>', 'better-wp-admin-search' ),
			$html_current_page,
			$html_total_pages
		) . $total_pages_after;
		// next page.
		if ( ! $disable_next ) :
      $pagenum = min( $total_pages, 2 );  
			$page_links[] = sprintf(
				"<a class='next-page button pagination-handler' href='#' data-page='" . $pagenum . "'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
				'</a>',
				/* translators: Hidden accessibility text. */
				__( 'Next page', 'better-wp-admin-search' ),
				'&rsaquo;'
			);
		endif;

    $pagenum = $total_pages;

		// last page.
		if ( ! $disable_last ) :
			$page_links[] = sprintf(
				"<a class='last-page button pagination-handler' href='#' data-page='" . $pagenum . "'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
				'</a>',
				/* translators: Hidden accessibility text. */
				__( 'Last page', 'better-wp-admin-search' ),
				'&raquo;'
			);
		endif;

		$output .= "\n<span class='pagination-links'>" . implode( "\n", $page_links ) . '</span>';

		$this->pagination = "<div class='tablenav-pages'>$output</div>"; 
		$allowed_html = array(  
			'a'  => array(
				'href' => array(),
				'target' 	=> array(),
				'class' 	=> array(),
				'id' 			=> array(),
				'data-page' => array(),
			),
			'div' => array(
				'class' => array(),
				'id' 		=> array(),
			),
			'span' => array(
				'class' => array(),
				'id' 		=> array(),
			),
			'input' => array(
				'class' => array(),
				'id' 		=> array(),
				'type' 	=> array(),
				'name' 	=> array(), 
				'value' => array(),
				'size' 	=> array(),
				'aria-describedby' 	=> array(),
				'data-total' 				=> array(),
			),
			'label' => array(
				'for' 	=> array(),
				'class' => array(),
			),
		);
	 
		echo wp_kses( $this->pagination, $allowed_html ); 
	}
  /**
	 * Displays the results list.
	 * 
	 * @since 0.0.1
	 * 
	 * @param int $result_count
	 * @param str $search_string
	 * @param arr $result_list
	 */
	public function bwpas_display_results_list( $results_count, $search_string, $result_list ) {
		$post_types_search = wp_json_encode( $this->result_pagination_args['post_types'] ); 
    ?> 
    <div class="bwpas-wrap search-results-wrapper" data-post-types=<?php echo esc_attr( $post_types_search ) ?>>

    <?php include BWPAS_DIR . '/includes/templates/result/result-info-block.php';  // uses $result_count and $search_string.
		 
			$this->bwpas_filter();
      $this->bwpas_pagination( 'top' );

      $this->bwpas_display_result_table( $result_list );   

      $this->bwpas_pagination( 'bottom' );
    ?>
    </div>
    <?php
	}
	/**
	 * Displays results list
	 * table
	 * 
	 * @since 0.0.1
	 * 
	 * @param arr $result_list
	 * @return void
	 */
	public function bwpas_display_result_table( $result_list ){  
		if ( count( $result_list ) > 0 ) : ?>
 
			<table class="form-table wp-list-table widefat fixed striped pages results-table">

				<thead>
					<tr>
						<th><?php esc_html_e( 'Title', 'better-wp-admin-search' ); ?></th>
						<th><?php esc_html_e( 'Type', 'better-wp-admin-search' ); ?></th>
						<th><?php esc_html_e( 'Author', 'better-wp-admin-search' ); ?></th> 
						<th><?php esc_html_e( 'Category', 'better-wp-admin-search' ); ?></th>  
						<th><?php esc_html_e( 'Date (UTC)', 'better-wp-admin-search' ); ?></th> 
					</tr>
				</thead>
    
				<tbody>
				<?php 
					foreach ( $result_list as $post_data ) :   
						$this->bwpas_result_row_init( $post_data );
					endforeach; 
				?>
				</tbody>
			</table> 
		<?php endif;
	}
	/**
	 * Displays results filter
	 * by post type 
	 *
	 * @return void
	 */
	public function bwpas_filter(){

		$count_filters = count( $this->filter_args['by_type'] ); 
		// show the filter block if 2 or more filters(post types).
		if ( 1 < $count_filters ) :
			// add only those post types that have search results
			$post_types_serach_filtered = array();
			foreach ( $this->filter_args['by_type'] as $filter ) :
				$post_types_serach_filtered[] = $filter->post_type;
			endforeach;
			$post_types_search = wp_json_encode( $post_types_serach_filtered );
		?>
		<ul class="subsubsub bwpas-filter-results">
			<li data-post-type="all">
				<a href="#" class="current filter-handler" data-post-types=<?php echo esc_attr( $post_types_search ); ?>>
				All <span class="count">(<?php echo esc_html( $this->filter_args['all'] ); ?>)</span></a>|
			</li>
			<?php for ( $i = 0; $i < $count_filters; $i++ ) : ?>
			<?php 
				$current_filter = $this->filter_args['by_type'][ $i ]; 
				// we still need to shape the single search-post-type as array for to unify the rest params.
				$single_search_post_type   = []; 
				$single_search_post_type[] = $current_filter->post_type;
				$single_search_post_type_str = wp_json_encode( $single_search_post_type );
			?>
			<li><a href="#" class="filter-handler" data-post-types=<?php echo esc_attr( $single_search_post_type_str ); ?>>
				<?php echo esc_attr( $current_filter->name ); ?> <span class="count">(<?php echo esc_attr( $current_filter->count_posts ); ?>)</span></a> 
				<?php if ( $count_filters - 1 > $i ) : ?>
					&#124;
				<?php endif; ?>
			</li>
			<?php endfor; ?>
		</ul>
		<?php
		endif;
	}
	/**
   * Single result display
   * entry point.
   * Routes the display 
   * by post type.
   *
	 * @since 0.0.1
	 * 
   * @param obj $result_data
   * @return void
   */ 
  public function bwpas_result_row_init( $result_data ){  
    switch ( $result_data->post_type ) {
      case 'attachment':
        $this->bwpas_result_display_media( $result_data );
				break;
      default:
        $this->bwpas_result_display_default( $result_data );
				break;
    }
  }
	/**
   * Serves the default
   * single result view.
   *
	 * @since 0.0.1
	 * 
   * @param obj $post
   * @return void
   */
  public function bwpas_result_display_default( $post ){ 
		?>
		<tr>
			<td> 
				<div>
				<?php 
					$allowed_html = array(  
						'a'     => array(
								'href' => array(),
								'target' => array(),
						),
						'span'  => array(),
					);
				 
					echo wp_kses( $post->title_output, $allowed_html ); 
				?>
				</div>
				<div class="actions">
					<?php 
						$allowed_html = array(  
							'a'     => array(
									'href' => array(),
									'target' => array(),
							),
							'span'  => array(),
						);
					 
						echo wp_kses( $post->actions_output, $allowed_html );
					?>
				</div>  
			</td>
			<td><?php echo esc_html( $post->post_name_output ); ?></td>
			
			<td><?php echo esc_html( $post->post_author_display_name_output ); ?></td> 
			<td>
				<?php 
					$allowed_html = array(  
						'br'     => array(),
					);
				?>
				<?php echo wp_kses( $post->post_categories_output, $allowed_html ); ?>
			</td>
			<td>
				<div> 
					<?php echo esc_html( $post->post_status_output ); ?>
				</div>
				<div class="post-date"> 
					<?php echo esc_html( $post->post_date_created_output ); ?>
				</div>
			</td> 
		</tr> 
		<?php 
  }
  /**
	 * Serves the attachements(media)
   * single result view.
   * 
	 * @since 0.0.1
	 * 
   * @param obj $post
   * @return void
   */
  public function bwpas_result_display_media( $post ){   
    ?>
		<tr>
			<td> 
				<div class="flex">
					<div class="col">
						<div class="attachment-title">
							<?php 
								$allowed_html = array(  
									'a'     => array(
											'href' => array(),
											'target' => array(),
									),
									'span'  => array(),
								);
							
								echo wp_kses( $post->title_output, $allowed_html ); 
							?>
						</div>
						<div class="actions">
						<?php 
							$allowed_html = array( 
								// links.
								'a'     => array(
										'href' => array(),
										'target' => array(),
								),
								'span'  => array(),
							);
						
							echo wp_kses( $post->actions_output, $allowed_html );
						?>
						</div>  
					</div>
					<div class="attachment-details col">
						<?php 
							$allowed_html = array(  
								'p'     => array(),
								'span'  => array(),
							);
						?>
						<?php echo wp_kses( $post->post_attachment_details, $allowed_html ); ?>
					</div>
				</div>
			</td>
			<td><?php echo esc_html( $post->post_name_output ); ?></td>      
			<td><?php echo esc_html( $post->post_author_display_name_output ); ?></td> 
			<td>
				<?php 
						$allowed_html = array(  
							'br'     => array(),
						);
				?>
				<?php echo wp_kses( $post->post_categories_output, $allowed_html ); ?>
			</td>
			<td>
				<div> 
					<?php echo esc_html( $post->post_status_output ); ?>
				</div>
				<div class="post-date"> 
					<?php echo esc_html( $post->post_date_created_output ); ?>
				</div>
			</td> 
		</tr> 
		<?php
  }
	/**  
	 * Prepares post-title and 
	 * wraps it with html.
	 * @since 0.0.1
	 *
	 * @param obj  $post
	 * @param bool $is_publicly_viewable
	 * @param str  $link
	 * @return str
	 */
	public function bwpas_prepare_search_result_title_output( $post, $is_publicly_viewable, $link ){
		
		if ( $is_publicly_viewable ) :
			$link = get_permalink( $post->post_id );
			return "<span><a href='" . $link . "' target='_blank'>" . $post->post_title . "</a></span>";
		else :
			return $post->post_title;
		endif;
	}
	/**
	 * Prepares the markup
	 * for the search result
	 * action block.
	 *
	 * @since 0.0.1
	 * 
	 * @param obj $post
	 * @param bool $is_publicly_viewable
	 * @param str $link
	 * @return str
	 */
	public function bwpas_prepare_search_result_action_output( $post, $is_publicly_viewable, $link ){
		$actions_output = "<a href='" . admin_url( 'post.php?post=' . $post->post_id . '&action=edit' ) . "' target='_blank'>";
		$actions_output .= __( 'Edit', 'better-wp-admin-search' );
		$actions_output .= "</a>";
		if ( $is_publicly_viewable ) :
			$actions_output .= "<span>&#124;</span>";
			$actions_output .= "<a href='" . $link . "' target='_blank'>";
			$actions_output .= __( 'View', 'better-wp-admin-search' );
			$actions_output .= "</a>";
		endif; 
		return $actions_output;
	}
	/**
	 * Escapes post-name. 
	 *
	 * @since 0.0.1
	 * 
	 * @param obj $post
	 * @param arr $post_types_list
	 * @return str
	 */
	public function bwpas_prepare_search_result_post_name_output( $post, $post_types_list ){
			$post->post_name = $this->bwpas_add_post_type_name( $post, $post_types_list );
			return $post->post_name;
	}
	/**
	 * Escapes post-author-name.
	 * 
	 * @since 0.0.1
	 * 
	 * @param obj $post 
	 * @return str
	 */
	public function bwpas_prepare_search_result_author_name( $post ){
		return $post->post_author_display_name;
	}
	/**
	 * Escapes post-categories and 
	 * wraps with html.
	 * 
	 * @since 0.0.1
	 * 
	 * @param obj $post 
	 * @return str
	 */
	public function bwpas_prepare_search_result_categories_output( $post ){
		$categories = wp_get_post_terms( $post->post_id, 'category' );
		$categories_output = '';
		if ( count( $categories ) > 0 ) :

			foreach ( $categories as $category ) :
				$categories_output .= $category->name . '<br>';
			endforeach;

		else : 
			$categories_output .= __( 'Uncategorized', 'better-wp-admin-search' ); 
		endif;
		return $categories_output;
	}
	/**
	 * Prepares post status for output.
	 * 
	 * @since 0.0.1.
	 * 
	 * @param obj $post
	 * @return str
	 */
	public function bwpas_prepare_search_result_status_output( $post ){
		return BWPAS_Helper::bwpas_status_convert( $post->post_status ); 
	}
	/**
	 * Prepares post date 
	 * created for output.
	 *
	 * @since 0.0.1
	 * 
	 * @param obj $post 
	 * @return str
	 */
	public function bwpas_prepare_search_result_date_created_output( $post ){ 
		return BWPAS_Helper::bwpas_date_time_format( $post->post_date_gmt ); 
	}
	/**
	 * Serves attachements
	 * details display block.
	 *
	 * @since 0.0.1
	 * 
	 * @param obj $post
	 * @return string
	 */
	public function bwpas_prepare_post_attachment_details( $post ){ 
		$attachment_output = '';
		$meta = [];
		if ( $post->attachement_meta ) :
			$meta = maybe_unserialize( $post->attachement_meta ); 
			$attachment_output .= '<p><span>' . __( 'File name:', 'better-wp-admin-search' ) . ' </span>'; 
			if ( isset( $post->attachement_file ) ) : 
				$attachment_output .= '<span>' . wp_basename( $post->attachement_file ) . '</span>';
			endif; 
			$attachment_output .= '</p><p>';
			$attachment_output .= '<span>' . __( 'File type:', 'better-wp-admin-search' ) . ' </span>';
			if ( isset( $post->post_mime_type ) ) : 
				$attachment_output .= '<span>' . $post->post_mime_type . '</span>';
			endif;
			$attachment_output .= '</p><p>'; 
			$attachment_output .= '<span>' . __( 'File size:', 'better-wp-admin-search' ) . ' </span>'; 
			if ( isset( $meta['filesize'] ) ) : 
				$attachment_output .= '<span>' . BWPAS_Helper::bwpas_b_to_kb( $meta['filesize'] ) . ' Kb</span>';
			endif; 
			$attachment_output .= '</p>'; 
			if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) : 
				$attachment_output .= '<p><span>' . __( 'Dimensions:', 'better-wp-admin-search' ) . '</span>';
				$attachment_output .= '<span>' . $meta['width'] . ' &cross; ' . $meta['height'] . ' pixels</span></p>';
			endif;   
		endif; 
		return $attachment_output;
	} 
}
