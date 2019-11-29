<?php

use app\modules\sbisTenzor\models\SBISAttachment;

/**
 * Class m191125_120503_add_sbis_attachment_modified_file
 */
class m191125_120503_add_sbis_attachment_modified_file extends \app\classes\Migration
{
    protected static $columnName = 'stored_path_modified';
    protected static $hashColumnName = 'hash_stored_path';

    public $tableName;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISAttachment::tableName();

        $this->addColumn(
            $this->tableName,
            self::$columnName,
            $this
                ->string(512)
                ->after('stored_path')
        );

        $this->addColumn(
            $this->tableName,
            self::$hashColumnName,
            $this
                ->string(512)
                ->after('hash')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->tableName = SBISAttachment::tableName();

        $this->dropColumn($this->tableName, self::$hashColumnName);
        $this->dropColumn($this->tableName, self::$columnName);
    }
}
