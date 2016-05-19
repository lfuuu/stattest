<?php
namespace app\commands\stat;

use app\models\NumberType;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use app\models\Number;
use app\models\DidGroup;
use app\models\City;
use app\models\Region;
use app\dao\NumberBeautyDao;

class VoipNumbersController extends Controller
{

    const INSERT_CHUNKS = 1000;

    public
        $BeautyLvl0,
        $BeautyLvl1,
        $BeautyLvl2,
        $BeautyLvl3,
        $BeautyLvl4;

    private
        $region = null,
        $city = null;

    /**
     * Вывод помощи
     *
     * @return int
     */
    public function actionIndex()
    {
        $scriptName = basename(Yii::$app->request->scriptFile);

        $this->stdout('Добавление номерных емкостей, установка уровня красоты / цены.', Console::BOLD);
        $this->stdout(PHP_EOL . PHP_EOL);

        $this->stdout('Добавление номерных емкостей:', Console::FG_CYAN);
        $this->stdout(PHP_EOL . "\t");
        $this->stdout(
            './' . $scriptName . ' stat/voip-numbers/append ' .
            $this->ansiFormat(' <regionId>', Console::FG_YELLOW) .
            $this->ansiFormat(' <cityId>', Console::FG_YELLOW) .
            $this->ansiFormat(' <rangeStart-rangeEnd>', Console::FG_YELLOW) . PHP_EOL
        );
        $this->stdout(
            "\t\t" . $this->ansiFormat(' <regionId>', Console::FG_YELLOW) . ': int regions.id' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' <cityId>', Console::FG_YELLOW) . ': int city.id' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' <rangeStart-rangeEnd>',
                Console::FG_YELLOW) . ': string pattern "\d+-\d+"' . PHP_EOL
        );

        $this->stdout(PHP_EOL . PHP_EOL);

        $this->stdout('Установка уровня красоты:', Console::FG_CYAN);
        $this->stdout(PHP_EOL . "\t");
        $this->stdout(
            './' . $scriptName . ' stat/voip-numbers/set-beauty ' .
            $this->ansiFormat(' <regionId>', Console::FG_YELLOW) .
            $this->ansiFormat(' <cityId>', Console::FG_YELLOW) .
            $this->ansiFormat(' <rangeStart-rangeEnd>', Console::FG_YELLOW) . PHP_EOL
        );
        $this->stdout(
            "\t\t" . $this->ansiFormat(' <regionId>', Console::FG_YELLOW) . ': int regions.id' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' <cityId>', Console::FG_YELLOW) . ': int city.id' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' <rangeStart-rangeEnd>',
                Console::FG_YELLOW) . ': string pattern "\d+-\d+"' . PHP_EOL
        );

        $this->stdout(PHP_EOL);

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Добавление номерных емкостей
     *
     * @param int $regionId
     * @param int $cityId
     * @param string $numbersRange - Pattern: \d+\-\d+
     *
     * @return int
     */
    public function actionAppend($regionId, $cityId, $numbersRange)
    {
        if ($this->checkRequiredParams($regionId, $cityId, $numbersRange) !== Controller::EXIT_CODE_NORMAL) {
            return Controller::EXIT_CODE_ERROR;
        }

        list ($rangeStart, $rangeEnd) = explode('-', $numbersRange);

        $this->runAction('clear-numbers', [
            $regionId,
            $cityId,
            $numbersRange
        ]);

        $insert = [];
        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $number = $cityId . $i;

            $this->stdout('Номер: ' . $number . PHP_EOL, Console::FG_GREEN);

            $insert[] = [$number, $regionId, $cityId, NumberType::ID_INTERNAL];
        }

        $inserted = 0;

        if (count($insert) > self::INSERT_CHUNKS) {
            $chunks = array_chunk($insert, self::INSERT_CHUNKS);

            foreach ($chunks as $chunk) {
                $inserted += Yii::$app->db->createCommand()->batchInsert(
                    Number::tableName(),
                    ['number', 'region', 'city_id', 'number_type_id'],
                    $chunk
                )->execute();
            }
        } else {
            $inserted += Yii::$app->db->createCommand()->batchInsert(
                Number::tableName(),
                ['number', 'region', 'city_id', 'number_type_id'],
                $insert
            )->execute();
        }

        $this->stdout('Добавленные номера: ' . $inserted . PHP_EOL, Console::FG_PURPLE);

        if ($this->confirm('Продолжить с установкой уровня красоты: ')) {
            $this->runAction('set-beauty', [
                $regionId,
                $cityId,
                $numbersRange
            ]);
        }

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Установка уровня красоты
     *
     * @param int $regionId
     * @param int $cityId
     * @param string $numbersRange - Pattern: \d+\-\d+
     *
     * @return int
     */
    public function actionSetBeauty($regionId, $cityId, $numbersRange)
    {
        if ($this->checkRequiredParams($regionId, $cityId, $numbersRange) !== Controller::EXIT_CODE_NORMAL) {
            return Controller::EXIT_CODE_ERROR;
        }

        list ($rangeStart, $rangeEnd) = explode('-', $numbersRange);

        $groups = DidGroup::dao()->getDidGroupMapByCityId($cityId);

        if (!count($groups)) {
            $this->stderr('Не найдены Did группы для города ID ' . $cityId . PHP_EOL, Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        $skippedCount =
        $changedCount =
        $errorsCount = 0;

        $numbers =
            Number::find()
                ->select([
                    'number',
                    'city_id',
                    'beauty_level',
                    'did_group_id'
                ])
                ->where(['region' => $regionId])
                ->andWhere(['city_id' => $cityId])
                ->andWhere(['between', 'number', $cityId . $rangeStart, $cityId . $rangeEnd]);

        foreach ($numbers->all() as $number) {
            $beautyLevel = NumberBeautyDao::getNumberBeautyLvl($number->number);
            $didGroupId = null;

            if (isset($groups[$beautyLevel])) {
                $didGroupId = $groups[$beautyLevel];
            }

            if ($didGroupId) {
                if ($number->did_group_id != $didGroupId || $number->beauty_level != $beautyLevel) {
                    $number->beauty_level = $beautyLevel;
                    $number->did_group_id = $didGroupId;
                    $number->update(false);

                    $this->stdout(
                        'Номер ' . $number->number . PHP_EOL .
                        "\t" . $this->ansiFormat(' уровень красоты ' . $beautyLevel, Console::FG_GREY) .
                        "\t" . $this->ansiFormat(' Did группа ' . $didGroupId, Console::FG_GREY) . PHP_EOL,
                        Console::FG_GREEN
                    );

                    $changedCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                $this->stdout('Номер ' . $number->number . ' не найдена Did группа' . PHP_EOL, Console::FG_RED);

                $errorsCount++;
            }
        }

        $this->stdout('Пропущено: ' . $skippedCount . PHP_EOL, Console::FG_PURPLE);
        $this->stdout('Обновлено: ' . $changedCount . PHP_EOL, Console::FG_PURPLE);
        $this->stdout('Ошибок: ' . $errorsCount . PHP_EOL, Console::FG_PURPLE);

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Удаление номеров
     *
     * @param int $regionId
     * @param int $cityId
     * @param string $numbersRange - Pattern: \d+\-\d+
     *
     * @return int
     */
    public function actionClearNumbers($regionId, $cityId, $numbersRange)
    {
        if ($this->confirm('Удалить существующие номера?')) {
            list ($rangeStart, $rangeEnd) = explode('-', $numbersRange);

            $deleted = Number::deleteAll([
                'and',
                ['region' => $regionId],
                ['city_id' => $cityId],
                ['between', 'number', $cityId . $rangeStart, $cityId . $rangeEnd]
            ]);

            $this->stdout(
                'Удалено ' . $deleted . ' номеров в регионе ID ' . $regionId . ' и городе ID ' . $cityId . PHP_EOL,
                Console::FG_PURPLE
            );
        }

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Проверка необходимых данных
     *
     * @param int $regionId
     * @param int $cityId
     * @param string $numbersRange - Pattern: \d+\-\d+
     *
     * @return int
     */
    private function checkRequiredParams($regionId, $cityId, $numbersRange)
    {
        if (!(int)$regionId) {
            $this->stderr('Некорректный ID региона' . PHP_EOL, Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        if (!(int)$cityId) {
            $this->stderr('Некорректный ID города' . PHP_EOL, Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        if (!preg_match('/^\d+\-\d+$/', $numbersRange)) {
            $this->stderr('Некорректный диапазон номеров' . PHP_EOL, Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        if (is_null($this->region) && ($this->region = Region::findOne($regionId)) === null) {
            $this->stderr('Регион ID ' . $regionId . ' не найден' . PHP_EOL, Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        if (is_null($this->city) && ($this->city = City::findOne($cityId)) === null) {
            $this->stderr('Город ID ' . $cityId . ' не найден' . PHP_EOL, Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        return Controller::EXIT_CODE_NORMAL;
    }

}