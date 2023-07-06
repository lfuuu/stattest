<?php

use app\classes\Migration;
use app\classes\Utils;
use app\models\EventCmdId;
use app\models\EventQueue;

/**
 * Class m230706_102055_ideop_cmd
 */
class m230706_102055_ideop_cmd extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(EventCmdId::tableName(), [
            'id' => $this->string(64)->notNull()->unique(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
        $this->addPrimaryKey('pk-' . EventCmdId::tableName(), EventCmdId::tableName(), 'id');


        $query = EventQueue::find()->where(['event' => EventQueue::EVENT_BUS_CMD])->createCommand()->query();

        $ids = [];
        foreach ($query as $row) {
            $json = Utils::fromJson($row['param']);
            $id = $json['payload']['id'] ?? null;

            if (!$id) {
                continue;
            }

            $ids[] = [$id];
            echo PHP_EOL . $id;
        }

        if ($ids) {
            Yii::$app->db->createCommand()->batchInsert(EventCmdId::tableName(), ['id'], $ids)->execute();
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(EventCmdId::tableName());
    }
}
