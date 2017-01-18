<?php

namespace app\modules\notifier\forms;

use Yii;
use app\classes\Html;
use app\classes\validators\ArrayValidator;
use app\modules\notifier\Module as Notifier;
use app\classes\Form;
use app\models\Language;
use app\models\ClientAccountOptions;
use app\forms\client\ClientAccountOptionsForm;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;

class PersonalSchemeForm extends Form
{

    public
        $clientAccountId,
        $clientData,
        $events,
        $language;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['clientAccountId', 'integer'],
            ['clientAccountId', 'required'],
            ['events', ArrayValidator::className()],
            ['language', 'in', 'range' => array_keys(Language::getList())],
            ['language', 'default', 'value' => Language::LANGUAGE_DEFAULT],
        ];
    }

    /**
     * @return array|bool
     * @throws InvalidConfigException
     * @throws \yii\base\Exception
     */
    public function loadData()
    {
        try {
            /** @var Notifier $notifier */
            $notifier = Notifier::getInstance();
        } catch (InvalidConfigException $e) {
            Yii::$app->session->addFlash('error', 'Отсутствует конфигурация для MAILER');
            return false;
        }

        try {
            $response = $notifier->actions->getPersonalSchemeForClientAccount($this->clientAccountId);
        } catch (ErrorException $e) {
            Yii::$app->session->addFlash(
                'error',
                'Ошибка работы с MAILER. Текст ошибки:' . $e->getMessage()
            );
            return false;
        } catch (\Exception $e) {
            Yii::$app->session->addFlash(
                'error',
                'Отсутствует соединение с MAILER' . PHP_EOL . Html::tag('br') . 'Ошибка: ' . $e->getMessage()
            );
            return false;
        }

        if (!is_array($response)) {
            Yii::$app->session->addFlash('error', 'Ошибка формата данных MAILER');
            return false;
        }

        $result = [];

        foreach ($response as $record) {
            $result[] = [
                'event' => $record['event_code'],
                'group_id' => $record['group_id'],
                'do_email_monitoring' => $record['do_email_monitoring'],
                'do_email_operator' => $record['do_email_operator'],
                'do_email' => $record['do_email'],
                'do_email_personal' => $record['do_email_personal'],
                'do_sms' => $record['do_sms'],
                'do_sms_personal' => $record['do_sms_personal'],
                'do_lk' => $record['do_lk'],
            ];
        }

        return $result;
    }

    /**
     * Сохранение формы
     *
     * @return bool
     */
    public function saveData()
    {
        try {
            /** @var Notifier $notifier */
            $notifier = Notifier::getInstance();
        } catch (InvalidConfigException $e) {
            Yii::$app->session->addFlash('error', 'Отсутствует конфигурация для MAILER');
            return false;
        }

        (new ClientAccountOptionsForm)
            ->setClientAccountId($this->clientAccountId)
            ->setOption(ClientAccountOptions::OPTION_MAIL_DELIVERY_LANGUAGE)
            ->setValue($this->language)
            ->save($deleteExisting = true);

        $requestData = [];

        foreach ($this->events as $eventName => $eventData) {
            $row = $eventData;
            $row['event_code'] = $eventName;
            $requestData[] = $row;
        }

        try {
            $response = $notifier->actions->applyPersonalSchemeForClientAccount($this->clientAccountId, $requestData);
        } catch (ErrorException $e) {
            Yii::$app->session->addFlash(
                'error',
                'Ошибка работы с MAILER. Текст ошибки:' . $e->getMessage()
            );
            return false;
        } catch (\Exception $e) {
            Yii::$app->session->addFlash(
                'error',
                'Отсутствует соединение с MAILER' . PHP_EOL . Html::tag('br') . 'Ошибка: ' . $e->getMessage()
            );
            return false;
        }

        if (!is_array($response) || empty($response['count'])) {
            Yii::$app->session->addFlash('error', 'Ошибка формата данных MAILER');
            return false;
        }

        Yii::$app->session->addFlash('success', 'Данные успешно обновлены (' . $response['count'] . ' позиций)');
        return true;
    }

}
