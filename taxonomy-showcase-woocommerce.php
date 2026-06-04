<?php
/**
 * Plugin Name:       WooCommerce Taxonomy Blocks
 * Description:       Gutenberg blocks for displaying WooCommerce taxonomy terms as Grid and Showcase layouts.
 * Version:           1.2.3
 * Author:            OpenWpClub
 * Author URI:        https://openwpclub.com
 * License:           GPL-2.0-or-later
 * Requires at least: 6.3
 * Requires PHP:      8.0
 * Requires Plugins:  woocommerce
 * Text Domain:       woo-taxonomy-blocks
 */

defined( 'ABSPATH' ) || exit;

define( 'WTB_VERSION', '1.2.3' );
define( 'WTB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WTB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once WTB_PLUGIN_DIR . 'includes/class-term-image.php';
require_once WTB_PLUGIN_DIR . 'includes/term-image-fields.php';
require_once WTB_PLUGIN_DIR . 'includes/rest-api.php';

add_action( 'init', function () {
	register_block_type( WTB_PLUGIN_DIR . 'build/taxonomy-grid' );
	register_block_type( WTB_PLUGIN_DIR . 'build/taxonomy-showcase' );
} );
