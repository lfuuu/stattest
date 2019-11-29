<?php

use app\modules\sbisTenzor\models\SBISOrganization;

/**
 * Class m191125_120002_add_sbis_document_error
 */
class m191125_120002_add_sbis_document_error extends \app\classes\Migration
{
    protected static $columnName = 'algorithm';

    public $tableName;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISOrganization::tableName();

        $this->addColumn(
            $this->tableName,
            self::$columnName,
            $this
                ->string(32)
                ->after('thumbprint')
        );

        // update data
        $this->update(
            $this->tableName,
            [
                self::$columnName => '1.2.643.7.1.1.2.2',
            ],
            [
                'id' => SBISOrganization::ID_MCN_TELECOM,
            ]
        );
        $this->update(
            $this->tableName,
            [
                self::$columnName => '1.2.643.7.1.1.2.2',
            ],
            [
                'id' => SBISOrganization::ID_MCN_TELECOM_SERVICE,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->tableName = SBISOrganization::tableName();

        $this->dropColumn($this->tableName, self::$columnName);
    }
}
