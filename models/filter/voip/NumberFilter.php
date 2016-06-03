<?php

namespace app\models\filter\voip;

use app\classes\Connection;
use app\classes\traits\GetListTrait;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * Фильтрация для Number
 */
class NumberFilter extends \app\models\Number
{
    public $number = '';
    public $city_id = '';
    public $status = '';
    public $did_group_id = '';
    public $beauty_level = '';
    public $usage_id = '';

    public function rules()
    {
        return [
            [['number', 'status'], 'string'],
            [['city_id', 'beauty_level', 'usage_id'], 'integer'], // , 'did_group_id'
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
        ]);

        // если ['LIKE', 'number', $mask], то он заэскейпит спецсимволы и добавить % в начало и конец. Подробнее см. \yii\db\QueryBuilder::buildLikeCondition
        $this->number !== '' &&
        ($this->number = strtr($this->number, ['.' => '_', '*' => '%'])) &&
        $query->andWhere('number LIKE :number', [':number' => $this->number]);

        $this->city_id !== '' && $query->andWhere(['city_id' => $this->city_id]);
        $this->status !== '' && $query->andWhere(['status' => $this->status]);
        $this->beauty_level !== '' && $query->andWhere(['beauty_level' => $this->beauty_level]);
        $this->did_group_id !== '' && $query->andWhere(['did_group_id' => $this->did_group_id]);

        switch ($this->usage_id) {
            case GetListTrait::$isNull:
                $query->andWhere('usage_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere('usage_id IS NOT NULL');
                break;
            default:
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

        if (!$status && $beautyLevel === '' && !$didGroupId) {
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

            $db->createCommand('DROP TABLE number_tmp')->execute();

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
