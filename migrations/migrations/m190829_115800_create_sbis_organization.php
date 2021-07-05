<?php

use app\models\Organization;
use app\modules\sbisTenzor\models\SBISOrganization;

/**
 * Class m190829_115800_create_sbis_organization
 */
class m190829_115800_create_sbis_organization extends \app\classes\Migration
{
    public $tableName;
    public $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8';

    public $tableNameUser = 'user_users';

    protected static $uniqueKeyColumns = [
        'organization_id',
        'is_active',
    ];
    protected static $uniqueKeySuffix = 'organization-active';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISOrganization::tableName();

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(11)->notNull(),
            'is_active' => $this->boolean()->notNull(),

            'is_sign_needed' => $this->boolean()->notNull(),
            'thumbprint' => $this->string(2048)->notNull(),
            'date_of_expire' => $this->date()->notNull(),

            'last_event_id' => $this->string(36),
            'previous_event_id' => $this->string(36),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime(),
            'last_fetched_at' => $this->dateTime(),
            'updated_by' => $this->integer(11),

        ], $this->tableOptions);

        $this->addCommentOnTable($this->tableName, 'Организация в системе СБИС');

        // indexes

        // foreign keys
        $this->addForeignKey(
            'fk-' . $this->tableName . '-' . 'updated_by',
            $this->tableName, 'updated_by',
            $this->tableNameUser, 'id'
        );

        // insert sbis organizations
        $this->insert($this->tableName, [
            'id' => SBISOrganization::ID_MCN_TELECOM,
            'organization_id' => Organization::MCN_TELECOM,
            'is_active' => true,
            'is_sign_needed' => false,
            'thumbprint' => '0A2921E3F16DC240D5FEFE005D7D3D255F6F628F',
            'date_of_expire' => '2019-12-26',
        ]);

        $this->insert($this->tableName, [
            'id' => SBISOrganization::ID_MCN_TELECOM_SERVICE,
            'organization_id' => Organization::MCN_TELECOM_SERVICE,
            'is_active' => true,
            'is_sign_needed' => true,
            'thumbprint' => '967D464639365CAA035523D871EC4DA836DCA912',
            'date_of_expire' => '2019-12-21',
        ]);

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
        $this->tableName = SBISOrganization::tableName();

        // foreign keys
        $this->dropForeignKey(
            'fk-' . $this->tableName . '-' . 'updated_by',
            $this->tableName
        );

        // indexes

        $this->dropTable($this->tableName);
    }
}
