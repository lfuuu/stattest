<?php

namespace app\forms\user;

use Yii;
use app\models\User;

class UserCreateForm extends UserForm
{

    public
        $user,
        $usergroup,
        $name;

    public $id, $pass;

    public function rules()
    {
        return [
            [
                ['user', 'usergroup', 'name',],
                'required'
            ],
            [
                ['user', 'usergroup', 'name',],
                'string'
            ],
            ['user', 'validateExistsUser'],

        ];
    }

    public function validateExistsUser($model)
    {
        $user = User::findOne(['user' => $this->{$model}]);
        if ($user instanceof User && $user->user)
            $this->addError($model, 'Пользователь уже существует');
    }

    public function save()
    {
        $this->pass = substr(Yii::$app->getSecurity()->generateRandomString(), 0, 8);

        $user = new User;
        $user->setAttributes($this->getAttributes(), false);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $user->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->id = $user->id;

        return true;
    }

}