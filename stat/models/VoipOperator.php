<?php

class VoipOperator extends ActiveRecord\Model
{
    static $connection = 'voip';
    static $db = 'voip';
    static $table_name = 'operator';
    static $primary_key = array('region', 'id');

    public function __construct(array $attributes=array(), $guard_attributes=true, $instantiating_via_find=false, $new_record=true)
    {
        parent::__construct($attributes, $guard_attributes, $instantiating_via_find, $new_record);

        if ($new_record) {
            $this->assign_attribute('region', null);
            $this->assign_attribute('id', null);
            $this->assign_attribute('short_name', null);
            $this->assign_attribute('name', null);
            $this->assign_attribute('pricelist_id', null);
            $this->assign_attribute('operator_7800_pricelist_id', null);
            $this->assign_attribute('client_7800_pricelist_id', null);
            $this->assign_attribute('minimum_payment', null);
            $this->assign_attribute('term_in_cost', null);
            $this->assign_attribute('stat_client_card_id', null);
        }
    }

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
