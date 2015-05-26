<?php
namespace app\forms\usage;

use app\classes\Assert;
use app\classes\Form;
use app\models\ClientAccount;
use app\models\Number;
use yii\db\Query;

class NumberDetailReportForm extends Form
{
    public $cityId;
    public $didGroupIds;
    public $statuses;

    public function rules()
    {
        return [
            [['cityId', 'didGroupIds', 'statuses'], 'safe'],
        ];
    }

    /**
     * @return Query
     */
    public function spawnQuery()
    {
        return Number::find();
    }

    public function applyFilter(Query $query)
    {
        if ($this->cityId) {
            $query->andWhere(['voip_numbers.city_id' => $this->cityId]);
        }
    }

}