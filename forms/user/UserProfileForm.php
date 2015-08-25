<?php

namespace app\forms\user;

use Yii;
use yii\web\UploadedFile;
use app\helpers\MediaFileHelper;
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
        $photo,
        $show_troubles_on_every_page = 0;

    public function rules()
    {
        return [
            [
                ['name', 'language','email', 'phone_work', 'phone_mobile', 'icq',],
                'string'
            ],
            [
                ['id', 'city_id', 'show_troubles_on_every_page',],
                'integer'
            ],
            ['photo', 'file'],
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
        if (!$this->setPhoto())
            return false;
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

    private function setPhoto()
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
