<?php

namespace app\modules\notifier\forms;

use app\classes\Form;
use app\classes\validators\ArrayValidator;
use app\models\Country;
use app\models\User;
use app\modules\notifier\components\decorators\SchemeDecorator;
use app\modules\notifier\components\decorators\WhiteListDecorator;
use app\modules\notifier\components\traits\FormExceptionTrait;
use app\modules\notifier\Module as Notifier;
use Yii;

/**
 * @property int $countryCode
 * @property array $whitelist
 * @property array|null $formData
 */
class ControlForm extends Form
{

    use FormExceptionTrait;

    /** @var int */
    public $countryCode;

    /** @var array */
    public $whitelist;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['countryCode', 'string'],
            ['whitelist', ArrayValidator::className()],
        ];
    }

    /**
     * @return string[]
     */
    public function getAvailableCountries()
    {
        return Country::getList();
    }

    /**
     * @return WhiteListDecorator
     */
    public function getWhiteList()
    {
        $whitelist = [];

        try {
            $whitelist = Notifier::getInstance()->actions->getWhiteList();
        } catch (\Exception $e) {
            $this->catchException($e);
        }

        return new WhiteListDecorator($whitelist);
    }

    /**
     * @param int $countryCode
     * @return SchemeDecorator
     */
    public function getScheme($countryCode)
    {
        try {
            return new SchemeDecorator(Notifier::getInstance()->actions->getScheme($countryCode));
        } catch (\Exception $e) {
            return $this->catchException($e);
        }
    }

    /**
     * @return bool
     */
    public function applyPublish()
    {
        try {
            return Notifier::getInstance()->actions->applyScheme($this->countryCode, $this->_getUserId());
        } catch (\Exception $e) {
            return $this->catchException($e);
        }
    }

    /**
     * @return bool
     */
    public function applyWhiteList()
    {
        try {
            return Notifier::getInstance()->actions->applyWhiteList($this->whitelist, $this->_getUserId());
        } catch (\Exception $e) {
            return $this->catchException($e);
        }
    }

    /**
     * @return int
     */
    private function _getUserId()
    {
        return Yii::$app->user->getId() ?: User::SYSTEM_USER_ID;
    }

}
