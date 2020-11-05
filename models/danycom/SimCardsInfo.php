<?php

namespace app\models\danycom;

use app\classes\model\ActiveRecord;

/**
 * Class SimCardsInfo
 * @property string $icc_id
 * @property string $track_id
 * @property string $client_id
 */
class SimCardsInfo extends ActiveRecord
{

    public static function tableName()
    {
        return 'dc_sim_cards_info';
    }
}
