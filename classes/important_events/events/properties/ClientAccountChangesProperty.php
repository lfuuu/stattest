<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;
use app\models\ClientAccount;
use app\models\HistoryChanges;
use app\models\important_events\ImportantEvents;

class ClientAccountChangesProperty extends UnknownProperty implements PropertyInterface
{

    private $clientAccountId = 0;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $this->clientAccountId = $this->setPropertyName('client_id')->getPropertyValue();
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $clientAccount = new ClientAccount;
        $labels = $clientAccount->attributeLabels();
        $history =
            HistoryChanges::find()
                ->where(['model' => $clientAccount->formName()])
                ->andWhere(['model_id' => $this->clientAccountId])
                ->orderBy('created_at DESC')
                ->one();

        $changes = '';
        $current = json_decode($history->data_json);
        $previous = json_decode($history->prev_data_json);

        foreach ($current as $field => $value) {
            $changes .=
                Html::beginTag('tr') .
                    Html::tag('td', $field . (isset($labels[$field]) ? ': ' . $labels[$field] : '' )) .
                    Html::tag('td', $previous->{$field}) .
                    Html::tag('td', $value) .
                Html::endTag('tr');
        }

        if (empty($changes)) {
            return $changes;
        }

        $changes =
            Html::beginTag('div', ['class' => 'important-events table-of-changes']) .
                Html::beginTag('table', ['width' => '100%', 'class' => 'table table-bordered']) .
                        Html::beginTag('tr') .
                        Html::tag('th', 'Поле') .
                        Html::tag('th', 'Значение "До"') .
                        Html::tag('th', 'Значение "После"') .
                    Html::endTag('tr') .
                    $changes .
                Html::endTag('table') .
            Html::endTag('div');

        return $changes;
    }

}