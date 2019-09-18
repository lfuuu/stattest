<?php

use app\modules\sbisTenzor\models\SBISAttachment;
use app\modules\sbisTenzor\models\SBISDocument;

/**
 * Class m190829_115856_create_sbis_attachment
 */
class m190829_115856_create_sbis_attachment extends \app\classes\Migration
{
    public $tableName;
    public $tableOptions;

    public $tableNameUser = 'user_users';
    public $tableNameSBISDocument;

    protected static $uniqueKeyColumns = [
        'sbis_document_id',
        'number',
    ];
    protected static $uniqueKeySuffix = 'doc_id-number';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISAttachment::tableName();
        $this->tableNameSBISDocument = SBISDocument::tableName();

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'sbis_document_id' => $this->integer(11)->notNull(),

            'external_id' => $this->string(36)->notNull(),
            'number' => $this->tinyInteger()->unsigned()->notNull(),
            'extension' => $this->string(10),
            'file_name' => $this->string(255),
            'stored_path' => $this->string(512),
            'hash' => $this->string(255),
            'signature_stored_path' => $this->string(512),

            'is_sign_needed' => $this->boolean()->notNull(),
            'is_signed' => $this->boolean()->notNull(),

            'link' => $this->string(255),
            'url_online' => $this->string(128),
            'url_html' => $this->string(2048),
            'url_pdf' => $this->string(2048),

            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),
            'signed_at' => $this->dateTime(),

        ], $this->tableOptions);

        $this->addCommentOnTable($this->tableName, 'Вложение из пакета документов в системе СБИС');

        // indexes

        // foreign keys
        $this->addForeignKey(
            'fk-' . $this->tableName . '-' . 'sbis_document_id',
            $this->tableName, 'sbis_document_id',
            $this->tableNameSBISDocument, 'id'
        );

        // create new unique index
        $this->createIndex(
            'uniq-' . $this->tableName . '-' . self::$uniqueKeySuffix,
            $this->tableName,
            self::$uniqueKeyColumns,
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->tableName = SBISAttachment::tableName();

        // foreign keys
        $this->dropForeignKey(
            'fk-' . $this->tableName . '-' . 'sbis_document_id',
            $this->tableName
        );

        // indexes

        $this->dropTable($this->tableName);
    }
}
