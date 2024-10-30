<?php 
  defined( 'ABSPATH' ) || exit;
?>
<div class="bwpas-wrap">
	<h1 class="wp-heading-inline">
    <?php esc_html_e( 'Search', 'better-wp-admin-search' ); ?>
  </h1>
	
  <div class="notice notice-info is-dismissible">
    <strong>
      <?php esc_html_e( 'Search for Posts, Pages and Custom Posts.', 'better-wp-admin-search' ); ?>
    </strong>
    <br/>
    <?php esc_html_e(
      'Search in Title, Slug, Content and Excerpt, also related meta content such as Meta Title, Meta Description, Custom Fields Content.', 
      'better-wp-admin-search'
      ); ?>
  </div> 
  <?php if ( ! empty( $this->search_errors ) ) : ?> 
    <?php foreach ( $this->search_errors as $error ) : ?> 
      <div class='notice notice-error is-dismissible'>
        <p><?php echo esc_html( $error ); ?></p>
      </div>
    <?php endforeach; ?> 
  <?php endif; ?> 
  <form class="search-form" action="<?php esc_url( admin_url( 'admin.php?page=better-wp-admin-search' ) ) ?>" method="post" >
    <div class="search-input-group">
      <label for="bwpas-input-search"><?php esc_html_e( 'Search for:', 'better-wp-admin-search' ); ?></label>
      <input type="text" id="bwpas-input-search" name="search[string_find]" value="<?php echo esc_attr( $input["string_find"] ); ?>">
      <input type="hidden" id="bwpas-input-search-hidden" value="<?php echo esc_attr( $input["string_find"] ); ?>">
      <label for="casesensitive">
        <input 
          type="checkbox" 
          name="search[casesensitive]"
          id="casesensitive"
          <?php if ( ! empty( $input["casesensitive"] ) ) : ?>
            checked="true"
          <?php endif; ?>
        /> <?php esc_html_e( 'Case Sensitive', 'better-wp-admin-search' ); ?>
        <input 
          type="hidden" 
          id="bwpas-casesensitive-search" 
          <?php if ( ! empty( $input["casesensitive"] ) ) : ?>
            value="1"
          <?php else : ?>
            value="0"
          <?php endif; ?>
          />
      </label>
    </div>
    <?php wp_nonce_field( 'bwpas_search_nonce', '_bwpas_search_nonce' ); ?> 
    <?php include BWPAS_DIR . '/includes/templates/post-types-list.php'; ?>
    <div class="submit">
      <button
        type="submit" 
        name="search[better_wpadmin_search_submit]" 
        id="bwpas-submit" 
        class="button button-primary" 
        >
        <?php esc_html_e( 'Search', 'better-wp-admin-search' ); ?> 
      </button>
      <div class="loader hidden-loader">
        <svg xmlns="http://www.w3.org/2000/svg" version="1.0" width="24px" height="24px" viewBox="0 0 128 128" xml:space="preserve">
          <rect x="0" y="0" width="100%" height="100%" fill="rgba(0,0,0,0)"/>
          <g>
            <circle cx="16" cy="64" r="16" fill="#000000"/>
            <circle cx="16" cy="64" r="16" fill="#555555" transform="rotate(45,64,64)"/>
            <circle cx="16" cy="64" r="16" fill="#949494" transform="rotate(90,64,64)"/>
            <circle cx="16" cy="64" r="16" fill="#cccccc" transform="rotate(135,64,64)"/>
            <circle cx="16" cy="64" r="16" fill="#e1e1e1" transform="rotate(180,64,64)"/>
            <circle cx="16" cy="64" r="16" fill="#e1e1e1" transform="rotate(225,64,64)"/>
            <circle cx="16" cy="64" r="16" fill="#e1e1e1" transform="rotate(270,64,64)"/>
            <circle cx="16" cy="64" r="16" fill="#e1e1e1" transform="rotate(315,64,64)"/>
            <animateTransform attributeName="transform" type="rotate" values="0 64 64;315 64 64;270 64 64;225 64 64;180 64 64;135 64 64;90 64 64;45 64 64" calcMode="discrete" dur="720ms" repeatCount="indefinite"></animateTransform>
          </g>
        </svg>
      </div>
    </div> 
  </form>
</div> 
 