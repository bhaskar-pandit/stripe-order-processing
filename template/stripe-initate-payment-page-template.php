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


<div class="loader__container processing__container">
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
global $wpdb;
$tableName = $wpdb->prefix.'sop_order_log';
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
$typageurl = 'https://gopher-driving-thoroughly.ngrok-free.app/Woo-Stripe/safe/stripe-thank-you-page/';

$SiteTitle = 'Payment for order #'.$orderId;
$OrderTotal = $total * 100;
echo $typageurl = $typageurl.'?code='.$logCode.'&id={CHECKOUT_SESSION_ID}';
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
            header("Location: ".$stripePaymentUrl);
            die();
        }
        else{
            $errorMessage = $StripePriceRes['messages'];
        }
    }else{
        $errorMessage = $StripePriceRes['messages'];
    }
} catch (\Throwable $th) {
    $errorMessage = 'Something went wrong. Please try again later.';
}


if ($errorMessage !== "") {
    ?>
    
    <div class="loader__container error__container" style="display: none;">
        <div class="loader__inner">
            <div class="icon__container">
                <svg id="error" version="1.1" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" xml:space="preserve">
                    <circle id="circle" cx="50" cy="50" r="46" fill="transparent" />
                    <line class="cross" x1="30" y1="30" x2="70" y2="70" style="stroke:#e56f6f;stroke-width:6" />
                    <line class="cross" x1="70" y1="30" x2="30" y2="70" style="stroke:#e56f6f;stroke-width:6" />
                </svg>
            </div>
            <div class="text__container error">
                <h2>Error!</h2>
                <p>Sorry, there was an error processing your request.</p>
                <p><?=$errorMessage?></p>
            </div>
        </div>
    </div>
    <script> 
        setTimeout(() => {
            document.querySelector(".processing__container").style.display = 'none'; 
            document.querySelector(".error__container").style.display = 'flex'; 
        }, 2000);
    </script>
    <?php
}
?>













