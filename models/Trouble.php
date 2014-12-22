<?php
namespace app\models;

use app\dao\TroubleDao;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $trouble_type
 * @property string $client
 * @property string $user_author
 * @property string $date_creation
 * @property string $problem
 * @property string $service
 * @property int $service_id
 * @property int $cur_stage_id
 * @property int $is_important
 * @property string $bill_no
 * @property string $bill_id
 * @property int $folder
 * @property string $doer_comment
 * @property int $all4geo_id
 * @property string $trouble_subtype
 * @property string $date_close
 * @property int $support_ticket_id
 * @property
 */
class Trouble extends ActiveRecord
{
    const DEFAULT_SUPPORT_USER = 'support';
    const DEFAULT_SUPPORT_FOLDER = 257;
    const DEFAULT_SUPPORT_STATE = 1;
    const TYPE_TROUBLE = 'trouble';
    const TYPE_TASK = 'task';
    const SUBTYPE_TROUBLE = 'trouble';

    public static function tableName()
    {
        return 'tt_troubles';
    }

    public static function dao()
    {
        return TroubleDao::me();
    }

}