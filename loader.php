<?php

/**
 * Plugin Name: TAPoR Client
 * Description: Integration with the Text Analysis Portal for Research (TAPoR 3).
 * Plugin URI: https://commons.gc.cuny.edu
 * Author: The CUNY Academic Commons
 * Author URI: https://commons.gc.cuny.edu
 * Version: 1.0.0
 * License: GPL2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TAPOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TAPOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

define( 'TAPOR_URL', 'http://tapor.ca/' );
define( 'TAPOR_ENDPOINT_URL', TAPOR_URL . 'api/' );

require __DIR__ . '/autoload.php';

/**
 * Shorthand function to fetch our TAPoR plugin instance.
 *
 * @since 0.1.0
 */
function tapor_app() {
	return \CAC\TAPoR\App::get_instance();
}

add_action( 'plugins_loaded', function() {
	tapor_app()->init();
} );
