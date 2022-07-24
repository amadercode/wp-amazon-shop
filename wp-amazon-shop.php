<?php
/*
 * Plugin Name: Dropshipping & Affiliation with Amazon
 * Version: 2.1.1
 * Plugin URI: https://www.wpamazonshop.com/
 * Description: Search and build products from Amazon store to make easy money by affiliation & dropshipping. No hassle, no coding, no amazon aws keys!.
 * Author: AmaderCode Lab
 * Author URI: http://www.amadercode.com/
 * Requires at least: 4.4
 * Tested up to: 5.7.2
 * WC requires at least: 3.0
 * WC tested up to: 5.4.1
 * Stable tag: 2.1.1
 * Text Domain: wp-amazon-shop
 * Domain Path: /lang/
 * @package WordPress
 */

if ( ! defined( 'ABSPATH' ) ) exit;
define( 'ACL_WPAS_VERSION', '2.1.0' );
define( 'ACL_WPAS_REQUIRED_PHP_VERSION', '5.3.0' );
define( 'ACL_WPAS_WP_VERSION', '4.0' );
define( 'ACL_WPAS_PRODUCT_PERMIT', 20);
if ( ! defined( 'ACL_WPAS_PLUGIN_FILE' ) ) {
    define( 'ACL_WPAS_PLUGIN_FILE', __FILE__ );
}
define('ACL_WPAS_IMG_PATH', plugin_dir_url(__FILE__).'assets/images/');
//Dependency functions.
if(!function_exists('wpas_clean')){
    function wpas_clean($var){
        if ( is_array( $var ) ) {
            return array_map( 'wpas_clean', $var );
        } else {
            return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
        }
    }
}
// Load plugin basic class files
require_once( 'includes/wp-amazon-shop-plugin.php' );
require_once( 'includes/wp-amazon-shop-settings.php');
require_once( 'includes/wp-amazon-shop-admin-api.php');
// Load Amazon sdk libraries
require_once( 'includes/wp-amazon-shop-install.php');
require_once( 'includes/lib/simplehtmldom_1_9_1/simple_html_dom.php');
//require_once( 'includes/lib/simplehtmldom_2_0-RC2/HtmlWeb.php');
//Load Plugin Operation class files
require_once( 'includes/wp-amazon-shop-handler.php');
require_once( 'includes/wp-amazon-shop-functions.php');

/**
 * Returns the main instance of WordPress_Plugin_Template to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WordPress_Plugin_Template
 */
function acl_amazon_product_template () {
	$instance = ACL_Amazon_Product_Plugin::instance( __FILE__, ACL_WPAS_VERSION );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = ACL_Amazon_Product_Settings::instance( $instance );
	}
	return $instance;
}

add_action('plugins_loaded', 'acl_amazon_product_template');
//Activation Hook
register_activation_hook( __FILE__, array('ACL_Amazon_Shop_Install','pre_installation_required_check') );
//Redirect to setting page.
if(!function_exists('acl_wpas_settings_redirect')){
    function acl_wpas_settings_redirect( $plugin ) {
        if( $plugin == plugin_basename( __FILE__ )) {
            wp_redirect( admin_url('admin.php?page=wp-amazon-shop'));
            exit();
        }
    }
    add_action( 'activated_plugin', 'acl_wpas_settings_redirect' );
}
