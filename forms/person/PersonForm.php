<?php
namespace app\forms\person;

use Yii;
use app\classes\Form;
use app\models\Person;

class PersonForm extends Form
{

    public
        $id,
        $name_nominativus,
        $name_genitivus = '',
        $post_nominativus,
        $post_genitivus = '',
        $signature_file_name = '';

    public function rules()
    {
        return [
            [['id',], 'integer'],
            [['name_nominativus', 'name_genitivus', 'post_nominativus', 'post_genitivus', 'signature_file_name',], 'string'],
            [['name_nominativus', 'post_nominativus',], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name_nominativus'      => 'ФИО (им. п.)',
            'name_genitivus'        => 'Фио (род. п.)',
            'post_nominativus'      => 'Должность (им. п.)',
            'post_genitivus'        => 'Должность (род. п.)',
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