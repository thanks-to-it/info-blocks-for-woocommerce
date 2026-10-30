<?php
/**
 * Plugin Name: Add Custom Messages Anywhere in WooCommerce
 * Plugin URI: https://wordpress.org/plugins/info-blocks-for-woocommerce/
 * Description: Add custom messages anywhere in WooCommerce. Beautifully.
 * Version: 2.1.0
 * Author: Algoritmika Ltd
 * Author URI: https://profiles.wordpress.org/algoritmika/
 * Requires at least: 4.7
 * Requires PHP: 7.4
 * Text Domain: info-blocks-for-woocommerce
 * Domain Path: /langs
 * WC tested up to: 11.2
 * Requires Plugins: woocommerce
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Algoritmika\WC_Info_Blocks
 */

defined( 'ABSPATH' ) || exit;

if ( 'info-blocks-for-woocommerce.php' === basename( __FILE__ ) ) {
	if ( ! function_exists( 'alg_wc_info_blocks_is_pro_activated' ) ) {
		/**
		 * Check if Pro plugin version is activated.
		 *
		 * @version 2.1.0
		 * @since   1.2.0
		 */
		function alg_wc_info_blocks_is_pro_activated() {
			$plugin = 'info-blocks-for-woocommerce-pro/info-blocks-for-woocommerce-pro.php';
			return (
				in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
				(
					is_multisite() &&
					array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) )
				)
			);
		}
	}

	if ( alg_wc_info_blocks_is_pro_activated() ) {
		defined( 'ALG_WC_INFO_BLOCKS_FILE_FREE' ) || define( 'ALG_WC_INFO_BLOCKS_FILE_FREE', __FILE__ );
		return;
	}
}

/**
 * Plugin version.
 */
defined( 'ALG_WC_INFO_BLOCKS_VERSION' ) || define( 'ALG_WC_INFO_BLOCKS_VERSION', '2.1.0' );

/**
 * Plugin file.
 */
defined( 'ALG_WC_INFO_BLOCKS_FILE' ) || define( 'ALG_WC_INFO_BLOCKS_FILE', __FILE__ );

/**
 * Include main plugin class.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-alg-wc-info-blocks.php';

if ( ! function_exists( 'alg_wc_info_blocks' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Info_Blocks to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_info_blocks() {
		return Alg_WC_Info_Blocks::instance();
	}
}

/**
 * Initialize the plugin.
 */
add_action( 'plugins_loaded', 'alg_wc_info_blocks' );
