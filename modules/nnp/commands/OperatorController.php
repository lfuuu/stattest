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

    protected $preProcessing = [
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
        'СИБИНТЕРТЕЛЕКОМ' => 'МТС',
        'Ростелеком' => 'Ростелеком',
        'Сибирьтелеком' => 'Ростелеком',
        'Теле2' => 'Теле2',
        'Т2 Мобайл' => 'Теле2',
        'Глобалстар' => 'Глобалстар',
        'Глобал Телеком' => 'Глобал Телеком',
        'К-телеком' => 'К-телеком',
        'ТранзитТелеком' => 'МТТ',
        'Скартел' => 'Скартел',
        'Антарес' => 'Антарес',
        'ЕКАТЕРИНБУРГ-2000' => 'Мотив',
        'Вайнах Телеком' => 'Вайнах Телеком',
        'Московская телекоммуникационная корпорация' => 'Акадо',
        'Московская городская телефонная сеть' => 'МГТС',
        'Арктур' => 'Арктур',
        'Астрахань GSM' => 'Астрахань GSM',
        'Ярославль-GSM' => 'Ярославль GSM',
        'Интеграл' => 'Интеграл',
        'КРЫМТЕЛЕКОМ' => 'КрымТелеком',
        'Центральный телеграф' => 'Центральный телеграф',
        'ЗЕБРА ТЕЛЕКОМ' => 'Зебра',
        'ТрансТелеКом' => 'ТрансТелеКом',
        'Нэт Бай Нэт' => 'NetByNet',
        'Твои мобильные технологии' => 'Твои мобильные технологии',
        'АКОС' => 'АКОС',
        'Элемтэ-Инвест' => 'Элемтэ-Инвест',
        'Сотовая связь Башкортостана' => 'Сотовая связь Башкортостана',
        'Императив' => 'Императив',
        'Наша сеть' => 'Наша сеть',
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

            $operatorSource = $this->preProcessing($numberRange->operator_source);
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
    protected function preProcessing($value)
    {
        foreach ($this->preProcessing as $preProcessingFrom => $preProcessingTo) {
            if (strpos($value, $preProcessingFrom) !== false) {
                return $preProcessingTo;
            }
        }
        return null;
    }
}
