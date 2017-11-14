<?php

use app\models\document\DocumentTemplate;
use app\models\Language;

/**
 * Class m171113_143507_document_template_legal_and_lang
 */
class m171113_143507_document_template_legal_and_lang extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(DocumentTemplate::tableName(), 'is_legal', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn(DocumentTemplate::tableName(), 'language', $this->string()->notNull()->defaultValue(Language::LANGUAGE_DEFAULT));
        $this->addForeignKey('fk-' . Language::tableName().'-code', DocumentTemplate::tableName(), 'language', Language::tableName(), 'code', 'RESTRICT', 'CASCADE');

        $this->update(DocumentTemplate::tableName(), ['is_legal' => 0], ['id' => 159]);
        $this->update(DocumentTemplate::tableName(), ['language' => Language::LANGUAGE_MAGYAR], ['id' => 133]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(DocumentTemplate::tableName(), 'is_legal');
        $this->dropForeignKey('fk-' . Language::tableName().'-code', DocumentTemplate::tableName());
        $this->dropColumn(DocumentTemplate::tableName(), 'language');
    }
}
