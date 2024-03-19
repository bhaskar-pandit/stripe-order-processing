<?php
@ob_start();
@session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors',1);
require_once (__DIR__).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
require_once dirname(__DIR__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."class-woocommerce.php";
require_once dirname(__DIR__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."class-stripe.php";



$PluginConfig = [];

$PluginConfig['sop_stripe_payment_mode']        = get_option( 'sop_stripe_payment_mode' );
$PluginConfig['sop_stripe_api_key_sandbox']     = get_option( 'sop_stripe_api_key_sandbox' );
$PluginConfig['sop_stripe_secret_key_sandbox']  = get_option( 'sop_stripe_secret_key_sandbox' );
$PluginConfig['sop_stripe_api_key_live']        = get_option( 'sop_stripe_api_key_live' );
$PluginConfig['sop_stripe_secret_key_live']     = get_option( 'sop_stripe_secret_key_live' );
$PluginConfig['sop_stripe_descriptor']          = get_option( 'sop_stripe_descriptor' );
$PluginConfig['sop_woocommerce_settings_raw']   = get_option( 'sop_woocommerce_settings' );

foreach ($PluginConfig['sop_woocommerce_settings_raw'] as $key => $value) {
    $PluginConfig['sop_woocommerce_settings'][$value['code']] = $value;
}
$SOP_WooCommerce_Config = [];
if (isset($PluginConfig['sop_woocommerce_settings'][$QUERY_STRING['AFFID']])) {
    $SOP_WooCommerce_Config = $PluginConfig['sop_woocommerce_settings'][$QUERY_STRING['AFFID']];
}
// print_r($PluginConfig);

if (empty($SOP_WooCommerce_Config)) {
    echo '<script>window.location.href = "'.get_site_url().'"; </script>';
    die();
}

$WC = new SOP_WooCommerce($SOP_WooCommerce_Config['offer_url'],$SOP_WooCommerce_Config['api_key'],$SOP_WooCommerce_Config['api_secret']);

if ($PluginConfig['sop_stripe_payment_mode'] === 'sandbox') {
    $StripeApiKey   = $PluginConfig['sop_stripe_api_key_sandbox'];
    $StripeSecretKey = $PluginConfig['sop_stripe_secret_key_sandbox'];
}else{
    $StripeApiKey   = $PluginConfig['sop_stripe_api_key_live'];
    $StripeSecretKey = $PluginConfig['sop_stripe_secret_key_live'];
}

$STRIPE = new SOP_Stripe($StripeApiKey,$StripeSecretKey);
