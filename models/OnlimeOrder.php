<?php
class OnlimeOrder extends ActiveRecord\Model
{
	static $table_name = 'onlime_order';
    static $primary_key = 'external_id';

    const STAGE_NEW = 'new'; // order parsed and added in db
    const STAGE_ADDED = 'add'; // order add in system
    const STAGE_ANSWERED = 'answer'; // onlime notice about order state

    public function saveOrder($order, $error)
    {
        if($error["status"] == "ignore" || (isset($error["possible_save"]) && !$error["possible_save"])) return null;

        $o = new OnlimeOrder();
        $o->external_id = $order["id"];
        $o->order_serialize = Encoding::toKoi8r(serialize($order));
        $o->status = 0;
        $o->stage = OnlimeOrder::STAGE_NEW;
        $o->error = "";

        //coupon fields
        $o->coupon = $order["coupon"]["groupon"];
        $o->seccode = $order["coupon"]["seccode"];
        $o->vercode = $order["coupon"]["vercode"];

        $o->save();

        return $o;
    }

    public function setStatus($status, $error)
    {
        $this->status = $status;
        $this->error = Encoding::toKoi8r($error);
        $this->save();
    }

    public function setInternalId($id) // internal id = bill_no
    {
        $this->bill_no = $id;
        $this->save();
    }

    public function setStage($stage)
    {
        $this->stage = $stage;
        $this->save();
    }

}
