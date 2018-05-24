<?php

namespace app\modules\callTracking\filter;

use app\classes\grid\ActiveDataProvider;
use app\modules\callTracking\models\Log;
use Yii;

class LogFilter extends Log
{
    public $id = '';
    public $account_tariff_id = '';
    public $voip_number = '';

    public $start_dt_from = '';
    public $start_dt_to = '';

    public $disconnect_dt_from = '';
    public $disconnect_dt_to = '';

    public $stop_dt_from = '';
    public $stop_dt_to = '';

    public $user_agent = '';
    public $ip = '';
    public $url = '';
    public $referrer = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'account_tariff_id', 'voip_number',], 'integer'],
            [['start_dt_from', 'start_dt_to',], 'string'],
            [['disconnect_dt_from', 'disconnect_dt_to',], 'string'],
            [['stop_dt_from', 'stop_dt_to'], 'string',],
            [['user_agent', 'ip', 'url', 'referrer',], 'string'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Log::find();

        $dataProvider = new ActiveDataProvider([
            'db' => Yii::$app->dbPgCallTracking,
            'query' => $query,
        ]);

        $this->id !== '' && $query->where(['id' => $this->id]);
        $this->account_tariff_id !== '' && $query->where(['account_tariff_id' => $this->account_tariff_id]);
        $this->voip_number !== '' && $query->where(['voip_number' => $this->voip_number]);
        $this->user_agent !== '' && $query->andWhere(['user_agent' => $this->user_agent]);
        $this->ip !== '' && $query->andWhere(['ip' => $this->ip]);
        $this->url !== '' && $query->andWhere(['url' => $this->url]);
        $this->referrer !== '' && $query->andWhere(['referrer' => $this->referrer]);

        $this->start_dt_from !== '' && $query->andWhere(['>=', 'start_dt', $this->start_dt_from]);
        $this->start_dt_to !== '' && $query->andWhere(['<=', 'start_dt', $this->start_dt_to]);

        $this->disconnect_dt_from !== '' && $query->andWhere(['>=', 'disconnect_dt', $this->disconnect_dt_from]);
        $this->disconnect_dt_to !== '' && $query->andWhere(['<=', 'disconnect_dt', $this->disconnect_dt_to]);

        $this->stop_dt_from !== '' && $query->andWhere(['>=', 'stop_dt', $this->stop_dt_from]);
        $this->stop_dt_to !== '' && $query->andWhere(['<=', 'stop_dt', $this->stop_dt_to]);

        return $dataProvider;
    }
}
