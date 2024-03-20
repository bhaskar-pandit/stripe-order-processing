<?php
require_once dirname(__DIR__).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."class-sop-functions.php";
global $wpdb;

$table_name = $wpdb->prefix . 'sop_order_log';
$code = !empty($_REQUEST['code'])?$_REQUEST['code']:'';

$sql = "SELECT * FROM $table_name WHERE log_code = $code AND status = 1";
$results = $wpdb->get_results( $sql );


$dataNeedProcessed = 0;
if (sizeof($results) > 0) {
    $QUERY_STRING = json_decode($results[0]->query_data,true);
    $orderId = !empty($QUERY_STRING['id'])?$QUERY_STRING['id']:'';
    $total = !empty($QUERY_STRING['total'])?$QUERY_STRING['total']:'';
    $currency = !empty($QUERY_STRING['currency'])?$QUERY_STRING['currency']:'';
    $AFFID = !empty($QUERY_STRING['AFFID'])?$QUERY_STRING['AFFID']:'';
    require_once dirname(__DIR__).DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR."require.php";
    $dataNeedProcessed = 1;
}
?>

<link rel="stylesheet" href="<?php echo plugin_dir_url( __DIR__ ) ?>template/assets/style.css">
<div class="loader__container">
    <div class="loader__inner">
        <div class="icon__container">
            <div class="loading">
                <svg xmlns="http://www.w3.org/2000/svg" width="124" height="124" viewBox="0 0 124 124">
                    <circle class="circle-loading" cx="62" cy="62" r="59" fill="none" stroke="#fff"
                        stroke-width="6px"></circle>
                    <circle class="circle" cx="62" cy="62" r="59" fill="none" stroke="#00cdc8"
                        stroke-width="6px" stroke-linecap="round"></circle>
                    <polyline class="check" points="73.56 48.63 57.88 72.69 49.38 62" fill="none"
                        stroke="#00cdc8" stroke-width="6px" stroke-linecap="round"></polyline>
                </svg>
            </div>
        </div>
        <div class="text__container">
            <h2>Payment Successful!</h2>
            <p>We are delighted to inform you that we received your payments.</p>
        </div>
    </div>
</div>

<?php
if ($dataNeedProcessed == 1) {
    $paymentid = $_REQUEST['id'];
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

    header("Location: ".$url);
    die();
}

    

?>