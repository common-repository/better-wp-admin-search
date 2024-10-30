<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. The file also includes the core plugin class and defines a function
 * that starts the plugin.
 * 
 * @package           Better_WP_Admin_Search
 *
 * @wordpress-plugin
 * Plugin Name:      	Better WP-Admin Search
 * Description:       Add essential search functionality to your WP Admin.
 * Version:           0.0.3
 * Author:            CloudSponge, Productive 
 * Author URI:        https://www.cloudsponge.com/ 
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       better-wp-admin-search

 */


namespace BWPAS; 

use BWPAS\BWPAS_Main;

defined( 'ABSPATH' ) || exit; 

/**
 * Current plugin version.  
 */
define( 'BWPAS_VERSION', '0.0.3' );

/**
 * Plugin filesystem directory path. 
 */
define( 'BWPAS_DIR', plugin_dir_path( __FILE__ ) );
define( 'BWPAS_URI', plugin_dir_url( __FILE__ ) );

require_once 'vendor/autoload.php';
 

class BWPAS_Plugin { 
  
	public function __construct() {

		$main = new BWPAS_Main();
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );  
	}
	/**
	 * Activate Plugin
	 * callback
	 * @return void
	 */
	public function activate() {}
	/**
	 * Deactivate Plugin
	 * callback
	 * @return void
	 */
	public function deactivate() {}
}

global $bwpas_plugin;

$bwpas_plugin = new BWPAS_Plugin();
