<?php
/**
 * BWPAS_Helper Class
 * Contributes with methods
 * shared among all classes
 *
 * @package  Better_WP_Admin_Search
 */
namespace BWPAS\Utils;  

defined( 'ABSPATH' ) || exit;

/**
 * BWPAS_Helper Class
 */
class BWPAS_Helper {
  /**
   * Formats date-time string
   *
   * @param str $date_str
   * @return str
   */
  public static function bwpas_date_time_format( $date_str ){ 
    $date_time = new \DateTime( $date_str ); 
    return $date_time->format( 'Y/m/d' ) . ' at ' . $date_time->format( 'g:i a' );
  }

  /**
   * Verb to adverb conversion
   * of the
   * standart wp statuses
   * to improve text readibility
   *
   * @param string $status
   * @return void
   */
  public static function bwpas_status_convert( $status ){
    switch ( $status ) {
      case 'publish': 
        $status = __( 'published', 'better-wp-admin-search' );
          break;
      case 'inherit':
        $status = __( 'inherited', 'better-wp-admin-search' );
          break;
    }
    return ucfirst( $status );
  } 
  /**
   * Converts
   * bytes to kilobytes.
   *
   * @param int $b
   * @return int
   */
  public static function bwpas_b_to_kb( $b ){
    return round( $b / 1024, 0, PHP_ROUND_HALF_UP );
  }
}
