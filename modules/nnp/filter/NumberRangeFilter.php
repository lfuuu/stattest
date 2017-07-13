<?php

namespace app\modules\nnp\filter;

use app\classes\Connection;
use app\classes\Event;
use app\classes\traits\GetListTrait;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\NumberRangePrefix;
use app\modules\nnp\models\Prefix;
use app\modules\nnp\Module;
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
    public $city_source = '';
    public $city_id = '';
    public $ndc_type_id = '';
    public $is_active = '';
    public $numbers_count_from = '';
    public $numbers_count_to = '';
    public $is_reverse_city_id = '';
    public $insert_time = ''; // чтобы не изобретать новое поле, названо как существующее. Хотя фактически это месяц добавления (insert_time) ИЛИ выключения (date_stop)
    public $date_resolution_from = '';
    public $date_resolution_to = '';

    public $prefix_id = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['operator_source', 'region_source', 'city_source', 'full_number_from', 'insert_time', 'full_number_mask'], 'string'],
            [['country_code', 'ndc', 'ndc_type_id', 'is_active', 'operator_id', 'region_id', 'city_id', 'is_reverse_city_id', 'prefix_id'], 'integer'],
            [['numbers_count_from', 'numbers_count_to'], 'integer'],
            [['date_resolution_from', 'date_resolution_to'], 'string'],
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
        $this->city_source && $query->andWhere(['LIKE', $numberRangeTableName . '.city_source', $this->city_source]);

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

        $this->date_resolution_from && $query->andWhere(['>=', $numberRangeTableName . '.date_resolution', $this->date_resolution_from]);
        $this->date_resolution_to && $query->andWhere(['<=', $numberRangeTableName . '.date_resolution', $this->date_resolution_to]);

        if ($this->prefix_id) {
            $query->joinWith('numberRangePrefixes');
            $query->andWhere([NumberRangePrefix::tableName() . '.prefix_id' => $this->prefix_id]);
        }

        return $dataProvider;
    }

    /**
     * Для всех отфильтрованных записей операторов/регионов/городов отвязать их и автоматически привязать заново.
     *
     * @param array $resetOptions
     * @return bool
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     */
    public function resetLinks($resetOptions)
    {
        $attributes = [];
        in_array('operator', $resetOptions, true) && $attributes['operator_id'] = null;
        in_array('region', $resetOptions, true) && $attributes['region_id'] = null;
        in_array('city', $resetOptions, true) && $attributes['city_id'] = null;
        if (!$attributes) {
            return false;
        }

        /** @var ActiveQuery $query */
        $query = $this->search()->query;
        $affectedRows = NumberRange::updateAll($attributes, $query->where);

        Yii::$app->session->setFlash('success',
            'Сброшено ' . Yii::t('common', '{n, plural, one{# entry} other{# entries}}', ['n' => $affectedRows]) .
            '. Через несколько минут они привяжутся заново автоматически.'
        );

        // поставить в очередь для пересчета операторов, регионов и городов
        Event::go(\app\modules\nnp\Module::EVENT_LINKER);

        return true;
    }

    /**
     * Добавить/удалить отфильтрованные записи в префикс
     *
     * @param array $postPrefix
     * @return bool
     * @throws \yii\db\Exception
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
     * @throws \yii\db\Exception
     */
    protected function addFilterModelToPrefix($sql, $prefixId)
    {
        // "чтобы продать что-нибудь не нужное, надо сначала купить что-нибудь ненужное" (С) Матроскин
        // повторное добавление дает ошибку, "on duplicate key" в postresql нет, поэтому проще удалить дубли заранее
        $this->removeFilterModelFromPrefix($sql, $prefixId);

        Module::transaction(
            function () use ($sql, $prefixId) {
                /** @var Connection $dbPgNnp */
                $dbPgNnp = Yii::$app->dbPgNnp;
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
            }
        );

        return true;
    }

    /**
     * Удалить отфильтрованные записи из префикса
     *
     * @param string $sql
     * @param int $prefixId
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function removeFilterModelFromPrefix($sql, $prefixId)
    {
        Module::transaction(
            function () use ($sql, $prefixId) {
                /** @var Connection $dbPgNnp */
                $dbPgNnp = Yii::$app->dbPgNnp;
                $numberRangePrefixTableName = NumberRangePrefix::tableName();
                $sql = <<<SQL
DELETE FROM {$numberRangePrefixTableName}
WHERE
    prefix_id = {$prefixId}
    AND number_range_id IN ( {$sql} )
SQL;
                $affectedRows = $dbPgNnp->createCommand($sql)->execute();
                Yii::$app->session->setFlash('success', 'Из префикса удалено ' . Yii::t('common', '{n, plural, one{# entry} other{# entries}}', ['n' => $affectedRows]));
            }
        );

        return true;
    }

}
