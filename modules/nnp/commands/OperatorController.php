<?php
namespace app\modules\nnp\commands;

use app\exceptions\ModelValidationException;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Operator;
use Yii;
use yii\console\Controller;
use yii\db\Expression;

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
                'id',
                'name' => new Expression('CONCAT(country_code, name)'),
            ])
            ->indexBy('name')
            ->column();

        // уже сделанные соответствия
        $operatorSourceToId += NumberRange::find()
            ->distinct()
            ->select([
                'id' => 'operator_id',
                'name' => new Expression('CONCAT(country_code, operator_source)'),
            ])
            ->where('operator_id IS NOT NULL')
            ->andWhere(['IS NOT', 'operator_source', null])
            ->andWhere(['!=', 'operator_source', ''])
            ->indexBy('name')
            ->column();

        $numberRangeQuery = NumberRange::find()
            // ->where('is_active')
            ->andWhere('operator_id IS NULL')
            ->andWhere(['IS NOT', 'operator_source', null])
            ->andWhere(['!=', 'operator_source', '']);
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
                    $operator->country_code = $numberRange->country_code;
                    if (!$operator->save()) {
                        throw new ModelValidationException($operator);
                    }

                    $operatorSourceToId[$operatorSource] = $operator->id;
                }

                $numberRange->operator_id = $operatorSourceToId[$operatorSource];
                if (!$numberRange->save()) {
                    throw new ModelValidationException($numberRange);
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
