<?php


use app\classes\Connection;

class m160519_111000_create_schema extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function up()
    {
        /** @var Connection $dbPgNnp */
        $dbPgNnp = Yii::$app->dbPgNnp;
        $dbPgNnp->createCommand('CREATE SCHEMA nnp')->execute();
    }

    /**
     * Откатить
     */
    public function down()
    {
        /** @var Connection $dbPgNnp */
        $dbPgNnp = Yii::$app->dbPgNnp;
        $dbPgNnp->createCommand('DROP SCHEMA nnp')->execute();
    }
}