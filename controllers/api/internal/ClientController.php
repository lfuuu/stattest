<?php

namespace app\controllers\api\internal;

use Yii;
use app\classes\ApiInternalController;
use app\models\ClientSuper;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\api\internal\PartnerNotFoundException;

class ClientController extends ApiInternalController
{
    public function actionIndex()
    {
        $requestData = $this->getRequestParams();

        $superId = isset($requestData['client_id']) ? $requestData['client_id'] : null;

        if (!$superId) {
            throw new BadRequestHttpException;
        }

        if ($superId && ($super = ClientSuper::findOne(['id' => $superId]))
        ) {

            $contragents = [];
            foreach ($super->contragents as $c) {
                $contracts = [];
                foreach ($c->contracts as $cc) {
                    $accounts = [];
                    foreach ($cc->accounts as $a) {
                        $accounts[] = $a->id;
                    }
                    $contracts[] = ['id' => $cc->id, 'number' => $cc->number, 'accounts' => $accounts];
                }

                $contragents[] = [
                    'id' => $c->id, 
                    'name' => $c->name, 
                    'contracts' => $contracts
                ];
            }

            $data = [
                'name' => $super->name, 
                'id' => $super->id, 
                'contragents' => $contragents
            ];

            return $data;
        } else {
            throw new BadRequestHttpException;
        }
    }
}
