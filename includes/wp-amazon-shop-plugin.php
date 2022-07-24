<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class ACL_Amazon_Product_Plugin {

	/**
	 * The single instance of ALC_Amazon_Product_Plugin.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
    public $image_path;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'acl_wpas';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
		$this->image_path = esc_url( trailingslashit( plugins_url( '/assets/images/', $this->file ) ) );

		$this->script_suffix = '';
		//$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
        if(isset($_GET['page']) && (sanitize_text_field($_GET['page'])=="wp-amazon-shop" || sanitize_text_field($_GET['page'])=="wp-amazon-shop-basic-import")){
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
        }


		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new ACL_Amazon_product_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		add_action( 'wp_loaded', array( $this, 'initial_option_load' ), 0 );
		//Ajax
        add_action('wp_ajax_wpas_chrome_ext_auth', array( $this, 'chrome_ext_auth' ));
	} // End __construct ()
    //initial option saved
    public function  initial_option_load(){
        // Update intials options
        if(!get_option('acl_wpas_enable_global_search')) {
            update_option('acl_wpas_enable_global_search','false');
        }
        if(!get_option('acl_wpas_product_page_number')) {
            update_option('acl_wpas_product_page_number','0');
        }
        if(!get_option('acl_wpas_product_per_page')) {
            update_option('acl_wpas_product_per_page','10');
        }
        if(!get_option('acl_wpas_amazon_associate_tag')) {
            update_option('acl_wpas_amazon_associate_tag','0205-21');
        }
        if(!get_option('acl_wpas_amazon_country')) {
            update_option('acl_wpas_amazon_country','com');
        }
        if(!get_option('acl_wpas_buy_now_label')) {
            update_option('acl_wpas_buy_now_label','Buy Now');
        }
        if(!get_option('acl_wpas_enable_direct_cart') || get_option('acl_wpas_enable_direct_cart')=="") {
            update_option('acl_wpas_enable_direct_cart','off');
        }
        if(!get_option('acl_wpas_namano_shoro')) {
            update_option('acl_wpas_namano_shoro',strtotime('now UTC'));
        }
        if(!get_option('acl_wpas_chrome_ext_auth')) {
            update_option('acl_wpas_chrome_ext_auth','AtvnZEgT9pom4oGdcOlE7K0QJlpPvC1aRHURAeF');
        }
        if(!get_option('acl_wpas_namano_koto')) {
            update_option('acl_wpas_namano_koto',0);
        }
        flush_rewrite_rules( true );
    }

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		//wp_register_style( $this->_token . '-bootstrap-grid', esc_url( $this->assets_url ) . 'css/bootstrap-grid.css', array(), $this->_version );
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/wpas-style.css', array(), $this->_version );
		//wp_enqueue_style( $this->_token . '-bootstrap-grid');
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
	    //Amazon Country
        $store_country="https://ws-eu.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=GB&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        if(get_option('acl_wpas_amazon_country')=='com'){
            $store_country="https://ws-na.amazon-adsystem.com/widgets/q?callback=search_callback&TemplateId=PubStudio&MarketPlace=US&Operation=ItemSearch&InstanceId=0&dataType=jsonp&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='co.uk'){
            $store_country="https://ws-eu.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=GB&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='de'){
            $store_country="https://ws-eu.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=DE&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='fr'){
            $store_country="https://ws-eu.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=FR&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='co.jp'){
            $store_country="https://ws-fe.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=JP&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='ca'){
            $store_country="https://ws-na.amazon-adsystem.com/widgets/q?callback=search_callback&TemplateId=PubStudio&MarketPlace=CA&Operation=ItemSearch&InstanceId=0&dataType=jsonp&ServiceVersion=20070822";
        }
        //Not working...
        else if(get_option('acl_wpas_amazon_country')=='com.mx'){
            $store_country="https://ws-na.amazon-adsystem.com/widgets/q?callback=search_callback&TemplateId=PubStudio&MarketPlace=MX&Operation=ItemSearch&InstanceId=0&dataType=jsonp&ServiceVersion=20070822";
        }
        //Not working...
        else if(get_option('acl_wpas_amazon_country')=='com.br'){
            $store_country="https://ws-na.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=BR&Operation=ItemSearch&InstanceId=0&dataType=jsonp&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='it'){
            $store_country="https://ws-eu.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=IT&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='in'){
            $store_country="https://ws-eu.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=IN&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='es'){
            $store_country="https://ws-eu.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=ES&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }
        else if(get_option('acl_wpas_amazon_country')=='cn'){
            $store_country="https://ws-cn.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=CN&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }
        wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
        wp_localize_script($this->_token . '-frontend', 'wpas_ajax_object',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'store_country' => $store_country,
                'action_label'=>get_option('acl_wpas_buy_now_label'),
                'is_cart'=>get_option('acl_wpas_enable_direct_cart'),
                'cart_prefix'=>'https://www.amazon.' . get_option('acl_wpas_amazon_country') . '/gp/aws/cart/add.html?AssociateTag=' . get_option('acl_wpas_amazon_associate_tag') . '&Quantity.1=1&',
                'prouct_per_page' => get_option('acl_wpas_product_per_page'),
                'page_number' => get_option('acl_wpas_product_page_number'),
                'image_path'=>$this->image_path,
                'enable_global_search'=>get_option('acl_wpas_enable_global_search'),
            )
        );

	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
        //Amazon Country
        $store_country='com';
        if(get_option('acl_wpas_amazon_country')=='com'){
            $store_country="https://ws-na.amazon-adsystem.com/widgets/q?callback=search_callback&TemplateId=PubStudio&MarketPlace=US&Operation=ItemSearch&InstanceId=0&dataType=jsonp&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='co.uk'){
            $store_country="https://ws-eu.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=GB&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='de'){
            $store_country="https://ws-eu.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=DE&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='fr'){
            $store_country="https://ws-eu.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=FR&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='co.jp'){
            $store_country="https://ws-fe.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=JP&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='ca'){
            $store_country="https://ws-na.amazon-adsystem.com/widgets/q?callback=search_callback&TemplateId=PubStudio&MarketPlace=CA&Operation=ItemSearch&InstanceId=0&dataType=jsonp&ServiceVersion=20070822";
        }
        //Not working...
        else if(get_option('acl_wpas_amazon_country')=='com.mx'){
            $store_country="https://ws-na.amazon-adsystem.com/widgets/q?callback=search_callback&TemplateId=PubStudio&MarketPlace=MX&Operation=ItemSearch&InstanceId=0&dataType=jsonp&ServiceVersion=20070822";
        }
        //Not working...
        else if(get_option('acl_wpas_amazon_country')=='com.br'){
            $store_country="https://ws-na.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=BR&Operation=ItemSearch&InstanceId=0&dataType=jsonp&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='it'){
            $store_country="https://ws-eu.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=IT&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='in'){
            $store_country="https://ws-eu.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=IN&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }else if(get_option('acl_wpas_amazon_country')=='es'){
            $store_country="https://ws-eu.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=ES&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }
        else if(get_option('acl_wpas_amazon_country')=='cn'){
            $store_country="https://ws-cn.amazon-adsystem.com/widgets/q?callback=search_callback&MarketPlace=CN&Operation=GetResults&InstanceId=0&dataType=jsonp&TemplateId=MobileSearchResults&ServiceVersion=20070822";
        }

		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
		wp_localize_script($this->_token . '-admin', 'wpas_admin_object',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'store_country' => $store_country,
                'prouct_per_page' => get_option('acl_wpas_product_per_page'),
                'page_number' => get_option('acl_wpas_product_page_number'),
                'image_path'=>$this->image_path,
                'store_url'=>'https://www.amazon.'.get_option('acl_wpas_amazon_country').'/',
                'admin_url'=>strtok(admin_url(), '?'),
                'import_variation'=>'off',
                'auth_token'=>get_option('acl_wpas_chrome_ext_auth'),
                'import_from'=>'https://www.amazon.'.get_option('acl_wpas_amazon_country'),
                'type'=>'global'
            )
        );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'wp-amazon-shop', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'wp-amazon-shop';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main WordPress_Plugin_Template Instance
	 *
	 * Ensures only one instance of WordPress_Plugin_Template is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WordPress_Plugin_Template()
	 * @return Main WordPress_Plugin_Template instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()
}