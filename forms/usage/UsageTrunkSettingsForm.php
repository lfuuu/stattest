<?php
namespace app\forms\usage;

use app\classes\Form;
use Yii;

/**
 * Class UsageTrunkSettingsForm
 */
class UsageTrunkSettingsForm extends Form
{
    public $id;
    public $usage_id;
    public $type;
    public $src_number_id;
    public $dst_number_id;
    public $pricelist_id;
    public $package_id;
    public $minimum_minutes;
    public $minimum_cost;
    public $minimum_margin;
    public $minimum_margin_type;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'id',
                    'usage_id',
                    'type',
                    'src_number_id',
                    'dst_number_id',
                    'pricelist_id',
                    'package_id',
                    'minimum_minutes',
                    'minimum_cost',
                    'minimum_margin_type'
                ],
                'integer'
            ],
            [['minimum_margin'], 'double'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'src_number_id' => 'A номер',
            'dst_number_id' => 'B номер',
            'pricelist_id' => 'Прайслист',
            'package_id' => 'Пакет',
        ];
    }
}
