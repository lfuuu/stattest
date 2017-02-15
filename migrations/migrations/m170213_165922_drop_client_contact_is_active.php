<?php
use app\models\ClientContact;

class m170213_165922_drop_client_contact_is_active extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        // удалить все выключенные. Если что - они будут в истории
        $query = ClientContact::find()->where(['is_active' => 0]);
        /** @var ClientContact $clientContact */
        foreach ($query->each() as $clientContact) {
            $clientContact->delete();
        }

        $this->dropColumn(ClientContact::tableName(), 'is_active');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->addColumn(ClientContact::tableName(), 'is_active', $this->integer()->notNull()->defaultValue(1));
    }
}
