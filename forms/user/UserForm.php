<?php

namespace app\forms\user;

use app\models\UserGrantUsers;
use Yii;
use app\classes\Form;
use app\classes\Language;
use app\classes\validators\ArrayValidator;
use app\models\User;
use app\models\City;
use yii\web\UploadedFile;
use app\helpers\MediaFileHelper;

class UserForm extends Form
{

    public
        $id,
        $user,
        $name,
        $email,
        $phone_work,
        $phone_mobile,
        $icq,
        $language = Language::DEFAULT_LANGUAGE,
        $city_id,
        $photo,
        $show_troubles_on_every_page = 0,
        $usergroup,
        $depart_id = 0,
        $trouble_redirect,
        $courier_id = 0,
        $enabled = 'yes';

    public $initModel, $rights;

    public function rules()
    {
        return [
            [['user', 'usergroup', 'name',], 'required'],
            [
                ['name', 'language','email', 'phone_work', 'phone_mobile', 'icq', 'trouble_redirect', 'usergroup', 'enabled'],
                'string'
            ],
            [
                ['id', 'city_id', 'show_troubles_on_every_page', 'depart_id', 'courier_id',],
                'integer'
            ],
            ['photo', 'file'],
            ['rights', ArrayValidator::className()],
            ['city_id', 'default', 'value' => City::DEFAULT_USER_CITY_ID],
            ['language', 'default', 'value' => Language::DEFAULT_LANGUAGE],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user' => 'Логин',
            'name' => 'Полное имя',
            'language' => 'Язык',
            'city_id' => 'Город',
            'usergroup' => 'Группа',
            'depart_id' => 'Отдел',
            'email' => 'E-mail',
            'phone_work' => 'Внутренний номер (логин в comcenter)',
            'phone_mobile' => 'Мобильный телефон',
            'icq' => 'ICQ',
            'trouble_redirect' => 'Перенаправление траблов',
            'photo' => 'Фотография',
            'show_troubles_on_every_page' => 'Показывать заявки на каждой странице',
            'courier_id' => 'Привязка к курьеру',
            'enabled' => 'Пользователь активен',
        ];
    }

    public function initModel(User $user)
    {
        $this->initModel = $user;
        $this->setAttributes($user->getAttributes(), false);
        return $this;
    }

    public function save($user = false)
    {
        if (!($user instanceof User))
            $user = new User;
        if (!$this->setPhoto())
            return false;
        $user->setAttributes($this->getAttributes(), false);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $user->save();

            if (Yii::$app->user->can('users.grant') && count($this->rights)) {
                UserGrantUsers::setRights($user, $this->rights);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        Language::setCurrentLanguage($this->language);

        return true;
    }

    public function delete(User $user)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            UserGrantUsers::deleteAll(['name' => $user->user]);

            $user->delete();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    protected function setPhoto()
    {
        $photo = UploadedFile::getInstance($this, 'photo');
        if ($photo instanceof UploadedFile) {
            list($width, $height, $type) = getimagesize($photo->tempName);

            if ($width > User::PHOTO_SIZE_OF_SQUARE_SIDE || $height > User::PHOTO_SIZE_OF_SQUARE_SIDE) {
                $image = null;
                switch ($type) {
                    case IMG_GIF:
                        $image = imagecreatefromgif($photo->tempName);
                        break;
                    case IMG_JPG:
                    case IMG_JPEG:
                        $image = imagecreatefromjpeg($photo->tempName);
                        break;
                    case 3:
                        $image = imagecreatefrompng($photo->tempName);
                        break;
                }

                if ($image) {
                    $maxSide = ($width > $height ? $width : $height);
                    $newWidth = floor(User::PHOTO_SIZE_OF_SQUARE_SIDE * $width / $maxSide);
                    $newHeight = floor(User::PHOTO_SIZE_OF_SQUARE_SIDE * $height / $maxSide);

                    $resample = imagecreatetruecolor($newWidth, $newHeight);
                    imagecopyresampled($resample, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagejpeg($resample, MediaFileHelper::getLocalPath() . Yii::$app->params['USER_PHOTO_DIR'] . $this->id . '.jpg', 65);

                    $this->photo = 'jpg';
                }
                else {
                    $this->addError('photo', 'Невозможно изменить размер картинки.');
                    return false;
                }
            }
        }

        return true;
    }

}