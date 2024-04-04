<?php
/*
Plugin Name: Stripe Order Processing
Description: Stripe Order Processing.
Version: 1.0.2
Author: Codeclouds
Author URI: codeclouds.com
*/


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
include_once(ABSPATH . 'wp-admin/includes/plugin.php');


// Function to create pages upon activation
function create_pages_on_activation() {
    global $wpdb;
    // Create page 1
    $page1_title = 'Stripe Initate Payment Page';
    $page1_content = 'Stripe Initate Payment Page';
    $page1_template = dirname( __FILE__ ) . '/template/stripe-initate-payment-page-template.php';

    $page1_id = wp_insert_post(array(
        'post_title' => $page1_title,
        'post_content' => $page1_content,
        'post_status' => 'publish',
        'post_type' => 'page',
    ));
    $page1_permalink = get_permalink($page1_id);

    // Set page 1 template
    update_post_meta($page1_id, '_wp_page_template', $page1_template);
    
    $pageData['InitatePaymentPage'] = [
        'id' => $page1_id,
        'title' => $page1_title,
        'permalink' => $page1_permalink
    ];
    // Create page 2
    $page2_title = 'Stripe Thank You Page';
    $page2_content = 'Stripe Thank You Page';
    $page2_template = dirname( __FILE__ ) . '/template/stripe-thank-you-page-template.php';

    $page2_id = wp_insert_post(array(
        'post_title' => $page2_title,
        'post_content' => $page2_content,
        'post_status' => 'publish',
        'post_type' => 'page',
    ));
    $page2_permalink = get_permalink($page2_id);

    $pageData['ThankYouPage'] = [
        'id' => $page2_id,
        'title' => $page2_title,
        'permalink' => $page2_permalink
    ];

    // Set page 2 template
    update_post_meta($page2_id, '_wp_page_template', $page2_template);

    update_option('stripe_hosted_page_details', $pageData);


    $table_name = $wpdb->prefix . 'sop_order_log';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int NOT NULL AUTO_INCREMENT,
        log_code varchar(500) NOT NULL,
        order_id int NOT NULL,
        payment_url varchar(500),
        query_data LONGTEXT NOT NULL,
        status ENUM('1','0') NOT NULL DEFAULT '1',
        created_at datetime DEFAULT CURRENT_TIMESTAMP() NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

// Hook the function to the activation hook of the plugin
register_activation_hook(__FILE__, 'create_pages_on_activation');

// Function to delete pages upon deactivation
function delete_pages_on_deactivation() {
    // Get page IDs
    $pageDetails = get_option('stripe_hosted_page_details');


    $page1_id = $pageDetails['InitatePaymentPage']['id'];
    $page2_id = $pageDetails['ThankYouPage']['id'];

    // Delete page 1
    wp_delete_post($page1_id, true);

    // Delete page 2
    wp_delete_post($page2_id, true);
    delete_option('stripe_hosted_page_details');
}

// Hook the function to the deactivation hook of the plugin
register_deactivation_hook(__FILE__, 'delete_pages_on_deactivation');




class WC_Stripe_Order_Processing {
    public $wpdb;

	public function __construct() {
        
        require_once 'includes/class-admin-settings-display.php';

		add_action( 'plugins_loaded', array( $this, 'init' ) );
        add_filter('page_template', array( $this, 'sop_page_template' ));

        
        add_action( 'admin_menu', array( $this, 'sop_add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'sop_initialize_settings' )  );
        

        add_action( 'wp_ajax_nopriv_initate_payment', array( $this, 'sop_initate_payment_process' ));
        add_action( 'wp_ajax_initate_payment', array( $this, 'sop_initate_payment_process' ) );


        add_action( 'wp_ajax_nopriv_thank_you', array( $this, 'sop_thank_you_process' ));
        add_action( 'wp_ajax_thank_you', array( $this, 'sop_thank_you_process' ) );

	}

  

    

    public function init() {
        global $wpdb;
		$hosted_page_details = get_option('stripe_hosted_page_details');
        $this->wpdb = $wpdb;

        
        // print_r($hosted_page_details);
	}

    public function sop_page_template( $page_template ){
        
		$hosted_page_details = get_option('stripe_hosted_page_details');


        if ( is_page( $hosted_page_details['InitatePaymentPage']['id'] ) ) {
			$page_template = dirname( __FILE__ ) . '/template/stripe-initate-payment-page-template.php';
		}


		if ( is_page( $hosted_page_details['ThankYouPage']['id'] ) ) {
			$page_template = dirname( __FILE__ ) . '/template/stripe-thank-you-page-template.php';
		}


		return $page_template;
	}


    public function sop_add_admin_menu() {
        $sop_admin_page_content = array( $this, 'sop_admin_page_content' );

        add_menu_page(
            'Stripe Order Processing', // Page title
            'SOP Settings', // Menu title
            'manage_options', // Capability required to access the menu
            'sop-admin-page', // Menu slug
            $sop_admin_page_content, // Callback function to display the page content
            'dashicons-admin-generic', // Icon URL or Dashicons class
            9 // Position in the menu
        );
    }

    public function sop_admin_page_content() {
        SOP_Settings_Display::sop_admin_page_content();
       
    }
    public function sop_initialize_settings() {
        SOP_Settings_Display::sop_initialize_settings();
    }


    public function sop_initate_payment_process() {
                
        $QRY_STRING =  $this->encrypt_decrypt($_REQUEST['cue'],'decrypt');

        parse_str($QRY_STRING, $QUERY_STRING);
        $orderId = !empty($QUERY_STRING['id'])?$QUERY_STRING['id']:'';
        $total = !empty($QUERY_STRING['total'])?$QUERY_STRING['total']:'';
        $currency = !empty($QUERY_STRING['currency'])?$QUERY_STRING['currency']:'';
        $AFFID = !empty($QUERY_STRING['AFFID'])?$QUERY_STRING['AFFID']:'';

        // print_r($QUERY_STRING);
        require_once (__DIR__).DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR."require.php";

        $_SESSION['__AFFID__'] = $AFFID;
        $_SESSION['__QUERY_STRING__'] = $QUERY_STRING;

        global $wpdb;
        $tableName = $wpdb->prefix.'sop_order_log';

        // Check if the link is already used or not
        $checkLinkQuery = "SELECT * FROM $tableName WHERE order_id='$orderId' AND status='0'";
        $linkResult = $wpdb->get_results($checkLinkQuery);
        if(!empty($linkResult)) {
            $responseArr = [
                'status' => 'fail',
                'message' => "Payment Link already used."
            ];
            echo json_encode($responseArr,true);
            wp_die();
        }

        $logCode = rand().$orderId;

        try {
            $wpdb->insert($tableName, array(
                'log_code' => $logCode,
                'order_id' => $orderId,
                'query_data' => json_encode($QUERY_STRING,true),
            ));
        } catch (\Throwable $th) {
            //throw $th;
        }

        $hosted_page_details = get_option('stripe_hosted_page_details');

        $typageurl = $hosted_page_details['ThankYouPage']['permalink'];

        $SiteTitle = 'Payment for order #'.$orderId;
        $OrderTotal = $total * 100;
        // 
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
            $typageurl = $typageurl.'?code='.$logCode.'&id={CHECKOUT_SESSION_ID}';

            try {
                $StripePriceRes = $STRIPE->stripePriceCreate($currency,$OrderTotal,$SiteTitle);
                $errorMessage = "";
                if ($StripePriceRes['result'] == 'succeeded') {
                    $metaData = [
                        'order_id' => $orderId,
                    ];
                    $StripePaymentLinkRes = $STRIPE->stripePaymentLinkCreate($StripePriceRes['data']['id'],$metaData,$typageurl);

                    if ($StripePaymentLinkRes['result'] == 'succeeded') {
                        $stripePaymentUrl = $StripePaymentLinkRes['data']['url'];


                        $responseArr = [
                            'status' => 'success',
                            'url' => $stripePaymentUrl,
                            'message' => "Redirecting..."
                        ];
                        // header("Location: ".$stripePaymentUrl);
                        // die();
                    }
                    else{
                       
                        $errorMessage = $StripePriceRes['messages'];
                        $responseArr = [
                            'status' => 'fail',
                            'message' => $errorMessage
                        ];
                    }
                }else{
                    $errorMessage = $StripePriceRes['messages'];
                     $responseArr = [
                        'status' => 'fail',
                        'message' => $errorMessage
                    ];
                }
            } catch (\Throwable $th) {
                $errorMessage = 'Something went wrong. Please try again later.';
                $responseArr = [
                    'status' => 'fail',
                    'message' => $errorMessage
                ];
            }
        }else{
            $stripePaymentUrl = $typageurl.'?code='.$logCode.'&id=TESTID_'.rand();
            $responseArr = [
                'status' => 'success',
                'url' => $stripePaymentUrl,
                'message' => "Simulate the payment..."
            ];
           

        }
        // echo $_SERVER['HTTP_HOST'];

        echo json_encode($responseArr,true);
        wp_die();
    }

    public function sop_thank_you_process() {
                
        global $wpdb;

        $table_name = $wpdb->prefix . 'sop_order_log';
        $code = !empty($_REQUEST['cue'])?$_REQUEST['cue']:'';

        $sql = "SELECT * FROM $table_name WHERE log_code = $code AND status = 1";
        $results = $wpdb->get_results( $sql );


        $dataNeedProcessed = 0;
        if (sizeof($results) > 0) {
            $QUERY_STRING = json_decode($results[0]->query_data,true);
            $orderId = !empty($QUERY_STRING['id'])?$QUERY_STRING['id']:'';
            $total = !empty($QUERY_STRING['total'])?$QUERY_STRING['total']:'';
            $currency = !empty($QUERY_STRING['currency'])?$QUERY_STRING['currency']:'';
            $AFFID = !empty($QUERY_STRING['AFFID'])?$QUERY_STRING['AFFID']:'';
            require_once (__DIR__).DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR."require.php";
            $dataNeedProcessed = 1;
        }

        if ($dataNeedProcessed == 1) {
            $paymentid = $_REQUEST['paymentid'];
            $OrderData = $WC->GetOrderData($orderId);
            $wc_key = !empty($QUERY_STRING['wc_key'])?$QUERY_STRING['wc_key']:'';

            $UpdateData = [
                'status' => 'processing',
                'meta_data' => [
                    [
                        'key'=> 'stripe_payment_id',
                        'value'=> $paymentid
                    ]
                ],
            ];

            $UpdateStatus = $WC->UpdateOrder($orderId,$UpdateData);
            $WC->AddOrderNote( $orderId,'Stripe charge complete (Charge ID: '.$paymentid.' )' );
            $url = $SOP_WooCommerce_Config['offer_url']."checkout/order-received/" . $orderId . "/?key=" .$wc_key. "&paymentid=" . $paymentid;


            $wpdb->update(
                $table_name,
                array(
                    'status' => 0
                ),
                array( 'log_code' => $code )
            );

            $responseArr = [
                'status' => 'success',
                'url' => $url,
                'message' => "Payment is processed successfully..."

            ];
        }else{
            $responseArr = [
                'status' => 'processed',
                'message' => "Payment is already processed..."
            ];  
        }

        echo json_encode($responseArr,true);
        wp_die();
    }

    public function encrypt_decrypt($string, $action = 'encrypt')
    {
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'AUJRDMGNSAMBJTTVUJNNGMCLC';      // user define private key
        $secret_iv = 'bFArbEMzzguOOnN';                 // user define secret key
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }

}

$WC_Stripe_Order_Processing =  new WC_Stripe_Order_Processing();
