<?php

namespace app\modules\sim\filters;

use app\helpers\DateTimeZoneHelper;
use app\models\danycom\PhoneHistory;
use app\modules\sim\columns\PhoneHistory\DateColumn;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Фильтрация для PhoneHistory
 */
class PhoneHistoryFilter extends PhoneHistory
{
    public $id = '';
    public $date = '';
    public $phone_ported = '';
    public $state = '';
    public $date_sent = '';
    public $created_at = '';

    public $sort;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'phone_ported'], 'integer'],
            [['state', 'date_sent', 'created_at', 'date'], 'string'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     * @throws \Exception
     */
    public function search()
    {
        $query = PhoneHistory::find();
        $query->andWhere(new Expression('phone_contact = phone_ported'));

        $phoneHistoryTableName = PhoneHistory::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id !== '' && $query->andWhere([$phoneHistoryTableName . '.id' => $this->id]);

        if ($this->date !== '') {
            list($dateFrom, $dateTo) = explode(':', $this->date);

            $dateFrom = new \DateTimeImmutable(trim($dateFrom), new \DateTimeZone('UTC'));
            $dateTo = new \DateTimeImmutable(trim($dateTo), new \DateTimeZone('UTC'));

            $dateFrom = $dateFrom->format(DateTimeZoneHelper::DATE_FORMAT);
            $dateTo = $dateTo->format(DateTimeZoneHelper::DATE_FORMAT);

            $query->andWhere(['BETWEEN', "STR_TO_DATE(date_request, '%d.%m.%Y')", $dateFrom, $dateTo]);
        }

        if ($this->phone_ported !== '') {
            $query->andWhere(['LIKE', $phoneHistoryTableName . '.phone_ported', $this->phone_ported]);

            $query->addOrderBy(['phone_ported' => SORT_ASC]);
        }

        if ($this->date_sent !== '') {
            DateColumn::specifyQuery($query, "STR_TO_DATE(date_sent, '%d.%m.%Y')", $this->date_sent);
        }

        if ($this->created_at !== '') {
            DateColumn::specifyQuery($query, $phoneHistoryTableName . '.created_at', $this->created_at);
        }

        $this->state !== '' && $query->andWhere([$phoneHistoryTableName . '.state' => $this->state]);

        if (!$this->sort) {
            $query->addOrderBy(['id' => SORT_DESC]);
        }

        return $dataProvider;
    }
}
