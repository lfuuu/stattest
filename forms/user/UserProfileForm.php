<?php

namespace app\forms\user;

use Yii;
use app\classes\Form;
use app\classes\Language;
use app\models\User;
use app\models\City;

class UserProfileForm extends UserForm
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
        $photo,
        $show_troubles_on_every_page = 0;

}
