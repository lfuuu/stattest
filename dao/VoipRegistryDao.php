<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\Number;
use app\models\voip\Registry;

/**
 * @method static VoipRegistryDao me($args = null)
 * @property
 */
class VoipRegistryDao extends Singleton
{
    /**
     * Функция возвращает массив пропущенных и залитых номеров
     *
     * @param Registry $registry
     * @return array
     */
    public function getPassMap(Registry $registry)
    {
        $numbers = Number::find()
            ->where(['city_id' => $registry->city_id])
            ->andWhere([
                'between',
                'number',
                $registry->number_from,
                $registry->number_to
            ])
            ->orderBy(['number' => SORT_ASC])
            ->createCommand();

        $data = [];
        $lastValue = null;
        $startValue = null;
        foreach($numbers->query() as $numberArr) {
            $number = $numberArr['number'];

            if (!$startValue) {
                if ($registry->number_from < $number) {
                    $data[] = ['filling' => 'pass', 'start' => $registry->number_from, 'end' => $number-1];
                }
                $startValue = $number;
                $lastValue = $number;
                continue;
            }

            if (($number - $lastValue - 1) > 0) {
                $data[] = ['filling' => 'fill', 'start' => $startValue, 'end' => $lastValue];
                $data[] = ['filling' => 'pass', 'start' => $lastValue+1, 'end' => $number-1];
                $startValue = $number;
            }

            $lastValue = $number;
        }

        if ($startValue) {
            $data[] = ['filling' => 'fill', 'start' => $startValue, 'end' => $lastValue];
            if ($lastValue < $registry->number_to) {
                $data[] = ['filling' => 'pass', 'start'=> $lastValue+1, 'end' => $registry->number_to];
            }
        } else {
            $data[] = ['filling' => 'pass', 'start' => $registry->number_from, 'end' => $registry->number_to];
        }

        return $data;
    }


}