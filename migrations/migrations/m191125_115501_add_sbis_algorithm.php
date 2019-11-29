<?php

use app\modules\sbisTenzor\models\SBISOrganization;

/**
 * Class m191125_115501_add_sbis_algorithm
 */
class m191125_115501_add_sbis_algorithm extends \app\classes\Migration
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

        // 1.2.643.2.2.9 для ГОСТ Р 34.11-94
        // 1.2.643.7.1.1.2.2 для ГОСТ Р 34.11-2012 256 bit
        // 1.2.643.7.1.1.2.3 для ГОСТ Р 34.11-2012 512 bi

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
