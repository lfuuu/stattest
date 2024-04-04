<?php

/**
 * Class m240403_143339_model_life_recoder
 */
class m240403_143339_model_life_recoder extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(\app\models\ModelLifeLog::tableName(), [
            'id' => $this->primaryKey(),
            'model' => $this->string(255)->notNull(),
            'model_id'=>$this->integer()->notNull(),
            'created_at' => $this->dateTime(),
            'action' => $this->string(32),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(\app\models\ModelLifeLog::tableName());
    }
}
