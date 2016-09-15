<?php
namespace app\forms\person;

use app\models\PersonI18N;
use Yii;
use app\classes\Form;
use app\models\Person;
use app\models\PersonLocalization;
use app\models\light_models\PersonLocalizationLight;

class PersonForm extends Form
{

    public
        $id,
        $signature_file_name = '';

    public function rules()
    {
        return [
            [['id', ], 'integer'],
            [['signature_file_name', ],'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'signature_file_name' => 'Подпись',
        ];
    }

    public function save($person = false)
    {
        if (!($person instanceof Person)) {
            $person = new Person;
        }
        $person->setAttributes($this->getAttributes(), false);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $person->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    public function delete(Person $person)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            PersonI18N::deleteAll([
                'person_id' => $person->id,
            ]);

            $person->delete();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

}