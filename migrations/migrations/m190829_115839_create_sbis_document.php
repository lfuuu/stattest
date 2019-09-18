<?php

use app\modules\sbisTenzor\models\SBISDocument;
use app\modules\sbisTenzor\models\SBISOrganization;

/**
 * Class m190829_115839_create_sbis_document
 */
class m190829_115839_create_sbis_document extends \app\classes\Migration
{
    public $tableName;
    public $tableNameOrganization;
    public $tableOptions;

    public $tableNameUser = 'user_users';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISDocument::tableName();
        $this->tableNameOrganization = SBISOrganization::tableName();

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'external_id' => $this->string(36)->notNull(),

            'sbis_organization_id' => $this->integer(11)->notNull(),
            'client_account_id' => $this->integer(11)->notNull(),

            'state' => $this->integer(3)->notNull(),
            'external_state' => $this->integer(2),
            'external_state_name' => $this->string(64),
            'external_state_description' => $this->string(255),

            'type' => $this->integer(2)->notNull(),
            'date' => $this->date(),
            'number' => $this->string(64),
            'comment' => $this->string(255),

            'flags' => $this->integer(11),
            'last_event_id' => $this->string(36),

            'url_our' => $this->string(100),
            'url_external' => $this->string(100),
            'url_pdf' => $this->string(1024),
            'url_archive' => $this->string(1024),

            'error_code' => $this->integer(),
            'errors' => $this->text(),

            'priority' => $this->integer(2)->notNull(),
            'tries' => $this->integer(2)->notNull(),

            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),

            'started_at' => $this->dateTime(),
            'saved_at' => $this->dateTime(),
            'prepared_at' => $this->dateTime(),
            'signed_at' => $this->dateTime(),
            'sent_at' => $this->dateTime(),
            'last_fetched_at' => $this->dateTime(),
            'read_at' => $this->dateTime(),
            'completed_at' => $this->dateTime(),

            'created_by' => $this->integer(11)->notNull(),
            'updated_by' => $this->integer(11),

        ], $this->tableOptions);

        $this->addCommentOnTable($this->tableName, 'Пакет документов в системе СБИС');

        // indexes
        $this->createIndex(
            'idx-' . $this->tableName . '-'  . 'sbis_organization_id-state',
            $this->tableName,
            [
                'sbis_organization_id',
                'state',
            ]
        );

        // foreign keys
        $this->addForeignKey(
            'fk-' . $this->tableName . '-' . 'sbis_organization_id',
            $this->tableName, 'sbis_organization_id',
            $this->tableNameOrganization, 'id'
        );

        $this->addForeignKey(
            'fk-' . $this->tableName . '-' . 'created_by',
            $this->tableName, 'created_by',
            $this->tableNameUser, 'id'
        );

        $this->addForeignKey(
            'fk-' . $this->tableName . '-' . 'updated_by',
            $this->tableName, 'updated_by',
            $this->tableNameUser, 'id'
        );

        // create new unique index
        $this->createIndex(
            'uniq-' . $this->tableName . '-' . 'external_id',
            $this->tableName,
            'external_id',
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->tableName = SBISDocument::tableName();

        // foreign keys
        $this->dropForeignKey(
            'fk-' . $this->tableName . '-' . 'updated_by',
            $this->tableName
        );
        $this->dropForeignKey(
            'fk-' . $this->tableName . '-' . 'created_by',
            $this->tableName
        );
        $this->dropForeignKey(
            'fk-' . $this->tableName . '-' . 'sbis_organization_id',
            $this->tableName
        );

        // indexes
        $this->dropIndex(
            'uniq-' . $this->tableName . '-'  . 'external_id',
            $this->tableName
        );
        $this->dropIndex(
            'idx-' . $this->tableName . '-'  . 'sbis_organization_id-state',
            $this->tableName
        );

        $this->dropTable($this->tableName);
    }
}
