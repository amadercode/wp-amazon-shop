<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ACL_Amazon_Shop_Feedback
{

    /**
     * The version.
     * @var     string
     * @access  public
     * @since   2.0.5
     */
    public $_token;
    /**
     * The token.
     * @var     string
     * @access  public
     * @since   2.0.5
     */
    public $_version;

    /**
     * The File.
     * @var     string
     * @access  public
     * @since   2.0.5
     */
    public $file;
    /**
     * The assets.
     * @var     string
     * @access  public
     * @since   2.0.5
     */
    public $_assets_url;

    /**
     * @since 2.0.5
     * @access public
     */
    public function initialize()
    {
        $this->_token = 'acl_wpas';
        $this->_version = ACL_WPAS_VERSION;
        $this->file = ACL_WPAS_PLUGIN_FILE;
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));
        add_action('current_screen', function () {
            if (!$this->is_plugins_screen()) {
                return;
            }
            add_action('admin_enqueue_scripts', array($this, 'enqueue_feedback_scripts'));
        });
        //add_action( 'admin_enqueue_scripts', array($this, 'enqueue_feedback_scripts') );
        // Ajax.
        add_action('wp_ajax_wpas_deactivate_feedback', array($this, 'deactivate_feedback'));

    }

    /**
     * Enqueue feedback dialog scripts.
     *
     * Registers the feedback dialog scripts and enqueues them.
     *
     * @since 1.0.0
     * @access public
     */
    public function enqueue_feedback_scripts()
    {
        add_action('admin_footer', array($this, 'deactivate_feedback_dialog'));
        wp_register_script($this->_token . '-admin-feedback', esc_url($this->assets_url) . 'js/admin-feedback.js', array('jquery'), $this->_version);
        wp_enqueue_script($this->_token . '-admin-feedback');
        wp_localize_script($this->_token . '-admin-feedback', 'wpas_feedback_object',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
            )
        );
    }

    /**
     * Print deactivate feedback dialog.
     *
     * Display a dialog box to ask the user why he deactivated WP Amazon Shop.
     *
     * Fired by `admin_footer` filter.
     *
     * @since 2.0.5
     * @access public
     */
    public function deactivate_feedback_dialog()
    {
        $deactivate_reasons = [
            'no_longer_needed' => [
                'title' => __('I no longer need the plugin', 'wp-amazon-shop'),
                'input_placeholder' => '',
            ],
            'found_a_better_plugin' => [
                'title' => __('I found a better plugin', 'wp-amazon-shop'),
                'input_placeholder' => __('Please share which plugin', 'wp-amazon-shop'),
            ],
            'couldnt_get_the_plugin_to_work' => [
                'title' => __('I couldn\'t get the plugin to work', 'wp-amazon-shop'),
                'input_placeholder' => '',
            ],
            'temporary_deactivation' => [
                'title' => __('It\'s a temporary deactivation', 'wp-amazon-shop'),
                'input_placeholder' => '',
            ],
            'wp_amazon_shop_pro' => [
                'title' => __('I have WP Amazon Shop Pro', 'wp-amazon-shop'),
                'input_placeholder' => '',
            ],
            'other' => [
                'title' => __('Other', 'wp-amazon-shop'),
                'input_placeholder' => __('Please share the reason', 'wp-amazon-shop'),
            ],
        ];

        ?>
        <div style="display: none;" id="wpas-deactivate-feedback-dialog-wrapper">

            <div id="wpas-deactivate-feedback-dialog-inner" class="wpas-deactivate-feedback-dialog-inner">
                <button id="wpas-deactivate-close-btn">X</button>
                <div id="wpas-deactivate-feedback-dialog-header">
                    <i class="wpas-logo" aria-hidden="true"></i>
                    <span id="wpas-deactivate-feedback-dialog-header-title"><?php echo __('Quick Feedback', 'wp-amazon-shop'); ?></span>
                </div>
                <form id="wpas-deactivate-feedback-dialog-form" method="post">
                    <input type="hidden" name="action" value="wpas_deactivate_feedback"/>
                    <div id="wpas-deactivate-feedback-dialog-form-caption"><?php echo __('If you have a moment, please share why you are deactivating WP Amazon Shop:', 'wp-amazon-shop'); ?></div>
                    <div id="wpas-deactivate-feedback-dialog-form-body">
                        <?php foreach ($deactivate_reasons as $reason_key => $reason) : ?>
                            <div class="wpas-deactivate-feedback-dialog-input-wrapper">
                                <input id="wpas-deactivate-feedback-<?php echo esc_attr($reason_key); ?>"
                                       class="wpas-deactivate-feedback-dialog-input" type="radio" name="reason_key"
                                       value="<?php echo esc_attr($reason_key); ?>" <?php echo($reason_key == 'other' ? 'checked' : ''); ?>/>
                                <label for="wpas-deactivate-feedback-<?php echo esc_attr($reason_key); ?>"
                                       class="wpas-deactivate-feedback-dialog-label"><?php echo esc_html($reason['title']); ?></label>
                                <?php if (!empty($reason['input_placeholder'])) : ?>
                                    <input class="wpas-feedback-text" type="text"
                                           name="reason_<?php echo esc_attr($reason_key); ?>"
                                           placeholder="<?php echo esc_attr($reason['input_placeholder']); ?>"/>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="wpas-deactivate-buttons-wrapper wpas-deactivate-lightbox-buttons-wrapper">
                        <p id="wpas-deactivate-message"></p>
                        <button type="button"
                                class="wpas-deactivate-submit"><?php echo __('Submit & Deactivate', 'wp-amazon-shop'); ?></button>
                        <a href=""
                           class="wpas-deactivate-skip"><?php echo __('Skip & Deactivate', 'wp-amazon-shop'); ?></a>
                    </div>
                </form>
            </div>
            <!--wpas-deactivate-feedback-dialog-inner-->
        </div>
        <!--#wpas-deactivate-feedback-dialog-wrapper-->
        <style>
            /*Customer feed back*/
            #wpas-deactivate-feedback-dialog-wrapper {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, .8);
                z-index: 9999;
                padding: 30px;
                box-sizing: border-box;
            }

            #wpas-deactivate-feedback-dialog-wrapper > div {
                max-width: 600px;
                margin: 0 auto;
                box-shadow: 0 0 5px #cccccc;
                padding: 30px;
                /*transform: translateY(50%);*/
                background: #ffffff;
                position: relative;
            }

            #wpas-deactivate-feedback-dialog-wrapper #wpas-deactivate-close-btn {
                display: block;
                padding: 10px 15px;
                background: #ff3907;
                color: #fff;
                border: 1px solid #ccc;
                border-radius: 3px;
                position: absolute;
                right: 0px;
                top: 0;
                cursor: pointer;
                font-size: 18px;
            }
            #wpas-deactivate-feedback-dialog-wrapper #wpas-deactivate-close-btn:hover{
                background: #da000d;
            }
            #wpas-deactivate-feedback-dialog-header {
                font-size: 22px;
                padding: 15px;
                box-shadow: 0 0 8px rgba(0, 0, 0, .1);
                margin: -30px -30px 0 -30px;
                display: flex;
                align-items: center;
                text-transform: uppercase;
            }

            .wpas-logo {
                display: inline-block;
                width: 30px;
                height: 30px;
                background: url("https://ps.w.org/wp-amazon-shop/assets/icon-256x256.png") no-repeat center;
                margin-right: 10px;
                background-size: cover;
            }

            #wpas-deactivate-feedback-dialog-form-caption {
                padding: 8px 0;
            }


            #wpas-deactivate-feedback-dialog-form-body > div {
                padding: 8px 0;
                position: relative;
            }

            /*#wpas-deactivate-feedback-dialog-form-body > div input[type="radio"] {*/
            /*    position: absolute;*/
            /*    left: 0;*/
            /*}*/

            #wpas-deactivate-feedback-dialog-form-body input[type="text"] {
                background-color: #f9f9f9;
                border: 1px solid #cccccc;
                box-sizing: border-box;
                padding: 5px;
                width: calc(100% - 24px);
                margin-top: 5px;
                margin-left: 24px;
                box-sizing: border-box;
            }

            .wpas-deactivate-buttons-wrapper {
                display: flex;
                justify-content: space-between;
                flex-wrap: wrap;
                align-items: center;
            }

            .wpas-deactivate-buttons-wrapper p {
                margin: 10px 0;
                flex: 0 0 100%;
            }

            .wpas-deactivate-buttons-wrapper button {
                display: block;
                padding: 10px 15px;
                background: #FF9800;
                color: #fff;
                border: 1px solid #ccc;
                border-radius: 3px;
                font-size: 14px;
                cursor: pointer;
            }
            .wpas-deactivate-buttons-wrapper button:hover{
                background: #ffbd1f;
            }
            .wpas-deactivate-buttons-wrapper a {
                text-decoration: none;
            }

            @media screen and(max-height: 900px) {
            }
        </style>
        <?php
    }

    /**
     * WP Amazon shop feed back ajax
     *
     * @since 2.0.5
     * @access public
     */
    public function deactivate_feedback()
    {

        $reason_text = '';
        $reason_key = '';

        if (!empty(sanitize_text_field($_POST['reason_key']))) {
            $reason_key = sanitize_text_field($_POST['reason_key']);
        }

        if (!empty(sanitize_text_field($_POST["reason_val"]))) {
            $reason_text = sanitize_text_field($_POST["reason_val"]);
        }
        $subject = __('Deactivation reason of WP Amazon Shop Free', 'wp-amazon-shop');
        $data['status'] = 'failed';
        $data['message'] = __('Problem in processing your feedback submission request! Apologies for the inconveniences.<br> 
Please email to <span style="color:#22A0C9;font-weight:bold !important;font-size:14px "> amadercode@gmail.com </span> with any feedback. We will get back to you right away!', 'wp-amazon-shop');


        if ($reason_key == "") {
            $data['message'] = 'Please fill up all the requried form fields.';
        } else {
            //build email body
            $bodyContent = "";
            $bodyContent .= "<p><strong>" . __('WP Amazon Shop Version', 'wp-amazon-shop') . ": ".ACL_WPAS_VERSION."</strong></p><hr>";
            $bodyContent .= "<p>" . __('Subject', 'wp-amazon-shop') . " : " . $subject . "</p>";
            $bodyContent .= "<p>" . __('Reason', 'wp-amazon-shop') . " : " . $reason_key . "</p>";
            $bodyContent .= "<p>" . __('Reason Text', 'wp-amazon-shop') . " : " . $reason_text . "</p>";
            $bodyContent .= "<p>" . __('URL', 'wp-amazon-shop') . " : " . get_bloginfo('url') . "</p>";
            $bodyContent .= "<p>" . __('Admin Email', 'wp-amazon-shop') . " : " . get_option('admin_email') . "</p>";

            $bodyContent .= "<p></p><p>" . __('Mail sent from', 'wp-amazon-shop') . ": <strong>" . get_bloginfo('name') . "</strong>, URL: [" . get_bloginfo('url') . "].</p>";
            $bodyContent .= "<p>" . __('Mail Generated on', 'wp-amazon-shop') . " : " . date("F j, Y, g:i a") . "</p>";

            $toEmail = "amadercode@gmail.com"; //Receivers email address

            //Extract Domain
            $url = get_site_url();
            $url = parse_url($url);
            $domain = $url['host'];


            $fakeFromEmailAddress = "wordpress@" . $domain;

            $to = $toEmail;
            $body = $bodyContent;
            $headers = array();
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $fakeFromEmailAddress . '>';
            $headers[] = 'Reply-To: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>';

            $result = wp_mail($to, $subject, $body, $headers);

            if ($result) {
                $data['status'] = 'success';
                $data['redirect_url'] = admin_url();
                $data['message'] = __('Your feedback has been sent successfully. Thanks!', 'wp-amazon-shop');
            }

        }
        wp_send_json($data);
    }

    /**
     * @since 2.3.0
     * @access private
     */
    private function is_plugins_screen()
    {
        return in_array(get_current_screen()->id, ['plugins', 'plugins-network']);
    }
}
//Loading Import class init hook
$wpas_plugin_feedback = new ACL_Amazon_Shop_Feedback();
$wpas_plugin_feedback->initialize();
/*
if(!function_exists('acl_wpas_feedback_init')){
    function acl_wpas_feedback_init(){
        $new ACL_Amazon_Shop_Feedback();
    }
    add_action('wp_loaded','acl_wpas_feedback_init');
}*/
