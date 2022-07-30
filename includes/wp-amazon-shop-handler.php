<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
//use html parser.
//use simplehtmldom\HtmlWeb;

class ACL_Amazon_Product_Handler
{

    public function initiate_hooks()
    {
        add_action('wp_ajax_wpas_search_by_keyword', array($this, 'wpas_search_by_keyword'));
        add_action('wp_ajax_nopriv_wpas_search_by_keyword', array($this, 'wpas_search_by_keyword'));
        add_action('wp_ajax_wpas_shortcode_by_asin', array($this, 'wpas_shortcode_by_asin'));
        add_action('wp_ajax_nopriv_wpas_shortcode_by_asin', array($this, 'wpas_shortcode_by_asin'));
        add_action('wp_ajax_wpas_comparision_by_asin', array($this, 'wpas_comparision_by_asin'));
        add_action('wp_ajax_nopriv_wpas_comparision_by_asin', array($this, 'wpas_comparision_by_asin'));
    }
    public function wpas_shortcode_by_asin(){
        $html="";
         $asins =sanitize_text_field($_POST['asin']);
         $asin_numbers=explode(',',$asins);
         $products=array();
         $product_counter=0;
         if(count($asin_numbers)>0){
             foreach ($asin_numbers as $asin){
                 $product=$this->basic_product_by_asin(trim($asin));
                 if($product['ASIN']!="" && $product['Title']!=""){
                     $products[]=$product;
                     if($product_counter>=3){
                         break;
                     }
                     $product_counter++;
                 }
             }
         }
        /*if(count($products)>0){
             $html .= $this->products_display($products);
         }else{
             $html .= "<p style=>".__('No Products Found!','wp-amazon-shop')."</p>";
         }*/
         echo json_encode($products);
        wp_die();
    }
    public function wpas_comparision_by_asin(){
        $html="";
        $asins = sanitize_text_field( $_POST['asin'] );
        $asin_numbers=explode(',',$asins);
        $products=array();
        $product_counter=0;
        if(count($asin_numbers)>0){
            foreach ($asin_numbers as $asin){
                $product=$this->basic_product_by_asin(trim($asin));
                if( isset($product['ASIN']) && $product['ASIN']!="" && isset($product['Title']) && $product['Title']!=""){
                    $products[]=$product;
                    if($product_counter>=2){
                        break;
                    }
                    $product_counter++;
                }
            }
        }
        /*if(count($products)>0){
            $html .=$this->comparision_display($products);
        }else{
            $html .= "<p style=>".__('No Products Found!','wp-amazon-shop')."</p>";
        }*/
        echo json_encode($products);
        wp_die();
    }
    public function wpas_search_by_keyword()
    {
         
        if (isset($_POST['page_num'])) {
             $page_num = intval($_POST['page_num']);
         } else {
             $page_num = intval(get_option('acl_wpas_product_page_number'));
         }
         $processed_products = $this->process_response(wpas_clean($_POST['data']));
         if(count($processed_products)>0){
            $html = $this->products_display($processed_products);
         }else{
            $html = "<p style=>".__('No Products Found!','wp-amazon-shop')."</p>";
         }
         
         echo wp_send_json(array('keyword' => sanitize_text_field( $_POST['keyword']), 'html' => $html, 'page_num' => intval($page_num + 1), 'product_num' => count($processed_products)));
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

    private function basic_product_by_asin($asin){
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
         //$doc = new HtmlWeb();
         // $html = $doc->load($proudct_url);
         //Stable 
         $html = new simple_html_dom();
         $html->load($proudct_url);//Search Result

         if(gettype($html->find('a#titlehref', 0)) != "NULL") {
             $title_raw =$html->find('a#titlehref', 0)->innertext;
         }else{
             $title_raw ="";
         }
         if(gettype($html->find('a#titlehref', 0)) != "NULL") {
             $url_raw =$html->find('a#titlehref', 0)->href ;
         }else{
             $url_raw ="";
         }
         if(gettype($html->find('img#prod-image', 0)) != "NULL") {
             $image_raw =str_replace('_AC_AC_SR98,95_','_AC_AC_SR160,160_',$html->find('img#prod-image', 0)->src);
             //$image_raw =$html->find('img#prod-image', 0)->src;
         }else{
             $image_raw ="";
         }
         if(gettype($html->find('span.price', 0)) != "NULL") {
             $price_raw =$html->find('span.price', 0)->innertext;
         }else{
             $price_raw ="";
         }
         if(gettype($html->find('span.prime', 0)) != "NULL") {
             $is_prime_raw =1;
         }else{
             $is_prime_raw ="";
         }
         //Build price
         if($price_raw!=""){
             $pricenocurrency  = preg_replace( '/[^.\d]/', '', $price_raw);
             $price_amount=($pricenocurrency*100);
         }
         //Build basic product
        if($title_raw!=""){
            $product['ASIN'] = $asin;
            $product['Title'] = $title_raw;
            $product['LowestNewPriceAmount'] = isset($price_amount) ? $price_amount: '';
            $product['Price'] = $price_raw;
            $product['ImageUrl'] = $image_raw;
            $product['DetailPageURL'] = $url_raw;
            $product['IsEligibleForPrime'] = $is_prime_raw;
            $product['TotalReviews'] = '';
            $product['Rating'] = '';
            //array_push($products,$product);
        }
         return $product;
    }

    private function products_display($products)
    {
        $affiliate_tag=get_option('acl_wpas_amazon_associate_tag');
        $template=get_option('acl_wpas_templates');
        ob_start();
        foreach ($products as $product) {
            if($template==1){ //Template one start
            ?>
            <div class="wpas-product-item">
                <div class="amazon-product-box">

                    <div class="amazon-product-thumb">
                        <?php if(isset($product['IsPrimeEligible']) && $product['IsPrimeEligible']=='1'){?>
                        <span class="amazon-product-prime"></span>
                        <?php } ?>
                        <img src="<?php echo $product['ImageUrl']; ?>" alt="Product">
                    </div>
                    <!-- amazon-product-thumb            -->
                    <div class="amazon-product-info">
                        <h3 title="<?php echo str_replace("\'", "", $product['Title']); ?>"><?php echo str_replace("\'", "", $product['Title']); ?> </h3>
                            <p><?php _e('Price', 'wp-amazon-shop') ?> : <?php echo $product['Price']; ?> </p>
                    </div>
                    <!-- amazon-product-info -->
                    <div class="amazon-product-action">
                        <button class="wpas-add-to-cart" type="button"
                                wpas-sku="<?php echo $product['ASIN']; ?>"
                                wpas-url="<?php echo $this->build_action_url($product['ASIN'],$product['DetailPageURL']); ?>"
                        > <?php echo (get_option('acl_wpas_buy_now_label')? get_option('acl_wpas_buy_now_label') : "Buy Now");  ?>  </button>
                    </div>
                    <?php if(isset($product['Rating']) && $product['Rating']!=""){
                        if( strpos( $product['Rating'], '.0' ) !== false) {
                            $formatted_rating=str_replace(".0","",$product['Rating']);
                        }else{
                            $formatted_rating=str_replace(".","-",$product['Rating']);
                        }

                        $rating_class="a-star-".$formatted_rating;
                        ?>
                    <div class="amazon-product-rating" data-product-asin="<?php echo $product['ASIN']; ?>">
                            <i class="a-icon a-icon-star <?php echo $rating_class?>"><span class="a-icon-alt"><?php echo $product['Rating']; ?> out of 5 stars</span></i>
                            <p>( <a href="<?php echo $product['DetailPageURL']; ?>?tag=<?php echo $affiliate_tag;  ?>#dp-summary-see-all-reviews" target="_blank"><?php echo $product['TotalReviews'] ;?></a>)</p>
                     </div>
                    <?php } ?>
                </div>
                <!-- amazon-product-box -->
            </div>
            <?php
            }
            if($template==2){  //Template two start
                ?>
                <div class="wpas-product-item">
                    <div class="amazon-product-box">

                        <div class="amazon-product-thumb">
                            <?php if(isset($product['IsPrimeEligible']) && $product['IsPrimeEligible']=='1'){?>
                            <span class="amazon-product-prime"></span>
                            <?php } ?>
                            <img src="<?php echo $product['ImageUrl']; ?>" alt="Product">
                        </div>
                        <!-- amazon-product-thumb            -->
                        <div class="amazon-product-info">
                            <h3 title="<?php echo str_replace("\'", "", $product['Title']); ?>"><?php echo str_replace("\'", "", $product['Title']); ?> </h3>
                                <p><?php _e('Price', 'wp-amazon-shop') ?> : <?php echo $product['Price']; ?> </p>

                                <?php if(isset($product['Rating']) && $product['Rating']!=""){
                            if( strpos( $product['Rating'], '.0' ) !== false) {
                                $formatted_rating=str_replace(".0","",$product['Rating']);
                            }else{
                                $formatted_rating=str_replace(".","-",$product['Rating']);
                            }

                            $rating_class="a-star-".$formatted_rating;
                            ?>
                        <div class="amazon-product-rating" data-product-asin="<?php echo $product['ASIN']; ?>">
                            <div><i class="a-icon a-icon-star <?php echo $rating_class?>"><span class="a-icon-alt"><?php echo $product['Rating']; ?> out of 5 stars</span></i></div> 
                            <div><a href="<?php echo $product['DetailPageURL']; ?>?tag=<?php echo $affiliate_tag;  ?>#dp-summary-see-all-reviews" target="_blank">(<?php echo $product['TotalReviews'] ;?>)</a></div>
                        </div>
                        <?php } ?>
                        </div>
                        <!-- amazon-product-info -->
                        <div class="amazon-product-action">
                            <button class="wpas-add-to-cart" type="button"
                                    wpas-sku="<?php echo $product['ASIN']; ?>"
                                    wpas-url="<?php echo $this->build_action_url($product['ASIN'],$product['DetailPageURL']); ?>"> 
                                    <span class="wpas-btn-text"><?php echo (get_option('acl_wpas_buy_now_label')? get_option('acl_wpas_buy_now_label') : "Buy Now");  ?> </span>
                                    <span class="wpas-btn-icon">
                                        
                                    <?xml version="1.0" ?><svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><defs><style>.cls-1{fill:none;}</style></defs><title/><g data-name="Layer 2" id="Layer_2"><path d="M23.52,29h-15a5.48,5.48,0,0,1-5.31-6.83L6.25,9.76a1,1,0,0,1,1-.76H24a1,1,0,0,1,1,.7l3.78,12.16a5.49,5.49,0,0,1-.83,4.91A5.41,5.41,0,0,1,23.52,29ZM8,11,5.11,22.65A3.5,3.5,0,0,0,8.48,27h15a3.44,3.44,0,0,0,2.79-1.42,3.5,3.5,0,0,0,.53-3.13L23.28,11Z"/><path d="M20,17a1,1,0,0,1-1-1V8a3,3,0,0,0-6,0v8a1,1,0,0,1-2,0V8A5,5,0,0,1,21,8v8A1,1,0,0,1,20,17Z"/></g><g id="frame"><rect class="cls-1" height="32" width="32"/></g></svg>
                                    </span>
                                    
                            </button>
                          

                        </div>
                       
                    </div>
                    <!-- amazon-product-box -->
                </div>
                <?php
            }
        }
        $content = ob_get_clean();
        return $content;
    }

    private function comparision_display($products)
    {
        ob_start();
        if (count($products) > 0) {
            ?>
            <div class="wpas-comparison-shortcode-inner">
                <div class="wpas-comparison-item wpas-comparison-unite">
                    <div class="wpas-comparison-item-inner">
                        <div class="wpas-comparison-base">
                            <p><?php _e('Product Image', 'wp-amazon-shop') ?></p>
                        </div>
                        <div class="wpas-comparison-base"><h4><?php _e('Product Name', 'wp-amazon-shop') ?></h4></div>
                        <div class="wpas-comparison-base"><p><?php _e('Unit Price', 'wp-amazon-shop') ?></p></div>
                        <div class="wpas-comparison-base"><p><?php _e('Availability', 'wp-amazon-shop') ?></p></div>
                        <div class="wpas-comparison-base wpas-comparison-product-action">
                            <p><?php _e('Buy Now', 'wp-amazon-shop') ?></p></div>
                    </div>
                    <!--wpas-comparison-item-inner-->
                </div>
                <!-- wpas-comparison-item-->
                <?php
                foreach ($products as $product) {
                    ?>
                    <div class="wpas-comparison-item">
                        <div class="wpas-comparison-item-inner">
                            <div class="wpas-comparison-base"><img
                                        src="<?php echo $product['ImageUrl']; ?>"
                                        alt="<?php echo $product['Title']; ?>">
                            </div>
                            <div class="wpas-comparison-base"><h4 title="<?php echo str_replace("\'", "", $product['Title']); ?>"><a href="<?php echo $product['DetailPageURL']; ?>"  target="_blank"><?php echo str_replace("\'", "", $product['Title']) ?> </a></h4>
                            </div>
                            <div class="wpas-comparison-base">
                                <p><?php echo $product['Price']; ?> </p>
                            </div>
                            <div class="wpas-comparison-base"><p><?php _e('In Stock', 'wp-amazon-shop'); ?></p></div>

                            <div class="wpas-comparison-base wpas-comparison-product-action">
                                <button class="wpas-add-to-cart" type="button"
                                        wpas-sku="<?php echo $product['ASIN']; ?>"
                                        wpas-url="<?php echo $this->build_action_url($product['ASIN'],$product['DetailPageURL']); ?>"
                                > <?php echo (get_option('acl_wpas_buy_now_label')? get_option('acl_wpas_buy_now_label') : "Buy Now");  ?>  </button>
                            </div>
                        </div>
                        <!--wpas-comparison-item-inner-->
                    </div>
                    <?php
                }
                ?>
                <!-- wpas-comparison-item-->
            </div>
            <!--wpas-comparison-shortcode-inner-->
            <?php
        } else {
            echo '<p>' . __('No Products Found', 'wp-amazon-shop') . '</p>';
        }
        $content = ob_get_clean();
        return $content;
    }

    private function build_action_url( $asin,$detail_url ){
        $country=get_option('acl_wpas_amazon_country');
        $affiliate_tag=get_option('acl_wpas_amazon_associate_tag');
        $url = $detail_url.'?tag=' . $affiliate_tag;
        if( get_option('acl_wpas_enable_direct_cart') == 'on' ){
            $url = 'https://www.amazon.' . $country . '/gp/aws/cart/add.html?AssociateTag=' . $affiliate_tag . '&ASIN.1='.$asin.'&Quantity.1=1';
        }
        return $url;
    }




} // End Class
function wpas_product_hanlder_init()
{
    $wpas_product_hanlder = new ACL_Amazon_Product_Handler();
    $wpas_product_hanlder->initiate_hooks();
}

add_action('plugins_loaded', 'wpas_product_hanlder_init');
?>