<?php
namespace app\modules\nnp\commands;

use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Operator;
use Yii;
use yii\console\Controller;

/**
 * Группировка операторов
 */
class OperatorController extends Controller
{

    protected $preTreatment = [
//        '"',
//        'ООО ',
//        'ОАО ',
//        'ЗАО ',
//        'ПАО ',
//        'АО ',
//        'Закрытое акционерное общество ',
//        'Акционерное общество ',
//        'ФГУП ',
//        'ГУП ',
//        'государственное унитарное предприятие ',
//        'Финансовая Компания ',
//        'Компания ',
        'Вымпел-Коммуникации' => 'Билайн',
        'МегаФон' => 'МегаФон',
        'Мобильные ТелеСистемы' => 'МТС',
        'Ростелеком' => 'Ростелеком',
        'СМАРТС' => 'СМАРТС',
        'Теле2' => 'Теле2',
    ];

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
            ->indexBy('name')
            ->asArray()
            ->all();

        $numberRangeQuery = NumberRange::find()
            ->where('operator_id IS NULL');
        $i = 0;

        /** @var NumberRange $numberRange */
        foreach ($numberRangeQuery->each() as $numberRange) {

            if ($i++ % 1000 === 0) {
                echo '. ';
            }

            $operatorSource = $this->preTreatment($numberRange->operator_source);
            if (!$operatorSource) {
                continue;
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (!isset($operatorSourceToId[$operatorSource])) {
                    $operator = new Operator();
                    $operator->name = $operatorSource;
                    $operator->save();
                    $operatorSourceToId[$operatorSource] = ['id' => $operator->id];
                }
                $numberRange->operator_id = $operatorSourceToId[$operatorSource]['id'];
                $numberRange->save();

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Ошибка Operator');
                Yii::error($e);
                printf('%s %s', $e->getMessage(), $e->getTraceAsString());
            }
        }

        echo PHP_EOL;
        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Обработать напильником
     * @param string $value
     * @return string
     */
    protected function preTreatment($value)
    {
        foreach ($this->preTreatment as $preTreatmentFrom => $preTreatmentTo) {
            if (strpos($value, $preTreatmentFrom) !== false) {
                return $preTreatmentTo;
            }
        }
        return null;
    }
}
