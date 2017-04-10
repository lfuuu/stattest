<?php
namespace app\commands;

use app\exceptions\ModelValidationException;
use app\models\Number;
use app\models\voip\Registry;
use app\modules\nnp\models\NumberRange;
use yii\console\Controller;


/**
 * Class NdcConvertController
 */
class NdcConvertController extends Controller
{

    /**
     * Запус конвертора
     */
    public function actionConvert()
    {
        $this->_convertRegistry();
        $this->_convertNumbers();
    }

    /**
     * Конвертировать номера
     */
    private function _convertNumbers()
    {
        /** @var Number $number */
        foreach (Number::find()->each() as $number) {
            $countryPrefix = $number->country->prefix;

            try {
                $ndc = NumberRange::detectNDC(
                    $number->number,
                    $countryPrefix,
                    $number->country_code,
                    $number->city_id
                );

                if ($ndc != $number->ndc) {
                    echo PHP_EOL . $number->number . ' ' . $number->ndc . ' => ' . $ndc;
                    $number->ndc = $ndc;
                }

                $number->number_subscriber = substr($number->number, strlen((string)$countryPrefix) + strlen((string)$ndc));

                if (!$number->save()) {
                    throw new ModelValidationException($number);
                }
            } catch (\Exception $e) {
                echo PHP_EOL . $e->getMessage();
            }
        }
    }

    /**
     * Конвертируем таблицу реестра номеров
     */
    private function _convertRegistry()
    {
        /** @var Registry $registry */
        foreach (Registry::find()->with('country')->each() as $registry) {
            $countryPrefix = $registry->country->prefix;

            $ndc = NumberRange::detectNDC(
                $registry->number_from,
                $countryPrefix,
                $registry->country_id,
                $registry->city_id
            );

            $registry->ndc = $ndc;
            $registry->number_full_from = $registry->number_from;
            $registry->number_full_to = $registry->number_to;

            $registry->number_from = substr($registry->number_from, strlen($countryPrefix) + strlen((string)$ndc));
            $registry->number_to = substr($registry->number_to, strlen($countryPrefix) + strlen((string)$ndc));

            if (!$registry->save()) {
                throw new ModelValidationException($registry);
            }

            echo PHP_EOL . $countryPrefix . ' ' . $registry->ndc . ' ' .
                $registry->number_from . '-' . $registry->number_to .
                ' (' . $registry->number_full_from . '-' . $registry->number_full_from . ')';
        }
    }
}
