<?php

namespace app\commands\convert;

use app\models\Number;
use app\models\voip\Registry;
use yii\console\Controller;
use app\exceptions\ModelValidationException;

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
}
