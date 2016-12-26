<?php

namespace app\classes\traits;

use Yii;

/**
 * Определяет getYesNoList (список для selectbox)
 */
trait YesNoTraits
{
    /**
     * @param bool $isWithEmpty
     * @return array
     */
    public static function getYesNoList($isWithEmpty = false)
    {
        $list = [
            0 => Yii::t('common', 'No'),
            1 => Yii::t('common', 'Yes'),
        ];

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }
}