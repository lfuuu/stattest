<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;
use app\models\User;
use app\models\important_events\ImportantEvents;

class UserProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_USER_ID = 'user.id';
    const PROPERTY_USER_NAME = 'user.name';

    /** @var User|null $userAccount */
    private $userAccount = null;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $user_id = $this->setPropertyName('user_id')->getPropertyValue();

        $userAccount = User::findOne(['id' => (int)$user_id]);
        if (!is_null($userAccount)) {
            $this->userAccount = $userAccount;
        }
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_USER_ID => 'ID пользователя',
            self::PROPERTY_USER_NAME => 'Пользователь',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_USER_ID => $this->getValue(),
            self::PROPERTY_USER_NAME => $this->getName(),
        ];
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return (!is_null($this->userAccount) ? $this->userAccount->id : 0);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (!is_null($this->userAccount) ? $this->userAccount->name : '');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        if (!$this->getValue()) {
            return '';
        }

        return Html::tag('b', 'Создал: ') . $this->getName();
    }

}