<?php

namespace app\modules\nnp\filter;

use app\classes\Connection;
use app\classes\traits\GetListTrait;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\NumberRangePrefix;
use app\modules\nnp\models\Prefix;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * Фильтрация для NumberRange
 */
class NumberRangeFilter extends NumberRange
{
    public $country_code = '';
    public $ndc = '';
    public $full_number_from = ''; // чтобы не изобретать новое поле, названо как существующее. Хотя фактически это full_number
    public $full_number_mask = '';
    public $operator_source = '';
    public $operator_id = '';
    public $region_source = '';
    public $region_id = '';
    public $ndc_type_id = '';
    public $is_active = '';
    public $numbers_count_from = '';
    public $numbers_count_to = '';
    public $city_id = '';
    public $is_reverse_city_id = '';
    public $insert_time = ''; // чтобы не изобретать новое поле, названо как существующее. Хотя фактически это месяц добавления (insert_time) ИЛИ выключения (date_stop)

    public $prefix_id = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['operator_source', 'region_source', 'full_number_from', 'insert_time', 'full_number_mask'], 'string'],
            [['country_code', 'ndc', 'ndc_type_id', 'is_active', 'operator_id', 'region_id', 'city_id', 'is_reverse_city_id', 'prefix_id'], 'integer'],
            [['numbers_count_from', 'numbers_count_to'], 'integer'],
        ];
    }

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return parent::attributeLabels() + [
                'is_reverse_city_id' => 'Кроме',
            ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = NumberRange::find();
        $numberRangeTableName = NumberRange::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->country_code && $query->andWhere([$numberRangeTableName . '.country_code' => $this->country_code]);
        $this->ndc && $query->andWhere([$numberRangeTableName . '.ndc' => $this->ndc]);

        $this->is_active !== '' && $query->andWhere([$numberRangeTableName . '.is_active' => (bool)$this->is_active]);

        switch ($this->operator_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.operator_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.operator_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.operator_id' => $this->operator_id]);
                break;
        }

        switch ($this->ndc_type_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.ndc_type_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.ndc_type_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.ndc_type_id' => $this->ndc_type_id]);
                break;
        }

        switch ($this->region_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.region_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.region_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.region_id' => $this->region_id]);
                break;
        }

        switch ($this->city_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.city_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.city_id IS NOT NULL');
                break;
            default:
                if ($this->is_reverse_city_id) {
                    $query->andWhere([
                        'OR',
                        $numberRangeTableName . '.city_id IS NULL',
                        ['!=', $numberRangeTableName . '.city_id', $this->city_id]
                    ]);
                } else {
                    $query->andWhere([$numberRangeTableName . '.city_id' => $this->city_id]);
                }
                break;
        }

        $this->operator_source && $query->andWhere(['LIKE', $numberRangeTableName . '.operator_source', $this->operator_source]);
        $this->region_source && $query->andWhere(['LIKE', $numberRangeTableName . '.region_source', $this->region_source]);

        if ($this->full_number_from) {
            $query->andWhere(['<=', $numberRangeTableName . '.full_number_from', $this->full_number_from]);
            $query->andWhere(['>=', $numberRangeTableName . '.full_number_to', $this->full_number_from]);
        }

        if ($this->full_number_mask) {
            $this->full_number_mask = strtr($this->full_number_mask, ['.' => '_', '*' => '%']);
            $query->andWhere($numberRangeTableName . '.full_number_from::VARCHAR LIKE :full_number_mask', [':full_number_mask' => $this->full_number_mask]);
        }

        if ($this->insert_time) {
            $query->andWhere([
                'OR',
                ["DATE_TRUNC('month', {$numberRangeTableName}.insert_time)::date" => $this->insert_time . '-01'],
                ["DATE_TRUNC('month', {$numberRangeTableName}.date_stop)::date" => $this->insert_time . '-01']
            ]);
        }

        $this->numbers_count_from && $query->andWhere('1 + ' . $numberRangeTableName . '.number_to - ' . $numberRangeTableName . '.number_from >= :numbers_count_from', [':numbers_count_from' => $this->numbers_count_from]);
        $this->numbers_count_to && $query->andWhere('1 + ' . $numberRangeTableName . '.number_to - ' . $numberRangeTableName . '.number_from <= :numbers_count_to', [':numbers_count_to' => $this->numbers_count_to]);

        if ($this->prefix_id) {
            $query->joinWith('numberRangePrefixes');
            $query->andWhere([NumberRangePrefix::tableName() . '.prefix_id' => $this->prefix_id]);
        }

        return $dataProvider;
    }

    /**
     * Добавить/удалить отфильтрованные записи в префикс
     *
     * @param array $postPrefix
     * @return bool
     */
    public function addOrRemoveFilterModelToPrefix($postPrefix)
    {
        if (isset($postPrefix['id'])) {
            $prefixId = (int)$postPrefix['id'];
        } else {
            $prefixId = 0;
        }

        if (isset($postPrefix['name'])) {
            $prefixName = trim($postPrefix['name']);
        } else {
            $prefixName = 0;
        }

        if (!$prefixId && !$prefixName) {
            Yii::$app->session->setFlash('error', 'Не указан префикс ни существующий, ни новый');
            return false;
        }

        // построить запрос, выбирающий все отфильтрованные записи
        /** @var ActiveQuery $query */
        $query = clone $this->search()->query;
        // обработать все записи, а не только на этой странице
        $query->offset(0);
        $query->limit(null);
        $query->select('id');
        $sql = $query->createCommand()->rawSql;


        if (isset($post['dropButton'])) {
            // удалить из префикса
            if (!$prefixId) {
                Yii::$app->session->setFlash('error', 'Для удаления отфильтрованных записей из префикса выберите его');
                return false;
            }

            return $this->removeFilterModelFromPrefix($sql, $prefixId);
        }

        // добавить в префикс
        if ($prefixName) {
            // .. в новый
            if ($prefixId) {
                Yii::$app->session->setFlash('error', 'Укажите только один префикс: либо существующий, либо новый');
                return false;
            }

            $prefix = new Prefix();
            $prefix->name = $prefixName;
            if (!$prefix->save()) {
                Yii::$app->session->setFlash('error', 'Ошибка создания нового префикса');
                return false;
            }

            $prefixId = $prefix->id;
        }

        return $this->addFilterModelToPrefix($sql, $prefixId);
    }

    /**
     * Добавить отфильтрованные записи в префикс
     *
     * @param string $sql
     * @param int $prefixId
     * @return bool
     */
    protected function addFilterModelToPrefix($sql, $prefixId)
    {
        /** @var Connection $dbPgNnp */
        $dbPgNnp = Yii::$app->dbPgNnp;
        $transaction = $dbPgNnp->beginTransaction();
        try {

            // "чтобы продать что-нибудь не нужное, надо сначала купить что-нибудь ненужное" (С) Матроскин
            // повторное добавление дает ошибку, "on duplicate key" в postresql нет, поэтому проще удалить дубли заранее
            $this->removeFilterModelFromPrefix($sql, $prefixId);

            $numberRangePrefixTableName = NumberRangePrefix::tableName();
            $userId = Yii::$app->user->getId();
            $sql = <<<SQL
INSERT INTO {$numberRangePrefixTableName}
    (number_range_id, prefix_id, insert_time, insert_user_id)
SELECT
    t.id, {$prefixId}, NOW(), {$userId}
FROM 
    ( {$sql} ) t
SQL;

            $affectedRows = $dbPgNnp->createCommand($sql)->execute();
            Yii::$app->session->setFlash('success', 'В префикс добавлено ' . Yii::t('common', '{n, plural, one{# entry} other{# entries}}', ['n' => $affectedRows]));
            $transaction->commit();
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Ошибка добавления отфильтрованных записей в префикс');
            Yii::error($e);
            return false;
        }
    }

    /**
     * Удалить отфильтрованные записи из префикса
     *
     * @param string $sql
     * @param int $prefixId
     * @return bool
     */
    protected function removeFilterModelFromPrefix($sql, $prefixId)
    {
        /** @var Connection $dbPgNnp */
        $dbPgNnp = Yii::$app->dbPgNnp;
        $transaction = $dbPgNnp->beginTransaction();
        try {

            $numberRangePrefixTableName = NumberRangePrefix::tableName();
            $sql = <<<SQL
DELETE FROM {$numberRangePrefixTableName}
WHERE
    prefix_id = {$prefixId}
    AND number_range_id IN ( {$sql} )
SQL;

            $affectedRows = $dbPgNnp->createCommand($sql)->execute();
            Yii::$app->session->setFlash('success', 'Из префикса удалено ' . Yii::t('common', '{n, plural, one{# entry} other{# entries}}', ['n' => $affectedRows]));
            $transaction->commit();
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Ошибка удаления отфильтрованных записей из префикса');
            Yii::error($e);
            return false;
        }
    }

}
