<?php

use app\modules\sbisTenzor\models\SBISGeneratedDraft;

/**
 * Class m191125_120002_add_sbis_draft_errors
 */
class m191125_120002_add_sbis_draft_errors extends \app\classes\Migration
{
    protected static $columnName = 'errors';

    public $tableName;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISGeneratedDraft::tableName();

        $this->addColumn(
            $this->tableName,
            self::$columnName,
            $this
                ->text()
                ->after('state')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->tableName = SBISGeneratedDraft::tableName();

        $this->dropColumn($this->tableName, self::$columnName);
    }
}
