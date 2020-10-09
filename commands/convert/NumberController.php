<?php

namespace app\commands\convert;

use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\HttpClient;
use app\models\Number;
use app\models\voip\Registry;
use app\modules\nnp\models\NumberRange;
use app\widgets\ConsoleProgress;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use app\exceptions\ModelValidationException;
use yii\db\Expression;

class NumberController extends Controller
{
    /**
     * Привязка номеров к реестру
     */
    public function actionCreateRelationWithRegistry()
    {
        $numbersQuery = Number::find()->where(['registry_id' => null]);
        foreach ($numbersQuery->each() as $numberModel) {
            /** @var $numberModel Number */

            $registry = Registry::find()
                ->where(['<=', 'number_full_from', $numberModel->number])
                ->andWhere(['>=', 'number_full_to', $numberModel->number])
                ->one();

            if (!$registry) {
                continue;
            }

            $numberModel->registry_id = $registry->id;
            $numberModel->source = $registry->source;
            try {
                if (!$numberModel->save()) {
                    throw new ModelValidationException($numberModel);
                }
            } catch (ModelValidationException $e) {
                echo $e->getMessage() . PHP_EOL;
            }
        }
    }

    /**
     * Заполнение поля ННП-оператор в номерах
     *
     * @throws ModelValidationException
     */
    public function actionSetNnpOperator()
    {
        $numberQuery = Number::find()
            ->where([
                'nnp_operator_id' => null,
                'source' => VoipRegistrySourceEnum::PORTABILITY_NOT_FOR_SALE,
            ]);

        $progress = new ConsoleProgress($numberQuery->count(), function ($string) {
            echo $string;
        });

        /** @var Number $number */
        foreach ($numberQuery->each() as $number) {
            $progress->nextStep();

            /** @var NumberRange $numberRange */
            $numberRange = NumberRange::find()
                ->where([
                    'is_active' => true
                ])
                ->andWhere(['<=', 'full_number_from', $number->number])
                ->andWhere(['>=', 'full_number_to', $number->number])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            if ($numberRange && $numberRange->operator_id) {
                $number->nnp_operator_id = $numberRange->operator_id;

                if (!$number->save()) {
                    throw new ModelValidationException($number);
                }
            }
        }
    }

    public function actionFillRegion()
    {
        $numbers = Number::find()->where(['nnp_region_id' => null])
            ->orWhere(['nnp_city_id' => null])
            ->orWhere(['nnp_operator_id' => null])
            ->select(['nnp_region_id', 'nnp_city_id', 'nnp_operator_id', 'number'])
            ->asArray()
            ->all();
        ;

        /** @var Number $number */
        foreach ($numbers as $number) {
            echo ' .';
            try {
                $numberInfo = Number::getNnpInfo($number['number']);
            } catch (\Exception $e) {
                echo PHP_EOL . 'ERROR: ' . $e->getMessage();
                continue;
            }

            $update = [];
            if (!$number['nnp_city_id']) {
                $update['nnp_city_id'] = $numberInfo['nnp_city_id'];
            }

            if (!$number['nnp_region_id']) {
                $update['nnp_region_id'] = $numberInfo['nnp_region_id'];
            }

            if (!$number['nnp_operator_id']) {
                $update['nnp_operator_id'] = $numberInfo['nnp_operator_id'];
            }

            if($update) {
                Number::updateAll($update, ['number' => $number['number']]);
            }
        }
    }
}
