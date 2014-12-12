<?php

class EventQueue extends ActiveRecord\Model
{
    static $table_name = 'event_queue';

    public static function getPlanedEvents()
    {
        return self::find("all", array(
            "conditions" => array("status" => "plan"),
            "order" => "id"
            )
        );
    }

    public static function getPlanedErrorEvents()
    {
        return self::find("all", array(
            "conditions" => array("status = ? and next_start < NOW()", ["error"]),
            "order" => "id"
        ));
    }

    public function setOk()
    {
        $this->status = 'ok';
        $this->save();
    }

    public function setError(Exception $e = null)
    {
        list($this->status, $this->next_start) = self::setNextStart($this);
        $this->iteration++;

        if ($e)
        {
            $this->log_error = "code: ".$e->getCode()."; message: ".$e->getMessage()."; ".$e->getTraceAsString();
            Yii::error($e);
        }

        $this->save();
    }

    private static function setNextStart($o)
    {
        if (substr($o->event, 0, 6) != "ats3__")
            $o->iteration = 19;

        switch ($o->iteration)
        {
            case 1: $time = "+1 minute"; break;
            case 2: $time = "+2 minute"; break;
            case 3: $time = "+3 minute"; break;
            case 4: $time = "+5 minute"; break;
            case 5: $time = "+10 minute"; break;
            case 6: $time = "+20 minute"; break;
            case 7: $time = "+30 minute"; break;
            case 8: $time = "+1 hour"; break;
            case 9: $time = "+2 hour"; break;
            case 10: $time = "+3 hour"; break;
            case 11: $time = "+6 hour"; break;
            case 12: $time = "+12 hour"; break;
            case 13: $time = "+1 day"; break;
            case 14: $time = "+1 day"; break;
            case 15: $time = "+1 day"; break;
            default: 
                return array('stop', date('Y-m-d H:i:s'));
        }

        return array('error', date('Y-m-d H:i:s', strtotime($time)));
    }

    public static function clean()
    {
        EventQueue::table()->conn->query("delete from event_queue where date < date_sub(now(), INTERVAL 3 month)");
    }
}
