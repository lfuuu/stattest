<?php

namespace app\forms\user;

use Yii;
use app\classes\Form;
use app\classes\AuthManager;
use app\models\User;

class UserPasswordForm extends Form
{

    public
        $id,
        $password,
        $passwordRepeat,
        $passwordCurrent;

    public function rules()
    {
        return [
            [
                ['password', 'passwordRepeat','passwordCurrent',],
                'required'
            ],
            [
                ['password', 'passwordRepeat','passwordCurrent',],
                'string'
            ],
        ];
    }

    public function attributeLabels()
    {
        return (new User())->attributeLabels();
    }

    public function save()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        if (AuthManager::getPasswordHash($this->passwordCurrent) !== $user->pass) {
            $this->addError('passwordCurrent', 'Старый пароль указан неверно');
        }

        if ($this->password != $this->passwordRepeat) {
            $this->addError('passwordRepeat', 'Пароли не совпадают');
        }

        if ($this->hasErrors())
            return false;

        /*$user->pass = AuthManager::getPasswordHash($this->password);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $user->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }*/

        Yii::$app->mailer->compose('user/change-password', ['form' => $this])
            ->setFrom('support@mcn.ru')
            ->setTo($user->email)
            ->setSubject('MCN.ru - ваш новый пароль | your new password')
            ->send();

        return true;
    }

}