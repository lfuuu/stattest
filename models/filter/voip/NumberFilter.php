<?php

namespace app\models\filter\voip;

use app\classes\Connection;
use app\classes\traits\GetListTrait;
use app\models\City;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * Фильтрация для Number
 */
class NumberFilter extends \app\models\Number
{
    const ROWS_PER_PAGE = 100;

    public $number = '';
    public $city_id = '';
    public $status = '';
    public $did_group_id = '';
    public $beauty_level = '';
    public $usage_id = '';
    public $client_id = '';
    public $country_id = '';
    public $number_type = '';

    public $calls_per_month_2_from = '';
    public $calls_per_month_2_to = '';

    public $calls_per_month_1_from = '';
    public $calls_per_month_1_to = '';

    public $calls_per_month_0_from = '';
    public $calls_per_month_0_to = '';

    public $number_tech = '';

    public function rules()
    {
        return [
            [['number', 'status', 'number_tech'], 'string'],
            [['city_id', 'beauty_level', 'usage_id', 'client_id', 'country_id', 'number_type'], 'integer'], // , 'did_group_id'
            [['calls_per_month_2_from', 'calls_per_month_2_to'], 'integer'],
            [['calls_per_month_1_from', 'calls_per_month_1_to'], 'integer'],
            [['calls_per_month_0_from', 'calls_per_month_0_to'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = \app\models\Number::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => self::ROWS_PER_PAGE,
            ],
        ]);

        $numberTableName = \app\models\Number::tableName();

        // если ['LIKE', 'number', $mask], то он заэскейпит спецсимволы и добавить % в начало и конец. Подробнее см. \yii\db\QueryBuilder::buildLikeCondition
//        $this->number !== '' &&
//        ($this->number = strtr($this->number, ['.' => '_', '*' => '%'])) &&
//        $query->andWhere('number LIKE :number', [':number' => $this->number]);

        $this->number !== '' && $query->andWhere(['LIKE', $numberTableName . '.number', $this->number]);

        $this->city_id !== '' && $query->andWhere([$numberTableName . '.city_id' => $this->city_id]);
        $this->status !== '' && $query->andWhere([$numberTableName . '.status' => $this->status]);
        $this->beauty_level !== '' && $query->andWhere([$numberTableName . '.beauty_level' => $this->beauty_level]);
        $this->did_group_id !== '' && $query->andWhere([$numberTableName . '.did_group_id' => $this->did_group_id]);
        $this->number_type !== '' && $query->andWhere([$numberTableName . '.number_type' => $this->number_type]);
        $this->number_tech !== '' && $query->andWhere([$numberTableName . '.number_tech' => $this->number_tech]);

        $this->calls_per_month_2_from !== '' && $query->andWhere(['>=', $numberTableName . '.calls_per_month_2', $this->calls_per_month_2_from]);
        $this->calls_per_month_2_to !== '' && $query->andWhere(['<=', $numberTableName . '.calls_per_month_2', $this->calls_per_month_2_to]);

        $this->calls_per_month_1_from !== '' && $query->andWhere(['>=', $numberTableName . '.calls_per_month_1', $this->calls_per_month_1_from]);
        $this->calls_per_month_1_to !== '' && $query->andWhere(['<=', $numberTableName . '.calls_per_month_1', $this->calls_per_month_1_to]);

        $this->calls_per_month_0_from !== '' && $query->andWhere(['>=', $numberTableName . '.calls_per_month_0', $this->calls_per_month_0_from]);
        $this->calls_per_month_0_to !== '' && $query->andWhere(['<=', $numberTableName . '.calls_per_month_0', $this->calls_per_month_0_to]);

        if ($this->country_id !== '') {
            $cityTableName = City::tableName();
            $query->joinWith('city');
            $query->andWhere([$cityTableName . '.country_id' => $this->country_id]);
        }

        switch ($this->usage_id) {
            case GetListTrait::$isNull:
                $query->andWhere($numberTableName . '.usage_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberTableName . '.usage_id IS NOT NULL');
                break;
            default:
                break;
        }

        switch ($this->client_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberTableName . '.client_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberTableName . '.client_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberTableName . '.client_id' => $this->client_id]);
                break;
        }

        return $dataProvider;
    }

    /**
     * Групповое редактирование
     * @param array $postNumber
     * @return bool
     */
    public function groupEdit($postNumber)
    {
        if (isset($postNumber['status'])) {
            $status = $postNumber['status'];
        } else {
            $status = '';
        }

        if (isset($postNumber['beauty_level'])) {
            $beautyLevel = $postNumber['beauty_level'];
        } else {
            $beautyLevel = '';
        }

        if (isset($postNumber['did_group_id'])) {
            $didGroupId = (int)$postNumber['did_group_id'];
        } else {
            $didGroupId = 0;
        }

        if (isset($postNumber['client_id'])) {
            $clientId = (int)$postNumber['client_id'];
        } else {
            $clientId = 0;
        }

        if (!$status && $beautyLevel === '' && !$didGroupId && !$clientId) {
            Yii::$app->session->setFlash('error', Yii::t('common', 'None of the new value is not specified'));
            return false;
        }

        // построить запрос, выбирающий все отфильтрованные записи
        /** @var ActiveQuery $query */
        $query = clone $this->search()->query;
        // обработать все записи, а не только на этой странице
        $query->offset(0);
        $query->limit(null);
        $query->select('number');
        $sqlSelect = $query->createCommand()->rawSql;

        /** @var Connection $db */
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {

            $modelTableName = \app\models\Number::tableName();
            $userId = Yii::$app->user->getId();

            $sqlSet = '';
            $params = [];
            if ($status) {
                $sqlSet .= 'voip_number.status = :status, ';
                $params[':status'] = $status;
            }
            if ($beautyLevel !== '') {
                $sqlSet .= 'voip_number.beauty_level = :beauty_level, ';
                $params[':beauty_level'] = $beautyLevel;
            }
            if ($didGroupId) {
                $sqlSet .= 'voip_number.did_group_id = :did_group_id, ';
                $params[':did_group_id'] = $didGroupId;
            }

            if ($clientId) {
                $sqlSet .= 'voip_number.client_id = :client_id, ';
                $params[':client_id'] = ($clientId == GetListTrait::$isNull) ? null : $clientId;
            }

            // сразу UPDATE было бы лучше, но
            // 1. из query его получить слишком извращённо
            // 2. в query могут быть join, тогда UPDATE получится слишком сложным и к тому же разным для MySQL и PostgreSQL.
            // поэтому проще SELECT + UPDATE
            // Одним запросом нельзя из-за ошибки "1093 You can't specify target table for update in FROM clause"
            $db->createCommand("CREATE TEMPORARY TABLE number_tmp {$sqlSelect}")->execute();

            $sqlUpdate = <<<SQL
UPDATE
    {$modelTableName} voip_number,
    number_tmp
SET
    {$sqlSet}
    voip_number.edit_user_id = {$userId}
WHERE 
    voip_number.number = number_tmp.number
SQL;
            $affectedRows = $db->createCommand($sqlUpdate, $params)->execute();

            $db->createCommand('DROP TEMPORARY TABLE number_tmp')->execute();

            Yii::$app->session->setFlash('success', Yii::t('common', '{n, plural, one{# entry was edited} other{# entries were edited}}', ['n' => $affectedRows]));
            $transaction->commit();
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', Yii::t('common', 'Internal error'));
            Yii::error($e);
            return false;
        }

    }
}
