<?php
namespace app\models;

use yii\db\ActiveRecord;


/**
 * @property int $pk
 * @property string $e164
 * @property string $action
 * @property int $client
 * @property int $user
 * @property string $time
 * @property string $addition
 *
 * Class NumberLog
 * @package app\models
 */
class NumberLog extends ActiveRecord
{
    const ACTION_CREATE = 'create';

    const ACTION_FIX = 'fix';
    const ACTION_UNFIX = 'unfix';

    const ACTION_HOLD = 'hold';
    const ACTION_UNHOLD = 'unhold';

    const ACTION_ACTIVE = 'active';
    const ACTION_ADDITION_TESTED = 'tested';
    const ACTION_ADDITION_COMMERCIAL = 'commercial';

    const ACTION_INVERTRESERVED = 'invertReserved';
    //const ACTION_INVERTOUR = 'invertOur';
    //const ACTION_INVERTSPECIAL = 'invertSpecial';

    const ACTION_SALE = 'sale';
    const ACTION_NOTSALE = 'notsale';


    /**
     * @return string
     */
    public static function tableName()
    {
        return 'e164_stat';
    }

}