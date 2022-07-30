<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ACL_Amazon_Shop_Info_Page
{

    function initialize()
    {
        add_action('admin_menu', array($this, 'info_menu_item'));
        add_action('admin_menu', array($this, 'pro_version_upgrade_menu'));
        add_action('plugin_action_links', array($this, 'pro_version_upgrade_action'), 10, 5);
        add_action('admin_notices', array($this, 'pro_version_upgrade_notice'));

    }

    function info_menu_item()
    {
        add_submenu_page( 'wp-amazon-shop', 'Manage Products', 'Manage Products', 'manage_options', 'admin.php?page=wp-amazon-shop&tab=wpas_manage_products');
        add_submenu_page(
            'wp-amazon-shop',
            'WP Amazon Shop Help & Info',
            'Help',
            'manage_options',
            'wp-amazon-shop-info-page',
            array($this, 'info_page_content')
        );
    }

    function info_page_content()
    {
        ?>
        <style>


            .wpas-info-page-container, .wpas-support-container {
                padding-left: 20px;
            }

            .wpas-info-page-headline {
                border-bottom: 1px dashed #cccccc;
                margin: 0 0 10px;
                padding: 10px 0;
            }
        </style>
        <div class="wrap">

            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-2">

                    <div id="post-body-content" style="position: relative;">
                        <div class="wpas-info-page-container">
                            <h2 class="wpas-info-page-headline"><?php _e('Help & Info','wp-amazon-shop');?> </h2>
                            <p>
                                <strong style="color:orange"><span class="dashicons dashicons-buddicons-community"></span><?php _e(' A Charming
                                    Feature','wp-amazon-shop');?> : </strong> <strong style="color:blue"> <?php _e('No Need Access Key ID OR Secret Key ID!','wp-amazon-shop');?> </strong> <?php _e('to search amazon products and build auto product links using shortcode
                                on your site.','wp-amazon-shop');?>
                            </p>
                            <h3><?php _e('Key Features','wp-amazon-shop');?></h3>
                            <ol style="list-style:disc">
                                <li><?php _e('Basic product import!','wp-amazon-shop');?> </li>
                                <li><?php _e('Completely Ajax Operation! No need to page refresh!','wp-amazon-shop');?> </li>
                                <li><?php _e('No Need Access Key ID OR Secret Key ID!','wp-amazon-shop');?> </li>
                                <li><?php _e('Amazon Products Search ShortCode','wp-amazon-shop');?></li>
                                <li><?php _e('Amazon Product auto link by shortCode (By ASIN and Phrase )','wp-amazon-shop');?> </li>
                                <li><?php _e('Load More products for Search and products link','wp-amazon-shop');?> </li>
                            </ol>

                            <h3><?php _e('ShortCodes','wp-amazon-shop');?>:</h3>
                            <p><?php _e('Wp Amazon Shop provides two powerful shortcode to build your store by pulling up products from amazon store','wp-amazon-shop');?>.</p>
                            <h4><?php _e('Amazon Products Search ShortCode','wp-amazon-shop');?> </h4>
                            <p>[wpas_search]</p>
                            <p><?php _e('Just copy and paste this shortcode where you want to display amazon product search form','wp-amazon-shop');?>.</p>

                            <h4><?php _e('Auto Products Link ShortCode','wp-amazon-shop');?> </h4>
                            <p>[wpas_products]</p>
                            <p><?php _e('Just copy and paste this shortcode where you want to display amazon product by Amazon Product ASIN or Keywords parameter','wp-amazon-shop');?> .</p>
                            <p><?php _e('Example of Multiple ASINs','wp-amazon-shop');?>  : [wpas_products ASIN="B077SXWSRP,B07CT3W44K"]</p>
                            <p><?php _e('Example of Single ASIN','wp-amazon-shop');?>  : [wpas_products ASIN="B077SXWSRP"]</p>
                            <p><?php _e('Example of Keywords','wp-amazon-shop');?>  : [wpas_products keywords="Alexa"]</p>
                            <p><?php _e('Example of Product Comparison Table','wp-amazon-shop');?>  : [wpas_products_comparison ASIN="B077SXWSRP,B07CT3W44K"]</p>

                            <h3><?php _e('WP AMAZON SHOP PRO VERSION FEATURES','wp-amazon-shop');?> ( <?php _e('Included Free Version Features','wp-amazon-shop');?>  ) </h3>
                            <ol style="list-style:disc">
                                <li><?php _e('Import products to your store directly from the Amazon by keyword search,ASIN number or url','wp-amazon-shop');?>. </li>
                                <li><?php _e('Import images from Amazon product page','wp-amazon-shop');?>. </li>
                                <li><?php _e('Import images from description to media library','wp-amazon-shop');?>. </li>
                                <li><?php _e('Edit the images from your dashboard','wp-amazon-shop');?>. </li>
                                <li><?php _e('Before importing the Amazon products you can customize the product title','wp-amazon-shop');?>.</li>
                                <li><?php _e('Before importing the Amazon products you can customize the product description','wp-amazon-shop');?>. </li>
                                <li><?php _e('Before importing the Amazon products you can customize the product short description','wp-amazon-shop');?>.  </li>
                                <li><?php _e('Import the weight of a product','wp-amazon-shop');?>. </li>
                                <li><?php _e('Import the reviews of a product','wp-amazon-shop');?>. </li>
                                <li><?php _e('Import product specification attributes','wp-amazon-shop');?>. </li>
                                <li><?php _e('Set your own price formula, add tax, service charges etc from your settings panel','wp-amazon-shop');?>. </li>
                                <li><?php _e('Customer can place orders from your store','wp-amazon-shop');?>. </li>
                                <li><?php _e('You can set custom price or price formula rate on each product','wp-amazon-shop');?>. </li>
                                <li><?php _e('Import the Amazon products to your specific category','wp-amazon-shop');?>. </li>
                                <li><?php _e('Auto Import the Amazon products to your store by schedule settings','wp-amazon-shop');?> .</li>
                                <li><?php _e('Automatically Product availability check','wp-amazon-shop');?> .</li>
                                <li><?php _e('Geolocation based multi country affiliation','wp-amazon-shop');?> .</li>
                            </ol>
                            <p style="text-align: center">
                                <a target="_blank"
                                   href="<?php echo esc_url_raw('https://www.wpamazonshop.com');?>"
                                   class="button button-primary"><?php _e('See Pro Features','wp-amazon-shop');?> </a>

                            </p>

                        </div>
                        <div class="wpas-support-container">
                            <h3 style="text-align: center"><?php _e('Bug report, feature request or any feedback – just inbox us at','wp-amazon-shop');?> </h3>
                            <p style="text-align: center;font-size: 20px;color:#0D72B2">amadercode@gmail.com</p>

                        </div>

                        <div style="padding: 15px 10px; border: 1px solid #ccc; text-align: center; margin-top: 20px;">
                            <?php _e('Developed By','wp-amazon-shop');?> : <a ="http://www.amadercode.com" target="_blank"> <?php _e('Web & Mobile Application Developer Company','wp-amazon-shop');?> </a> - AmaderCode Lab
                        </div>

                    </div>
                    <!-- /post-body-content -->


                </div>
                <!-- /post-body-->

            </div>
            <!-- /poststuff -->

        </div>
        <!-- /wrap -->

        <?php
    }

    function pro_version_upgrade_menu()
    {
        global $submenu;
        $upgrade_link = "https://www.wpamazonshop.com";

        $current_user = wp_get_current_user();
        $link_text = '<span class="wpas-upgrade-link-text" style="font-weight: bold; color: orange">' . __('See Pro Features', 'wp-amazon-shop') . '</span>';
        if (isset($current_user->roles[0]) && $current_user->roles[0] != 'subscriber')
            $submenu["wp-amazon-shop"][12] = array($link_text, 'activate_plugins', $upgrade_link);
        return $submenu;
    }

    function pro_version_upgrade_action($actions, $plugin_file)
    {
        static $plugin;
        $upgrade_link = "https://www.wpamazonshop.com";

        if (!isset($plugin)) {
            $plugin = "wp-amazon-shop/wp-amazon-shop.php";
        }

        if ($plugin == $plugin_file) {
            $site_link = array('settings' => '<a href="' . esc_url_raw($upgrade_link) . '" target="_blank"><span class="wpas-upgrade-link-text" style="font-weight: bold; color: orange">' . __('See Pro Features', 'wp-amazon-shop') . '</span></a>');
            $actions = array_merge($site_link, $actions);

        }

        return $actions;
    }
    function pro_version_upgrade_notice()
    {
        $screen = get_current_screen();
        if (is_admin() && ($screen->parent_base == 'wp-amazon-shop')) {
            ?>
            <div class="notice wpas-pro-upgrade-notice notice-warning is-dismissible">
                <div>
                    <h4 style="color: #0085ba;    text-shadow: 0 0 2px #dcdcdc;    font-weight: bolder;    letter-spacing: 1px;    margin-bottom: 5px;"><strong><?php _e('YAHOOO! NOW USE OUR PREMIUM PLUGIN WITHOUT AWS SECRET & ACCESS KEY!.', 'wp-amaxon-shop') ?></strong></h4>
                </div>
                <div>
                    <p> <strong><?php _e('WP Amazon Shop Pro Version is available.', 'wp-amaxon-shop') ?></strong>
                        <a target="_blank"
                           href="<?php echo esc_url_raw('https://www.wpamazonshop.com')?>"
                           class="button button-primary"><?php _e('Upgrade to Pro Version', 'wp-amaxon-shop') ?></a>
                    </p>
                </div>

            </div>
            <?php
        }
    }
}
if(!function_exists('acl_wpas_info_page_init')){
    function acl_wpas_info_page_init(){
        $wpas_plugin_info = new ACL_Amazon_Shop_Info_Page();
        $wpas_plugin_info->initialize();
    }
    add_action('init','acl_wpas_info_page_init');
}