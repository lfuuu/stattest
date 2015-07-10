<?php
namespace app\forms\person;

use Yii;
use app\classes\Form;
use app\models\Person;

class PersonForm extends Form
{

    public
        $id,
        $name_nominative,
        $name_genitive = '',
        $post_nominative,
        $post_genitive = '',
        $signature_file_name = '';

    public function rules()
    {
        return [
            [['id',], 'integer'],
            [['name_nominative', 'name_genitive', 'post_nominative', 'post_genitive', 'signature_file_name',], 'string'],
            [['name_nominative', 'post_nominative',], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name_nominative'      => 'ФИО (им. п.)',
            'name_genitive'        => 'Фио (род. п.)',
            'post_nominative'      => 'Должность (им. п.)',
            'post_genitive'        => 'Должность (род. п.)',
            'signature_file_name'   => 'Подпись',
        ];
    }

    public function save($person = false)
    {
        if (!($person instanceof Person))
            $person = new Person;
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
            $person->delete();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

}