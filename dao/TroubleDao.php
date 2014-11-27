<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\Trouble;

/**
 * @method static TroubleDao me($args = null)
 * @property
 */
class TroubleDao extends Singleton
{
    public function getMyTroublesCount()
    {
        return
            Trouble::getDb()->createCommand("
                select count(*)
                from tt_troubles as t
                inner join tt_stages as s  on s.stage_id = t.cur_stage_id and s.trouble_id = t.id
                where s.state_id not in (2,20,21,39,40) and s.date_start<=now() and s.user_main=:userLogin
            ", [':userLogin' => \Yii::$app->user->getIdentity()->user])
                ->queryScalar();
    }
}
