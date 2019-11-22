<?php

use app\models\Invoice;
use app\modules\sbisTenzor\models\SBISDocument;
use app\modules\sbisTenzor\models\SBISGeneratedDraft;

/**
 * Class m191105_122005_create_sbis_generated_draft
 */
class m191105_122005_create_sbis_generated_draft extends \app\classes\Migration
{
    public $tableName;
    public $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8';
    public $tableNameInvoice;
    public $tableNameSBISDocument;

    protected static $indexField = 'state';
    protected static $uniqueKeyColumn = 'invoice_id';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISGeneratedDraft::tableName();
        $this->tableNameInvoice = Invoice::tableName();
        $this->tableNameSBISDocument = SBISDocument::tableName();

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'state' => $this->integer(3)->notNull(),
            'invoice_id' => $this->integer(11)->notNull(),
            'sbis_document_id' => $this->integer(11),

            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),

        ], $this->tableOptions);

        $this->addCommentOnTable($this->tableName, 'Сгенерированный черновик пакета документов для отправки в СБИС');

        // foreign keys
        $this->addForeignKey(
            'fk-' . $this->tableName . '-' . 'invoice_id',
            $this->tableName, 'invoice_id',
            $this->tableNameInvoice, 'id'
        );
        $this->addForeignKey(
            'fk-' . $this->tableName . '-' . 'sbis_document_id',
            $this->tableName, 'sbis_document_id',
            $this->tableNameSBISDocument, 'id'
        );

        // indexes
        $this->createIndex(
            'idx-' . $this->tableName . '-' . self::$indexField,
            $this->tableName,
            self::$indexField
        );

        // create new unique index
        $this->createIndex(
            'uniq-' . $this->tableName . '-' . self::$uniqueKeyColumn,
            $this->tableName,
            self::$uniqueKeyColumn,
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->tableName = SBISGeneratedDraft::tableName();

        // foreign keys
        $this->dropForeignKey(
            'fk-' . $this->tableName . '-' . 'sbis_document_id',
            $this->tableName
        );
        $this->dropForeignKey(
            'fk-' . $this->tableName . '-' . 'invoice_id',
            $this->tableName
        );

        // indexes
        $this->dropIndex(
            'uniq-' . $this->tableName . '-' . self::$uniqueKeyColumn,
            $this->tableName
        );

        $this->dropIndex(
            'idx-' . $this->tableName . '-' . self::$indexField,
            $this->tableName
        );

        $this->dropTable($this->tableName);
    }
}
