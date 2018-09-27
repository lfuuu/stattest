<?php

namespace app\modules\notifier\forms;

use app\classes\Form;
use app\classes\validators\ArrayValidator;
use app\forms\client\ClientAccountOptionsForm;
use app\models\ClientAccount;
use app\models\ClientAccountOptions;
use app\models\Language;
use app\models\User;
use app\modules\notifier\components\decorators\SchemePersonalDecorator;
use app\modules\notifier\components\decorators\WhiteListDecorator;
use app\modules\notifier\components\traits\FormExceptionTrait;
use app\modules\notifier\Module as Notifier;
use Yii;
use yii\base\InvalidConfigException;

class PersonalSchemeForm extends Form
{

    use FormExceptionTrait;

    /** @var ClientAccount */
    public $clientAccount;

    /** @var array */
    public $clientData;

    /** @var array */
    public $events;

    /** @var string */
    public $language;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['events', ArrayValidator::class],
            ['language', 'in', 'range' => array_keys(Language::getList())],
            ['language', 'default', 'value' => Language::LANGUAGE_DEFAULT],
        ];
    }

    /**
     * @return WhiteListDecorator
     */
    public function getAvailableEvents()
    {
        $whitelist = [
            'isAvailable' => true,
        ];

        try {
            $whitelist += Notifier::getInstance()->actions->getWhiteList();
        } catch (\Exception $e) {
            $this->catchException($e);
        }

        return new WhiteListDecorator($whitelist);
    }

    /**
     * @return array|bool
     * @throws InvalidConfigException
     * @throws \yii\base\Exception
     */
    public function loadScheme()
    {
        try {
            $response = Notifier::getInstance()->actions->getSchemePersonal($this->clientAccount->id);
            return (new SchemePersonalDecorator($response))
                ->prettyScheme;
        } catch (\Exception $e) {
            return $this->catchException($e);
        }
    }

    /**
     * @return \app\modules\notifier\models\Schemes[]
     */
    public function loadGlobalScheme()
    {
        return (new SchemesForm)->getCountryNotificationScheme($this->clientAccount->contragent->country->code);
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function saveData()
    {
        (new ClientAccountOptionsForm)
            ->setClientAccountId($this->clientAccount->id)
            ->setOption(ClientAccountOptions::OPTION_MAIL_DELIVERY_LANGUAGE)
            ->setValue($this->language)
            ->save($deleteExisting = true);

        try {
            return Notifier::getInstance()->actions->applySchemePersonal(
                $this->clientAccount->id,
                $this->events,
                Yii::$app->user->getId() ?: User::SYSTEM_USER_ID
            );
        } catch (\Exception $e) {
            return $this->catchException($e);
        }
    }

}