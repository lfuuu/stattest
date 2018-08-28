<?php

namespace app\commands\convert;

use app\dao\NumberBeautyDao;
use app\exceptions\ModelValidationException;
use app\models\City;
use app\models\Country;
use app\models\DidGroup;
use app\models\Number;
use app\modules\nnp\models\NdcType;
use yii\console\Controller;
use yii\db\ActiveQuery;

class ChangeBeautyLevelsController extends Controller
{
    /**
     * Был произведен перенос некоторых масок для шестизначных и семизначных номеров из группы "Платина" в группу
     * "Эксклюзив"
     */
    public function actionPlatinumToExclusiveLevels()
    {
        $numbers = Number::find()
            ->innerJoin(['city' => City::tableName()], 'city.id = voip_numbers.city_id')
            ->where([
                'voip_numbers.country_code' => Country::RUSSIA,
                'voip_numbers.beauty_level' => DidGroup::BEAUTY_LEVEL_PLATINUM,
                'city.postfix_length' => [6, 7],
            ]);
        // Применяем ко всем номерам красивость
        $this->_changeBeautyLevel($numbers);
    }

    /**
     * Изменение статусов красивости и без группы - Служебные:
     * для Австрии с типами Geographic, Nomadic
     * для России с типом Mobile и городом - Москва
     */
    public function actionAustrianAndRussianNumbers()
    {
        $numbers = Number::find()
            ->where(['AND',
                ['=', 'country_code', Country::AUSTRIA],
                ['IN', 'ndc_type_id', [
                    NdcType::ID_GEOGRAPHIC,
                    NdcType::ID_NOMADIC
                ]],
            ])
            ->orWhere([
                'city_id' => 7495,
                'ndc_type_id' => NdcType::ID_MOBILE
            ])
            ->andWhere(['NOT IN', 'did_group_id', [242, 284]]);
        // Применяем ко всем номерам красивость
        $this->_changeBeautyLevel($numbers);
    }

    /**
     * Изминение статуса красивости для шестизначных номеров России с типом Geographic
     */
    public function actionRussianAndGeographicSixNumbers()
    {
        $numbers = Number::find()
            ->innerJoin(['city' => City::tableName()], 'city.id = voip_numbers.city_id')
            // Фильтруем шестизначные номера по России и типу - географический
            ->where([
                'voip_numbers.ndc_type_id' => NdcType::ID_GEOGRAPHIC,
                'voip_numbers.country_code' => Country::RUSSIA,
                'city.postfix_length' => 6,
            ])
            // Добавляем исправленные city_id, требующие перерасчета красивости номера и DID-группы
            ->orWhere([
                'voip_numbers.city_id' => [115676, 7473, 115876, 100663, 7401, 102252, 100538, 7471, 115902, 7815, 100354, 7482, 100426, 116550, 7485]
            ]);
        // Применяем ко всем номерам красивость
        $this->_changeBeautyLevel($numbers);
    }

    /**
     * @param ActiveQuery $numbers
     */
    private function _changeBeautyLevel($numbers)
    {
        foreach ($numbers->each() as $number) {
            /** @var Number $number */

            // Изменение BeautyLevel
            $postfixLength = $number->city_id ? $number->city->postfix_length : NumberBeautyDao::DEFAULT_POSTFIX_LENGTH;
            $beautyLevel = NumberBeautyDao::getNumberBeautyLvl($number->number, $postfixLength);

            $number->beauty_level = $beautyLevel;

            /**
             * Изменение DID-группы
             * @see VoipRegistryDao::_addNumber
             */
            $didGroupId = DidGroup::dao()->getIdByNumber($number);
            if (!$didGroupId) {
                echo sprintf('ID для DID-группы с номером %s не найдена', $number->number) . PHP_EOL;
                continue;
            }
            if (!$didGroup = DidGroup::findOne(['id' => $didGroupId])) {
                echo sprintf('DID-группа с номером %s не найдена', $number->number) . PHP_EOL;
                continue;
            }
            $number->did_group_id = $didGroup->id;

            try {
                if (!$number->save()) {
                    throw new ModelValidationException($number);
                }
            } catch (ModelValidationException $e) {
                echo $e->getMessage() . PHP_EOL;
            }
        }
    }
}