<?php

namespace app\classes\eventHandlers;

use Yii;


class EventHandlerAddNumberToAts3
{
    protected $data = [];

    public static function create($data)
    {
        return new static($data);
    }

    private function __construct($data)
    {
        $this->data = $data;
    }

    public function addNumber()
    {
        $this->execQuery("add_did");
    }

    public function delNumber()
    {
        $this->execQuery("disable_did");
    }

    public function updateNumber()
    {
        $this->execQuery("edit_did");
    }

    public function changeClient()
    {
        $this->execQuery("edit_client_id");
    }

    public function blocked()
    {
        //function is not implemented
    }

    public function disabled()
    {
        //function is not implemented
    }


    private function execQuery($action)
    {
        \JSONQuery::exec("https://".PHONE_SERVER."/phone/api/".$action, $this->data);
    }
}
