<?php

namespace app\modules\notifier\forms;

use app\classes\Form;
use app\classes\validators\ArrayValidator;
use app\exceptions\ModelValidationException;
use app\models\Country;
use app\modules\notifier\components\decorators\WhiteListDecorator;
use app\modules\notifier\components\traits\FormExceptionTrait;
use app\modules\notifier\models\Schemes;
use app\modules\notifier\Module as Notifier;
use Exception;
use Yii;

/**
 * @property array|null $formData
 */
class SchemesForm extends Form
{

    use FormExceptionTrait;

    public $formData;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['formData', ArrayValidator::className()],
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
     * @param int $countryCode
     * @return Schemes[]
     */
    public function getCountryNotificationScheme($countryCode)
    {
        return Schemes::find()
            ->where(['country_code' => $countryCode])
            ->all();
    }

    /**
     * @param array $scheme
     * @param string $notificationType
     * @param string $eventCode
     * @return int
     */
    public function isNotificationUsed(array $scheme, $notificationType, $eventCode)
    {
        foreach ($scheme as $row) {
            if ($row->event === $eventCode) {
                return (int)$row->{$notificationType};
            }
        }

        return 0;
    }

    /**
     * @return bool
     */
    public function load()
    {
        $this->formData = Yii::$app->request->post('formData');
        return $this->formData !== null;
    }

    /**
     * @return bool
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function save()
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            foreach ($this->formData as $countryCode => $countryData) {
                Schemes::deleteAll(['country_code' => $countryCode]);

                foreach ($countryData as $event => $notificationData) {
                    $record = new Schemes;
                    $record->country_code = $countryCode;
                    $record->event = $event;

                    $isNotSkipped = count($notificationData);

                    foreach ($notificationData as $notificationType => $flag) {
                        $record->{$notificationType} = $flag;
                        if (!$flag) {
                            $isNotSkipped--;
                        }
                    }

                    if ($isNotSkipped && !$record->save()) {
                        throw new ModelValidationException($record);
                    }
                }
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

}
