<?php


class OnlimeCheckOrders
{
    public function check($orders)
    {
        //$orders = array($orders[1]);

        $checkedOrders = array();

        foreach($orders as $order)
        {
            $error = "";

            self::_checkValuesFormat($order, $error);

            /*
            if(!$error)
                self::_checkValuesInDB($order, $error);
                */

            if(!$error)
                self::_checkValuesDelivRange($order, $error);

            //self::_saveOrder($order, $error);

            $checkedOrders[] = array("order" => $order, "error" => $error);
        }

        return $checkedOrders;


    }

    private function _checkValuesFormat($order, &$error)
    {
        try{
            $isPossibleSave = false;
            CheckFormat::isEmpty($order["id"], "ID не задан");
            CheckFormat::isNotInt($order["id"], "ID имеет неправльный формат (".$order["id"].")");
            CheckFormat::isZero($order["id"], "ID не задан");
            $isPossibleSave = true;

            CheckFormat::isDateDB($order["date"], "Дата задана не верно (".$order["date"].")");
            CheckFormat::isEq($order["date"], "0000-00-00 00:00:00", "Задана пустая дата");

            CheckFormat::isEmpty($order["fio"], "ФИО не задано");

            CheckFormat::isEmpty($order["phones"], "Не найден контактный телефон");

            CheckFormat::isEmpty($order["address"], "Адрес не задан");

            CheckFormat::isDate($order["delivery"]["date"], "Дата доставки задана не верно");
            CheckFormat::isEq($order["delivery"]["date"], "0000-00-00", "Не задана дата доставки");

            CheckFormat::isEmpty($order["delivery"]["time"]["from"], "Время доставки не задано");
            CheckFormat::isTime($order["delivery"]["time"]["from"], "Не верно задано время доставки");
            CheckFormat::isEq($order["delivery"]["time"]["from"], "00:00", "Не верно задано время доставки");

            CheckFormat::isEmpty($order["delivery"]["time"]["to"], "Время доставки не задано");
            CheckFormat::isTime($order["delivery"]["time"]["to"], "Не верно задано время доставки");
            CheckFormat::isEq($order["delivery"]["time"]["to"], "00:00", "Не верно задано время доставки");

            CheckFormat::isEmpty($order["products"], "Продукты заказа не найдены");

            foreach($order["products"] as $idx => $product)
            {
                CheckFormat::isEmpty($product["id"], "Id продукта #".($idx+1)." не задано");
                CheckFormat::isNotInt($product["id"], "Id продукта #".($idx+1)." задано неверно");
                CheckFormat::isNotInArray($product["id"], array(3, 9, 11, 12, 18, 19, 20, 21, 22), "Id продукта #".($idx+1)." задано неверно");

                CheckFormat::isEmpty($product["quantity"], "Количество продукта #".($idx+1)." не задано");
                CheckFormat::isNotInt($product["quantity"], "Количество продукта #".($idx+1)." задано неверно");
                CheckFormat::isNotBetween($product["quantity"], 1, 10, "Количество продукта #".($idx+1)." задано неверно.");
            }

        }catch(Exception $e)
        {
            $error = array("status" => "critical", "message" => $e->getMessage());

            if(!$isPossibleSave)
                $error["possible_save"] = false;
        }

    }

    private function _checkValuesInDB($order, &$error)
    {
        $r = OnlimeOrder::find_by_external_id($order["id"]);        

        if($r)
            $error = array("status" => "ignore", "message" => "Заказ уже сохранен");
    }

    private function _checkValuesDelivRange($order, &$error)
    {
        try{
            OnlimeDeliveryLimit::checkOnDate($order["delivery"]["date"], $order["delivery"]["time"]["from"]);
        }catch(Exception $e){
            $error = array("status" => "critical", "message" => $e->getMessage());
        }
    }

            


}

