<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'db.php';

function dd($var,$type = 0){
    echo "<pre>";

    if($type){
        var_dump($var);
    }else{
        print_r($var);
    }
    echo "</pre>";
    die;
}

function dl($var,$type = 0){
    echo "<pre>";

    if($type){
        var_dump($var);
    }else{
        print_r($var);
    }
    echo "</pre>";
}

class API {

    const url               = null;
    const addProduct        = null;
    const login             = null;
    const addOrder          = null;
    const shippingAddress   = null;
    const shippingMethod    = null;
    const shippingMethods   = null;
    const paymentMethods    = null;
    const paymentMethod     = null;
    const paymentAddress    = null;
    const customer          = null;
    const showCart          = null;

    public function __construct($url=null){

        if($url){
            $this->$url                     = $url;
            $this->login                    = $this->$url . '/index.php?route=api/login'           ;
            $this->addProduct               = $this->$url . '/index.php?route=api/cart/add'        ;
            $this->addOrder                 = $this->$url . '/index.php?route=api/order/add'       ;
            $this->customer                 = $this->$url . '/index.php?route=api/customer'        ;
            $this->shippingAddress          = $this->$url . '/index.php?route=api/shipping/address';
            $this->shippingMethods          = $this->$url . '/index.php?route=api/shipping/methods';
            $this->shippingMethod           = $this->$url . '/index.php?route=api/shipping/method' ;
            $this->paymentMethods           = $this->$url . '/index.php?route=api/payment/methods' ;
            $this->paymentMethod            = $this->$url . '/index.php?route=api/payment/method'  ;
            $this->paymentAddress           = $this->$url . '/index.php?route=api/payment/address' ;
            $this->showCart                 = $this->$url . '/index.php?route=api/cart/products'   ;
            $this->productRemoveFromCart    = $this->$url . '/index.php?route=api/cart/remove'     ;
        }else{
            die('URL не передан!');
        }
    }

    public function curl_start($url=null, $params=array()) {

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/tmp/apicookie.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/tmp/apicookie.txt');

        $params_string = '';

        if (is_array($params) && count($params)) {
            foreach($params as $key=>$value) {
                $params_string .= $key.'='.$value.'&';
            }
            rtrim($params_string, '&');

            curl_setopt($ch,CURLOPT_POST, count($params));
            curl_setopt($ch,CURLOPT_POSTFIELDS, $params_string);
        }

        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;

    }

    public function login($username,$key,$json_decode = FALSE){

        $fields = array(
            'username'  => $username,
            'key'       => $key,
        );

        if($json_decode){
            $json = $this->curl_start($this->login,$fields);
            $data = json_decode($json,1);
            return $data;
        }else{
            return $this->curl_start($this->login,$fields);
        }

    }

    public function addProduct($id, $quantity,$json_decode = TRUE){

        $fields = array(
            'product_id' => (string) $id,
            'quantity'   => (string) $quantity
        );

        if($json_decode){
            $json = $this->curl_start($this->addProduct,$fields);
            $data = json_decode($json,1);
            return $data;

        }else{
            return $this->curl_start($this->addProduct,$fields);
        }
    }

    public function productRemoveFromCart($id,$json_decode = TRUE){

        $fields = array(
            'key' => (string) $id,
        );

        if($json_decode){
            $json = $this->curl_start($this->productRemoveFromCart,$fields);
            $data = json_decode($json,1);
            return $data;

        }else{
            return $this->curl_start($this->productRemoveFromCart,$fields);
        }
    }



    public function orderAdd($json_decode = 1){

        if($json_decode){
            $result = $this->curl_start($this->addOrder);
            $result = json_decode($result,1);
            return $result;
        }else{
            return $this->curl_start($this->addOrder);
        }
    }

    public function shippingAddress($data = FALSE, $json_decode = 1){

        if($data != FALSE){

            if($json_decode){

                $result = $this->curl_start($this->shippingAddress, $data);
                $result = json_decode($result,1);
                return $result;

            }else{

                return $this->curl_start($this->shippingAddress,$data);

            }

        }else{
            die('Долбаеб! Передай массив с адрессом!');
        }
    }

    public function customer($data = FALSE, $json_decode = 1){

        if($data != FALSE){

            if($json_decode){

                $result = $this->curl_start($this->customer, $data);
                $result = json_decode($result,1);
                return $result;

            }else{

                return $this->curl_start($this->customer,$data);

            }

        }else{
            die('Долбаеб! Передай массив с адрессом!');
        }
    }

    public function shippingMethods($json_decode = 1){

        if($json_decode){
            $json = $this->curl_start($this->shippingMethods);
            $result = json_decode($json,1);
            return $result;
        }else{
            return $this->curl_start($this->shippingMethods);
        }
    }

    public function showCart($json_decode = 1){

        if($json_decode){
            $json = $this->curl_start($this->showCart);
            $result = json_decode($json,1);
            return $result;
        }else{
            return $this->curl_start($this->showCart);
        }
    }



    public function paymentAddress($data = FALSE, $json_decode = 1){

        if($data != FALSE){

            if($json_decode){

                $result = $this->curl_start($this->paymentAddress, $data);
                $result = json_decode($result,1);
                return $result;

            }else{

                return $this->curl_start($this->paymentAddress,$data);

            }

        }else{
            die('Долбаеб! Передай массив с адрессом!');
        }
    }

    public function paymentMethods($json_decode = 1){

        if($json_decode){
            $json = $this->curl_start($this->paymentMethods);
            $result = json_decode($json,1);
            return $result;
        }else{
            return $this->curl_start($this->paymentMethods);
        }
    }

    public function shippingMethod($data, $json_decode = 1){

        if($json_decode){
            $result = $this->curl_start($this->shippingMethod,$data);
            $result = json_decode($result,1);
            return $result;
        }else{
            return $this->curl_start($this->shippingMethod,$data);
        }
    }

    public function paymentMethod($data, $json_decode = 1){

        if($json_decode){
            $result = $this->curl_start($this->paymentMethod,$data);
            $result = json_decode($result,1);
            return $result;
        }else{
            return $this->curl_start($this->paymentMethod,$data);
        }
    }

    public function getIdProduct($bx_id){
        $item = DB::getValue("SELECT `product_id` FROM `oc_product` WHERE model = ?", $bx_id);
        return $item;
    }

}

?>