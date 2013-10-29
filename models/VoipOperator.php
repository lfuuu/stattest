<?php

class VoipOperator extends ActiveRecord\Model
{
    static $connection = 'voip';
    static $db = 'voip';
    static $table_name = 'operator';


    static public function getByIdAndInstanceId($id, $instanceId)
    {
        return
            self::find('first',
                array('conditions' => array('region' => $instanceId, 'id' => $id))
            );
    }
}
