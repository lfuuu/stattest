<?php
namespace app\modules\nnp\commands;

use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Operator;
use Yii;
use yii\base\InvalidParamException;
use yii\console\Controller;

/**
 * Группировка операторов
 */
class OperatorController extends Controller
{

    /**
     * @return int
     */
    public function actionIndex()
    {
        // Группированные значение
        $operatorSourceToId = Operator::find()
            ->select([
                'name',
                'id',
            ])
            ->indexBy('name')
            ->asArray()
            ->all();

        // уже сделанные соответствия
        $operatorSourceToId += NumberRange::find()
            ->distinct()
            ->select([
                'name' => 'operator_source',
                'id' => 'operator_id',
            ])
            ->where('operator_id IS NOT NULL')
            ->andWhere('operator_source IS NOT NULL AND operator_source != :empty')
            ->params([
                ':empty' => '',
            ])
            ->indexBy('name')
            ->asArray()
            ->all();

        $numberRangeQuery = NumberRange::find()
            ->where('is_active')
            ->andWhere('operator_id IS NULL')
            ->andWhere('operator_source IS NOT NULL AND operator_source != :empty')
            ->params([
                ':empty' => '',
            ]);
        $i = 0;

        /** @var NumberRange $numberRange */
        foreach ($numberRangeQuery->each() as $numberRange) {

            if ($i++ % 1000 === 0) {
                echo '. ';
            }

            $operatorSource = $numberRange->operator_source;

            $transaction = Yii::$app->db->beginTransaction();
            try {

                if (!isset($operatorSourceToId[$operatorSource])) {
                    $operator = new Operator();
                    $operator->name = $operatorSource;
                    $operator->country_prefix = $numberRange->country_prefix;
                    if (!$operator->save()) {
                        throw new InvalidParamException(implode('. ', $operator->getFirstErrors()));
                    }
                    $operatorSourceToId[$operatorSource] = ['id' => $operator->id];
                }

                $numberRange->operator_id = $operatorSourceToId[$operatorSource]['id'];
                if (!$numberRange->save()) {
                    throw new InvalidParamException(implode('. ', $numberRange->getFirstErrors()));
                }

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Ошибка Operator');
                Yii::error($e);
                printf('%s %s', $e->getMessage(), $e->getTraceAsString());
            }
        }

        // Обновить столбец cnt
        Operator::updateCnt();

        echo PHP_EOL;
        return Controller::EXIT_CODE_NORMAL;
    }
}
