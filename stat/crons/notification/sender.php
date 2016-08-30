<?php

use app\classes\Smser;
use app\models\LkNotice;

define('NO_WEB', 1);
define('PATH_TO_ROOT', '../../');
include PATH_TO_ROOT . "conf_yii.php";
include INCLUDE_PATH . "runChecker.php";

echo "\n" . date("r") . ": ";

/**
 * Класс отправки сообщений пользователям
 * Class NoticeSender
 */
class NoticeSender
{
    private $perMin = 20;
    private $count = 0;

    private $mailers = [];
    private $smser = null;
    private $monitoringEmail = null;

    /**
     * Sender constructor.
     */
    public function __construct()
    {
        $this->smser = new Smser();

        $this->monitoringEmail = isset(Yii::$app->params['monitoring_email']) ? Yii::$app->params['monitoring_email'] : null;

        $this->run();
    }

    /**
     * Запуск отправки сообщений
     */
    private function run()
    {
        do {
            if ($this->count++) {
                sleep(60 / $this->perMin);
            }

            $notices = LkNotice::find()->limit(20)->orderBy(['id' => SORT_ASC]);

            /** @var LkNotice $notice */
            foreach ($notices->each() as $notice) {

                Yii::info("Message to send \n" . var_export($notice->getAttributes(), true));

                try {
                    $this->processNotice($notice);
                } catch (Exception $e) {
                    Yii::error($e);
                }

                $notice->delete();
            }

            $m = (60 - ($this->count * (60 / $this->perMin)));
            echo ".";
        } while ($m > 0);
    }

    /**
     * Отправка одного сообщения
     *
     * @param LkNotice $notice
     */
    private function processNotice(LkNotice $notice)
    {
        switch ($notice->type) {

            case LkNotice::TYPE_EMAIL: {
                $result = $this->getMailer($notice->lang)->send($notice->data, $notice->subject, $notice->message);

                Yii::info("Message send result\n" . var_dump($result, true));
                break;
            }

            case LkNotice::TYPE_PHONE: {
                $result = $this->smser->send($notice->data, $notice->message);

                Yii::info("Message send result\n" . var_dump($result, true));
                break;
            }
        }

        //monitoring
        if ($this->monitoringEmail) {
            $subject = $notice->subject;
            if ($notice->type == LkNotice::TYPE_PHONE) {
                $subject = "SMS";
            }
            $subject .= " - " . $notice->data;
            $this->getMailer()->send($this->monitoringEmail, $subject, $notice->message);
        }
    }

    /**
     * Возвращает объект для Email отправки по языку
     *
     * @param string $lang
     * @return \Mailer
     */
    private function getMailer($lang = \app\models\Language::LANGUAGE_DEFAULT)
    {
        if (isset($this->mailers[$lang])) {
            return $this->mailers[$lang];
        }

        $mailer = new \Mailer();
        $mailer->setFrom(\Yii::t('settings', 'notification_from_email', [], $lang));
        $mailer->setFromName(\Yii::t('settings', 'notification_from_name', [], $lang));

        $this->mailers[$lang] = $mailer;

        return $this->mailers[$lang];
    }

}

//run
new NoticeSender();