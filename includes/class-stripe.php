<?php


class SOP_Stripe{
        private   $STRIPE_API_KEY, $STRIPE_SECRET_KEY, $STRIPE;

       function __construct($API_KEY,$SECRET_KEY) {
            $this->STRIPE_API_KEY       = $API_KEY;
            $this->STRIPE_SECRET_KEY    = $SECRET_KEY;

            $stripeClient = new \Stripe\StripeClient($this->STRIPE_SECRET_KEY);
            $this->STRIPE = $stripeClient;
        }
    

        public function stripePriceCreate($currency='usd',$amount=0,$product_name=""){
            try {
                $priceResponse = $this->STRIPE->prices->create([
                    'currency' => $currency,
                    'unit_amount' => $amount,
                    'product_data' => ['name' => $product_name],
                ]);


                $ResponseData['result'] = "succeeded";
                $ResponseData['data'] = json_decode(json_encode($priceResponse,true),true);
            } 
            catch(\Stripe\Exception\CardException $e) {
                // Since it's a decline, \Stripe\Exception\CardException will be caught            
                $ResponseData = ['result'=>'failure','messages'=>$e->getError()->message];
            } catch (\Stripe\Exception\RateLimitException $e) {
                // Too many requests made to the API too quickly
                $ResponseData = ['result'=>'failure','messages'=>$e->getError()->message];
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                // Invalid parameters were supplied to Stripe's API
                $ResponseData = ['result'=>'failure','messages'=>$e->getError()->message];
            } catch (\Stripe\Exception\AuthenticationException $e) {
                // Authentication with Stripe's API failed
                // (maybe you changed API keys recently)
                $ResponseData = ['result'=>'failure','messages'=>$e->getError()->message];
            } catch (\Stripe\Exception\ApiConnectionException $e) {
                // Network communication with Stripe failed
                $ResponseData = ['result'=>'failure','messages'=>$e->getError()->message];
            } catch (\Stripe\Exception\ApiErrorException $e) {
                // Display a very generic error to the user, and maybe send
                // yourself an email
                $ResponseData = ['result'=>'failure','messages'=>$e->getError()->message];
            } catch (Exception $e) {
                // Something else happened, completely unrelated to Stripe
                $ResponseData = ['result'=>'failure','messages'=>'Somthing Went Wrong...'];
            }
            
            return $ResponseData;
        }


         public function stripePaymentLinkCreate($priceToken='',$meta=[],$rediect_url=""){
            try {
                
                $paymentLinkRes = $this->STRIPE->paymentLinks->create([
                    'line_items' => [
                        [
                            'price' => $priceToken,
                            'quantity' => 1,
                        ],
                    ],
                    'metadata' => $meta,
                    'after_completion' => [
                        'type' => 'redirect',
                        'redirect' => ['url' => $rediect_url],
                    ],
                ]);

                $ResponseData['result'] = "succeeded";
                $ResponseData['data'] = json_decode(json_encode($paymentLinkRes,true),true);
            } 
            catch(\Stripe\Exception\CardException $e) {
                // Since it's a decline, \Stripe\Exception\CardException will be caught            
                $ResponseData = ['result'=>'failure','messages'=>$e->getError()->message];
            } catch (\Stripe\Exception\RateLimitException $e) {
                // Too many requests made to the API too quickly
                $ResponseData = ['result'=>'failure','messages'=>$e->getError()->message];
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                // Invalid parameters were supplied to Stripe's API
                $ResponseData = ['result'=>'failure','messages'=>$e->getError()->message];
            } catch (\Stripe\Exception\AuthenticationException $e) {
                // Authentication with Stripe's API failed
                // (maybe you changed API keys recently)
                $ResponseData = ['result'=>'failure','messages'=>$e->getError()->message];
            } catch (\Stripe\Exception\ApiConnectionException $e) {
                // Network communication with Stripe failed
                $ResponseData = ['result'=>'failure','messages'=>$e->getError()->message];
            } catch (\Stripe\Exception\ApiErrorException $e) {
                // Display a very generic error to the user, and maybe send
                // yourself an email
                $ResponseData = ['result'=>'failure','messages'=>$e->getError()->message];
            } catch (Exception $e) {
                // Something else happened, completely unrelated to Stripe
                $ResponseData = ['result'=>'failure','messages'=>'Somthing Went Wrong...'];
            }

            return $ResponseData;
        }
}
