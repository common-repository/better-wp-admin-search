<?php 
  defined( 'ABSPATH' ) || exit;
?>
<h3 class="wp-heading-inline">
  <?php esc_html_e( 'Found', 'better-wp-admin-search' ); ?> 
  <?php echo esc_html( $results_count ); ?> 
  <?php esc_html_e( 'posts containing', 'better-wp-admin-search' ); ?>
  <span><?php echo esc_html( $search_string ); ?></span>.
</h3>

	