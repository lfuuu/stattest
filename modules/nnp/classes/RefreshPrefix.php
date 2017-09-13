<?php

namespace app\modules\nnp\classes;

use app\classes\Connection;
use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\modules\nnp\filter\NumberRangeFilter;
use app\modules\nnp\models\FilterQuery;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\NumberRangePrefix;
use app\modules\nnp\models\Prefix;
use app\modules\nnp\models\PrefixDestination;
use app\modules\nnp\Module;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * @method static RefreshPrefix me($args = null)
 */
class RefreshPrefix extends Singleton
{
    const EVENT_FILTER_TO_PREFIX = 'nnp_filter_to_prefix';

    /**
     * Конвертировать фильтры в префиксы
     *
     * @return string[]
     * @throws \yii\db\StaleObjectException
     * @throws \app\exceptions\ModelValidationException
     * @throws \LogicException
     * @throws \yii\db\Exception
     */
    public function filterToPrefix()
    {
        if (NumberRange::isTriggerEnabled()) {
            throw new \LogicException('Обновление префиксов невозможно, потому что триггер включен');
        }

        $logs = [];

        /** @var Connection $dbPgNnp */
        $dbPgNnp = Yii::$app->dbPgNnp;
        $numberRangePrefixTableName = NumberRangePrefix::tableName();

        $prefixIdToCount = NumberRangePrefix::find()
            ->select(['count' => new Expression('COUNT(*)'), 'prefix_id'])
            ->groupBy('prefix_id')
            ->indexBy('prefix_id')
            ->asArray()
            ->column();

        /** @var Prefix[] $prefixes */
        $prefixes = Prefix::find()
            ->indexBy('id')
            ->all();

        $query = FilterQuery::find()
            ->where([
                'model_name' => (new NumberRangeFilter)->getClassName(),
            ]);
        /** @var FilterQuery $filterQuery */
        foreach ($query->each() as $filterQuery) {

            Module::transaction(
                function () use (&$prefixes, $filterQuery, $dbPgNnp, $numberRangePrefixTableName, &$affectedRows) {

                    // очистить старое
                    if (isset($prefixes[$filterQuery->id])) {
                        unset($prefixes[$filterQuery->id]);
                        NumberRangePrefix::deleteAll(['prefix_id' => $filterQuery->id]);
                    }

                    // добавить новое
                    $filterModel = new NumberRangeFilter();
                    $filterModel->attributes = $filterQuery->data;;
                    $dataProvider = $filterModel->search();
                    /** @var ActiveQuery $dataProviderQuery */
                    $dataProviderQuery = $dataProvider->query;
                    $dataProviderQuery->select('id');

                    $userId = $filterQuery->update_user_id ?: 'null';

                    $sql = <<<SQL
INSERT INTO {$numberRangePrefixTableName}
    (number_range_id, prefix_id, insert_time, insert_user_id)
SELECT
    t.id, {$filterQuery->id}, '{$filterQuery->update_time}', {$userId}
FROM
    ( {$dataProviderQuery->createCommand()->rawSql} ) t
SQL;
                    $affectedRows = $dbPgNnp->createCommand($sql)->execute();
                }
            );

            $logs[] = sprintf(
                'Префикс %s: было %d, стало %d',
                $filterQuery->name,
                isset($prefixIdToCount[$filterQuery->id]) ? $prefixIdToCount[$filterQuery->id] : 0,
                $affectedRows);
        }

        // удалить
        foreach ($prefixes as $prefix) {

            Module::transaction(
                function () use ($prefix, &$logs) {

                    $logs[] = 'Префикс ' . $prefix->name . ' удален';
                    NumberRangePrefix::deleteAll(['prefix_id' => $prefix->id]);
                    PrefixDestination::deleteAll(['prefix_id' => $prefix->id]);
                    if (!$prefix->delete()) {
                        throw new ModelValidationException($prefix);
                    }

                }
            );
        }

        return $logs;
    }
}