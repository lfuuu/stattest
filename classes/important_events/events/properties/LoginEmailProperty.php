<?php

namespace app\classes\important_events\events\properties;

use Yii;
use app\classes\Html;
use app\classes\IpUtils;
use app\models\important_events\ImportantEvents;

class LoginEmailProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_LOGIN_EMAIL = 'login_email';

    private $email = '';

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        if (
            property_exists($event->properties, 'support_email')
            && property_exists($event->properties, 'is_support')
            && $event->properties->is_support
        ) {
            $this->email = $event->properties->support_email;
        } else {
            $this->email = $event->login;
        }
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_LOGIN_EMAIL => 'Login email',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_LOGIN_EMAIL => $this->getValue(),
        ];
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->email ?: 'не задано';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        if (!$this->getValue()) {
            return '';
        }

        return Html::tag('b', self::labels()[self::PROPERTY_LOGIN_EMAIL] . ': ') . $this->getValue();
    }

}