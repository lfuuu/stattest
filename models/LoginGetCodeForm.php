<?php

namespace app\models;

use app\classes\api\ApiPhone;
use app\dao\ClientContactDao;
use app\exceptions\ModelValidationException;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class LoginGetCodeForm extends Model
{
    public $username;

    private $user = false;
    private $phone = '';

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username'], 'required'],
            [['username'], 'validateUser'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Логин',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateUser($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->getUser();

        if (!$user) {
            $this->addError($attribute, 'Пользователь не найден');
            return;
        }

        $phone = trim($user->phone_mobile);

        if (!$phone) {
            return;
        }

        if ($phone) {
            $phone = ClientContactDao::me()->getE164($phone);
            $phone = reset($phone[1]);
            $phone = str_replace(['+'], '', $phone);
        }

        if (!$phone || !preg_match('/^79\d{9}$/', $phone)) {
            $this->addError($attribute, 'Мобильный номер не верен или заведен не правильно ' . var_export($phone, true));
            return;
        }

        $this->phone = $phone;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->user === false) {
            $this->user = User::findByUsername($this->username);
        }

        return $this->user;
    }

    public function makeCode()
    {
        $user = $this->getUser();

        if (!$user->phone_mobile) {
            return false;
        }

        if (!$this->phone) {
            throw new \UnexpectedValueException('У пользователя не задан номер сотового телефона');
        }

        $code = ApiPhone::me()->flashCall($this->phone);

        if (!$code) {
            throw new \BadMethodCallException('Flash call API error');
        }

        UserFlashCode::setCode($user->id, $code);

        return true;
    }
}
