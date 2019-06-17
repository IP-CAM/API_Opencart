<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: text/html; charset=utf-8');

require_once "config.php"                               ; // конфиг
require_once "config_directory.php"                     ; // справочники
require_once "common.php"                               ; // API opencart
require_once 'db.php'                                   ; // wrap PDO
require_once(__DIR__ . '/rest_api/essence.php')         ; // API Bitrix

function notNull($var,$var2='null'){

    if($var == '')
        $var = $var2;
    return $var;
}


$date      = date("Y-m-d H:i:s")          ; // для записи в БД
$eseence   = new essenceAdd($webHookScript)      ; // библтотека для REST_API битрикс
$API       = new API($url)                       ; // объект api opencart

$id                     = $_POST['data']['FIELDS']['ID'];
$listData               = array(
    'order'  => array("STAGE_ID"    => "ASC"),
    'filter' => array("ID"          => $id),
    'select' => array("*", "UF_*","CLIENT"),
);

$deal       = $eseence->dealGet($id) ; // получаем сделку
$DEAL       = $deal['result']        ;
$order_id   = $DEAL['UF_CRM_1560320627'];

if($DEAL['UF_CRM_1560320627'] != 0 && $DEAL['UF_CRM_1559245394235'] == $storeID) // проверяем на нужный магазин и заполненное поле "id заказа в опенкарт"

$contact    = $eseence->contactGet($DEAL['CONTACT_ID']) ; // получаем контакт
$CONTACT    = $contact['result']                        ;

$products   = $eseence->dealProductrowsGet($id)         ; // получаем все товары
$productsBX = array()                                   ;

foreach ($products['result'] as $item) {

    $productsArrBX                      = $eseence->productGet($item['PRODUCT_ID'])         ; // берем товар из битрикса
    $productsArrBX['result']['bx_id']   = $productsArrBX['result']['PROPERTY_108']['value'] ;
    $productsBX[]                       = $productsArrBX['result']                          ;

}

foreach ($productsBX as &$item) {
    $item['oc_id'] = $API->getIdProduct($item['bx_id']); // берем id товара по modal_id
}


unset($item); // вызывало баг на строке  125

if(count($productsBX) > 0){

    $rowInDB = DB::getAll("SELECT * FROM `oc_order_product` WHERE `order_id` = $DEAL[UF_CRM_1560320627]"); // Узнаем сколько товаров было в сделке в опенкарте

    DB::set("UPDATE oc_order SET order_status_id = " . $order_status_bitrix[$DEAL['STAGE_ID']] . "  WHERE order_id = $order_id"); // пишем статус заказа

    $str   = "INSERT INTO oc_order_history (order_id, order_status_id, notify, comment, date_added) VALUES ( " . $order_id . " , " . $order_status_bitrix[$DEAL['STAGE_ID']] . " , 1 ,'$DEAL[COMMENTS]', '$date')";
    $resss = DB::add($str);
    //dd($resss);
    goto a;
    // код между goto оказался не нужным, оставлю его тут на всякий случай
    if(count($rowInDB) > 0 && count($productsBX) == 1){


        $rowInDB;
        $productName    = $productsBX[0]['NAME']    ;
        $productPRICE   = $productsBX[0]['PRICE']   ;
        $productMODEL   = $productsBX[0]['bx_id']   ;
        $orderID        = $DEAL['UF_CRM_1560320627'];

        $update_product = DB::set("UPDATE `oc_order_product` SET 
                                                                    `name`      = '$productName',
                                                                    `total`     = $productPRICE,                    
                                                                    `quantity`  = 1,
                                                                    `model`     = '$productMODEL'                                                                                                
                                                                    WHERE
                                                                     
                                                                    order_id = $DEAL[UF_CRM_1560320627]");

        if($update_product){

            $order_id   = $DEAL['UF_CRM_1560320627'];
            $q          = "UPDATE `oc_order_total` SET value= $productPRICE WHERE code = 'sub_total' AND order_id = $orderID";
            $res        = DB::set($q);

        }

    }elseif(count($rowInDB) > 0 && count($productsBX) > 1){
a:
        $q              = "DELETE FROM `oc_order_product` WHERE order_id = $order_id";
        $res            = DB::set($q);
        $update_product = 1;

        if($update_product){

              $order_id   = $DEAL['UF_CRM_1560320627'];

              $totalCahe  = 0;

            for ($i = 0; $i < count($productsBX); $i++){ // ответ на строке 78

                $item           = $productsBX[$i];
                $product_bitrix =  $item['oc_id'];
                $q              = "SELECT DISTINCT *, pd.name AS name, p.image, (SELECT md.name FROM oc_manufacturer_description md WHERE md.manufacturer_id = p.manufacturer_id AND md.language_id = '1') AS manufacturer, (SELECT price FROM oc_product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '1' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM oc_product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '1' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM oc_product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = '1') AS reward, (SELECT ss.name FROM oc_stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '1') AS stock_status, (SELECT wcd.unit FROM oc_weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '1') AS weight_class, (SELECT lcd.unit FROM oc_length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '1') AS length_class, (SELECT AVG(rating) AS total FROM oc_review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM oc_review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM oc_product p LEFT JOIN oc_product_description pd ON (p.product_id = pd.product_id) LEFT JOIN oc_product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN oc_manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '$product_bitrix' AND pd.language_id = '1' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '0'";
                $product        = DB::getAll($q);
                $product = $product[0];

                $totalCahe += $product['price'];
                $result  = DB::add("INSERT INTO `oc_order_product` (
                                    `order_id`,
                                    `product_id`,
                                    `name`,
                                    `model`,
                                    `weight`,
                                    `quantity`,
                                    `price`,
                                    `total`
                                )VALUES(

                                    $order_id,
                                    $product[product_id],
                                    '$product[name]',
                                    '$product[model]',
                                    '$product[weight]',
                                    1,
                                    '$product[price]',
                                    '$product[price]'
                                )
                                                            ");
            }

            $q        = "UPDATE `oc_order_total` SET value= $totalCahe WHERE code = 'sub_total' AND order_id = $order_id";
            $res      = DB::set($q);

            $q        = "SELECT `value` FROM `oc_order_total` WHERE `order_id` = $order_id AND `code` = 'shipping'";
            $shiping  = DB::getValue($q);

            $totalCahe = ($totalCahe + $shiping);

            $q        = "UPDATE `oc_order_total` SET value= $totalCahe WHERE code = 'total' AND order_id = $order_id";
            $res      = DB::set($q);

        }else{
            die();
        }


    }else{
        die;
    }
}
?>