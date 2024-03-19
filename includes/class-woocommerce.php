<?php
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

class SOP_WooCommerce
{
    private  $WOOCOMMERCE_API_URL, $WOOCOMMERCE_API_KEY, $WOOCOMMERCE_API_SECRET, $WC;
    function __construct($API_URL,$API_KEY,$API_SECRET) {
        $this->WOOCOMMERCE_API_URL      = $API_URL;
        $this->WOOCOMMERCE_API_KEY      = $API_KEY;
        $this->WOOCOMMERCE_API_SECRET   = $API_SECRET;
        $WCClient = new Client(
            $this->WOOCOMMERCE_API_URL,
            $this->WOOCOMMERCE_API_KEY,
            $this->WOOCOMMERCE_API_SECRET,
            [
                'wp_api' => true,
                'version' => 'wc/v3',
            ]
        );
        $this->WC = $WCClient;
    }

    public function GetOrderData($orderid = "")
    {
        try {
            // Array of response results.
            return $this->WC->get('orders/'.$orderid);
        } catch (HttpClientException $e) {            
            return $response = [
                'status' => false,
                'responseCode' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }

    
    public function UpdateOrder($orderid,$data = [])
    {
        if(empty($data)){ return; }

        try {
            // Array of response results.
            $results = $this->WC->put('orders/'.$orderid, $data);

            $response = [
                'status' => true,
                'responseCode' => 200,
                'data' => (array) $results
            ];      
        } catch (HttpClientException $e) {
            $response = [
                'status' => false,
                'responseCode' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }        
        return $response;
    }

    
    public function AddOrderNote($orderid,$note = "")
    {
        if(empty($note) || $note == ""){ return; }

        try {
            // Array of response results.
            $data = [
                'note' => $note
            ];
            
            $results = $this->WC->post('orders/'.$orderid.'/notes', $data);


            $response = [
                'status' => true,
                'responseCode' => 200,
                'data' => (array) $results
            ];      
        } catch (HttpClientException $e) {
            $response = [
                'status' => false,
                'responseCode' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }

        
        return $response;  
    }



    public function GetUserIP() {
	    if( array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
	        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')>0) {
	            $addr = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
	            $ipaddress = trim($addr[0]);
	        } else {
	            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	        }
	    }
	    else {
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    }

        if($ipaddress === "::1"){ $ipaddress = "127.0.0.1"; }

        return $ipaddress;
	}
}
