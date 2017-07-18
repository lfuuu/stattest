<?php

namespace app\classes\helpers;


class ArrayHelper extends \yii\helpers\ArrayHelper
{
    /**
     * Двумерный массив сплющить в одномерный
     *
     * @param array $sourceArray
     * @return array
     */
    public static function flatten($sourceArray)
    {
        $resultArray = [];
        foreach ($sourceArray as $items) {
            foreach ($items as $item) {
                $resultArray[] = $item;
            }
        }

        return $resultArray;
    }
}