<?php

namespace app\modules\notifier\forms;

use app\classes\Form;
use app\classes\validators\ArrayValidator;
use app\exceptions\ModelValidationException;
use app\models\Country;
use app\models\important_events\ImportantEventsNames;
use app\modules\notifier\models\Schemes;
use Exception;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * @property array|null $formData
 */
class SchemesForm extends Form
{

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
     * @return Country[]
     */
    public function getAvailableCountries()
    {
        return Country::getList();
    }

    /**
     * @return ActiveDataProvider
     */
    public function getAvailableEvents()
    {
        return new ActiveDataProvider([
            'query' => ImportantEventsNames::find(),
            'sort' => false,
            'pagination' => false,
        ]);
    }

    /**
     * @param int $countryCode
     * @return NotificationScheme[]
     */
    public function getCountryNotificationScheme($countryCode)
    {
        return
            Schemes::find()
                ->where(['country_code' => $countryCode])
                ->all();
    }

    /**
     * @param array $scheme
     * @param string $notificationType
     * @param string $eventCode
     * @return bool
     */
    public function isNotificationUsed(array $scheme, $notificationType, $eventCode)
    {
        return (bool)array_filter($scheme,
            function ($row) use ($notificationType, $eventCode) {
                return $row->event === $eventCode && $row->{$notificationType};
            }
        );
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
