<?php
/*
Plugin Name: Stripe Order Processing
Description: Stripe Order Processing.
Version: 1.0
Author: CodeClouds
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
        

        

	}

    public function init() {
        global $wpdb;
		$hosted_page_details = get_option('stripe_hosted_page_details');
        $this->wpdb = $wpdb;

        
        // print_r($hosted_page_details);
	}

    public function sop_page_template( $page_template )
	{
        
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

}

$WC_Stripe_Order_Processing =  new WC_Stripe_Order_Processing();
