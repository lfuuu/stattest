<?php
namespace app\dao\important_events;

use Yii;
use app\classes\Singleton;
use yii\helpers\ArrayHelper;

class ImportantEventsDao extends Singleton
{

    public function getList($withEmpty = false)
    {
        $result =
            ArrayHelper::map(
                Yii::$app->db->createCommand('
                    SELECT
                        DISTINCT(ie.`event`), IFNULL(ien.`value`, ie.`event`) AS title
                    FROM
                        `important_events` ie
                            LEFT JOIN `important_events_names` `ien` ON ien.`code` = ie.`event`
                ')->queryAll(),
                'event', 'title'
            );

        if ($withEmpty) {
            $result = ['' => '- Выбрать -'] + $result;
        }

        return $result;
    }

}