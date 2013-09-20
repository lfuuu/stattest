<?php


class OnlimeParserXML
{
    function parse($xml)
    {
        $orders = array();
        foreach($xml->order_item as $i)
        {

            $phones = array();
            foreach(array("cell", "home", "work") as $phoneType)
            {
                if(isset($i->phones->{$phoneType}))
                {
                    $phones[$phoneType] = (string)$i->phones->{$phoneType};
                }
            }

            if(isset($i->delivery))
            {
                $delivTimeAttr = $i->delivery->time->attributes();
                $delivery = array(
                        "cost" => (float)$i->delivery->cost,
                        "date" => (string)$i->delivery->date,
                        "time" => array("from" => (string)$delivTimeAttr["time_from"], "to" => (string)$delivTimeAttr["time_to"]),
                        "inside_mkad" => 1
                        );
            }else{
                $delivTimeAttr = $i->delivery_time->attributes();
                $delivery = array(
                        "cost" => (float)$i->delivery_cost,
                        "date" => (string)$i->delivery_date,
                        "time" => array("from" => (string)$delivTimeAttr["time_from"], "to" => (string)$delivTimeAttr["time_to"]),
                        "inside_mkad" => 1
                        );
            }

            foreach(array("from", "to") as $f)
                $delivery["time"][$f] = str_replace(".", ":", $delivery["time"][$f]);

            $products = array();
            if(isset($i->order_product))
            {
                foreach($i->order_product as $product)
                {
                    $productAttrs = $product->product->attributes();

                    $products[] = array(
                            "id" => isset($productAttrs->id) ? (string)$productAttrs->id : "",
                            "quantity" => isset($productAttrs->quantity) ? (string)$productAttrs->quantity : "",
                            );
                }
            }

            $coupon = array(
                "groupon" => "",
                "seccode" => "0",
                "vercode" => ""
                );

            if(isset($i->coupon))
            {
                $couponAttr = $i->coupon->attributes();

                if(isset($couponAttr->groupon)) $coupon["groupon"] = trim((string)$couponAttr->groupon);
                if(isset($couponAttr->seccode)) $coupon["seccode"] = trim((string)$couponAttr->seccode);
                if(isset($couponAttr->vercode)) $coupon["vercode"] = trim((string)$couponAttr->vercode);
            }


            $order = array(
                    "id" => trim((string)$i->id),
                    "date" => trim((string)$i->date),
                    "fio" => trim((string)$i->name),
                    "phones" => $phones,
                    "address" => trim((string)$i->address),
                    "delivery" => $delivery,
                    "products" => $products,
                    "comment" => trim((string)$i->comment),
                    "request" => array(
                        "status" => trim((string)$i->request_status),
                        "text" => trim((string)$i->request_text)
                        ),
                    "coupon" => $coupon
                    );


            $orders[] = $order;
        }
        return $orders;
    }
}
