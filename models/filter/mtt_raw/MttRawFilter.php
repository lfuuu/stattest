<?php

namespace app\models\filter\mtt_raw;

use app\models\mtt_raw\MttRaw;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для MttRawFilter
 *
 * Class MttRawFilter
 * @package app\models\filter\mtt_raw
 */
class MttRawFilter extends MttRaw
{
    public $id = '';
    public $account_id = '';
    public $number_service_id = '';
    public $serviceid = '';

    public $connect_time_from = '';
    public $connect_time_to = '';

    public $chargedqty_from = '';
    public $chargedqty_to = '';

    public $chargedamount_from = '';
    public $chargedamount_to = '';

    public $usedqty_from = '';
    public $usedqty_to = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['account_id'], 'integer'],
            [['number_service_id'], 'integer'],
            [['serviceid'], 'integer'],
            [['connect_time_from', 'connect_time_to'], 'string'],
            [['src_number'], 'integer'],
            [['dst_number'], 'integer'],
            [['chargedqty_from', 'chargedqty_to'], 'integer'],
            [['usedqty_from', 'usedqty_to'], 'integer'],
            [['chargedamount_from', 'chargedamount_to'], 'double'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = MttRaw::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->src_number !== '' && $query->andWhere(['src_number' => $this->src_number]);
        $this->account_id !== '' && $query->andWhere(['account_id' => $this->account_id]);
        $this->number_service_id !== '' && $query->andWhere(['number_service_id' => $this->number_service_id]);
        $this->serviceid !== '' && $query->andWhere(['serviceid' => $this->serviceid]);

        $this->connect_time_from !== '' && $query->andWhere(['>=', 'connect_time', $this->connect_time_from]);
        $this->connect_time_to !== '' && $query->andWhere(['<=', 'connect_time', $this->connect_time_to]);

        $this->chargedqty_from !== '' && $query->andWhere(['>=', 'chargedqty', $this->chargedqty_from]);
        $this->chargedqty_to !== '' && $query->andWhere(['<=', 'chargedqty', $this->chargedqty_to]);

        $this->chargedamount_from !== '' && $query->andWhere(['>=', 'chargedamount', $this->chargedamount_from]);
        $this->chargedamount_to !== '' && $query->andWhere(['<=', 'chargedamount', $this->chargedamount_to]);

        $this->usedqty_from !== '' && $query->andWhere(['>=', 'usedqty', $this->usedqty_from]);
        $this->usedqty_to !== '' && $query->andWhere(['<=', 'usedqty', $this->usedqty_to]);

        return $dataProvider;
    }
}
