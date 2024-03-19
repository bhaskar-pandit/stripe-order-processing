<?php
require_once dirname(__DIR__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."class-sop-functions.php";

$QRY_STRING =  SOP_Functions::encrypt_decrypt($_REQUEST['cue'],'decrypt');

parse_str($QRY_STRING, $QUERY_STRING);
$orderId = !empty($QUERY_STRING['id'])?$QUERY_STRING['id']:'';
$total = !empty($QUERY_STRING['total'])?$QUERY_STRING['total']:'';
$currency = !empty($QUERY_STRING['currency'])?$QUERY_STRING['currency']:'';
$AFFID = !empty($QUERY_STRING['AFFID'])?$QUERY_STRING['AFFID']:'';

// print_r($QUERY_STRING);
require_once dirname(__DIR__).DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR."require.php";

$_SESSION['__AFFID__'] = $AFFID;
$_SESSION['__QUERY_STRING__'] = $QUERY_STRING;
?>

<link rel="stylesheet" href="<?php echo plugin_dir_url( __DIR__ ) ?>template/assets/style.css">

<div class="loader__container">
    <div class="loader__inner">
        <div class="icon__container">                
                <img src="<?php echo plugin_dir_url( __DIR__ ) ?>template/assets/credit-card.gif" alt="">
        </div>
        <div class="text__container">
            <h2>Redirecting To Secure Payment</h2>
            <p>Please wait while we generate secure payment gateway.</p>
        </div>
    </div>
</div>


<?php
$hosted_page_details = get_option('stripe_hosted_page_details');

$typageurl = $hosted_page_details['ThankYouPage']['permalink'];
$typageurl = 'https://gopher-driving-thoroughly.ngrok-free.app/Woo-Stripe/safe/stripe-thank-you-page/';

$SiteTitle = 'Payment for order #'.$orderId;
$OrderTotal = $total * 100;
// $OrderData = $WC->GetOrderData($orderId);
// print_r($OrderData);
// die();
$StripePriceRes = $STRIPE->stripePriceCreate($currency,$OrderTotal,$SiteTitle);

if ($StripePriceRes['result'] == 'succeeded') {
    $typageurl = $typageurl.'?cue='.$_REQUEST['cue'].'&id={CHECKOUT_SESSION_ID}';
    $metaData = [
        'order_id' => $orderId,
    ];
    $StripePaymentLinkRes = $STRIPE->stripePaymentLinkCreate($StripePriceRes['data']['id'],$metaData,$typageurl);

    if ($StripePaymentLinkRes['result'] == 'succeeded') {
        $stripePaymentUrl = $StripePaymentLinkRes['data']['url'];
        header("Location: ".$stripePaymentUrl);
        die();
    }

}

echo "Error";
print_r($StripePriceRes);
?>













