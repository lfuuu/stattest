<?php
namespace app\models;

use yii\base\Exception;

class TaxType
{
    private static $types = [
        '0'     => ['name' => 'Без НДС',    'rate' => 0             ],
        '18'    => ['name' => '18%',        'rate' => 0.18          ],
        '18118' => ['name' => '18%/118%',   'rate' => 0.15254237    ],
        '10'    => ['name' => '10%',        'rate' => 0.10          ],
        '10110' => ['name' => '10%/110%',   'rate' => 0.09090909    ],
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