<?php
use app\models\voip\Registry;
use app\modules\nnp\models\NdcType;

/**
 * Handles the dropping for table `number_type`.
 */
class m170511_144727_drop_number_type extends \app\classes\Migration
{
    // копипаст из удаленной модели NumberType
    const ID_GEO_DID = 1;
    const ID_NON_GEO_DID = 2;
    const ID_7800 = 8;
    const ID_MOBILE = 10;

    /**
     * Up
     */
    public function safeUp()
    {
        $this->_convertNumber();
        $this->_convertRegistry();
        $this->_convertDidGroup();
        $this->dropTable('voip_number_type_country');
        $this->dropTable('voip_number_type');
    }

    /**
     * Number
     */
    private function _convertNumber()
    {
        $tableName = \app\models\Number::tableName();

        // создать новое поле
        $this->addColumn($tableName, 'ndc_type_id', $this->integer());

        // конвертировать
        $this->update($tableName, ['ndc_type_id' => NdcType::ID_GEOGRAPHIC], ['number_type' => self::ID_GEO_DID]);
        $this->update($tableName, ['ndc_type_id' => NdcType::ID_NOMADIC], ['number_type' => self::ID_NON_GEO_DID]);
        $this->update($tableName, ['ndc_type_id' => NdcType::ID_FREEPHONE], ['number_type' => self::ID_7800]);
        $this->update($tableName, ['ndc_type_id' => NdcType::ID_MOBILE], ['number_type' => self::ID_MOBILE]);

        // удалить старое
        $this->dropForeignKey('fk-voip_numbers-number_type', $tableName);
        $this->dropColumn($tableName, 'number_type');
    }

    /**
     * Registry
     */
    private function _convertRegistry()
    {
        $tableName = Registry::tableName();

        // создать новое поле
        $this->addColumn($tableName, 'ndc_type_id', $this->integer());

        // конвертировать
        $this->update($tableName, ['ndc_type_id' => NdcType::ID_GEOGRAPHIC], ['number_type_id' => self::ID_GEO_DID]);
        $this->update($tableName, ['ndc_type_id' => NdcType::ID_NOMADIC], ['number_type_id' => self::ID_NON_GEO_DID]);
        $this->update($tableName, ['ndc_type_id' => NdcType::ID_FREEPHONE], ['number_type_id' => self::ID_7800]);
        $this->update($tableName, ['ndc_type_id' => NdcType::ID_MOBILE], ['number_type_id' => self::ID_MOBILE]);

        // удалить старое
        $this->dropForeignKey('fk-voip_registry-number_type_id', $tableName);
        $this->dropColumn($tableName, 'number_type_id');
    }

    /**
     * DidGroup
     */
    private function _convertDidGroup()
    {
        $tableName = \app\models\DidGroup::tableName();

        // создать новое поле
        $this->addColumn($tableName, 'ndc_type_id', $this->integer());

        // конвертировать
        $this->update($tableName, ['ndc_type_id' => NdcType::ID_GEOGRAPHIC], ['number_type_id' => self::ID_GEO_DID]);
        $this->update($tableName, ['ndc_type_id' => NdcType::ID_NOMADIC], ['number_type_id' => self::ID_NON_GEO_DID]);
        $this->update($tableName, ['ndc_type_id' => NdcType::ID_FREEPHONE], ['number_type_id' => self::ID_7800]);
        $this->update($tableName, ['ndc_type_id' => NdcType::ID_MOBILE], ['number_type_id' => self::ID_MOBILE]);

        // удалить старое
        $this->dropForeignKey('fk-number_type_id', $tableName);
        $this->dropColumn($tableName, 'number_type_id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        // восстанавливать только из бэкапа
        return false;
    }
}
