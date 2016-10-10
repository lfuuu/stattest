<?php

use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\NumberRange;

class m161010_190100_add_prefix_type extends \app\classes\Migration
{
    public function up()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $ndcTypeTableName = NdcType::tableName();
        $this->createTable($ndcTypeTableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
        ]);

        $this->insert($ndcTypeTableName, ['id' => NdcType::ID_ABC, 'name' => 'ABC']);
        $this->insert($ndcTypeTableName, ['id' => NdcType::ID_DEF, 'name' => 'DEF']);

        $numberRangeTableName = NumberRange::tableName();
        $fieldName = 'ndc_type_id';
        $this->addColumn($numberRangeTableName, $fieldName, $this->integer());
        $this->addForeignKey('fk-' . str_replace('.', '_', $numberRangeTableName) . '-' . $fieldName, $numberRangeTableName, $fieldName, $ndcTypeTableName, 'id', 'RESTRICT');

        // работать с исходной БД
        $this->db = $db;
    }

    public function down()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $numberRangeTableName = NumberRange::tableName();
        $this->dropColumn($numberRangeTableName, 'ndc_type_id');

        $ndcTypeTableName = NdcType::tableName();
        $this->dropTable($ndcTypeTableName);

        // работать с исходной БД
        $this->db = $db;
    }
}