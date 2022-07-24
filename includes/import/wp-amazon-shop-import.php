<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
require_once( 'wp-amazon-shop-ikits.php');
//use html parser
//use simplehtmldom\HtmlWeb;
class ACL_Amazon_Product_Import
{

    public function initiate_hooks() {
        add_action( 'admin_menu' , array(&$this, 'hook_import_submenu'));
        add_action('wp_ajax_wpas_products_import_search', array($this, 'wpas_products_import_search'));
        add_action('wp_ajax_wpas_import_product_from_amazon', array($this, 'wpas_import_product_from_amazon'));
        // Check if WooCommerce is active, and is required WooCommerce version.
       if( isset($_GET['page']) && sanitize_text_field($_GET['page'])=='wp-amazon-shop-basic-import'){
           if (!class_exists('WooCommerce') || version_compare(get_option('woocommerce_db_version'), 2.2, '<')) {
               add_action('admin_notices', array($this, 'woocommerce_required_notice'));
               return;
           }
       }
    }
    function hook_import_submenu(){
        add_submenu_page(
            'wp-amazon-shop',
            'WP Amazon Shop Basic Products Import',
            'Basic Import',
            'import',
            'wp-amazon-shop-basic-import',
            array($this, 'products_basic_import')
        );
    }
    public static function woocommerce_required_notice(){
        if (current_user_can('activate_plugins')) :
            if (!class_exists('WooCommerce')) :
                ?>
                <div id="message" class="error">
                    <p>
                        <?php
                        printf(
                            __('WooCommerce is requred for %sWP Amazon Shop Products Import%s to work. Please install & activate %sWooCommerce%s.', 'wp-amazon-shop'),
                            '<strong>',
                            '</strong><br>',
                            '<a href="'.esc_url_raw('https://wordpress.org/plugins/woocommerce/').'" target="_blank" >',
                            '</a>'
                        );
                        ?>
                    </p>
                </div>
            <?php
            elseif (version_compare(get_option('woocommerce_db_version'), 2.2, '<')) :
                ?>
                <div id="message" class="error">
                    <p>
                        <?php
                        printf(
                            __('%WP Amazon Shop Import requires WooCommerce %s or newer. For more information about our WooCommerce version support %sclick here%s.', 'wp-amazon-shop'),
                            '<strong>',
                            '</strong><br>',
                            2.2
                        );
                        ?>
                    </p>
                    <div style="clear:both;"></div>
                </div>
            <?php
            endif;
        endif;
    }
    public function products_basic_import(){
        ?>
        <div class="wrap" id="acl_wpas_products_import">
            <h2><?php _e('WP Amazon Shop Products Import','wp-amazon-shop');?></h2>
            <div class="wpas-import-search-container" style="padding: 10px">
            <table class="widefat" style="border:none;">
                <thead>
                </thead>
                <tbody>

                <tr>
                    <td style="width:30%;text-align:right ">
                        <?php
                        $categories = $this->product_categories_by_country_domain(get_option('acl_wpas_amazon_country'));

                        ?>
                        <select name="wpas_import_search_category" id="wpas_import_search_category" class="wpas_import_search_category">
                            <?php
                            if(!empty($categories)){
                                foreach ( $categories['categories'] as $index => $category ) {
                                    echo '<option value="'.$index.'">'.$category.'</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td style="width:30%">
                        <input type="text" id="wpas_import_search_keywords" name="wpas_import_search_keywords" class="wpas_import_search_keywords" size="100" style="width:100%;" placeholder="Search Keyword to Import Products" /><br />
                    </td>
                    <td style="width:40%;text-align:left ">
                        <input type="button" class="button button-primary" id="wpas_import_search_btn" value="<?php _e('Search Products','wp-amazon-shop');?>">
                    </td>
                </tr>
                <tr id="wpas-woo-categories-container">
                    <td style="width:40%;text-align:right "><?php _e('Select a Category where to Import Product','wp-amazon-shop');?></td>
                    <td>
                        <?php
                        $taxonomy     = 'product_cat';
                        $orderby      = 'name';
                        $show_count   = 0;      // 1 for yes, 0 for no
                        $pad_counts   = 0;      // 1 for yes, 0 for no
                        $hierarchical = 1;      // 1 for yes, 0 for no
                        $empty        = 0;

                        $args = array(
                            'taxonomy'     => $taxonomy,
                            'orderby'      => $orderby,
                            'show_count'   => $show_count,
                            'pad_counts'   => $pad_counts,
                            'hierarchical' => $hierarchical,
                            'hide_empty'   => $empty
                        );
                        $wpas_woo_categories = get_categories( $args );
                        ?>
                        <select name="wpas_woo_category" id="wpas_woo_category" class="wpas_woo_category">
                            <?php
                            if(!empty($wpas_woo_categories)){
                                foreach ( $wpas_woo_categories as $category ) {
                                    echo '<option value="'.$category->name.'">'.$category->name.'</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>

                    <td></td>
                </tr>

                </tbody>
            </table>
                <p style="display:none;color:red;font-weight: 600;text-align: center" id="wpas-import-validation-msg"><?php _e('Entry Keyword and hit ENTER key or click on button ','wp-amazon-shop') ?></p>
            </div>
            <div class="clear" style="padding: 10px"></div>
            <div class="wpas-import-products-container">
                <table class="wp-list-table widefat fixed striped ">
                    <thead>
                    <tr>
                       <th scope="col" id="wpas-import-thumb" class="manage-column column-primary"><?php _e('Thumbnail', 'wp-amazon-shop') ?></th>
                        <th scope="col" id="wpas-import-title" class="manage-column"><?php _e('Title', 'wp-amazon-shop') ?></th>
                        <th scope="col" id="wpas-import-asin" class="manage-column "><?php _e('ASIN', 'wp-amazon-shop') ?></th>
                        <th scope="col" id="wpas-import-price" class="manage-column"><?php _e('Price', 'wp-amazon-shop') ?></th>
                        <th scope="col" id="wpas-import-review-rating" class="manage-column"><?php _e('Rating & Review', 'wp-amazon-shop') ?></th>
                        <th scope="col" id="wpas-import-action" class="manage-column"><?php _e('Action', 'wp-amazon-shop') ?></th>
                    </tr>
                    </thead>
                    <tbody id="the-list" class="wpas-import-products-list">
                    </tbody>
                </table>
                <div style="text-align:center" class="wpas-import-pre-loader-container">
                </div>
                <div class="wpas-load-more-wrapper" style="display: none">
                    <button id="wpas-import-load-more-btn" class="wpas-load-more-btn" data-keyword="" data-page-num=""><?php _e('Load More', 'wp-amazon-shop') ?> <span id="wpas-load-more-loader"></span></button>
                </div>
            </div>

        </div>

        <?php
    }

    /***
     * Get Country based Product Categories
     * @param null $country_domain
     * @return array
     */
    private function product_categories_by_country_domain( $country_domain = null ) {
        $categories_by_country_domain = array();
        switch ( $country_domain ) {
            case 'com.br':
                $categories_by_country_domain['categories'] = array( 'All'         => 'Todos os departmentos',
                    'Books'       => 'Livros',
                    'KindleStore' => 'Loja Kindle',
                    'MobileApps'  => 'Apps e Jogos'
                );

                break; // End com.br

            case 'ca':

                $categories_by_country_domain['categories'] = array( 'All'                  => 'All Departments',
                    'Apparel'              => 'Clothing & Accessories',
                    'Automotive'           => 'Automotive',															  			                              					   'Baby'                 => 'Baby',

                    'Beauty'               => 'Beauty',
                    'Blended'              => 'Blended',
                    'Books'                => 'Books',
                    'DVD'                  => 'Movies & TV',

                    'Electronics'          => 'Electronics',
                    'GiftCards'            => 'Gift Cards',
                    'Grocery'              => 'Grocery & Gourmet Food',
                    'HealthPersonalCare'   => 'Health & Personal Care',

                    'Industrial'           => 'Industrial & Scientific',
                    'Jewelry'              => 'Jewelry',
                    'KindleStore'          => 'Kindle Store',
                    'Kitchen'              => 'Home & Kitchen',

                    'LawnAndGarden'        => 'Patio, Lawn & Garden',
                    'Luggage'              => 'Luggage & Bags',
                    'Marketplace'          => 'Marketplace',
                    'MobileApps'           => 'Apps & Games',

                    'Music'               => 'Music',
                    'MusicalInstruments'   => 'Musical Instruments, Stage & Studio',
                    'OfficeProducts'       => 'Office Products',
                    'PetSupplies'          => 'Pet Supplies',

                    'Shoes'                => 'Shoes & Handbags',
                    'Software'             => 'Software',
                    'SportingGoods'        => 'Sports & Outdoors',
                    'Tools'                => 'Tools & Home Improvement',

                    'Toys'                 => 'Toys & Games',
                    'VideoGames'           => 'Video Games',
                    'Watches'              => 'Watches'
                );
                break; // End ca

            case 'cn':
                $categories_by_country_domain['categories'] = array( 'All'                  => '全部分类',
                    'Apparel'              => '服饰箱包',
                    'Appliances'           => '大家电',
                    'Automotive'           => '汽车用品',															  			                              					   'Baby'                 => '母婴用品',

                    'Beauty'               => '美容化妆',
                    'Books'                => '图书',

                    'Electronics'          => '电子',
                    'GiftCards'            => '礼品卡',
                    'Grocery'              => '食品',
                    'HealthPersonalCare'   => '个护健康',

                    'Home'                 => '家用',
                    'HomeImprovement'      => '家居装修',

                    'Jewelry'              => '珠宝首饰',
                    'KindleStore'          => 'Kindle商店',
                    'Kitchen'              => '厨具',

                    'MobileApps'           => '应用程序和游戏',

                    'Music'                => '音乐',
                    'MusicalInstruments'   => '乐器',
                    'OfficeProducts'       => '办公用品',
                    'PCHardware'           => '电脑/IT',

                    'PetSupplies'          => '宠物用品',
                    'Photo'                => '摄影/摄像',

                    'Shoes'                => '鞋靴',
                    'Software'             => '软件',
                    'SportingGoods'        => '运动户外休闲',

                    'Toys'                 => '玩具',
                    'Video'                => '音像',
                    'VideoGames'           => '游戏/娱乐',
                    'Watches'              => '钟表'
                );

                break; // End cn

            case 'fr':
                $categories_by_country_domain['categories'] = array( 'All'                  => 'Toutes nos boutiques',
                    'Apparel'              => 'Vêtements et accessoires',
                    'Appliances'           => 'Gros électroménager',
                    'Baby'                 => 'Bébés & Puériculture',

                    'Beauty'               => 'Beauté et Parfum',
                    'Blended'              => 'Blended',
                    'Books'                => 'Livres en français',
                    'Classical'            => 'Musique classique',

                    'DVD'                  => 'DVD & Blu-ray',

                    'Electronics'          => 'High-Tech',
                    'ForeignBooks'         => 'Livres anglais et étrangers',
                    'GiftCards'            => 'Boutique chèques-cadeaux',
                    'Grocery'              => 'Epicerie',
                    'Handmade'             => 'Handmade',
                    'HealthPersonalCare'   => 'Hygiène et Santé',

                    'HomeImprovement'      => 'Bricolage',

                    'Industrial'           => 'Secteur industriel & scientifique',
                    'Jewelry'              => 'Bijoux',
                    'KindleStore'          => 'Boutique Kindle',
                    'Kitchen'              => 'Cuisine & Maison',

                    'LawnAndGarden'        => 'Jardin',
                    'Lighting'             => 'Luminaires et Eclairage',

                    'Luggage'              => 'Bagages',
                    'Marketplace'          => 'Marketplace',
                    'MobileApps'           => 'Applis & Jeux',

                    'MP3Downloads'         => 'Téléchargement de musique',

                    'Music'                => 'Musique : CD & Vinyles',
                    'MusicalInstruments'   => 'Instruments de musique & Sono',
                    'OfficeProducts'       => 'Fournitures de bureau',

                    'PCHardware'           => 'Informatique',
                    'PetSupplies'          => 'Animalerie',

                    'Shoes'                => 'Chaussures et Sacs',
                    'Software'             => 'Logiciels',
                    'SportingGoods'        => 'Sports et Loisirs',

                    'Toys'                 => 'Jeux et Jouets',
                    'VideoGames'           => 'Jeux vidéo',
                    'Watches'              => 'Montres'
                );

                break; // End fr

            case 'de':
                $categories_by_country_domain['categories'] = array( 'All'                  => 'Alle Kategorien',
                    'Apparel'              => 'Bekleidung',
                    'Appliances'           => 'Elektro-Großgeräte',
                    'Automotive'           => 'Auto & Motorrad',															  			                              					   'Baby'                 => 'Baby',

                    'Beauty'               => 'Beauty',
                    'Blended'              => 'Blended',
                    'Books'                => 'Bücher',
                    'Classical'            => 'Klassik',

                    'DVD'                  => 'DVD & Blu-ray',

                    'Electronics'          => 'Elektronik & Foto',
                    'ForeignBooks'         => 'Fremdsprachige Bücher',
                    'GiftCards'            => 'Geschenkgutscheine',
                    'Grocery'              => 'Lebensmittel & Getränke',
                    'Handmade'             => 'Handmade',
                    'HealthPersonalCare'   => 'Drogerie & Körperpflege',

                    'HomeGarden'           => 'Garten',

                    'Industrial'           => 'Technik & Wissenschaft',
                    'Jewelry'              => 'Schmuck',
                    'KindleStore'          => 'Kindle-Shop',
                    'Kitchen'              => 'Küche & Haushalt',

                    'Lighting'             => 'Beleuchtung',

                    'Luggage'              => 'Koffer, Rucksäcke & Taschen',
                    'Magazines'            => 'Zeitschriften',
                    'Marketplace'          => 'Marketplace',
                    'MobileApps'           => 'Apps & Spiele',

                    'MP3Downloads'         => 'Musik-Downloads',

                    'Music'                => 'Musik-CDs & Vinyl',
                    'MusicalInstruments'   => 'Musikinstrumente & DJ-Equipment',
                    'OfficeProducts'       => 'Bürobedarf & Schreibwaren',
                    'Pantry'               => 'Amazon Pantry',

                    'PCHardware'           => 'Computer & Zubehör',
                    'PetSupplies'          => 'Haustier',
                    'Photo'                => 'Kamera & Foto',

                    'Shoes'                => 'Schuhe & Handtaschen',
                    'Software'             => 'Software',
                    'SportingGoods'        => 'Sport & Freizeit',
                    'Tools'                => 'Baumarkt',

                    'Toys'                 => 'Spielzeug',
                    'UnboxVideo'           => 'Amazon Instant Video',
                    'VideoGames'           => 'Games',
                    'Watches'              => 'Uhren'
                );
                break; // End de

            case 'in':
                $categories_by_country_domain['categories'] = array( 'All'                  => 'All Departments',
                    'Apparel'              => 'Clothing & Accessories',
                    'Appliances'           => 'Appliances',
                    'Automotive'           => 'Car & Motorbike',

                    'Baby'                 => 'Baby',
                    'Beauty'               => 'Beauty',
                    'Books'                => 'Books',
                    'DVD'                  => 'Movies & TV Shows',

                    'Electronics'          => 'Electronics',
                    'Furniture'            => 'Furniture',
                    'GiftCards'            => 'Gift Cards',
                    'Grocery'              => 'Gourmet & Specialty Foods',

                    'HealthPersonalCare'   => 'Health & Personal Care',
                    'HomeGarden'           => 'Home & Kitchen',
                    'Industrial'           => 'Industrial & Scientific',
                    'Jewelry'              => 'Jewellery',

                    'KindleStore'          => 'Kindle Store',
                    'LawnAndGarden'        => 'Lawn & Garden',
                    'Luggage'              => 'Luggage & Bags',
                    'LuxuryBeauty'         => 'Luxury Beauty',

                    'Marketplace'          => 'Marketplace',
                    'Music'                => 'Music',
                    'MusicalInstruments'   => 'Musical Instruments',
                    'OfficeProducts'       => 'Office Products',

                    'Pantry'               => 'Amazon Pantry',
                    'PCHardware'           => 'Computers & Accessories',
                    'PetSupplies'          => 'Pet Supplies',
                    'Shoes'                => 'Shoes & Handbags',

                    'Software'             => 'Software',
                    'SportingGoods'        => 'Sports, Fitness & Outdoors',
                    'Toys'                 => 'Toys & Games',
                    'VideoGames'           => 'Video Games',

                    'Watches'              => 'Watches',

                );
                break; // End in

            case 'it':
                $categories_by_country_domain['categories'] = array( 'All'                  => 'Tutte le categorie',
                    'Apparel'              => 'Abbigliamento',
                    'Automotive'           => 'Auto e Moto',

                    'Baby'                 => 'Prima infanzia',
                    'Beauty'               => 'Bellezza',
                    'Books'                => 'Libri',
                    'DVD'                  => 'Film e TV',

                    'Electronics'          => 'Elettronica',
                    'ForeignBooks'         => 'Libri in altre lingue',
                    'Garden'               => 'Giardino e giardinaggio',

                    'GiftCards'            => 'Buoni Regalo',
                    'Grocery'              => 'Alimentari e cura della casa',
                    'Handmade'             => 'Handmade',
                    'HealthPersonalCare'   => 'Cura della Persona',


                    'Industrial'           => 'Industria e Scienza',
                    'Jewelry'              => 'Gioielli',
                    'KindleStore'          => 'Kindle Store',
                    'Kitchen'              => 'Casa e cucina',

                    'Lighting'             => 'Illuminazione',


                    'Luggage'              => 'Valigeria',

                    'MobileApps'           => 'App e Giochi',
                    'MP3Downloads'         => 'Musica Digitale',
                    'Music'                => 'CD e Vinili',

                    'MusicalInstruments'   => 'Strumenti musicali e DJ',
                    'OfficeProducts'       => 'Cancelleria e prodotti per ufficio',
                    'PCHardware'           => 'Informatica',
                    'Shoes'                => 'Scarpe e borse',

                    'Software'             => 'Software',
                    'SportingGoods'        => 'Sport e tempo libero',
                    'Tools'                => 'Fai da te',

                    'Toys'                 => 'Giochi e giocattoli',
                    'VideoGames'           => 'Videogiochi',

                    'Watches'              => 'Orologi',

                );
                break; //End it

            case 'co.jp':
                $categories_by_country_domain['categories'] = array( 'All'                  => 'すべてのカテゴリー',
                    'Apparel'              => '服＆ファッション小物',
                    'Appliances'           => '大型家電',
                    'Automotive'           => 'カー・バイク用品',

                    'Baby'                 => 'ベビー&マタニティ',
                    'Beauty'               => 'コスメ',
                    'Blended'              => 'Blended',
                    'Books'                => '本',

                    'Classical'            => 'クラシック',
                    'CreditCards'          => 'クレジットカード',
                    'DVD'                  => 'DVD',

                    'Electronics'          => '家電&カメラ',
                    'ForeignBooks'         => '洋書',

                    'GiftCards'            => 'ギフト券',
                    'Grocery'              => '食品・飲料・お酒',
                    'HealthPersonalCare'   => 'ヘルス&ビューティー',
                    'Hobbies'              => 'Hobbies',


                    'HomeImprovement'      => 'DIY・工具',
                    'Industrial'           => '産業・研究開発用品',
                    'Jewelry'              => 'ジュエリー',
                    'KindleStore'          => 'Kindleストア',
                    'Kitchen'              => 'ホーム&キッチン',

                    'Marketplace'          => 'Marketplace',
                    'MobileApps'           => 'Android アプリ',
                    'MP3Downloads'         => 'デジタルミュージック',
                    'Music'                => 'ミュージック',

                    'MusicalInstruments'   => '楽器',
                    'OfficeProducts'       => '文房具・オフィス用品',
                    'PCHardware'           => 'パソコン・周辺機器',

                    'PetSupplies'          => 'ペット用品',
                    'Shoes'                => 'シューズ＆バッグ',
                    'Software'             => 'PCソフト',
                    'SportingGoods'        => 'スポーツ&アウトドア',

                    'Toys'                 => 'Toys',
                    'Video'                => 'DVD',
                    'VideoDownload'        => 'Amazon インスタント・ビデオ',
                    'VideoGames'           => 'TVゲーム',

                    'Watches'              => '腕時計',

                );
                break; //End co.jp

            case 'com.mx':
                $categories_by_country_domain['categories'] = array( 'All'                  => 'Todos los departamentos',
                    'Baby'                 => 'Bebé',
                    'Books'                => 'Libros',
                    'DVD'                  => 'Películas y Series de TV',

                    'Electronics'          => 'Electrónicos',
                    'HealthPersonalCare'   => 'Salud, Belleza y Cuidado Personal',


                    'HomeImprovement'      => 'Herramientas y Mejoras del Hogar',
                    'KindleStore'          => 'Tienda Kindle',
                    'Kitchen'              => 'Hogar y Cocina',

                    'Music'                => 'Música',

                    'OfficeProducts'       => 'Oficina y Papelería',
                    'Software'             => 'Software',
                    'SportingGoods'        => 'Deportes y Aire Libre',
                    'VideoGames'           => 'Videojuegos',


                    'Watches'              => 'Relojes',

                );

                break; //End com.mx

            case 'es':
                $categories_by_country_domain['categories'] = array( 'All'                  => 'Todos los departamentos',
                    'Apparel'              => 'Ropa y accesorios',
                    'Automotive'           => 'Coche y moto',

                    'Baby'                 => 'Bebé',
                    'Beauty'               => 'Belleza',
                    'Books'                => 'Libros',
                    'DVD'                  => 'Películas y TV',

                    'Electronics'          => 'Electrónica',
                    'ForeignBooks'         => 'Libros en idiomas extranjeros',

                    'GiftCards'            => 'Cheques regalo',
                    'Grocery'              => 'Supermercado',
                    'Handmade'             => 'Handmade',
                    'HealthPersonalCare'   => 'Salud y cuidado personal',


                    'Industrial'           => 'Industria y ciencia',
                    'Jewelry'              => 'Joyería',
                    'KindleStore'          => 'Tienda Kindle',
                    'Kitchen'              => 'Hogar',
                    'LawnAndGarden'        => 'Jardín',

                    'Lighting'             => 'Iluminación',
                    'Luggage'              => 'Equipaje',

                    'MobileApps'           => 'Apps y Juegos',
                    'MP3Downloads'         => 'Música Digital',
                    'Music'                => 'Música: CDs y vinilos',

                    'MusicalInstruments'   => 'Instrumentos musicales',
                    'OfficeProducts'       => 'Oficina y papelería',
                    'PCHardware'           => 'Informática',
                    'Shoes'                => 'Zapatos y complementos',

                    'Software'             => 'Software',
                    'SportingGoods'        => 'Deportes y aire libre',
                    'Tools'                => 'Bricolaje y herramientas',

                    'Toys'                 => 'Juguetes y juegos',
                    'VideoGames'           => 'Videojuegos',

                    'Watches'              => 'Relojes',

                );
                break; // End es

            case 'co.uk':
                $categories_by_country_domain['categories'] = array( 'All'                  => 'All Departments',
                    'Apparel'              => 'Clothing',
                    'Appliances'           => 'Large Appliances',
                    'Automotive'           => 'Car & Motorbike',

                    'Baby'                 => 'Baby',
                    'Beauty'               => 'Beauty',
                    'Blended'              => 'Blended',
                    'Books'                => 'Books',

                    'Classical'            => 'Classical',
                    'DVD'                  => 'DVD & Blu-ray',
                    'Electronics'          => 'Electronics & Photo',
                    'GiftCards'            => 'Gift Cards',

                    'Grocery'              => 'Grocery',
                    'Handmade'             => 'Handmade',
                    'HealthPersonalCare'   => 'Health & Personal Care',
                    'HomeGarden'           => 'Garden & Outdoors',


                    'Industrial'           => 'Industrial & Scientific',
                    'Jewelry'              => 'Jewellery',
                    'KindleStore'          => 'Kindle Store',
                    'Kitchen'              => 'Kitchen & Home',

                    'Lighting'             => 'Lighting',
                    'Luggage'              => 'Luggage',
                    'Marketplace'          => 'Marketplace',
                    'MobileApps'           => 'Apps & Games',

                    'MP3Downloads'         => 'Digital Music',
                    'Music'                => 'CDs & Vinyl',
                    'MusicalInstruments'   => 'Musical Instruments & DJ',
                    'OfficeProducts'       => 'Stationery & Office Supplies',

                    'Pantry'               => 'Amazon Pantry',
                    'PCHardware'           => 'Computers',
                    'PetSupplies'          => 'Pet Supplies',
                    'Shoes'                => 'Shoes & Bags',

                    'Software'             => 'Software',
                    'SportingGoods'        => 'Sports & Outdoors',
                    'Tools'                => 'DIY & Tools',
                    'Toys'                 => 'Toys & Games',

                    'UnboxVideo'           => 'Amazon Instant Video',
                    'VHS'                  => 'VHS',
                    'VideoGames'           => 'PC & Video Games',
                    'Watches'              => 'Watches',

                );


                break; //End co.uk

            case 'com':
                $categories_by_country_domain['categories'] = array( 'All'                  => 'All Departments',
                    'Appliances'           => 'Appliances',
                    'ArtsAndCrafts'        => 'Arts, Crafts & Sewing',
                    'Automotive'           => 'Automotive',

                    'Baby'                 => 'Baby',
                    'Beauty'               => 'Beauty',
                    'Blended'              => 'Blended',
                    'Books'                => 'Books',

                    'Collectibles'         => 'Collectibles & Fine Arts',
                    'Electronics'          => 'Electronics',
                    'Fashion'              => 'Clothing, Shoes & Jewelry',
                    'FashionBaby'          => 'Clothing, Shoes & Jewelry - Baby',

                    'FashionBoys'          => 'Clothing, Shoes & Jewelry - Boys',
                    'FashionGirls'         => 'Clothing, Shoes & Jewelry - Girls',
                    'FashionMen'           => 'Clothing, Shoes & Jewelry - Men',
                    'FashionWomen'         => 'Clothing, Shoes & Jewelry - Women',

                    'GiftCards'            => 'Gift Cards',
                    'Grocery'              => 'Grocery & Gourmet Food',
                    'Handmade'             => 'Handmade',
                    'HealthPersonalCare'   => 'Health & Personal Care',


                    'HomeGarden'           => 'Home & Kitchen',
                    'Industrial'           => 'Industrial & Scientific',
                    'KindleStore'          => 'Kindle Store',
                    'LawnAndGarden'        => 'Patio, Lawn & Garden',

                    'Luggage'              => 'Luggage & Travel Gear',
                    'Magazines'            => 'Magazine Subscriptions',
                    'Marketplace'          => 'Marketplace',
                    'Merchants'            => 'Merchants',


                    'MobileApps'           => 'Apps & Games',
                    'Movies'               => 'Movies & TV',
                    'MP3Downloads'         => 'Digital Music',
                    'Music'                => 'CDs & Vinyl',

                    'MusicalInstruments'   => 'Musical Instruments',
                    'OfficeProducts'       => 'Office Products',
                    'Pantry'               => 'Prime Pantry',
                    'PCHardware'           => 'Computers',

                    'PetSupplies'          => 'Pet Supplies',
                    'Software'             => 'Software',
                    'SportingGoods'        => 'Sports & Outdoors',
                    'Tools'                => 'Tools & Home Improvement',

                    'Toys'                 => 'Toys & Games',
                    'UnboxVideo'           => 'Amazon Instant Video',
                    'Vehicles'             => 'Vehicles',
                    'VideoGames'           => 'Video Games',

                    'Wine'                 => 'Wine',
                    'Wireless'             => 'Cell Phones & Accessories',

                );
                break; // End com

        } // End Switch
        return $categories_by_country_domain;
    }
    public function wpas_products_import_search(){
        if (isset($_POST['page_num'])) {
            $page_num = intval($_POST['page_num']);
        } else {
            $page_num = intval(get_option('acl_wpas_product_page_number'));
        }
        $processed_products = $this->process_response(wpas_clean($_POST['data']));
        if(count($processed_products)>0){
            $html = $this->import_products_display($processed_products);
        }else{
            $html = "<p style=>".__('No Products Found!','wp-amazon-shop')."</p>";
        }

        echo wp_send_json(array('keyword' => sanitize_text_field($_POST['keyword']), 'html' => $html, 'page_num' => intval($page_num + 1), 'product_num' => count($processed_products)));
        wp_die();
    }

    private function process_response($items = null) {
        $item_index = 0;
        $products = array();
        foreach ($items as $item) {
            $products[$item_index]['ASIN'] = $item['ASIN'];
            $products[$item_index]['Title'] = $item['Title'];
            $products[$item_index]['Price'] = isset($item['Price']) ? $item['Price'] : '';
            $products[$item_index]['ListPrice'] = isset($item['ListPrice']) ? $item['ListPrice'] : '';
            $products[$item_index]['ImageUrl'] = isset($item['ImageUrl']) ? $item['ImageUrl'] : '';
            $products[$item_index]['DetailPageURL'] = isset($item['DetailPageURL']) ? $item['DetailPageURL'] : '';
            $products[$item_index]['Rating'] = isset($item['Rating']) ? $item['Rating'] : '';
            $products[$item_index]['TotalReviews'] = isset($item['TotalReviews']) ? $item['TotalReviews'] : '';
            $products[$item_index]['Subtitle'] = isset($item['Subtitle']) ? $item['Subtitle'] : '';
            $products[$item_index]['IsPrimeEligible'] = isset($item['IsPrimeEligible']) ? $item['IsPrimeEligible'] : '';
            $item_index++;
        }
        return $products;
    }

    private function import_products_display($products)
    {
        $affiliate_tag=get_option('acl_wpas_amazon_associate_tag');
        ob_start();
        foreach ($products as $product) {
            $import_data=base64_encode(json_encode($product));
            ?>
            <tr>
                <td class="wpas-import-thumb">
                    <div class="amazon-product-thumb">
                        <?php if(isset($product['IsPrimeEligible']) && $product['IsPrimeEligible']=='1'){?>
                            <span class="amazon-product-prime"></span>
                        <?php } ?>
                        <img src="<?php echo $product['ImageUrl']; ?>" alt="Product">
                    </div>
                </td>
                <td class="wpas-import-title" >
                    <p class="field_text"><?php echo str_replace("\'", "", $product['Title']); ?> </p>
                    <p class="field_text"><a href="<?php echo esc_url_raw($product['DetailPageURL']); ?>?tag=<?php echo $affiliate_tag;  ?>" target="_blank" class="link_to_source product_url"><?php _e('Amazon Product page', 'wp-amazon-shop') ?></a></p>
                <td class="wpas-import-asin" > <strong><?php echo $product['ASIN'];?></strong></td>
                <td class="wpas-import-price">
                    <p class="field_text"><?php echo $product['Price'];?></strong></p>
                </td>
                <td class="wpas-import-rating-review" >
                    <?php if(isset($product['Rating']) && $product['Rating']!=""){
                        if( strpos( $product['Rating'], '.0' ) !== false) {
                            $formatted_rating=str_replace(".0","",$product['Rating']);
                        }else{
                            $formatted_rating=str_replace(".","-",$product['Rating']);
                        }

                        $rating_class="a-star-".$formatted_rating;
                        ?>
                        <div class="amazon-product-rating" data-product-asin="<?php echo $product['ASIN']; ?>">
                            <i class="a-icon a-icon-star <?php echo $rating_class?>"><span class="a-icon-alt"><?php echo $product['Rating']; ?> <?php _e('out of 5 stars', 'wp-amazon-shop') ?></span></i>
                            <p>( <a href="<?php echo esc_url_raw($product['DetailPageURL']); ?>?tag=<?php echo $affiliate_tag;  ?>#dp-summary-see-all-reviews" target="_blank"><?php echo $product['TotalReviews'] ;?></a>)</p>
                        </div>
                    <?php } ?>
                </td>
                <td class="wpas-import-action">
                    <button type="button" wpas-import-data="<?php echo $import_data;?>" class="button button-primary button-small wpas-item-import-btn"><?php _e('Import to WooCommerce', 'wp-amazon-shop') ?></button>
                    <p class="wpas-import-success-action"></p>
                </td>
            </tr>
            <?php
        }
        $content = ob_get_clean();
        return $content;
    }

    /***
     * Import as basic
     */
    public function wpas_import_product_from_amazon() {
        global $wp_error;
        $response = array();
        $sku = sanitize_text_field( $_POST['sku']);
        $title = str_replace("\'", "", sanitize_text_field($_POST['title']));
        $price = wpas_clean($_POST['price']);
        $product_url = esc_url_raw( $_POST['amazon_url']);
        $image_url = esc_url_raw($_POST['image']);
        $product_id = wc_get_product_id_by_sku($sku);
        if ($product_id) {
            $response['status'] = 200;
            $response['success_action'] ='<a href="'.get_admin_url().'/post.php?post='.$product_id.'&action=edit" target="_blank">'.__('Edit','wp-amazon-shop').'</a> | <a href="'.get_permalink( $product_id ).'" target="_blank">'.__('View','wp-amazon-shop').'</a>';
            $response['message'] = __('Already Imported','wp-amazon-shop');
        } else {
            if($this->wpas_capabitlity()){
                //inserting functionality
                $post = array(
                    'post_author' => 1,
                    'post_content' => $title . '<br><img src="' . $image_url . '" alt="' . $title . '"><br> <br>  <a href="' . esc_url_raw($product_url) . '"  target="_blank">Product Link</a>',
                    'post_status' => "publish",
                    'post_title' => $title,
                    'post_parent' => '',
                    'post_type' => "product",
                );

                //Create post
                $post_id = wp_insert_post($post, $wp_error);
                //Update term & meta.
                wp_set_object_terms($post_id, wpas_clean($_POST['importing_category']), 'product_cat');
               // wp_set_object_terms($post_id, get_option('acl_wpas_category_name'), 'product_cat');
                wp_set_object_terms($post_id, 'simple', 'product_type');

                update_post_meta($post_id, '_visibility', 'visible');
                update_post_meta($post_id, '_stock_status', 'instock');
                update_post_meta($post_id, 'total_sales', '0');
                update_post_meta($post_id, '_virtual', 'yes');
                update_post_meta($post_id, '_purchase_note', "");
                update_post_meta($post_id, '_featured', "no");
                update_post_meta($post_id, '_weight', "");
                update_post_meta($post_id, '_length', "");
                update_post_meta($post_id, '_width', "");
                update_post_meta($post_id, '_height', "");
                update_post_meta($post_id, '_sku', $sku);
                update_post_meta($post_id, '_product_attributes', array());
                update_post_meta($post_id, '_sale_price_dates_from', "");
                update_post_meta($post_id, '_sale_price_dates_to', "");
                update_post_meta($post_id, '_price', $price);
                update_post_meta($post_id, '_sold_individually', "");
                update_post_meta($post_id, '_manage_stock', "no");
                update_post_meta($post_id, '_backorders', "no");
                update_post_meta($post_id, '_stock', "");
                    if ($post_id) {
                        // Add Featured Image to Post
                        $big_image_url = str_replace('_SL160_', '_SL500_', $image_url);
                        $image_url_array = explode("/", $big_image_url);
                        $image_name = end($image_url_array);
                        $upload_dir = wp_upload_dir(); // Set upload folder
                        $image_data = file_get_contents($big_image_url); // Get image data
                        $unique_file_name = wp_unique_filename($upload_dir['path'], $image_name); // Generate unique name
                        $filename = basename($unique_file_name); // Create image file name

                        // Check folder permission and define file location
                        if (wp_mkdir_p($upload_dir['path'])) {
                            $file = $upload_dir['path'] . '/' . $filename;
                        } else {
                            $file = $upload_dir['basedir'] . '/' . $filename;
                        }

                        // Create the image  file on the server
                        file_put_contents($file, $image_data);

                        // Check image file type
                        $wp_filetype = wp_check_filetype($filename, null);

                        // Set attachment data
                        $attachment = array(
                            'post_mime_type' => $wp_filetype['type'],
                            'post_title' => sanitize_file_name($filename),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        );

                        // Create the attachment
                        $attach_id = wp_insert_attachment($attachment, $file, $post_id);

                        // Include image.php
                        require_once(ABSPATH . 'wp-admin/includes/image.php');

                        // Define attachment metadata
                        $attach_data = wp_generate_attachment_metadata($attach_id, $file);

                        // Assign metadata to attachment
                        wp_update_attachment_metadata($attach_id, $attach_data);

                        // And finally assign featured image to post
                        $set_thumb = set_post_thumbnail($post_id, $attach_id);
                        if ($set_thumb != false) {
                            $response['status'] = 200;
                            $response['success_action'] = '<a href="' . get_admin_url() . '/post.php?post=' . $post_id . '&action=edit" target="_blank">' . __('Edit', 'wp-amazon-shop') . '</a> | <a href="' . get_permalink($post_id) . '" target="_blank">' . __('View', 'wp-amazon-shop') . '</a>';
                            $response['message'] = __('Imported successfully', 'wp-amazon-shop');
                        } else {
                            $response['status'] = 200;
                            $response['success_action'] = '<a href="' . get_admin_url() . '/post.php?post=' . $post_id . '&action=edit" target="_blank">' . __('Edit', 'wp-amazon-shop') . '</a> | <a href="' . get_permalink($post_id) . '" target="_blank">' . __('View', 'wp-amazon-shop') . '</a>';
                            $response['message'] = __('Imported content but image successfully', 'wp-amazon-shop');
                        }
                        if(get_option('acl_wpas_namano_koto')!=""){
                            $koto=get_option('acl_wpas_namano_koto');$koto++;
                            update_option('acl_wpas_namano_koto',$koto);
                        }

                }else{
                    $response['status'] = 300;
                    $response['success_action'] ='';
                    $response['message'] =  __('Sorry Try again','wp-amazon-shop');
                }
            } else {
                $response['status'] = 300;
                $response['success_action'] = '<a style="color: orangered" href="'.esc_url_raw('https://www.wpamazonshop.com/').'" target="_blank">'.__('Upgrade to Pro for more', 'wp-amazon-shop').'</a> ';
                $response['message'] = __('20 Products imported per day!', 'wp-amazon-shop');

            }
        }
        echo wp_send_json($response);
        wp_die();
    }

    /***
     *
     * Extended Import
     */
    private function import_operation($product){
        $response = array();
        $has_variation=false;
        global $wp_error;
        $product_id = wc_get_product_id_by_sku($product['ASIN']);
        if ($product_id) {
            $edit_post = array(
                'ID' => $product_id,
                'post_title' => $product['Title'],
                'post_content' =>wp_kses_post(base64_decode($product['Description'])),
            );
            // Update the post into the database
            wp_update_post($edit_post);
            $response['status'] = 200;
            $response['sku'] = $product['ASIN'];
            $response['has_variation'] = false;
            $response['message'] =  __('Product was imported and updated now', 'wp-amazon-shop').'<a href="' . get_admin_url() . '/post.php?post=' . $product_id . '&action=edit" target="_blank">' . __('Edit', 'wp-amazon-shop') . '</a> | <a href="' . get_permalink($product_id) . '" target="_blank">' . __('View', 'wp-amazon-shop') . '</a>';
        } else {
            if($this->wpas_capabitlity()) {
                //inserting functionality
                $post = array(
                    'post_author' => 1,
                    'post_content' => wp_kses_post($product['Description']),
                    'post_status' => 'publish',
                    'ping_status' => 'closed',
                    'post_title' => $product['Title'],
                    'post_parent' => '',
                    'post_type' => "product",
                );

                //Create post
                $post_id = wp_insert_post($post, $wp_error);
                //Update term & meta.
                //get_option('acl_wpas_category_name')
                if (is_array($product['category']) && !empty($product['category'])) {
                    wp_set_object_terms($post_id, end($product['category']), 'product_cat');
                } else if ($product['category'] != "") {
                    wp_set_object_terms($post_id, $product['category'], 'product_cat');
                } else {
                    wp_set_object_terms($post_id, 'Uncategorized', 'product_cat');
                }
                //setup meta
                wp_set_object_terms($post_id, 'simple', 'product_type');
                update_post_meta($post_id, '_visibility', 'visible');
                update_post_meta($post_id, '_stock_status', 'instock');
                update_post_meta($post_id, 'total_sales', '0');
                update_post_meta($post_id, '_purchase_note', "");
                update_post_meta($post_id, '_featured', "no");
                update_post_meta($post_id, '_virtual', 'no');
                update_post_meta($post_id, '_length', "");
                update_post_meta($post_id, '_width', "");
                update_post_meta($post_id, '_height', "");
                update_post_meta($post_id, '_sku', $product['ASIN']);
                update_post_meta($post_id, '_sale_price_dates_from', "");
                update_post_meta($post_id, '_sale_price_dates_to', "");
                update_post_meta($post_id, '_price', $product['price']);
                update_post_meta($post_id, '_regular_price', $product['price']);
                update_post_meta($post_id, '_sold_individually', "");
                update_post_meta($post_id, '_manage_stock', "no");
                update_post_meta($post_id, '_backorders', "no");
                update_post_meta($post_id, 'wpas_import', 'import');
                if ($post_id) {
                    $raw_gallery = unserialize(base64_decode($product['gallery']));
                    $gallery_images = $raw_gallery['large'];
                    //setting high resolution thumb
                    if (isset($gallery_images[0]) && $gallery_images[0] != "") {
                        $product['thumb'] = $gallery_images[0];
                    }
                    // And finally assign featured image to post
                    if (!empty($product['thumb'])) {
                        if (strlen($product['Title']) > 15) {
                            $image_name = substr($product['Title'], 0, 15) . '.jpg';
                        } else {
                            $image_name = $product['Title'] . '.jpg';
                        }

                        if (isset($product['thumb']) && !empty($product['thumb'])) {
                            $thumb_id = ACL_WPAS_iKits::insert_single_image($product['thumb'], $image_name, $post_id);
                            $set_thumb = set_post_thumbnail($post_id, $thumb_id);
                        }

                    }
                    if (!empty($product['gallery'])) {
                        $gallery_ids = ACL_WPAS_iKits::import_image_gallery($product['gallery'], $product['Title'], $post_id);
                        if ($gallery_ids != "") {
                            update_post_meta($post_id, '_product_image_gallery', $gallery_ids);
                        }
                    }
                    if (isset($set_thumb) && $set_thumb != false) {
                        $response['status'] = 200;
                        $response['sku'] = $product['ASIN'];
                        $response['has_variation'] = $has_variation;
                        $response['message'] = __('Imported Successfully', 'wp-amazon-shop') . '<a href="' . get_admin_url() . '/post.php?post=' . $post_id . '&action=edit" target="_blank">' . __('Edit', 'wp-amazon-shop') . '</a> | <a href="' . get_permalink($post_id) . '" target="_blank">' . __('View', 'wp-amazon-shop') . '</a>';
                    } else {
                        $response['status'] = 200;
                        $response['sku'] = $product['ASIN'];
                        $response['has_variation'] = $has_variation;
                        $response['message'] = __('Imported content but image successfully', 'wp-amazon-shop') . '<a href="' . get_admin_url() . '/post.php?post=' . $post_id . '&action=edit" target="_blank">' . __('Edit', 'wp-amazon-shop') . '</a> | <a href="' . get_permalink($post_id) . '" target="_blank">' . __('View', 'wp-amazon-shop') . '</a>';
                    }
                    if(get_option('acl_wpas_namano_koto')!=""){
                        $koto=get_option('acl_wpas_namano_koto');$koto++;
                        update_option('acl_wpas_namano_koto',$koto);
                    }
                } else {
                    $response['status'] = 300;
                    $response['sku'] = $product['ASIN'];
                    $response['message'] = __('Error occurred! Try again', 'wp-amazon-shop');
                }
            }else{
                $response['status'] = 300;
                $response['sku'] = $product['ASIN'];
                $response['message'] = __('20 Products can be imported per day, <br>Need to import UNLIMITED,VARIATIONS,GALLERY etc <a href="'.esc_url_raw('https://www.wpamazonshop.com/').'" target="_blank">Upgrade to Pro</a> ', 'wp-amazon-shop');
            }
        }
        return $response;
    }
    private function price_by_asin($asin){
        //$products=array();
        $product=array();
        //Basic info url
        $basic_url="http://ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=GB&ad_type=product_link&marketplace=amazon&region=GB&tracking_id=".get_option('acl_wpas_amazon_associate_tag')."&asins=";
        if(get_option('acl_wpas_amazon_country')=='com'){
            $basic_url="http://ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&ad_type=product_link&marketplace=amazon&region=US&tracking_id=".get_option('acl_wpas_amazon_associate_tag')."&asins=";
        }else if(get_option('acl_wpas_amazon_country')=='co.uk'){
            $basic_url="http://ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=GB&ad_type=product_link&marketplace=amazon&region=GB&tracking_id=".get_option('acl_wpas_amazon_associate_tag')."&asins=";
        }else if(get_option('acl_wpas_amazon_country')=='de'){
            $basic_url="http://ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=DE&ad_type=product_link&marketplace=amazon&region=DE&tracking_id=".get_option('acl_wpas_amazon_associate_tag')."&asins=";
        }else if(get_option('acl_wpas_amazon_country')=='fr'){
            $basic_url="http://ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=FR&ad_type=product_link&marketplace=amazon&region=FR&tracking_id=".get_option('acl_wpas_amazon_associate_tag')."&asins=";
        }else if(get_option('acl_wpas_amazon_country')=='co.jp'){
            $basic_url="http://ws-fe.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=JP&ad_type=product_link&marketplace=amazon&region=JP&tracking_id=".get_option('acl_wpas_amazon_associate_tag')."&asins=";
        }else if(get_option('acl_wpas_amazon_country')=='ca'){
            $basic_url="http://ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=CA&ad_type=product_link&marketplace=amazon&region=CA&tracking_id=".get_option('acl_wpas_amazon_associate_tag')."&asins=";
        }
        //Not working...
        else if(get_option('acl_wpas_amazon_country')=='com.mx'){
            $basic_url="http://ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=MX&ad_type=product_link&marketplace=amazon&region=MX&tracking_id=".get_option('acl_wpas_amazon_associate_tag')."&asins=";
        }
        //Not working...
        else if(get_option('acl_wpas_amazon_country')=='com.br'){
            $basic_url="http://ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=BR&ad_type=product_link&marketplace=amazon&region=BR&tracking_id=".get_option('acl_wpas_amazon_associate_tag')."&asins=";
        }else if(get_option('acl_wpas_amazon_country')=='it'){
            $basic_url="http://ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=IT&ad_type=product_link&marketplace=amazon&region=IT&tracking_id=".get_option('acl_wpas_amazon_associate_tag')."&asins=";
        }else if(get_option('acl_wpas_amazon_country')=='in'){
            $basic_url="http://ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=IN&ad_type=product_link&marketplace=amazon&region=IN&tracking_id=".get_option('acl_wpas_amazon_associate_tag')."&asins=";
        }else if(get_option('acl_wpas_amazon_country')=='es'){
            $basic_url="http://ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=ES&ad_type=product_link&marketplace=amazon&region=ES&tracking_id=".get_option('acl_wpas_amazon_associate_tag')."&asins=";
        }
        else if(get_option('acl_wpas_amazon_country')=='cn'){
            $basic_url="http://ws-cn.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=CN&ad_type=product_link&marketplace=amazon&region=CN&tracking_id=".get_option('acl_wpas_amazon_associate_tag')."&asins=";
        }
        $proudct_url=$basic_url.$asin;
        // Create a DOM object
        // latest 2.0
        //$doc = new HtmlWeb(); 
        //$html = $doc->load($proudct_url);
        //Stable 
        $html = new simple_html_dom();
        $html->load($proudct_url);//Search Result

        if(gettype($html->find('span.price', 0)) != "NULL") {
            $price_raw =$html->find('span.price', 0)->innertext;
        }else{
            $price_raw ="";
        }
        return $price_raw;
    }
    private function wpas_capabitlity(){
        $flag=false;
        $somoy_now=strtotime('now UTC');
        $shoro=get_option('acl_wpas_namano_shoro');
        if($somoy_now - $shoro <= DAY_IN_SECONDS){
            $koto=get_option('acl_wpas_namano_koto');
            if($koto <= ACL_WPAS_PRODUCT_PERMIT){
                $flag=true;
            }
        }else{
           update_option('acl_wpas_namano_shoro',$somoy_now);
           update_option('acl_wpas_namano_koto',0);
            $flag=true;
        }
        return $flag;
    }



} // End Class
//Loading Import class init hook
$wpas_product_import = new ACL_Amazon_Product_Import();
$wpas_product_import->initiate_hooks();
?>