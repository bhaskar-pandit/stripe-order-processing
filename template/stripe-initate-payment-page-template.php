<?php
require_once dirname(__DIR__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."class-sop-functions.php";

$QRY_STRING =  SOP_Functions::encrypt_decrypt($_REQUEST['cue'],'decrypt');

parse_str($QRY_STRING, $QUERY_STRING);
$orderId = !empty($QUERY_STRING['id'])?$QUERY_STRING['id']:'';
$AFFID = !empty($QUERY_STRING['AFFID'])?$QUERY_STRING['AFFID']:'';
$_SESSION['__AFFID__'] = $AFFID;
print_r($QUERY_STRING);
require_once dirname(__DIR__).DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR."require.php";
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
// echo "<pre>";
// $SiteTitle = get_bloginfo();
// $OrderData = $WC->GetOrderData($orderId);


// $StripePriceRes = $STRIPE->stripePriceCreate('usd',100,$SiteTitle);

// print_r($StripePriceRes);

?>

<script>
    window.location.href = '<?=$typageurl.'?cue='.$_REQUEST['cue']?>';
</script>












