<?php

namespace app\modules\notifier\forms;

use Yii;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use app\classes\Form;
use app\classes\Html;
use app\classes\Event;
use app\classes\validators\ArrayValidator;
use app\models\Country;
use app\models\important_events\ImportantEventsNames;
use app\models\User;
use app\modules\notifier\Module as Notifier;
use app\modules\notifier\models\Logger;
use app\modules\notifier\models\Schemes;

/**
 * @property array|null $formData
 */
class ControlForm extends Form
{

    /** @var int */
    public $country_code;
    /** @var array */
    public $whitelist;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['country_code', 'string'],
            ['whitelist', ArrayValidator::className()],
        ];
    }

    /**
     * @return bool
     */
    public function applyPublish()
    {
        return Event::go(
            Event::PUBLISH_NOTIFICATION_SCHEME,
            [
                'country' => $this->country_code,
                'user_id' => \Yii::$app->user->getId() ?: User::SYSTEM_USER_ID,
            ]
        ) ? true : false;
    }

    /**
     * @return bool
     */
    public function saveWhiteList()
    {
        try {
            /** @var Notifier $notifier */
            $notifier = Notifier::getInstance();
        } catch (InvalidConfigException $e) {
            Yii::$app->session->addFlash('error', 'Отсутствует конфигурация для MAILER');
        }

        try {
            $notifier->actions->applyWhiteList($this->whitelist);
            return true;
        } catch (ErrorException $e) {
            Yii::$app->session->addFlash(
                'error',
                'Ошибка работы с MAILER. Текст ошибки:' . $e->getMessage()
            );
        } catch (\Exception $e) {
            Yii::$app->session->addFlash(
                'error',
                'Отсутствует соединение с MAILER' . PHP_EOL . Html::tag('br') . 'Ошибка: ' . $e->getMessage()
            );
        }

        return false;
    }

    /**
     * @return Country[]
     */
    public function getAvailableCountries()
    {
        return Country::getList();
    }

    /**
     * @return ArrayDataProvider
     */
    public function getAvailableEvents()
    {
        try {
            /** @var Notifier $notifier */
            $notifier = Notifier::getInstance();
        } catch (InvalidConfigException $e) {
            Yii::$app->session->addFlash('error', 'Отсутствует конфигурация для MAILER');
            return false;
        }

        $events = ImportantEventsNames::find()->all();
        $whitelist = [];
        $data = [];

        try {
            $whitelist = $notifier->actions->getWhiteList();
        } catch (ErrorException $e) {
            Yii::$app->session->addFlash(
                'error',
                'Ошибка работы с MAILER. Текст ошибки:' . $e->getMessage()
            );
        } catch (\Exception $e) {
            Yii::$app->session->addFlash(
                'error',
                'Отсутствует соединение с MAILER' . PHP_EOL . Html::tag('br') . 'Ошибка: ' . $e->getMessage()
            );
        }

        foreach ($events as $event) {
            $data[] = [
                'id' => $event->id,
                'code' => $event->code,
                'title' => $event->value,
                'group_id' => $event->group_id,
                'in_use' => array_key_exists($event->code, $whitelist) ? $whitelist[$event->code] : 0,
            ];
        }

        return new ArrayDataProvider([
            'allModels' => $data,
            'sort' => false,
            'pagination' => false,
        ]);
    }

    /**
     * @param int $countryCode
     * @return bool|string
     */
    public function getCountOfClientsInCountry($countryCode)
    {
        return
            Schemes::findClientInCountry($countryCode)
            ->select(new Expression('COUNT(*)'))
            ->scalar();
    }

    /**
     * @param int $countryCode
     * @return array
     */
    public function getSchemeLastPublishData($countryCode)
    {
        return Logger::find()
            ->where([
                'action' => Logger::ACTION_APPLY_SCHEME,
                'value' => $countryCode,
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();
    }

    /**
     * @return null|\yii\db\ActiveRecord
     */
    public function getWhitelistLastPublishData()
    {
        return Logger::find()
            ->where([
                'action' => Logger::ACTION_APPLY_WHITELIST,
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();
    }

}
