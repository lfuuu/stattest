<?php

class VoipNetworkFile extends ActiveRecord\Model
{
    static $connection = 'voip';
    static $db = 'voip';
    static $table_name = 'network_file';
    static $sequence = 'voip.network_file_id_seq';
    static $primary_key = array('id');

    static $belongs_to = array(
        array('config', 'class_name' => 'VoipNetworkConfig', 'foreign_key' => 'network_config_id'),
    );

    public function __construct(array $attributes=array(), $guard_attributes=true, $instantiating_via_find=false, $new_record=true)
    {
        parent::__construct($attributes, $guard_attributes, $instantiating_via_find, $new_record);

        if ($new_record) {
            $this->assign_attribute('id', null);
            $this->assign_attribute('network_config_id', null);
            $this->assign_attribute('startdate', null);
            $this->assign_attribute('created_at', null);
            $this->assign_attribute('active', 'f');
            $this->assign_attribute('created_at', null);
            $this->assign_attribute('rows', 0);
            $this->assign_attribute('filename', '');
        }
    }

}
