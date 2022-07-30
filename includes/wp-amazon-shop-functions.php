<?php
if (!defined('ABSPATH')) exit;
/**
 * ShortCode for Search Form
 */

if (!function_exists('wpas_search_form')) {
    add_shortcode('wpas_search', 'wpas_search_form');
    function wpas_search_form()
    {

        $template=get_option('acl_wpas_templates');
        ob_start();
		?>
        <div class="wp-amazon-shop-shortcode-wrapper">
            <div id="wpas-search-form-container" class="wpas-search-form-container">
                <div class="wpas-search-input-field">
                    <input id="wpas-search-input" class="wpas-search-input" required type="text" name="wpas-search-keyword">
                    <label for="wpas-search-input"><?php _e('Search amazon products', 'wp-amazon-shop') ?></label>
                </div>
                <button type="button" id="wpas-search-btn"
                        class="wpas-search-btn"><?php _e('Search', 'wp-amazon-shop') ?></button>
            </div>
            <div class="wpas-products-wrapper" <?php echo "template-".$template?>"></div>
            <div class="wpas-load-more-wrapper" style="display: none">
                <button id="wpas-load-more-btn" class="wpas-load-more-btn" data-keyword="" data-page-num=""><?php _e('Load More', 'wp-amazon-shop') ?> <span id="wpas-load-more-loader"></span></button>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        return $content;
    }
}
if (!function_exists('wpas_product_shortcode')) {
    add_shortcode('wpas_products', 'wpas_product_shortcode');
    function wpas_product_shortcode($atts=array())
    {

        extract( shortcode_atts(
            array(
                'asin' => 'B0792KWK57',
                'keywords' => 'Kids & Baby Fashion',
            ), $atts
        ));
        $type="";
        if(isset($atts['asin']) && $atts['asin']!=""){
            $key=$atts['asin'];
            $type='asin';
        }else if(isset($atts['keywords']) && $atts['keywords']!=""){
            $key=$atts['keywords'];
            $type='keyword';
        }else{
            $key='echo dots';
            $type='keyword';
        }
        $template=get_option('acl_wpas_templates');
       ob_start();
       
        ?>
        <div class="wp-amazon-shop-auto-link-shortcode-wrapper <?php echo "template-".$template?>">
            <div class="row wpas-products-wrapper">
                <div class="wp-amazon-shop-products" shortcode-type="<?php echo $type; ?>" asin-keys="<?php echo $key; ?>">
                    <img style="margin:0 auto;text-align: center" src="<?php echo ACL_WPAS_IMG_PATH; ?>dummy_product.png" alt="<?php _e('Pre Products','wp-amazon-shop')?>">
                </div>
            </div>
            <div class="wpas-load-more-wrapper" style="display: none">
                <button id="wpas-load-more-btn" class="wpas-load-more-btn" data-keyword="" data-page-num=""><?php _e('Load More', 'wp-amazon-shop') ?> <span id="wpas-load-more-loader"></span></button>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
       return $content;
    }
}
if (!function_exists('wpas_comparision_shortcode')) {
    add_shortcode('wpas_products_comparison', 'wpas_comparision_shortcode');
    function wpas_comparision_shortcode($atts=array())
    {

        extract( shortcode_atts(
            array(
                'asin' => 'B0792KWK57',
            ), $atts
        ));
        $key=$atts['asin'];
        ob_start();
        ?>
            <div class="wp-amazon-shop-products wpas-comparison-shortcode-wrapper" shortcode-type="comparision"  asin-keys="<?php echo $key; ?>">
                <img style="margin:0 auto;text-align: center" src="<?php echo ACL_WPAS_IMG_PATH; ?>dummy_product.png" alt="<?php _e('Pre Products','wp-amazon-shop')?>">
            </div>
        <?php
        $content = ob_get_clean();
        return $content;
    }
}
if (!function_exists('wpas_custom_style')) {
    function wpas_custom_style() {
        if(get_option('acl_wpas_custom_css')!=""){
            ?>
            <style>
                <?php echo get_option('acl_wpas_custom_css');  ?>
            </style>
        <?php }
    }
    add_action('wp_head', 'wpas_custom_style');
}
if(is_admin()){
    if (isset( $_GET['page'] ) && ( sanitize_text_field ($_GET['page'])=='wp-amazon-shop' || sanitize_text_field ($_GET['page'])=='wp-amazon-shop-basic-import' || sanitize_text_field ($_GET['page'])=='wp-amazon-shop-info-page')) {
        add_filter('admin_footer_text','acl_wpas_rate_us_notice');
    }
    if (!function_exists('acl_wpas_rate_us_notice')) {
        function acl_wpas_rate_us_notice()
        {
            $screen = get_current_screen();
            if (is_admin() && ($screen->parent_base == 'wp-amazon-shop')) {
                ?>
                <div class="wpas-pro-upgrade-notice">
                    <div>
                        <p><?php _e('If you like ', 'wp-amaxon-shop') ?><strong><?php _e('WP Amazon Shop', 'wp-amaxon-shop') ?></strong>
                            <?php _e(' plugin please leave us a ', 'wp-amaxon-shop') ?>
                            <a target="_blank"
                               href="<?php echo esc_url_raw('https://wordpress.org/support/plugin/wp-amazon-shop/reviews/#new-post')?>"
                               class="button button-warning" style="background-color:orange;font-size:22px; color:#fff ; vertical-align: middle; line-height: 22px;    text-shadow: 0 0 2px #000;    border: 1px solid #e09305; "><?php _e('&starf;&starf;&starf;&starf;&starf;', 'wp-amaxon-shop') ?></a>
                            <?php _e(' rating. A huge thanks in advance!', 'wp-amaxon-shop') ?>
                        </p>
                    </div>
                </div>
                <?php
            }
        }
    }
}

