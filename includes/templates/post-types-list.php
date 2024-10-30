<?php 
  defined( 'ABSPATH' ) || exit;
?>
<div class="post-types-wrapper wp-native">
  <?php if ( count( $this->wp_native_post_types ) > 0 ) : ?> 
    <h3>
      <?php esc_html_e( 'Search in WordPress native post types:', 'better-wp-admin-search' ); ?>
    </h3>
    <div>
      <span>
        <a href="#" class="opt-bulk-handler" data-select="select">
          <?php esc_html_e( 'Select All', 'better-wp-admin-search' ); ?>
        </a>
      </span>
      <span>/</span>
      <span>
        <a href="#" class="opt-bulk-handler" data-select="clear">
          <?php esc_html_e( 'Deselect All', 'better-wp-admin-search' ); ?>
        </a>
      </span>
    </div> 
    <ul class="post-types-opt">
    <?php foreach ( $this->wp_native_post_types as $type_key => $type_data ) : ?> 
      <li>
        <label for="pt-<?php echo esc_attr( $type_key ); ?>">
          <input 
            type="checkbox" 
            name="search[post_type][<?php echo esc_attr( $type_key ) ?>]" 
            id="pt-<?php echo esc_attr( $type_key ); ?>"

            <?php if ( in_array( $type_key, $input["post_types_search"] ) ) : ?>
              checked="true"
            <?php endif; ?> 

          />
          <?php echo esc_html( $type_data['name'] ); ?> 
        </label>
      </li>
    <?php endforeach; ?>
    </ul>
   
  <?php else : ?>
    <h3>
      <?php esc_html_e( 'No', 'better-wp-admin-search' ); ?>
      <b>
        <?php esc_html_e( 'WordPress native post types', 'better-wp-admin-search' ); ?>
      </b> 
      <?php esc_html_e( 'found', 'better-wp-admin-search' ); ?> 
    </h3>
  <?php endif; ?>
</div>
<div class="post-types-wrapper wp-plugin">
  <?php if ( count( $this->plugin_post_types ) > 0 ) : ?>  
    <h3>
      <?php esc_html_e( 'Search in plugin post types:', 'better-wp-admin-search' ); ?>
    </h3>
    <div>
      <span>
        <a href="#" class="opt-bulk-handler" data-select="select"> 
          <?php esc_html_e( 'Select All', 'better-wp-admin-search' ); ?>
        </a>
      </span>
      <span>/</span>
      <span>
        <a href="#" class="opt-bulk-handler" data-select="clear">
          <?php esc_html_e( 'Deselect All', 'better-wp-admin-search' ); ?>
        </a>
      </span>
    </div> 
    <ul class="post-types-opt">
    <?php foreach ( $this->plugin_post_types as $type_key => $type_data ) : ?> 
      <li>
        <label for="pt-<?php echo esc_attr( $type_key ); ?>">
          <input 
            type="checkbox" 
            name="search[post_type][<?php echo esc_attr( $type_key ) ?>]" 
            id="pt-<?php echo esc_attr( $type_key ); ?>"

            <?php if ( in_array( $type_key, $input["post_types_search"] ) ) : ?>
              checked="true"
            <?php endif; ?> 

          />
          <?php echo esc_html( $type_data['name'] ); ?> &#40;<?php echo esc_html( $type_key ); ?>&#41;
        </label>
      </li>
    <?php endforeach; ?>
    </ul>
   
  <?php else : ?>
    <h3>
      <?php esc_html_e( 'No', 'better-wp-admin-search' ); ?>
      <b>
        <?php esc_html_e( 'WordPress plugin post types', 'better-wp-admin-search' ); ?>
      </b> 
      <?php esc_html_e( 'found', 'better-wp-admin-search' ); ?> 
    </h3>
  <?php endif; ?>
</div>
