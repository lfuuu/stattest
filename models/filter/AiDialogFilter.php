<?php

namespace app\models\filter;

use app\classes\grid\ActiveDataProvider;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\AiDialogRaw;
use app\models\billing\api\ApiRaw;
use DateTimeImmutable;
use Yii;
use yii\db\Expression;

class AiDialogFilter extends AiDialogRaw
{
    public $accountId = null;
    public $isLoad = false;

    public
        $action_start_from = '',
        $action_start_to = '',

        $duration_from = '',
        $duration_to = '',

        $agent_id = '',
        $agent_name = ''
    ;

    public function rules()
    {
        $fieldList = ['action_start_from', 'action_start_to', 'duration_from', 'duration_to', 'agent_id', 'agent_name'];
        return array_merge(parent::rules(), [
            [$fieldList, 'required'],
            [array_merge($fieldList), 'string'],
        ]);
    }

    /**
     * @param int $clientId
     * @return $this
     */
    public function load($clientId)
    {
        $this->accountId = $clientId;

        $get = Yii::$app->request->get();

        if (!is_array($get)) {
            $get = [];
        }

        $classData = explode('\\', self::className());
        $className = array_pop($classData);

        if (!isset($get[$className])) {
            $now = new DateTimeImmutable('now');
            $get[$className] = [
                'action_start_from' => $now->modify('first day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
                'action_start_to' => $now->format(DateTimeZoneHelper::DATE_FORMAT)
            ];
        }
        parent::load($get);

        return $this;
    }

    private function makeQuery()
    {
        $query = AiDialogRaw::find()->with('clientAccount');

        if (!($this->action_start_from && $this->action_start_to && $this->accountId)) {
            $query->andWhere('false');
        } else {
            $this->isLoad = true;
            $query->andWhere(['between', 'action_start', $this->action_start_from . ' 00:00:00', $this->action_start_to . ' 23:59:59.999999']);
        }

        if ($this->duration_from && $this->duration_to) {
            $query->andWhere(['between', 'duration', $this->duration_from, $this->duration_to]);
        }

        $query->andWhere(['account_id' => $this->accountId]);

        $this->agent_id && $query->andWhere(['agent_id' => $this->agent_id]);
        $this->agent_name && $query->andWhere(['agent_name' => $this->agent_name]);
        return $query;
    }

    /**
     * @return bool|ActiveDataProvider
     */
    public function search()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $this->makeQuery(),
            'db' => AiDialogRaw::getDb(),
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC,
                ]
            ],
        ]);

        return $dataProvider;
    }

    public function getTotal()
    {
        $query = $this->makeQuery();

        $query->select([
            'sum_sec' => new Expression('sum(duration)'),
            'sum_min' => new Expression('sum(ceil(duration/60.0))'),
        ]);

        return $query->asArray()->one();
    }
}