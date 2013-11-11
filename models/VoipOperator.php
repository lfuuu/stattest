<?php

class VoipOperator extends ActiveRecord\Model
{
    static $connection = 'voip';
    static $db = 'voip';
    static $table_name = 'operator';
    static $primary_key = array('region', 'id');


    static public function getByIdAndInstanceId($id, $instanceId)
    {
        return
            self::find('first',
                array('conditions' => array('region' => $instanceId, 'id' => $id))
            );
    }

    public function getRegionName()
    {
        try {
            return Region::getCachedById($this->region)->name;
        } catch (\ActiveRecord\RecordNotFound $e) {
            return '';
        }
    }
}
