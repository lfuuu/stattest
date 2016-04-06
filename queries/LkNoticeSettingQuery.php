<?php
namespace app\queries;

use app\models\LkNoticeSetting;
use yii\db\ActiveQuery;

/**
 * @method LkNoticeSetting[] all($db = null)
 * @property
 */
class LkNoticeSettingQuery extends ActiveQuery
{
    /**
     * @return static the query object itself
     */
    public function active()
    {
        return $this->andWhere(['status' => LkNoticeSetting::STATUS_WORK]);
    }

}