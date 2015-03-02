<?php
namespace app\models;

use yii\base\Exception;

class TaxType
{
    const TAX_0         = '0';
    const TAX_18        = '18';
    const TAX_18118     = '18118';
    const TAX_10        = '10';
    const TAX_10110     = '10110';

    private static $types = [
        self::TAX_0     => ['name' => 'Без НДС',    'rate' => 0             ],
        self::TAX_18    => ['name' => '18%',        'rate' => 0.18          ],
        self::TAX_18118 => ['name' => '18%/118%',   'rate' => 0.15254237    ],
        self::TAX_10    => ['name' => '10%',        'rate' => 0.10          ],
        self::TAX_10110 => ['name' => '10%/110%',   'rate' => 0.09090909    ],
    ];

    public static function rate($id)
    {
        $id = (string) $id;
        if (!isset(self::$types[$id])) {
            throw new Exception('Не известный идентификатор налога "' . $id . '"');
        }

        return self::$types[$id]['rate'];
    }

    public static function name($id)
    {
        $id = (string) $id;
        if (!isset(self::$types[$id])) {
            throw new Exception('Не известный идентификатор налога "' . $id . '"');
        }

        return self::$types[$id]['name'];
    }

}