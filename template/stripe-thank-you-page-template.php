<link rel="stylesheet" href="<?php echo plugin_dir_url( __DIR__ ) ?>template/assets/style.css">
<title>Thank You</title>
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
            <p id="error_message"></p>
            <a href="<?php echo get_site_url(); ?>" class="btn" style="display: none;">« Return to shop</a>
        </div>
    </div>
</div>

<script>
    const __CONFIG__ = {
        'action' : "thank_you",
        'ajaxurl': "<?=admin_url( 'admin-ajax.php' )?>",
        // 'ajaxurl': "https://jay-workable-locust.ngrok-free.app/Woo-Stripe/safe/wp-admin/admin-ajax.php",
        'cue': "<?=$_REQUEST['code']?>",
        'paymentid': "<?=$_REQUEST['id']?>"
    };
</script>
<script src="<?php echo plugin_dir_url( __DIR__ ) ?>template/assets/stripe-order-processing.js"></script>
