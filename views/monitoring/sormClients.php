<?php
/**
 * Мониторинг: СОРМ: Клиенты
 *
 * @var app\classes\BaseView $this
 * @var SormClientFilter $filterModel
 */

use app\classes\BillContract;
use app\classes\grid\column\universal\RegionColumn;
use app\classes\grid\GridView;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\models\filter\SormClientFilter;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'СОРМ: Клиенты') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => $this->title, 'url' => '/monitoring/sorm-clients/'],
    ],
]) ?>

<?php

$baseView = $this;
$columns = [
    [
        'attribute' => 'id',
        'value' => function (ClientAccount $account) {
            return $account->getLink();
        },
        'format' => 'raw',
    ],
    [
        'label' => $filterModel->getAttributeLabel('name_full'),
        'format' => 'raw',
        'value' => function (ClientAccount $account) {
            $returnStr = $account->contragent->name_full;

            if ($account->contragent->legal_type == ClientContragent::PERSON_TYPE) {
                $person = $account->contragent->person;

                $returnStr .= '<br>' . $person->last_name . '*' . $person->first_name . '*' . $person->middle_name . '<br>' .
                    $person->birthday . '*' . $person->passport_serial . '*' . $person->passport_number . '<br>' .
                    $person->passport_issued . '<br>' . $person->passport_date_issued;
            }

            return $returnStr;
        }
    ],
    [
        'label' => $filterModel->getAttributeLabel('account_manager'),
        'value' => 'clientContractModel.accountManagerName'
    ],
    [
        'attribute' => 'voip_credit_limit_day',
    ],
    [
        'label' => $filterModel->getAttributeLabel('legal_type'),
        'value' => function (ClientAccount $account) {
            return $account->contragent->legal_type;
        }
    ],
    [
        'label' => $filterModel->getAttributeLabel('contract_no'),
        'value' => function (ClientAccount $account) {
            $contractInfo = BillContract::getLastContract($account->contract_id, null, false);
            if ($contractInfo && isset($contractInfo['no'])) {
                return $contractInfo['no'];
            }
            return $account->contract_id;
        }
    ],
    [
        'label' => $filterModel->getAttributeLabel('inn'),
        'contentOptions' => [
            'class' => 'sorm-client-cell',
            'data' => ['field' => 'inn']
        ],

        'value' => function (ClientAccount $account) {
            return $account->contragent->inn;
        }
    ],
    [
        'label' => $filterModel->getAttributeLabel('bik'),
        'contentOptions' => [
            'class' => 'sorm-client-cell',
            'data' => ['field' => 'bik']
        ],
        'value' => 'bik',
    ], [
        'label' => $filterModel->getAttributeLabel('bank'),
        'value' => 'bank_name',
    ],
    [
        'attribute' => 'pay_acc',
        'contentOptions' => [
            'class' => 'sorm-client-cell',
            'data' => ['field' => 'pay_acc']
        ],
        'value' => 'pay_acc',
    ],
    [
        'label' => $filterModel->getAttributeLabel('contact_fio'),
        'contentOptions' => [
            'class' => 'sorm-client-cell',
            'data' => ['field' => 'contact_fio']
        ],

        'value' => function (ClientAccount $account) use ($filterModel) {
            /** @var \app\models\ClientContact $contact */
            $contact = SormClientFilter::getContactByAccount($account);

            if ($contact) {
                return $contact->comment;
            }

            return '';
        }
    ],
    [
        'label' => $filterModel->getAttributeLabel('contact_phone'),
        'contentOptions' => [
            'class' => 'sorm-client-cell',
            'data' => ['field' => 'contact_phone']
        ],

        'value' => function (ClientAccount $account) use ($contactWhere, $contactOrderBy) {
            /** @var \app\models\ClientContact $contact */
            $contact = SormClientFilter::getContactByAccount($account);

            if ($contact) {
                return $contact->data;
            }

            return '';
        }
    ],
    [
        'label' => $filterModel->getAttributeLabel('address_jur'),
        'contentOptions' => [
            'class' => 'sorm-client-cell',
            'data' => ['field' => 'address_jur'],
        ],
        'value' => function (ClientAccount $account) {
            /** @var \app\models\ClientContact $contact */
            return
                $account->contragent->legal_type == ClientContragent::PERSON_TYPE
                    ? $account->contragent->person->registration_address
                    : $account->contragent->address_jur;
        }
    ],
];

$filterColumns = [
    [
        'attribute' => 'region_id',
        'class' => RegionColumn::class,
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
    'beforeHeader' => [ // фильтры вне грида
        'columns' => $filterColumns,
    ],
]);
