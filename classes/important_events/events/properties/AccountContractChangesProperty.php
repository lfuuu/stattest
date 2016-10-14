<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;
use app\models\ClientContract;
use app\models\HistoryChanges;
use app\models\important_events\ImportantEvents;

class AccountContractChangesProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_CONTRACT_CHANGES = 'contract.changes';

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
        return [
            self::PROPERTY_CONTRACT_CHANGES => '',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_CONTRACT_CHANGES => $this->getName(),
        ];
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
    public function getName()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $clientContract = new ClientContract;
        $labels = $clientContract->attributeLabels();
        $history =
            HistoryChanges::find()
                ->where(['model' => $clientContract->formName()])
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