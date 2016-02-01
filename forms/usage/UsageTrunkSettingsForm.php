<?php
namespace app\forms\usage;

use app\classes\Form;
use Yii;

class UsageTrunkSettingsForm extends Form
{
    public $id;
    public $usage_id;
    public $type;
    public $src_number_id;
    public $dst_number_id;
    public $pricelist_id;
    public $minimum_minutes;
    public $minimum_cost;

    public function rules()
    {
        return [
            [['id','usage_id','type','src_number_id','dst_number_id','pricelist_id', 'minimum_minutes', 'minimum_cost'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'src_number_id' => 'A номер',
            'dst_number_id' => 'B номер',
            'pricelist_id' => 'Прайслист',
        ];
    }
}