<?php
class EventQueue extends ActiveRecord\Model
{
    static $table_name = 'event_queue';

    public function getUnhandledEvents()
    {
        return self::find("all", array("conditions" => array("is_handled" => 0, "is_stoped" => 0)));
    }

    public function setHandled()
    {
        $this->is_handled = 1;
        $this->save();
    }

    public function setStoped()
    {
        $this->is_stoped = 1;
        $this->save();
    }
}
