<?php

namespace app\classes\grid\column\important_events\details;

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\classes\Html;
use app\models\important_events\ImportantEvents;
use app\models\HistoryChanges;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\ClientSuper;

abstract class ClientAccountColumn
{

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderNewAccountDetails($column)
    {
        return self::renderDetails($column);
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderAccountChangedDetails($column)
    {
        $result = self::renderDetails($column);

        $clientAccount = new ClientAccount;
        $labels = $clientAccount->attributeLabels();
        $history =
            HistoryChanges::find()
                ->where(['model' => $clientAccount->formName()])
                ->andWhere(['model_id' => $column->client_id])
                ->orderBy('created_at DESC')
                ->one();

        $changes = '';
        $current = json_decode($history->data_json);
        $previous = json_decode($history->prev_data_json);

        foreach ($current as $field => $value) {
            $changes .=
                Html::beginTag('tr') .
                    Html::tag('td', $labels[$field]) .
                    Html::tag('td', $previous->{$field}) .
                    Html::tag('td', $value) .
                Html::endTag('tr');
        }

        $changes =
            Html::beginTag('div', ['class' => 'important-events table-of-changes']) .
                Html::beginTag('table', ['width' => '100%', 'class' => 'table table-bordered']) .
                    Html::beginTag('tr') .
                        Html::tag('th', 'Поле').
                        Html::tag('th', 'Значение "До"').
                        Html::tag('th', 'Значение "После"').
                    Html::endTag('tr') .
                    $changes .
                Html::endTag('table') .
            Html::endTag('div');

        array_unshift($result, $changes);

        return $result;
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderExtendAccountContractDetails($column)
    {
        $result = self::renderDetails($column);
        $properties = ArrayHelper::map((array) $column->properties, 'property', 'value');

        if (
            isset($properties['contract_id'])
            &&
            ($clientContract = ClientContract::findOne($properties['contract_id'])) !== false
        ) {
            /** @var ClientContract $clientContract */
            $result[] =
                Html::tag('b', 'Договор: ') .
                Html::a(
                    'Договор №' . ($clientContract->number ? $clientContract->number : 'Без номера') .
                    '(' . $clientContract->organization->name . ')',
                    Url::toRoute(['/contract/edit', 'id' => $clientContract->id]),
                    ['target' => '_blank']
                );
        }

        return $result;
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderContractTransferDetails($column)
    {
        $result = self::renderDetails($column);
        $properties = ArrayHelper::map((array) $column->properties, 'property', 'value');

        if (
            isset($properties['contract_id'])
            &&
            ($clientContract = ClientContract::findOne($properties['contract_id'])) !== false
        ) {
            /** @var ClientContract $clientContract */
            $result[] =
                Html::tag('b', 'Договор: ') .
                Html::a(
                    'Договор №' . ($clientContract->number ? $clientContract->number : 'Без номера') .
                    '(' . $clientContract->organization->name . ')',
                    Url::toRoute(['/contract/edit', 'id' => $clientContract->id]),
                    ['target' => '_blank']
                );
        }

        if (
            isset($properties['to_contragent_id'])
            &&
            ($clientContragent = ClientContragent::findOne($properties['to_contragent_id'])) !== false
        ) {
            /** @var ClientContragent $clientContragent */
            $result[] =
                Html::tag('b', 'Контрагент: ') .
                Html::a(
                    $clientContragent->name,
                    Url::toRoute(['/contragent/edit', 'id' => $clientContragent->id]),
                    ['target' => '_blank']
                );
        }

        return $result;
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderAccountContractChangedDetails($column)
    {
        $result = self::renderDetails($column);

        $clientContract = new ClientContract;
        $labels = $clientContract->attributeLabels();
        $history =
            HistoryChanges::find()
                ->where(['model' => $clientContract->formName()])
                ->andWhere(['model_id' => $column->client_id])
                ->orderBy('created_at DESC')
                ->one();

        $changes = '';
        $current = json_decode($history->data_json);
        $previous = json_decode($history->prev_data_json);

        foreach ($current as $field => $value) {
            $changes .=
                Html::beginTag('tr') .
                    Html::tag('td', $labels[$field]) .
                    Html::tag('td', $previous->{$field}) .
                    Html::tag('td', $value) .
                Html::endTag('tr');
        }

        $changes =
            Html::beginTag('div', ['class' => 'important-events table-of-changes']) .
                Html::beginTag('table', ['width' => '100%', 'class' => 'table table-bordered']) .
                    Html::beginTag('tr') .
                        Html::tag('th', 'Поле').
                        Html::tag('th', 'Значение "До"').
                        Html::tag('th', 'Значение "После"').
                    Html::endTag('tr') .
                    $changes .
                Html::endTag('table') .
            Html::endTag('div');

        array_unshift($result, $changes);

        return $result;
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderTransferContragentDetails($column)
    {
        $result = self::renderDetails($column);
        $properties = ArrayHelper::map((array) $column->properties, 'property', 'value');

        if (
            isset($properties['contragent_id'])
            &&
            ($clientContragent = ClientContragent::findOne($properties['contragent_id'])) !== false
        ) {
            /** @var $clientContragent ClientContragent */
            $result[] =
                Html::tag('b', 'Контрагент: ') .
                Html::a(
                    $clientContragent->name,
                    Url::toRoute(['/contragent/edit', 'id' => $clientContragent->id]),
                    ['target' => '_blank']
                );
        }

        if (
            isset($properties['to_super_id'])
            &&
            ($clientSuper = ClientSuper::findOne($properties['to_super_id'])) !== false
        ) {
            /** @var ClientSuper $clientSuper */
            $result[] =
                Html::tag('b', 'Клиент: ') .
                $clientSuper->name;
        }

        return $result;
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    private static function renderDetails($column)
    {
        $result = [];
        $properties = ArrayHelper::map((array) $column->properties, 'property', 'value');

        if (
            $column->client_id
            &&
            ($value = DetailsHelper::renderClientAccount($column->client_id)) !== false
        ) {
            $result[] = $value;
        }

        if (
            isset($properties['user_id'])
            &&
            ($value = DetailsHelper::renderUser($properties['user_id'])) !== false
        ) {
            $result[] = $value;
        }

        return $result;
    }

}