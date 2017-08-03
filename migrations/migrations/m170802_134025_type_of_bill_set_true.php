<?php
use app\models\ClientAccount;
use app\models\HistoryChanges;
use app\models\HistoryVersion;
use yii\db\Expression;

/**
 * Class m170802_134025_type_of_bill_set_true
 */
class m170802_134025_type_of_bill_set_true extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->update(
            ClientAccount::tableName(),
            ['type_of_bill' => ClientAccount::TYPE_OF_BILL_DETAILED],
            ['type_of_bill' => ClientAccount::TYPE_OF_BILL_SIMPLE]
        );

        $this->update(HistoryChanges::tableName(),
            ['data_json' => new Expression('replace(replace(data_json, \'"type_of_bill":0\', \'"type_of_bill":1\'), \'"type_of_bill":"0"\', \'"type_of_bill":1\')')],
            ['rlike', 'data_json', '"type_of_bill":"?0']
        );

        $this->update(HistoryChanges::tableName(),
            ['prev_data_json' => new Expression('replace(replace(prev_data_json, \'"type_of_bill":0\', \'"type_of_bill":1\'), \'"type_of_bill":"0"\', \'"type_of_bill":1\')')],
            ['rlike', 'prev_data_json', '"type_of_bill":"?0']
        );

        $this->update(HistoryVersion::tableName(),
            ['data_json' => new Expression('replace(replace(data_json, \'"type_of_bill":0\', \'"type_of_bill":1\'), \'"type_of_bill":"0"\', \'"type_of_bill":1\')')],
            ['rlike', 'data_json', '"type_of_bill":"?0']
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        // nothing
    }
}
