<?php

namespace app\forms\user;

use Yii;
use yii\db\Query;
use app\classes\Form;
use app\models\UserDeparts;

class DepartmentForm extends Form
{

    public $name;

    public function rules()
    {
        return [
            [['name',], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
        ];
    }

    /**
     * @return Query
     */
    public function spawnQuery()
    {
        return UserDeparts::find()->orderBy('name asc');
    }

    public function save($department = false)
    {
        if (!($department instanceof UserDeparts))
            $department = new UserDeparts;
        $department->setAttributes($this->getAttributes(), false);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $department->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    public function delete(UserDeparts $department)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $department->delete();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

}
