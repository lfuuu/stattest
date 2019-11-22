<?php

use app\modules\sbisTenzor\models\SBISOrganization;

/**
 * Class m191105_115509_add_sbis_exchange_id
 */
class m191105_115509_add_sbis_exchange_id extends \app\classes\Migration
{
    protected static $exchangeIdField = 'exchange_id';

    public $tableName;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISOrganization::tableName();

        $this->addColumn(
            $this->tableName,
            self::$exchangeIdField,
            $this
                ->string(46)
                ->after('is_active')
        );

        // update data
        $this->update(
            $this->tableName,
            [
                self::$exchangeIdField => '2BE7a65bc0e36f811e38923005056917125',
            ],
            [
                'id' => SBISOrganization::ID_MCN_TELECOM,
            ]
        );
        $this->update(
            $this->tableName,
            [
                self::$exchangeIdField => '2BEba59b68c0cbd49b68da49a14e227d6b6',
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

        $this->dropColumn($this->tableName, self::$exchangeIdField);
    }
}
