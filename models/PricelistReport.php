<?php

use \ActiveRecord\DateTime;

class PricelistReport extends ActiveRecord\Model
{
    static $connection = 'voip';
    static $db = 'voip';
    static $table_name = 'pricelist_report';
    static $sequence = 'voip.pricelist_report_id_seq';
    static $primary_key = 'id';

    const TYPE_ROUTING = 1;
    const TYPE_OPERATOR = 2;
    const TYPE_ANALYZE = 3;

    public function __construct(array $attributes=array(), $guard_attributes=true, $instantiating_via_find=false, $new_record=true)
    {
        parent::__construct($attributes, $guard_attributes, $instantiating_via_find, $new_record);

        if ($new_record) {
            $this->assign_attribute('id', null);
            $this->assign_attribute('name', '');
            $this->assign_attribute('report_type_id', 0);
            $this->assign_attribute('pricelist_ids', '{}');
            $this->assign_attribute('dates', '{}');
            $this->assign_attribute('generated_at', null);
            $this->assign_attribute('instance_id', null);
            $this->assign_attribute('use_rossvyaz_codes', false);
        }
    }

    public function getFields()
    {
        $pricelist_ids = $this->pricelist_ids;
        $dates = $this->dates;

        $fields = array();
        $i = 0;
        while (isset($pricelist_ids[$i]) && isset($dates[$i])) {
            $fields[] = array(
                'pricelist_id' => (int)$pricelist_ids[$i],
                'pricelist' => Pricelist::getCachedById((int)$pricelist_ids[$i]),
                'date' => $dates[$i] !== 'NULL' ? DateTime::createFromFormat('Y-m-d', $dates[$i])->setTime(0, 0, 0) : null,
            );
            $i++;
        }

        return $fields;
    }

    public function get_pricelist_ids()
    {
        $pricelist_ids = $this->read_attribute('pricelist_ids');
        $pricelist_ids = substr($pricelist_ids, 1, strlen($pricelist_ids) - 2);
        return $pricelist_ids ? explode(',', $pricelist_ids) : array();
    }

    public function set_pricelist_ids($value)
    {
        $this->assign_attribute('pricelist_ids', '{' . implode(',', $value) . '}');
    }

    public function get_dates()
    {
        $dates = $this->read_attribute('dates');
        $dates = substr($dates, 1, strlen($dates) - 2);
        return $dates ? explode(',', $dates) : array();
    }

    public function set_dates($value)
    {
        $this->assign_attribute('dates', '{' . implode(',', $value) . '}');
    }
}
