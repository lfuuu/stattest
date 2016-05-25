<?php
namespace app\modules\nnp\commands;

use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Region;
use Yii;
use yii\console\Controller;

/**
 * Группировка регионов
 */
class RegionController extends Controller
{

    const FUNC_PREG_REPLACE = 'preg_replace'; // замена с помощью регулярного выражения
    const FUNC_STR_REPLACE = 'str_replace'; // строчная замена
    const FUNC_STRPOS = 'strpos'; // замена, если есть вхождение

    protected $preProcessing = [
        [self::FUNC_PREG_REPLACE, '/.*\|/', ''],
        [self::FUNC_STR_REPLACE, 'область', 'обл.'],
        [self::FUNC_STR_REPLACE, 'г.о. ', '',],
        [self::FUNC_STR_REPLACE, 'г. ', '',],
        [self::FUNC_STR_REPLACE, 'р-н ', '',],
        [self::FUNC_STR_REPLACE, 'город ', ''],
        [self::FUNC_STR_REPLACE, 'автономный округ', 'АО'],
        [self::FUNC_STR_REPLACE, 'Республика', ''],
        [self::FUNC_STR_REPLACE, ' - ', '-'],

        [
            self::FUNC_PREG_REPLACE,
            '/Балашиха|Бронницы|Дзержинский|Долгопрудный|Домодедово|Дубна|Жуковский|Звенигород|Ивантеевка|Коломна|Королев|Королёв, Юбилейный|Котельники|Красноармейск|Краснознаменск|Лобня|Лыткарино|Орехово-Зуево|Подольск|Протвино|Пущино|Реутов|Рошаль|Сельцо|Серпухов|Фрязино|Химки|Черноголовка|Электрогорск|Электросталь|Наро-Фоминский, Московская обл.|Щёлковский, Московская обл./',
            'Московская обл.'
        ],

        [self::FUNC_STRPOS, 'Севастополь', 'Крым'],
        [self::FUNC_STRPOS, 'Крым', 'Крым'],
        [self::FUNC_STRPOS, 'Кабардино-Балкарская', 'Кабардино-Балкария'],
        [self::FUNC_STRPOS, 'Карачаево-Черкесская', 'Карачаево-Черкессия'],
        [self::FUNC_STRPOS, 'Удмуртская', 'Удмуртия'],
        [self::FUNC_STRPOS, 'Ханты-Мансийский', 'Ханты-Мансийский АО'],
        [self::FUNC_STRPOS, 'Чувашская', 'Чувашия'],
        [self::FUNC_STRPOS, 'Чеченская', 'Чечня'],
        [self::FUNC_STRPOS, 'Чукотский', 'Чукотка'],
        [self::FUNC_STRPOS, 'Якутия', 'Якутия'],
        [self::FUNC_STRPOS, 'Башкортостан', 'Башкирия'],
        [self::FUNC_STRPOS, 'Камчатский', 'Камчатка'],
        [self::FUNC_STRPOS, 'Татарстан', 'Татарстан'],

        [self::FUNC_STR_REPLACE, 'АО. Ленинский', 'Еврейская автономная обл.'],
        [self::FUNC_STR_REPLACE, 'Губкинский', 'Ямало-Ненецкий АО'],
        [self::FUNC_STR_REPLACE, 'Инская', 'Новосибирская обл.'],
        [self::FUNC_STR_REPLACE, 'Лысьвенский р-н', 'Пермский край'],
        [self::FUNC_STR_REPLACE, 'Москва (Новомосковский)', 'Москва'],
        [self::FUNC_STR_REPLACE, 'Москва (Троицкий)', 'Москва'],
        [self::FUNC_STR_REPLACE, 'н.п. Константиновка', 'Татарстан'],
        [self::FUNC_STR_REPLACE, 'НПС-2 НП Пурпе-Самотлор Пуровский', 'Тюменская обл.'],
        [self::FUNC_STR_REPLACE, 'Сургут и Сургутский район', 'Ханты-Мансийский АО'],
        [self::FUNC_STR_REPLACE, 'Добрянский район', 'Пермский край'],
    ];

    /**
     * @return int
     */
    public function actionIndex()
    {
        // Группированные значение
        $regionSourceToId = Region::find()
            ->select([
                'name',
                'id',
            ])
            ->indexBy('name')
            ->asArray()
            ->all();

        // уже сделанные соответствия
        $regionSourceToId += NumberRange::find()
            ->distinct()
            ->select([
                'name' => 'region_source',
                'id' => 'region_id',
            ])
            ->where('region_id IS NOT NULL')
            ->indexBy('name')
            ->asArray()
            ->all();

        $numberRangeQuery = NumberRange::find()
            ->where('region_id IS NULL');
        $i = 0;

        /** @var NumberRange $numberRange */
        foreach ($numberRangeQuery->each() as $numberRange) {

            if ($i++ % 1000 === 0) {
                echo '. ';
            }

            $regionSource = $this->preProcessing($numberRange->region_source);
            if (!$regionSource) {
                continue;
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (!isset($regionSourceToId[$regionSource])) {
                    $region = new Region();
                    $region->name = $regionSource;
                    $region->save();
                    $regionSourceToId[$regionSource] = ['id' => $region->id];
                }
                $numberRange->region_id = $regionSourceToId[$regionSource]['id'];
                $numberRange->save();

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Ошибка Region');
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
        foreach ($this->preProcessing as $preProcessing) {
            switch ($preProcessing[0]) {

                case self::FUNC_PREG_REPLACE:
                    $value = preg_replace($preProcessing[1], $preProcessing[2], $value);
                    break;

                case self::FUNC_STR_REPLACE:
                    $value = str_replace($preProcessing[1], $preProcessing[2], $value);
                    break;

                case self::FUNC_STRPOS:
                    if (strpos($value, $preProcessing[1]) !== false) {
                        $value = $preProcessing[2];
                    }
                    break;
            }
        }

        $value = trim($value);
        return $value;
    }
}
