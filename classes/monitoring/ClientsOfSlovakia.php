<?php

namespace app\classes\monitoring;

use app\models\ClientContragent;
use app\models\Country;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use app\classes\Html;
use app\models\ClientAccount;
use app\models\ClientContract;

class ClientsOfSlovakia extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'clients_of_slovakia';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Клиенты из Словакии';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            [
                'attribute' => 'id',
                'label' => 'ID договора',
                'width' => '100px',
            ],
            [
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->contragent->name ?: $data->contragent->name_full, ['/contragent/edit', 'id' => $data->contragent->id]);
                },
                'width' => '30%',
            ],
            [
                'label' => '№ Договор',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->number, ['/contract/edit', 'id' => $data->id]);
                },
                'width' => '30%',
            ],
            [
                'label' => 'Лицевые счета',
                'format' => 'raw',
                'value' => function ($data) {
                    $accounts = '';
                    foreach ($data->accounts as $clientAccount) {
                        $accounts[] = Html::a('Л/С ' . $clientAccount->id,
                            ['/client/view', 'id' => $clientAccount->id]);
                    }
                    return implode(', ', $accounts);
                },
                'width' => '*',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return new ArrayDataProvider([
            'allModels' =>
                ClientContract::find()
                    ->from(['contract' => ClientContract::tableName()])
                    ->leftJoin(['client' => ClientAccount::tableName()], 'client.contract_id = contract.id')
                    ->leftJoin(['contragent' => ClientContragent::tableName()], 'contragent.id = contract.contragent_id')
                    ->where(['contragent.country_id' => Country::SLOVAKIA])
                    ->groupBy('contract.id')
                    ->all(),
        ]);
    }

}