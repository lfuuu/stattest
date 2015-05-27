<?php

namespace app\controllers;

use Yii;
use DateTime;
use app\classes\Assert;
use app\classes\BaseController;
use app\models\ClientAccount;
use app\models\Emails;
use app\models\UsageExtra;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\UsageWelltime;
use app\classes\transfer\ExtraTransfer;
use app\classes\transfer\EmailTransfer;
use app\classes\transfer\WelltimeTransfer;

class TransferController extends BaseController
{
    public $activateDatesVariants = [
        'first day of next month midnight',
        'first day of next month +1 month midnight',
        'first day of next month +2 month midnight'
    ];

    public function actionIndex($client)
    {
        $clientAccount = ClientAccount::findOne($client);
        Assert::isObject($clientAccount);

        $accounts =
            ClientAccount::find()
                ->andWhere(['super_id' => $clientAccount->super_id])
                ->andWhere('id != :id', [':id' => $clientAccount->id])
                ->all();

        $services = [
            Emails::find(),
            UsageExtra::find(),
            UsageIpPorts::find(),
            UsageSms::find(),
            UsageVirtpbx::find(),
            UsageVoip::find(),
            UsageWelltime::find()
        ];

        $now = new DateTime();
        $usage = [];
        foreach ($services as $service) {
            $records =
                $service
                    ->andWhere(['client' => $clientAccount->client])
                    ->andWhere('actual_to < :date', [':date' => $now->format('Y-m-d')])
                    ->all();
            if (sizeof($records))
                for ($i=0, $s=sizeof($records); $i<$s; $i++)
                    $usage[$records[$i]->getServiceType()][] = $records[$i];
        }

        return $this->renderPartial('index', [
            'client'    => $clientAccount,
            'accounts'  => $accounts,
            'dates'     => $this->activateDatesVariants,
            'services'  => $usage
        ]);
    }

    public function actionProcess() {
        $data = Yii::$app->request->post('transfer');
        Assert::isArray($data);

        $src_account = ClientAccount::findOne($data['current_id']);
        Assert::isObject($src_account);

        $dst_account = ClientAccount::findOne($data['account']);
        Assert::isObject($dst_account);

        print_r($data);
        $result = [];

        if ($data['services'] == 'all') {
            $result = $this->getAllServices($src_account);
        }
        else if ($data['services'] == 'custom' && array_key_exists('custom', $data)) {
            $services = [
                Emails::find(),
                UsageExtra::find(),
                UsageIpPorts::find(),
                UsageSms::find(),
                UsageVirtpbx::find(),
                UsageVoip::find(),
                UsageWelltime::find()
            ];

            foreach ($services as $service) {
                $records =
                    $service
                        ->andWhere(['client' => $src_account->client])
                        ->andWhere(['id' => (array) $data['custom']])
                        ->all();
                if (sizeof($records))
                    $result += $records;
            }


        }

        for ($i=0, $s=sizeof($result); $i<$s; $i++) {
            $object = $result[$i];

            switch (true) {
                case $object instanceof Emails: {
                    print 'E-mail';
                    break;
                }
                case $object instanceof UsageExtra: {
                    print 'Extra';
                    ExtraTransfer::process($object, $dst_account, $data['actual_from']);
                    break;
                }
                case $object instanceof UsageWelltime: {
                    print 'Welltime';
                    break;
                }
            }
            print '<br />';
        }
    }

    private function getAllServices($client) {
        $result = [];
        $services = [
            Emails::find(),
            UsageExtra::find(),
            UsageIpPorts::find(),
            UsageSms::find(),
            UsageVirtpbx::find(),
            UsageVoip::find(),
            UsageWelltime::find()
        ];

        foreach ($services as $service) {
            $records = $service->andWhere(['client' => $client->client])->all();
            if (sizeof($records))
                $result += $records;
        }

        return $result;
    }

}