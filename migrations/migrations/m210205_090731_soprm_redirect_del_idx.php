<?php

use app\classes\Migration;
use app\models\important_events\ImportantEventsNames;

/**
 * Class m210205_090731_soprm_redirect_del_idx
 */
class m210205_090731_soprm_redirect_del_idx extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $idx = Yii::$app->db->createCommand('SHOW INDEX FROM sorm_redirects WHERE KEY_NAME = \'sorm_redirects__idx\'')->queryOne();

        if ($idx) {
            $this->dropIndex('sorm_redirects__idx', 'sorm_redirects');
        }

        $idx = Yii::$app->db->createCommand('SHOW INDEX FROM sorm_redirects WHERE KEY_NAME = \'sorm_redirects__idx_delete\'')->queryOne();

        if ($idx) {
            $this->dropIndex('sorm_redirects__idx_delete', 'sorm_redirects');
        }

        $this->createIndex('sorm_redirects__idx_delete', 'sorm_redirects', ['delete_time']);

        $this->addColumn('sorm_redirects', 'client_id', $this->integer()->notNull()->after('id'));
        $this->renameColumn('sorm_redirects', 'account_id', 'usage_id');

        if (!ImportantEventsNames::find()
            ->where(['code' => ImportantEventsNames::REDIRECT_ADD])
            ->exists()) {
            $this->insert(ImportantEventsNames::tableName(), [
                'code' => ImportantEventsNames::REDIRECT_ADD,
                'value' => 'Добавление переадресации',
                'group_id' => 9,
                'comment' => ''
            ]);

            $this->insert(ImportantEventsNames::tableName(), [
                'code' => ImportantEventsNames::REDIRECT_DELETE,
                'value' => 'Удаление переадресации',
                'group_id' => 9,
                'comment' => ''
            ]);

        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn('sorm_redirects', 'client_id');
        $this->renameColumn('sorm_redirects', 'usage_id', 'account_id');
        $this->delete(ImportantEventsNames::tableName(), [
                'code' => [
                    ImportantEventsNames::REDIRECT_ADD,
                    ImportantEventsNames::REDIRECT_DELETE
                ]
            ]
        );
    }
}
