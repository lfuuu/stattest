<?php
namespace app\commands\stat;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use app\models\Number;
use app\models\DidGroup;

class VoipNumbersController extends Controller
{

    const PRICE_BEAUTY_STANDART = 0;
    const PRICE_BEAUTY_PLATINUM = null;
    const PRICE_BEAUTY_GOLD = 9999;
    const PRICE_BEAUTY_SILVER = 5999;
    const PRICE_BEAUTY_BRONZE = 1999;

    public
        $prefix,

        $BeautyLvl0,
        $BeautyLvl1,
        $BeautyLvl2,
        $BeautyLvl3,
        $BeautyLvl4;

    /**
     * Список доступных опций
     *
     * @param string $actionID
     * @return array
     */
    public function options($actionID)
    {
        switch ($actionID) {
            case 'append':
            case 'clear-numbers':
            case 'set-beauty':
                return [
                    'prefix'
                ];
            case 'set-prices':
                return [
                    'BeautyLvl0',
                    'BeautyLvl1',
                    'BeautyLvl2',
                    'BeautyLvl3',
                    'BeautyLvl4',
                ];
        }

        return [];
    }

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
            $this->ansiFormat(' <rangeStart-rangeEnd>', Console::FG_YELLOW) .
            $this->ansiFormat(' [-- prefix]', Console::FG_YELLOW) . PHP_EOL
        );
        $this->stdout(
            "\t\t" . $this->ansiFormat(' <regionId>', Console::FG_YELLOW) . ': int regions.id' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' <cityId>', Console::FG_YELLOW) . ': int city.id' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' <rangeStart-rangeEnd>', Console::FG_YELLOW) . ': string pattern "\d+-\d+"' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' -- prefix', Console::FG_YELLOW) . ': int pattern city.id + "\d*"'
        );

        $this->stdout(PHP_EOL . PHP_EOL);

        $this->stdout('Установка уровня красоты:', Console::FG_CYAN);
        $this->stdout(PHP_EOL . "\t");
        $this->stdout(
            './' . $scriptName . ' stat/voip-numbers/set-beauty ' .
            $this->ansiFormat(' <regionId>', Console::FG_YELLOW) .
            $this->ansiFormat(' <cityId>', Console::FG_YELLOW) .
            $this->ansiFormat(' [-- prefix]', Console::FG_YELLOW) . PHP_EOL
        );
        $this->stdout(
            "\t\t" . $this->ansiFormat(' <regionId>', Console::FG_YELLOW) . ': int regions.id' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' <cityId>', Console::FG_YELLOW) . ': int city.id' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' -- prefix', Console::FG_YELLOW) . ': int pattern city.id + "\d*"'
        );

        $this->stdout(PHP_EOL . PHP_EOL);

        $this->stdout('Установка стоимости в зависимости от красоты:', Console::FG_CYAN);
        $this->stdout(PHP_EOL . "\t");
        $this->stdout(
            './' . $scriptName . ' stat/voip-numbers/set-prices ' .
            $this->ansiFormat(' <regionId>', Console::FG_YELLOW) .
            $this->ansiFormat(' <cityId>', Console::FG_YELLOW) .
            $this->ansiFormat(' [-- BeautyLvl0=numberPrice]', Console::FG_YELLOW) .
            $this->ansiFormat(' [-- BeautyLvl1=numberPrice]', Console::FG_YELLOW) .
            $this->ansiFormat(' [-- BeautyLvl2=numberPrice]', Console::FG_YELLOW) .
            $this->ansiFormat(' [-- BeautyLvl3=numberPrice]', Console::FG_YELLOW) .
            $this->ansiFormat(' [-- BeautyLvl4=numberPrice]', Console::FG_YELLOW) . PHP_EOL
        );
        $this->stdout(
            "\t\t" . $this->ansiFormat(' <regionId>', Console::FG_YELLOW) . ': int regions.id' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' <cityId>', Console::FG_YELLOW) . ': int city.id' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' -- BeautyLvl0=numberPrice', Console::FG_YELLOW) . ': float' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' -- BeautyLvl1=numberPrice', Console::FG_YELLOW) . ': float' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' -- BeautyLvl2=numberPrice', Console::FG_YELLOW) . ': float' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' -- BeautyLvl3=numberPrice', Console::FG_YELLOW) . ': float' . PHP_EOL .
            "\t\t" . $this->ansiFormat(' -- BeautyLvl4=numberPrice', Console::FG_YELLOW) . ': float'
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
     */
    public function actionAppend($regionId, $cityId, $numbersRange)
    {
        if (!(int) $regionId) {
            $this->stderr('Invalid regionId' . PHP_EOL, Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        if (!(int) $cityId) {
            $this->stderr('Invalid cityId' . PHP_EOL, Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        if (!preg_match('/^\d+\-\d+$/', $numbersRange)) {
            $this->stderr('Invalid numbersRange' . PHP_EOL, Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        list ($rangeStart, $rangeEnd) = explode('-', $numbersRange);

        $this->runAction('clear-numbers', [$regionId, $cityId]);

        $insert = [];
        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $number = $cityId . str_pad($i, 11 - strlen($cityId), '0', STR_PAD_LEFT);
            $this->stdout('Number: ' . $number . PHP_EOL, Console::FG_GREEN);

            $insert[] = [$number, $regionId, $cityId];
        }

        $inserted = Yii::$app->db->createCommand()->batchInsert(
            Number::tableName(),
            ['number', 'region', 'city_id'],
            $insert
        )->execute();

        $this->stdout('Inserted numbers: ' . $inserted . PHP_EOL, Console::FG_PURPLE);

        if ($this->confirm('Would you like set beauty level: ')) {
            $this->runAction('set-beauty', [
                $regionId, $cityId
            ]);
        }

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Установка уровня красоты
     *
     * @param int $regionId
     * @param int $cityId
     */
    public function actionSetBeauty($regionId, $cityId)
    {
        if (!(int) $regionId) {
            $this->stderr('Invalid regionId' . PHP_EOL, Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        if (!(int) $cityId) {
            $this->stderr('Invalid cityId' . PHP_EOL, Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        $didGroups = [];
        $groups =
            DidGroup::find()
                ->select([
                    'id', 'city_id', 'beauty_level'
                ])
                ->orderBy([
                    'city_id' => SORT_ASC,
                    'beauty_level' => SORT_ASC,
                    'id' => SORT_ASC,
                ]);

        foreach ($groups->each() as $group) {
            if (!isset($didGroups[$group->city_id])) {
                $didGroups[$group->city_id] = [];
            }

            if (!isset($didGroups[$group->city_id][$group->beauty_level])) {
                $didGroups[$group->city_id][$group->beauty_level] = $group->id;
            }
        }

        $skippedCount =
        $changedCount =
        $errorsCount = 0;

        $numbers =
            Number::find()
                ->select([
                    'number', 'city_id', 'beauty_level', 'did_group_id'
                ])
                ->where(['region' => $regionId])
                ->andWhere(['city_id' => $cityId]);

        if (!is_numeric($this->prefix) && ($this->confirm('Do you want use number prefix ?'))) {
            $this->prefix = $this->prompt('Enter number prefix:');

            if (is_numeric($this->prefix)) {
                $numbers->andWhere(['like', 'number', $cityId . $this->prefix . '%', false]);
            }
        }

        foreach($numbers->all() as $number) {
            $beautyLevel = Number::getNumberBeautyLvl(substr($number->number, -7));
            $didGroupId = null;

            if (isset($didGroups[$number->city_id][$beautyLevel])) {
                $didGroupId = $didGroups[$number->city_id][$beautyLevel];
            }

            if ($didGroupId) {
                if ($number->did_group_id != $didGroupId || $number->beauty_level != $beautyLevel) {
                    $number->beauty_level = $beautyLevel;
                    $number->did_group_id = $didGroupId;
                    $number->update(false);

                    $this->stdout(
                        'Number ' . $number->number . PHP_EOL .
                        "\t" . $this->ansiFormat(' beautyLevel ' . $beautyLevel, Console::FG_GREY) .
                        "\t" . $this->ansiFormat(' didGroupId ' . $didGroupId, Console::FG_GREY) . PHP_EOL,
                        Console::FG_GREEN
                    );

                    $changedCount++;
                }
                else {
                    $skippedCount++;
                }
            }
            else {
                $this->stdout('Number ' . $number->number . ' Did group not found' . PHP_EOL, Console::FG_RED);

                $errorsCount++;
            }
        }

        $this->stdout('Skipped: ' . $skippedCount . PHP_EOL, Console::FG_PURPLE);
        $this->stdout('Changed: ' . $changedCount . PHP_EOL, Console::FG_PURPLE);
        $this->stdout('Errors: ' . $errorsCount . PHP_EOL, Console::FG_PURPLE);

        if ($this->confirm('Would you like set prices: ')) {
            $this->runAction('set-prices', [
                $regionId, $cityId
            ]);
        }

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Установка стоимости номера в зависимости от красоты
     *
     * @param int $regionId
     * @param int $cityId
     * @return int
     */
    public function actionSetPrices($regionId, $cityId)
    {
        if (!is_numeric($this->BeautyLvl0) && $this->BeautyLvl0 !== 'null') {
            $this->BeautyLvl0 = $this->prompt('Enter "Standart" beauty level price:');
        }

        $this->BeautyLvl0 = (
            $this->BeautyLvl0 === 'null'
                ? null
                : (is_numeric($this->BeautyLvl0) ? $this->BeautyLvl0 : self::PRICE_BEAUTY_STANDART)
        );

        $this->stdout(
            '"Standart" beauty level updated ' .
            Number::updateAll(
                ['price' => $this->BeautyLvl0],
                [
                    'region' => $regionId,
                    'city_id' => $cityId,
                    'beauty_level' => 0,
                ]
            ) . PHP_EOL,
            Console::FG_PURPLE
        );

        if (!is_numeric($this->BeautyLvl1) && $this->BeautyLvl1 !== 'null') {
            $this->BeautyLvl1 = $this->prompt('Enter "Platinum" beauty level price:');
        }

        $this->BeautyLvl1 = (
            $this->BeautyLvl1 === 'null'
                ? null
                : (is_numeric($this->BeautyLvl1) ? $this->BeautyLvl1 : self::PRICE_BEAUTY_PLATINUM)
        );

        $this->stdout(
            '"Platinum" beauty level updated ' .
            Number::updateAll(
                ['price' => $this->BeautyLvl1],
                [
                    'region' => $regionId,
                    'city_id' => $cityId,
                    'beauty_level' => 1,
                ]
            ) . PHP_EOL,
            Console::FG_PURPLE
        );

        if (!is_numeric($this->BeautyLvl2) && $this->BeautyLvl2 !== 'null') {
            $this->BeautyLvl2 = $this->prompt('Enter "Gold" beauty level price:');
        }

        $this->BeautyLvl2 = (
            $this->BeautyLvl2 === 'null'
                ? null
                : (is_numeric($this->BeautyLvl2) ? $this->BeautyLvl2 : self::PRICE_BEAUTY_GOLD)
        );

        $this->stdout(
            '"Gold" beauty level updated ' .
            Number::updateAll(
                ['price' => $this->BeautyLvl2],
                [
                    'region' => $regionId,
                    'city_id' => $cityId,
                    'beauty_level' => 2,
                ]
            ) . PHP_EOL,
            Console::FG_PURPLE
        );

        if (!is_numeric($this->BeautyLvl3) && $this->BeautyLvl3 !== 'null') {
            $this->BeautyLvl3 = $this->prompt('Enter "Silver" beauty level price:');
        }

        $this->BeautyLvl3 = (
            $this->BeautyLvl3 === 'null'
                ? null
                : (is_numeric($this->BeautyLvl3) ? $this->BeautyLvl3 : self::PRICE_BEAUTY_SILVER)
        );

        $this->stdout(
            '"Silver" beauty level updated ' .
            Number::updateAll(
                ['price' => $this->BeautyLvl3],
                [
                    'region' => $regionId,
                    'city_id' => $cityId,
                    'beauty_level' => 3,
                ]
            ) . PHP_EOL,
            Console::FG_PURPLE
        );

        if (!is_numeric($this->BeautyLvl4) && $this->BeautyLvl4 !== 'null') {
            $this->BeautyLvl4 = $this->prompt('Enter "Bronze" beauty level price:');
        }

        $this->BeautyLvl4 = (
            $this->BeautyLvl4 === 'null'
                ? null
                : (is_numeric($this->BeautyLvl4) ? $this->BeautyLvl4 : self::PRICE_BEAUTY_BRONZE)
        );

        $this->stdout(
            '"Bronze" beauty level updated ' .
            Number::updateAll(
                ['price' => $this->BeautyLvl4],
                [
                    'region' => $regionId,
                    'city_id' => $cityId,
                    'beauty_level' => 4,
                ]
            ) . PHP_EOL,
            Console::FG_PURPLE
        );

        return Controller::EXIT_CODE_NORMAL;
    }

    public function actionClearNumbers($regionId, $cityId)
    {
        if ($this->confirm('Delete existing numbers ?')) {
            $deleted = 0;

            if (!is_numeric($this->prefix) && ($this->confirm('Do you want use number prefix ?'))) {
                $this->prefix = $this->prompt('Enter number prefix:');

                if (is_numeric($this->prefix)) {
                    $deleted = Number::deleteAll([
                        'and',
                        ['region' => $regionId],
                        ['like', 'number', $cityId . $this->prefix . '%', false]
                    ]);
                }
                else {
                    $this->stderr('Invalid numberPrefix' . PHP_EOL, Console::FG_RED);
                }
            }
            else {
                $deleted = Number::deleteAll(['region' => $regionId]);
            }

            $this->stdout(
                'Deleted ' . $deleted . ' numbers in regionId: ' . $regionId . ' and cityId' . $cityId . PHP_EOL,
                Console::FG_PURPLE
            );
        }

        return Controller::EXIT_CODE_NORMAL;
    }

}