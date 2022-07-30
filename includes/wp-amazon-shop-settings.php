<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class ACL_Amazon_Product_Settings {

	/**
	 * The single instance of WordPress_Plugin_Template_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'acl_wpas_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );
		/**
         * Have to include all others page here .
         */
        if(is_admin()){
            require_once( 'import/wp-amazon-shop-import.php');
            require_once( 'wp-amazon-shop-feedback.php');
            require_once( 'wp-amazon-shop-info-page.php');
        }
		// Add settings link to plugins page
		//add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings (){
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		//$page = add_options_page( __( 'WP Amazon Shop', 'wp-amazon-shop' ) , __( 'WP Amazon Shop', 'wp-amazon-shop' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		//add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
        add_menu_page('WP Amazon Shop', 'WP Amazon Shop', 'manage_options', 'wp-amazon-shop', '', 'dashicons-image-filter', 25);
        add_submenu_page(
            'wp-amazon-shop',
            __( 'WP Amazon Shop Settings', 'wp-amazon-shop' ),
            __( 'Settings', 'wp-amazon-shop' ),
            'manage_options',
            'wp-amazon-shop',
            array( $this, 'settings_page' )
        );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'wp-amazon-shop' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['wpas_general'] = array(
			'title'					=> __( 'General', 'wp-amazon-shop' ),
			'description'			=> __( 'All General setting for Amazon Shop.', 'wp-amazon-shop' ),
			'fields'				=> array(
                array(
                    'id' 			=> 'use_for',
                    'label'			=> __( 'Bussiness For <strong style="color:orange">Pro</strong>', 'wp-amazon-shop' ),
                    'description'	=> __( 'Select a Business for which WP Amazon Shop will be used.', 'wp-amazon-shop' ),
                    'type'			=> 'select',
                    'options'		=> array( 'affiliate' => 'Affiliate Marketing', 'dropship' => 'Drop Shipping Business'),
                    'default'		=> 'affiliate'
                ),
                array(
                    'id' 			=> 'enable_global_search',
                    'label'			=> __( 'Enable Amazon Search Globally', 'wp-amazon-shop' ),
                    'description'	=> __( 'If Enable Amazon Search Globally option is checked then it will work for all search forms.', 'wp-amazon-shop' ),
                    'type'			=> 'checkbox',
                    'default'		=> true
                ),
			    array(
                    'id' 			=> 'product_page_number',
                    'label'			=> __( 'Intial Products Page Number'  , 'wp-amazon-shop' ),
                    'description'	=> __( 'To display products for given the page number intially. Note: Starting value is 0', 'wp-amazon-shop' ),
                    'type'			=> 'text',
                    'default'		=> '1',
                    'placeholder'	=> __( 'Product Page Number', 'wp-amazon-shop' )
                ),
                array(
                    'id' 			=> 'product_per_page',
                    'label'			=> __( 'Product Per Page'  , 'wp-amazon-shop' ),
                    'description'	=> __( 'To display products per request to Amazon store by restful api.Example -5,6,8,10 etc', 'wp-amazon-shop' ),
                    'type'			=> 'text',
                    'default'		=> '10',
                    'placeholder'	=> __( 'Product Per Page', 'wp-amazon-shop' )
                ),
                array(
                    'id' 			=> 'product_display_option',
                    'label'			=> __( 'Product Display Options <strong style="color:orange">Pro</strong>', 'wp-amazon-shop' ),
                    'description'	=> __( 'Selected features in product grid will be displayed.', 'wp-amazon-shop' ),
                    'type'			=> 'checkbox_multi',
                    'options'		=> array( 'title' => 'Title', 'thumbnail' => 'Image Thumbnail','prime' => 'Prime Icon (If Prime Product)', 'price' => 'Price', 'price_label' => 'Price Label', 'cart_action' => 'Add ot Cart','details_action' => 'View Details','rating' => 'Rating','rating_num' => 'Review & Rating Number With Link ','amazon_link'=>'Amazon Product Link (Affiliate)' ),
                    'default'		=> array( 'title', 'thumbnail','prime', 'price', 'price_label', 'cart_action','details_action','rating','rating_num','amazon_link'),
                ),
                array(
                    'id' 			=> 'buy_now_label',
                    'label'			=> __( 'Buy Now Label'  , 'wp-amazon-shop' ),
                    'description'	=> __( 'To display Product action label on search or product by shortcodes page.Example "Buy Now","View on Amazon" etc', 'wp-amazon-shop' ),
                    'type'			=> 'text',
                    'default'		=> 'Buy Now',
                    'placeholder'	=> __( 'Buy Now', 'wp-amazon-shop' )
                ),
                array(
                    'id' 			=> 'buy_now_action',
                    'label'			=> __( 'Buy Now Button Action <strong style="color:orange">Pro</strong>' , 'wp-amazon-shop' ),
                    'description'	=> __( 'Which price format will be display on the store front', 'wp-amazon-shop' ),
                    'type'			=> 'radio',
                    'options'		=> array( 'onsite' => 'Product will be added to site cart then it will be redirected to Amazon from checkout (Affiliate) & order list (DropShip)  ', 'redirect' => 'Direct Amazon Cart Page (for Affiliate)', 'details' => 'Amazon Product Details (for Affiliate)' ),
                    'default'		=> 'onsite',
                    'placeholder'	=> __( 'Buy Now Button Action', 'wp-amazon-shop' )
                ),
                array(
                    'id' 			=> 'cart_import_category',
                    'label'			=> __( 'Default Category for Add to Cart <strong style="color:orange">Pro</strong>', 'wp-amazon-shop' ),
                    'description'	=> __( 'The Product will be imported to selected category (if product does not imported ) before adding to cart when user will click on add to cart button .', 'wp-amazon-shop' ),
                    'type'			=> 'select',
                    'options'		=> array( 'uncategorized' => 'Uncategorized'),
                    'default'		=> 'uncategorized'
                ),
                array(
                    'id' 			=> 'enable_no_follow',
                    'label'			=> __( 'Enable No Follow to Link <strong style="color:orange">Pro</strong>' , 'wp-amazon-shop' ),
                    'description'	=> __( 'If "YES" option is checked then it will add nofollow value to rel attribule of proudct anchor tag .', 'wp-amazon-shop' ),
                    'type'			=> 'radio',
                    'options'		=> array( 'on' => 'YES', 'off' => 'No'),
                    'default'		=> 'on',
                    'placeholder'	=> __( 'No Follow to Link', 'wp-amazon-shop' )
                ),
                array(
                    'id' 			=> 'enable_direct_cart',
                    'label'			=> __( 'Enable BUY NOW to direct amazon store cart page', 'wp-amazon-shop' ),
                    'description'	=> __( 'If Enable direct to cart page, then WP amazon shop BUY NOW will redirect the user to cart page instead of product details page.', 'wp-amazon-shop' ),
                    'type'			=> 'checkbox',
                    'default'		=> 'off'
                ),
			    /*array(
					'id' 			=> 'text_field',
					'label'			=> __( 'Some Text' , 'wp-amazon-shop' ),
					'description'	=> __( 'This is a standard text field.', 'wp-amazon-shop' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text', 'wp-amazon-shop' )
				),
				array(
					'id' 			=> 'password_field',
					'label'			=> __( 'A Password' , 'wp-amazon-shop' ),
					'description'	=> __( 'This is a standard password field.', 'wp-amazon-shop' ),
					'type'			=> 'password',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text', 'wp-amazon-shop' )
				),
				array(
					'id' 			=> 'secret_text_field',
					'label'			=> __( 'Some Secret Text' , 'wp-amazon-shop' ),
					'description'	=> __( 'This is a secret text field - any data saved here will not be displayed after the page has reloaded, but it will be saved.', 'wp-amazon-shop' ),
					'type'			=> 'text_secret',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text', 'wp-amazon-shop' )
				),
				array(
					'id' 			=> 'text_block',
					'label'			=> __( 'A Text Block' , 'wp-amazon-shop' ),
					'description'	=> __( 'This is a standard text area.', 'wp-amazon-shop' ),
					'type'			=> 'textarea',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text for this textarea', 'wp-amazon-shop' )
				),
				array(
					'id' 			=> 'single_checkbox',
					'label'			=> __( 'An Option', 'wp-amazon-shop' ),
					'description'	=> __( 'A standard checkbox - if you save this option as checked then it will store the option as \'on\', otherwise it will be an empty string.', 'wp-amazon-shop' ),
					'type'			=> 'checkbox',
					'default'		=> ''
				),
				array(
					'id' 			=> 'select_box',
					'label'			=> __( 'A Select Box', 'wp-amazon-shop' ),
					'description'	=> __( 'A standard select box.', 'wp-amazon-shop' ),
					'type'			=> 'select',
					'options'		=> array( 'drupal' => 'Drupal', 'joomla' => 'Joomla', 'wordpress' => 'WordPress' ),
					'default'		=> 'wordpress'
				),
				array(
					'id' 			=> 'radio_buttons',
					'label'			=> __( 'Some Options', 'wp-amazon-shop' ),
					'description'	=> __( 'A standard set of radio buttons.', 'wp-amazon-shop' ),
					'type'			=> 'radio',
					'options'		=> array( 'superman' => 'Superman', 'batman' => 'Batman', 'ironman' => 'Iron Man' ),
					'default'		=> 'batman'
				),
				array(
					'id' 			=> 'multiple_checkboxes',
					'label'			=> __( 'Some Items', 'wp-amazon-shop' ),
					'description'	=> __( 'You can select multiple items and they will be stored as an array.', 'wp-amazon-shop' ),
					'type'			=> 'checkbox_multi',
					'options'		=> array( 'square' => 'Square', 'circle' => 'Circle', 'rectangle' => 'Rectangle', 'triangle' => 'Triangle' ),
					'default'		=> array( 'circle', 'triangle' )
				)*/
			)
		);

		$settings['wpas_amazon'] = array(
			'title'					=> __( 'Amazon Settings', 'wp-amazon-shop' ),
			'description'			=> __( 'Amazon Affiliate Account Tag. ( <strong style="color:orange">   No Need  Access Key ID OR Secret Key ID from Amazon Affiliate portal </strong>) ', 'wp-amazon-shop' ),
			'fields'				=> array(
                array(
                    'id' 			=> 'amazon_associate_tag',
                    'label'			=> __( 'Amazon Affiliate Associate Tag' , 'wp-amazon-shop' ),
                    'description'	=> __( 'Amazon Affiliate Associate Account Tag.', 'wp-amazon-shop' ),
                    'type'			=> 'text',
                    'default'		=> '',
                    'placeholder'	=> __( 'Associate ID', 'wp-amazon-shop' )
                ),
                array(
                    'id' 			=> 'amazon_country',
                    'label'			=> __( 'Select Country', 'wp-amazon-shop' ),
                    'description'	=> __( 'Amazon Store Country.', 'wp-amazon-shop' ),
                    'type'			=> 'select',
                    'options'		=> array( 'com' => 'United States', 'co.uk' => 'United Kingdom', 'es' => 'Spain','co.jp' => 'Japan', 'it' => 'Italy', 'in' => 'India', 'de' => 'Germany', 'fr' => 'France', 'cn' => 'China', 'ca' => 'Canada'),
                    'default'		=> 'com'
                ),
				/*array(
					'id' 			=> 'number_field',
					'label'			=> __( 'A Number' , 'wp-amazon-shop' ),
					'description'	=> __( 'This is a standard number field - if this field contains anything other than numbers then the form will not be submitted.', 'wp-amazon-shop' ),
					'type'			=> 'number',
					'default'		=> '10',
					'placeholder'	=> __( '42', 'wp-amazon-shop' )
				),
				array(
					'id' 			=> 'colour_picker',
					'label'			=> __( 'Pick a colour', 'wp-amazon-shop' ),
					'description'	=> __( 'This uses WordPress\' built-in colour picker - the option is stored as the colour\'s hex code.', 'wp-amazon-shop' ),
					'type'			=> 'color',
					'default'		=> '#21759B'
				),
				array(
					'id' 			=> 'an_image',
					'label'			=> __( 'An Image' , 'wp-amazon-shop' ),
					'description'	=> __( 'This will upload an image to your media library and store the attachment ID in the option field. Once you have uploaded an imge the thumbnail will display above these buttons.', 'wp-amazon-shop' ),
					'type'			=> 'image',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'multi_select_box',
					'label'			=> __( 'A Multi-Select Box', 'wp-amazon-shop' ),
					'description'	=> __( 'A standard multi-select box - the saved data is stored as an array.', 'wp-amazon-shop' ),
					'type'			=> 'select_multi',
					'options'		=> array( 'linux' => 'Linux', 'mac' => 'Mac', 'windows' => 'Windows' ),
					'default'		=> array( 'linux' )
				)*/
			)
		);
        $settings['wpas_store'] = array(
            'title'					=> __( 'DropShipping', 'wp-amazon-shop' ),
            'description'			=> __( 'All necessary settings for DropShipping.', 'wp-amazon-shop' ),
            'fields'				=> array(),
        );
        $settings['wpas_import'] = array(
            'title'					=> __( 'Products Imports', 'wp-amazon-shop' ),
            'description'			=> __( 'All Configuration for products import.', 'wp-amazon-shop' ),
            'fields'				=> array(),
        );
        $settings['wpas_manage_products'] = array(
            'title'					=> __( 'Manage Products', 'wp-amazon-shop' ),
            'description'			=> __( 'Manage imported products by updating & deleting products', 'wp-amazon-shop' ),
            'fields'				=> array(),
        );
		$settings['wpas_template'] = array(
            'title'					=> __( 'Templates', 'wp-amazon-shop' ),
            'description'			=> __( 'Select a template to display cart on your WooCommerce store as default', 'wp-amazon-shop' ),
            'fields'				=> array(
                array(
                    'id' 			=> 'templates',
                    'label'			=> __( 'Cart Templates', 'wp-amazon-shop' ),
                    'description'	=> __( '', 'wp-amazon-shop' ),
                    'type'			=> 'template',
                    'options'		=> array( '1' => 'Template 01', '2' => 'Template 02',),
                    'default'		=> '1'
                ),

            ),
        );
		$settings['wpas_custom_style'] = array(
            'title'					=> __( 'Custom Style', 'wp-amazon-shop' ),
            'description'			=> __( 'Design your store by overriding default with your own style (CSS).', 'wp-amazon-shop' ),
            'fields'				=> array(
				array(
					'id' 			=> 'custom_css',
					'label'			=> __( 'Custom CSS' , 'wp-amazon-shop' ),
					'description'	=> __( '', 'wp-amazon-shop' ),
					'type'			=> 'textarea',
					'default'		=> '',
					'placeholder'	=> __( 'Example:.amazon-product-action button {background-color:red !important;}', 'wp-amazon-shop' )
				),

            ),
        );

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && sanitize_text_field($_POST['tab'])!=''  ) {
				$current_section = sanitize_text_field($_POST['tab']);
			} else {
				if ( isset( $_GET['tab'] ) && sanitize_text_field($_GET['tab'])!="") {
					$current_section = sanitize_text_field($_GET['tab']);
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
        $html= '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
        $html .= '<h2>' . __( 'WP Amazon Shop Settings' , 'wp-amazon-shop' ) .'</h2>' . "\n";
        $tab = '';
			if ( isset( $_GET['tab'] ) && sanitize_text_field($_GET['tab'])!="") {
				$tab .= sanitize_text_field($_GET['tab']);
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {
			    $html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == sanitize_text_field($_GET['tab'])) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}
        if ( isset( $_GET['tab'] ) && 'wpas_store' == sanitize_text_field($_GET['tab'])) {
            $html .= '<img src="'.ACL_WPAS_IMG_PATH.'dropshipping-pro.jpg" alt="All necessary settings for DropShipping">' . "\n";
        }
        else if ( isset( $_GET['tab'] ) && 'wpas_import' == sanitize_text_field($_GET['tab']) ) {
            $html .= '<img src="'.ACL_WPAS_IMG_PATH.'product-import-pro.jpg" alt="Products Import">' . "\n";
        }
        else if ( isset( $_GET['tab'] ) && 'wpas_manage_products' == sanitize_text_field($_GET['tab']) ) {
            $html .= '<img src="'.ACL_WPAS_IMG_PATH.'manage-products-pro.jpg" alt="Manage Products">' . "\n";
        }
        else {
            $html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

            // Get settings fields
            ob_start();
            settings_fields($this->parent->_token . '_settings');
            do_settings_sections($this->parent->_token . '_settings');
            $html .= ob_get_clean();

            $html .= '<p class="submit">' . "\n";
            $html .= '<input type="hidden" name="tab" value="' . esc_attr($tab) . '" />' . "\n";
            $html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr(__('Save Settings', 'wp-amazon-shop')) . '" />' . "\n";
            $html .= '</p>' . "\n";
            $html .= '</form>' . "\n";
            $html .= '</div>' . "\n";
        }

		echo $html;
	}

	/**
	 * Main WordPress_Plugin_Template_Settings Instance
	 *
	 * Ensures only one instance of WordPress_Plugin_Template_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WordPress_Plugin_Template()
	 * @return Main WordPress_Plugin_Template_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}

