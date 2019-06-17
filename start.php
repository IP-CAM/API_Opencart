<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: text/html; charset=utf-8');

require_once "config.php"                               ; // Кофигурация
require_once "config_directory.php"                     ; // Справочники значений
require_once "common.php"                               ; // API opencart
require_once 'db.php'                                   ; // wrap PDO
require_once(__DIR__ . '/rest_api/essence.php')         ; // API Bitrix

function notNull($var,$var2='null'){

    if($var == '')
        $var = $var2;
        return $var;
}
$eseence        = new essenceAdd($webHookScript);


    $id = $_POST['data']['FIELDS']['ID']; // тут ловим id сделки при создании
    $listData = array(
        'order'  => array("STAGE_ID"    => "ASC"),
        'filter' => array("ID"          => $id),
        'select' => array("*", "UF_*","CLIENT"),
    );

    $deal = $eseence->dealGet($id)  ; // получаем сделку
    $DEAL = $deal['result']         ;
    if($DEAL['UF_CRM_1559245394235'] == $storeID) // проверка на нужный магазин

    $contact         = $eseence->contactGet($DEAL['CONTACT_ID']); // получаем контакт
    $CONTACT         = $contact['result']                       ;

    $products   = $eseence->dealProductrowsGet($id) ; // получаем все товары
    $productsID = array()                           ;

    foreach ($products['result'] as $item) {

        $product        = $eseence->productGet($item['PRODUCT_ID'])     ; // берем товар из битрикса
        $model_id       = $product['result']['PROPERTY_108']['value']   ; // берем modelID
        $productsID[]   = $model_id                                     ; // Складываем все товары в одном массиве

    }

    // переменные для опенкарта
    $shipingMethod  = array('shipping_method' => 'revship2.revship2') ;

    $dataAdress     = array(

        'firstname'  => notNull($CONTACT['NAME'], 'user')                   ,
        'lastname'   => notNull($CONTACT['LAST_NAME'],'user')               ,
        'address_1'  => notNull($DEAL['UF_CRM_OC_SH_ADRESS'], 'Не указан')  ,
        'city'       => notNull($DEAL['UF_CRM_OC_SH_CITY'],'Не указан')     ,
        'country_id' => 'RUS',
        'zone_id'    => 'KGD'
    );
    $customer       = array(

        'firstname' => notNull($CONTACT['NAME'], 'не заполненно')         ,
        'lastname'  => notNull($CONTACT['LAST_NAME'], 'не заполненно')  ,
        'email'     => notNull($CONTACT['EMAIL'][0]['VALUE'],'Не_заполненно@gmail.com'),
        'telephone' => notNull($CONTACT['PHONE'][0]['VALUE'],'Не_заполненно')

    );

    $API  = new API($url)                               ; // объект api OpenCart
    $data = $API->login($username, $key, 1)  ; // логинимся

if ($data['token']) {


    $products_opencart_ID   = array();

    $cart                   = $API->showCart(); // смотрим что в корзине с предыдущего сеанса

    foreach ($cart['products'] as $item) {
        $API->productRemoveFromCart($item['cart_id']); // удаляем товары из корзины
    }

    foreach ($productsID as $item) {
        $products_opencart_ID[] = $API->getIdProduct($item); // берем id товара по modal_id
    }

    if (count($products_opencart_ID)  > 0) {

        foreach ($products_opencart_ID as $item_local) {
            $result = $API->addProduct($item_local, '1', 1); // кладем товар в корзину
        }

        if ($result['success']) {

            $result = $API->shippingAddress($dataAdress); // Установим адресс доставки

            if ($result['success']) {

                $result     = $API->shippingMethods();
                $shipMethod = $result['shipping_methods']['revship2']['quote']['revship2']['code'];
                $result     = $API->shippingMethod(array('shipping_method' => $shipMethod));

                if ($result['success']) {

                    $result = $API->paymentAddress($dataAdress);

                    if ($result['success']) {

                        $result = $API->paymentMethods()                                    ; // Все возможные варианты
                        $result = $result['payment_methods']['yandexplusplus']['code']      ; // Выбрать получается только есть взять элемент массива
                        $result = $API->paymentMethod(array('payment_method' => $result))   ; // Выбиравем метод оплаты

                        if ($result['success']) {

                            $result = $API->customer($customer); // установим данные о пользователе

                            if ($result['success']) {

                                $result = $API->orderAdd(); // Оформим корзину

                                if ($result['order_id'] > 1) {

                                    dl($result);

                                    $ORDER_ID   = $result['order_id'];
                                    $data       = array('UF_CRM_1560320627' => $result['order_id']) ; // установим в сделке ID заказа из опенкарта
                                    $result     = $eseence->dealUpdata($DEAL['ID'],$data)           ; // установим в сделке ID заказа из опенкарта
                                    $item       = DB::set("UPDATE `oc_order` SET `order_status_id` = " . $order_status_bitrix[$DEAL['STAGE_ID']] . " WHERE `order_id` = $ORDER_ID"); // пишем статус заказа

                                    if($DEAL['COMMENTS'] != '') // если есть комментарий

                                        $item = DB::set("UPDATE `oc_order_history` SET `comment` = '" . $DEAL['COMMENTS'] . "' WHERE `order_id` = $ORDER_ID"); // пишем комментарий

                                    return $item;

                                    dd($result,1);

                                }else{
                                    echo 'Что то не так с созданием заказа';
                                    dd($result);
                                }

                            }else{
                                echo 'Что то не так с данными о пользователе';
                                dd($result);
                            }
                        }else{
                            echo 'Что-то не так с платежным адрессом!';
                            dd($result);
                        }

                    } else {
                        echo 'Что-то не так с платежным адрессом!';
                        dd($result);
                    }

                } else {
                    echo 'Что-то не так с выбором метода доставки';
                    dd($result);
                }

                dd($result['shipping_methods']['revship2']['quote']['revship2']['code']);
            }else{
                echo 'Адресс доставки не прошел!';
                dd($result);

            }

        } else {
            echo "Неудалось добавить товар в корзину!";
            dd($result);
        }

    } else {
        echo 'Неудалось получить id товара по modal_id ';
        dd($products_opencart_ID);
    }


} else {
    echo "Токен не получен! <hr>";
    dd($data);
}