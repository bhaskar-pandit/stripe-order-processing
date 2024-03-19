<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 */


class SOP_Settings_Display {
    public static function sop_admin_page_content() {
        $woocommerce_settings = get_option('sop_woocommerce_settings', array());
        $hosted_page_details = get_option('stripe_hosted_page_details');
        
        ?>
        <!-- This file should primarily consist of HTML with a little bit of PHP. -->
        <div class="wrap sop-admin-page">
            <h1>Stripe Order Processing Config</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'sop-settings-group' ); ?>
                <?php do_settings_sections( 'sop-settings-group' ); ?>
                <table class="form-table">


                    <tr>
                        <th  scope="row"><strong>Payment Page URL:</strong></th>
                        <td><?=$hosted_page_details['InitatePaymentPage']['permalink']?></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Stripe Payment Mode</th>
                        <td>
                            <select name="sop_stripe_payment_mode" class="regular-text" >
                                <option value="sandbox" <?php echo selected( get_option( 'sop_stripe_payment_mode' ), 'sandbox',false ); ?> >Sandbox</option>
                                <option value="live"  <?php echo selected( get_option( 'sop_stripe_payment_mode' ), 'live',false ); ?> >Live</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Stripe Key (Sandbox)</th>
                        <td><input type="text" name="sop_stripe_api_key_sandbox" class="regular-text" value="<?php echo esc_attr( get_option('sop_stripe_api_key_sandbox') ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Stripe Secret (Sandbox)</th>
                        <td><input type="text" name="sop_stripe_secret_key_sandbox" class="regular-text" value="<?php echo esc_attr( get_option('sop_stripe_secret_key_sandbox') ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Stripe Key (Live)</th>
                        <td><input type="text" name="sop_stripe_api_key_live" class="regular-text" value="<?php echo esc_attr( get_option('sop_stripe_api_key_live') ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Stripe Secret (Live)</th>
                        <td><input type="text" name="sop_stripe_secret_key_live" class="regular-text" value="<?php echo esc_attr( get_option('sop_stripe_secret_key_live') ); ?>" /></td>
                    </tr>


                    <tr valign="top">
                        <th scope="row">Stripe Descriptor</th>
                        <td><input type="text" name="sop_stripe_descriptor" class="regular-text" value="<?php echo esc_attr( get_option('sop_stripe_descriptor') ); ?>" /></td>
                    </tr>

                </table>

              


                <div id="repeatable_options_woocommerce_data">
                    <?php  
                    if (!empty($woocommerce_settings)) { 
                        foreach ($woocommerce_settings as $index => $setting) {
                            ?>
                            <table class="form-table" style="border-top: 1px dashed #0f0f0f;">

                                <?php self::sop_admin_woocommerce_settings_block($setting, $index);?>
                            </table>
                            <?php
                        }
                    }
                    ?>
                </div>

                <hr>
                <button class="button button-secondary" type="button" id="add-option">Add More WooCommerce Config</button>


                

                   
                     
                <?php submit_button(); ?>
            </form>

            <table class="form-table" style="border-top: 1px dashed #0f0f0f; display: none;" id="repeatable_options_woocommerce_settings">
                <?php self::sop_admin_woocommerce_settings_block(); ?>

            </table>
        </div>

        <script src="<?php echo plugin_dir_url( __DIR__ ) ?>assets/admin-settings.js"></script>
        <?php
    }

    public static function sop_initialize_settings() {
        register_setting( 'sop-settings-group', 'sop_stripe_payment_mode' );
        register_setting( 'sop-settings-group', 'sop_stripe_api_key_sandbox' );
        register_setting( 'sop-settings-group', 'sop_stripe_secret_key_sandbox' );
        register_setting( 'sop-settings-group', 'sop_stripe_api_key_live' );
        register_setting( 'sop-settings-group', 'sop_stripe_secret_key_live' );
        register_setting( 'sop-settings-group', 'sop_stripe_descriptor' );
        register_setting( 'sop-settings-group', 'sop_woocommerce_settings' );
    }

    private static function sop_admin_woocommerce_settings_block($data=[], $index="__ID__") {
    
       ?>
       <tr>
            <th colspan="2" scope="row"><strong>WooCommerce Settings Config [#<?=$index?>]</strong></th>
        </tr>
       <tr valign="top">
            <th scope="row">WooCommerce Code</th>
            <td><input type="text" name="sop_woocommerce_settings[<?=$index?>][code]" class="regular-text" value="<?php  echo isset($data['code']) ? esc_attr($data['code']) : ''; ?>"  /></td>
        </tr>
        <tr valign="top">
            <th scope="row">WooCommerce Offer URL</th>
            <td><input type="text" name="sop_woocommerce_settings[<?=$index?>][offer_url]" class="regular-text" value="<?php  echo isset($data['offer_url']) ? esc_attr($data['offer_url']) : ''; ?>" /></td>
        </tr>
        <tr valign="top">
            <th scope="row">WooCommerce API Key</th>
            <td><input type="text" name="sop_woocommerce_settings[<?=$index?>][api_key]" class="regular-text" value="<?php  echo isset($data['api_key']) ? esc_attr($data['api_key']) : ''; ?>" /></td>
        </tr>
        <tr valign="top">
            <th scope="row">WooCommerce API Secret</th>
            <td><input type="text" name="sop_woocommerce_settings[<?=$index?>][api_secret]" class="regular-text" value="<?php  echo isset($data['api_secret']) ? esc_attr($data['api_secret']) : ''; ?>" /></td>
        </tr>
        <tr>
            <td colspan="2" scope="row"><button class="button button-secondary remove-option" type="button" >Remove WooCommerce Config</button></td>
        </tr>

       <?php
    }
}
