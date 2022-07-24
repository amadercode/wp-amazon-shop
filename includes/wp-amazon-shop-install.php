<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ACL_Amazon_Shop_Install {

    static function pre_installation_required_check() {
        if ( version_compare( PHP_VERSION, ACL_WPAS_REQUIRED_PHP_VERSION, '<' ) ) {
            wp_die('Minimum PHP Version required: ' . ACL_WPAS_REQUIRED_PHP_VERSION );
        }
        global $wp_version;
        if ( version_compare( $wp_version, ACL_WPAS_WP_VERSION, '<' ) ) {
            wp_die('Minimum Wordpress Version required: ' . ACL_WPAS_WP_VERSION );
        }
    }
				
} // End Class
?>