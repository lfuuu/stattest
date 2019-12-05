<?php

use app\modules\sbisTenzor\models\SBISGeneratedDraft;

/**
 * Class m191202_171002_add_sbis_draft_warnings
 */
class m191202_171002_add_sbis_draft_warnings extends \app\classes\Migration
{
    protected static $columnName = 'warnings';

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
