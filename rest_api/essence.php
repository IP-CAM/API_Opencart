<?php
require_once(__DIR__.'/transferData.php');

class essenceAdd extends transferData {
    /* 
        Этот класс для добавления/редактирования/обновления сущностей.
        По конструкту требует url вебхука
    */

    /* методы */
    const   methodAdd         = 'crm.lead.add.json'       ;
    const   methodUpdata      = 'crm.lead.update.json'    ;
    const   listUserfiledList = 'crm.lead.userfield.list' ;
    const   leadList          = 'crm.lead.list'           ;
    const   leadGet           = 'crm.lead.get'            ;

    const   contactGet        = 'crm.contact.get'         ;
    const   contactAdd        = 'crm.contact.add'         ;
    const   contactList       = 'crm.contact.list'        ;
    const   contactDelete     = 'crm.contact.delete'      ;
    const   contactUpdate     = 'crm.contact.update'      ;

    const   companyGet        = 'crm.company.get'         ;
    const   companyUpdate     = 'crm.company.update'      ;
    const   companyList       = 'crm.company.list'        ;

    const   dealAdd             = 'crm.deal.add'                ;
    const   dealUpdata          = 'crm.deal.update'             ;
    const   dealList            = 'crm.deal.list'               ;
    const   dealGet             = 'crm.deal.get'                ;
    const   dealDelete          = 'crm.deal.delete'             ;
    const   dealcategoryStage   = 'crm.dealcategory.stage.list' ;
    const   dealProductrowsGet  = 'crm.deal.productrows.get'    ;

    const   productGet          = 'crm.product.get';

    public function __construct($url){

        $this->add                  = $url . self::methodAdd         ;
        $this->upData               = $url . self::methodUpdata      ;
        $this->leadGet              = $url . self::leadGet           ;
        $this->leadList             = $url . self::leadList          ;
        $this->companyList          = $url . self::companyList       ;

        $this->userFieldList        = $url . self::listUserfiledList ;
        
        $this->contactAdd           = $url . self::contactAdd        ;
        $this->contactGet           = $url . self::contactGet        ;
        $this->contactList          = $url . self::contactList       ;
        $this->contactDelete        = $url . self::contactDelete     ;
        $this->contactUpdate        = $url . self::contactUpdate     ;

        $this->companyGet           = $url . self::companyGet        ;
        $this->companyUpdate        = $url . self::companyUpdate     ;
        
        $this->dealAdd              = $url . self::dealAdd           ;
        $this->dealUpdata           = $url . self::dealUpdata        ;
        $this->dealList             = $url . self::dealList          ;
        $this->dealGet              = $url . self::dealGet           ;
        $this->dealDelete           = $url . self::dealDelete        ;
        $this->dealcategoryStage    = $url . self::dealcategoryStage ;
        $this->dealProductrowsGet   = $url . self::dealProductrowsGet;
        $this->productGet           = $url . self::productGet        ;
    }

    public function companyUpdate($id, $arr){
        $data = array(
            'id'        =>  $id,
            'fields'    =>  $arr,
            'params'    =>  array("REGISTER_SONET_EVENT" => "Y")
        );
        return parent::curlStart($this->companyUpdate, $data);
    }

    public function dealcategoryStage($id){
        $data = array(
            'id'    => $id,
        );
        return parent::curlStart($this->dealcategoryStage, $data);
    }

    public function contactGet($id){
        $data = array(
            'id'    => $id,
        );
        return parent::curlStart($this->contactGet, $data);
    }

    public function productGet($id){
        $data = array(
            'id'    => $id,
        );
        return parent::curlStart($this->productGet, $data);
    }



    public function dealProductrowsGet($id){
        $data = array(
            'id'    => $id,
        );
        return parent::curlStart($this->dealProductrowsGet, $data);
    }



    public function companyGet($id){
        $data = array(
            'id'    => $id,
        );
        return parent::curlStart($this->companyGet, $data);
    }

    public function leadGet($id){
        $data = array(
            'id'    => $id,
        );
        return parent::curlStart($this->leadGet, $data);
    }

    public function dealDelete($id){
        $data = array(
            'id'    => $id,
        );
        return parent::curlStart($this->dealDelete, $data);
    }

    public function contactDelete($id){
        $data = array(
            'id'    => $id,
        );
        return parent::curlStart($this->contactDelete, $data);
    }

    public function dealGet($id){
        $data = array(
            'id'    => $id,
        );
        return parent::curlStart($this->dealGet, $data);
    }

    public function leadList($data){
        return parent::curlStart($this->leadList, $data);
    }

    public function contactList($data){
        return parent::curlStart($this->contactList, $data);
    }

    public function companyList($data){
        return parent::curlStart($this->companyList, $data);
    }

    public function contactAdd($data, $phone){
        $data = array(
            "fields" => $data,
            'params' => array("REGISTER_SONET_EVENT" => "Y")
        );
        if(is_numeric($phone)){
            $data['fields']['PHONE'] = array(array("VALUE" => $phone, "VALUE_TYPE" => "WORK"));
        }
        return parent::curlStart($this->contactAdd, $data);
    }

    public function add($data,$phone){
        $data = array(
            'fields' => $data,
            'params' => array("REGISTER_SONET_EVENT" => "Y")
        );

        if(is_numeric($phone)){
            $data['fields']['PHONE'] = array(array("VALUE" => $phone, "VALUE_TYPE" => "WORK"));
        }
        $result =  parent::curlStart($this->add, $data);

        if(!$result['result'] > 1){
            $result = FALSE;
        }
        return $result;
    }

    public function listUserfiled(){
        $data = array(
            'order'     => array( "SORT"        => "ASC" ),
            'filter'    => array( "MANDATORY"   => "N"   )
        );
        return  parent::curlStart($this->userFieldList, $data)  ;
    }

    public function leadUpdata($id, $arr){
        $data = array(
            'id'        =>  $id,
            'fields'    =>  $arr,
            'params'    =>  array("REGISTER_SONET_EVENT" => "Y")
        );
        return parent::curlStart($this->upData, $data);
    }

    public function dealUpdata($id, $arr){
        $data = array(
            'id'        =>  $id,
            'fields'    =>  $arr,
            'params'    =>  array("REGISTER_SONET_EVENT" => "Y")
        );
        return parent::curlStart($this->dealUpdata, $data);
    }

    public function dealList($data){
        return parent::curlStart($this->dealList, $data);
    }

    public function dealAdd($data, $phone){
        $data = array(
            'fields' => $data,
            'params' => array("REGISTER_SONET_EVENT" => "Y")
        );

        $result =  parent::curlStart($this->dealAdd, $data);

        if(!$result['result'] > 1){
            $result = FALSE;
        }
        return $result;
    }
    public function contactUpdate($id, $arr){
        $data = array(
            'id'        =>  $id,
            'fields'    =>  $arr,
            'params'    =>  array("REGISTER_SONET_EVENT" => "Y")
        );
        return parent::curlStart($this->contactUpdate, $data);
    }

     /* получить значение промокода, либо вернет false */
     public function setPromoInDeal($promo,$dealId=false,$api,$result = false){

        $api .= $promo                                  ; // формируем url
        $data = json_decode( file_get_contents($api),1) ; // получаем ответ

        $this::writeToLog($data,'зашлa data');

        if($data['valid']){
            $value = $data['value'] ;
            $type  = $data['type']  ;

            // обновим сделку
            if($type != 'fix'){
                $arr = array("UF_DISC_PROMOCODE"    => $value); // для процентов
            }else{
                $arr = array("UF_DISC_PROMOCODE" => $value); // для фиксированной цены
            }

            if($result){
                return $arr;
            }

            $data = array(
                'id'        =>  $dealId ,
                'fields'    =>  $arr    ,
                'params'    =>  array("REGISTER_SONET_EVENT" => "N")
            );

            return parent::curlStart($this->dealUpdate, $data); // обновляем сделку

        }else{
            return false;
        }
        
    }
}
?>