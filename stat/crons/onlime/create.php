<?php

class Onlime1CCreateBill
{
    public function create($o)
    {
        /*
        Array
            (
             [id] => 68
             [date] => 2013-08-27 20:58:35
             [fio] => Саньков Кирилл Андреевич
             [phones] => Array
             (
              [cell] => 926 3459974
             )

             [address] => Москва, 9-я Парковая улица | Измайловская | д. 49, корп. 1 | 22
             [delivery] => Array
             (
              [cost] => 0
              [date] => 2013-08-29
              [time] => Array
              (
               [from] => 10:00
               [to] => 15:00
              )

              [inside_mkad] => 1
             )

             [comment] =>
             [request] => Array
             (
              [status] => 0
              [text] =>
             )

             )
             */
        global $db;
        include_once INCLUDE_PATH."1c_integration.php";

        $bm = new \_1c\billMaker($db);

        $zeroDescr = "00000000-0000-0000-0000-000000000000";

        /*
        $metro = "";
        if(preg_match_all("/м\.(.*)$/six",$d['address'], $o ))
            $metro = $o[1][0];

        if($metro == "-")
            $metro = "";
            */
            $metro = "";

        $ai = array (
                'ФИО' => $o['fio'],
                'Адрес' => $o['address'],
                'НомерЗаявки' => $o["id"],
                'ЛицевойСчет' => '',
                'НомерПодключения' => '',
                'Комментарий1' => "Доставка на: ".$o["delivery"]["date"]." с ".$o["delivery"]["time"]["from"]." по ".$o["delivery"]["time"]["to"],
                'Комментарий2' => "Оператор: onlime_shop",
                'ПаспортСерия' => '',
                'ПаспортНомер' => '',
                'ПаспортКемВыдан' => '',
                'ПаспортКогдаВыдан' => '',
                'ПаспортКодПодразделения' => '',
                'ПаспортДатаРождения' => '',
                'ПаспортГород' => '',
                'ПаспортУлица' => '',
                'ПаспортДом' => '',
                'ПаспортКорпус' => '',
                'ПаспортСтроение' => '',
                'ПаспортКвартира' => '',
                'Email' => '',
                'ПроисхождениеЗаказа' => '',
                'КонтактныйТелефон' => self::_phoneToStr($o["phones"]),
                'Метро' => $metro,
                'Логистика' => '',
                'ВладелецЛинии' => '',
                );

        $res = array(
                "client_tid" => "onlime",
                "order_number" => false,//($d["bill_no"] ? $d["bill_no"] : false),//"201109/0094",//false,
                "items_list" => array(),
                "order_comment" => \_1c\trr($o["comment"]),
                "is_rollback" => false,
                "add_info" => $ai,
                "store_id" => "8e5c7b22-8385-11df-9af5-001517456eb1"
                );

        /*
           ид3=телекард,              12032   f75a5b2f-382f-11e0-9c3c-d485644c7711    12032
           ид9=хд ресивер онлайм,     14593   4acdb33c-0319-11e2-9c41-00155d881200    14593
           ид11=хд ресивер с диском   14787   72904487-32f6-11e2-9369-00155d881200    14787

           from 5.05.2014
           14723f35-d423-11e3-9fe5-00155d881200 16206 <= 14593 
           2c6d3955-d423-11e3-9fe5-00155d881200 16207 <= 14787

           from 18.07.2014
           ид12=NetGear Беспроводной роутер, JNR3210-1NNRUS e1a5bf94-0764-11e4-8c79-00155d881200 16315

         */

        foreach($o["products"] as $product)
        {
            switch($product["id"])
            {
                case '3': $goodId = "f75a5b2f-382f-11e0-9c3c-d485644c7711"; break;
                case '9': $goodId = "14723f35-d423-11e3-9fe5-00155d881200"; break;
                case '11': $goodId = "2c6d3955-d423-11e3-9fe5-00155d881200"; break;
                case '12': $goodId = "e1a5bf94-0764-11e4-8c79-00155d881200"; break;
            }

            if(isset($o["coupon"]) && isset($o["coupon"]["groupon"]) && $o["coupon"]["groupon"])
            {
                $goodId = "6d2dfd2a-211e-11e3-95df-00155d881200"; //15804  OnLime TeleCARD Акция
            }

            $res["items_list"][] =
                    array(
                        "id" => $goodId.":".$zeroDescr,
                        "quantity" => $product["quantity"],
                        "code_1c" => 0,
                        "price" => 1);
        }
        /*
           за мкад   13621 81d52245-4d6c-11e1-8572-00155d881200 
           по москве 13619 81d52242-4d6c-11e1-8572-00155d881200 
         */

            $res["items_list"][] =
                array(
                        "id" => "81d52242-4d6c-11e1-8572-00155d881200".":".$zeroDescr,
                        "quantity" => 1,
                        "code_1c" => 0,
                        "price" => 1);

        try{
            $null = null;
            $ret = $bm->saveOrder($res, $null, false);
        }catch(Exception $e){
            print_r($res);
            var_dump($e);
            exit();
        }

        $c1error = '';
        $cl = new stdClass();
        $cl->order = $ret;
        $cl->isRollback = false;

        $bill_no = $ret->{\_1c\tr('Номер')};

        $sh = new \_1c\SoapHandler();
        $sh->statSaveOrder($cl, $bill_no, $c1error);

        $od = new OnlimeDelivery();
        $od->bill_no = $bill_no;
        $od->delivery_date = $o["delivery"]["date"]." ".$o["delivery"]["time"]["from"];


        echo ">>>>".$o["delivery"]["date"]." ".$o["delivery"]["time"]["from"]."<<<<<";

        $od->save();

        return $bill_no;
    }

    private function _phoneToStr($phones)
    {
        $phone = array();

        $phone[] = isset($phones["home"]) ? $phones["home"] :"";
        $phone[] = isset($phones["cell"]) ? $phones["cell"] :"";
        $phone[] = isset($phones["work"]) ? $phones["work"] :"";

        return implode(" ^ ",$phone);
    }
    
}
