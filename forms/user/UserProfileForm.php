<?php

namespace app\forms\user;

use Yii;
use app\classes\Form;
use app\classes\Language;
use app\models\User;
use app\models\City;

class UserProfileForm extends Form
{

    public
        $id,
        $user,
        $name,
        $email,
        $phone_work,
        $phone_mobile,
        $icq,
        $language,
        $city_id,
        $photo_file_name,
        $show_troubles_on_every_page = 0;

    public function rules()
    {
        return [
            [
                ['name', 'language','email', 'phone_work', 'phone_mobile', 'icq', 'photo_file_name',],
                'string'
            ],
            [
                ['id', 'city_id', 'show_troubles_on_every_page',],
                'integer'
            ],
            ['city_id', 'default', 'value' => City::DEFAULT_USER_CITY_ID],
            ['language', 'default', 'value' => Language::DEFAULT_LANGUAGE],
        ];
    }

    public function attributeLabels()
    {
        return (new User())->attributeLabels();
    }

    public function initModel()
    {
        $this->setAttributes(Yii::$app->user->identity->getAttributes(), false);
        return $this;
    }

    public function save($user = false)
    {
        if (!($user instanceof User))
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

        Language::setCurrentLanguage($this->language);

        return true;
    }

}
