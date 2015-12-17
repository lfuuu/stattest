<?php

namespace app\controllers\api\internal;

use Yii;
use Exception;
use app\classes\ApiInternalController;
use app\models\ClientSuper;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\api\internal\PartnerNotFoundException;
use app\forms\client\ClientCreateExternalForm;

class ClientController extends ApiInternalController
{
    public function actionIndex()
    {
        $superId = isset($this->requestData['client_id']) ? $this->requestData['client_id'] : null;

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

    public function actionCreate()
    {
        $form = new ClientCreateExternalForm;
        $form->setAttributes($this->requestData);

        if ($form->validate()) {
            if ($form->create()) {
                return [
                    'client_id' => $form->super_id,
                    'is_created' => $form->isCreated,
                    ];
            }
        } else {
            $fields = array_keys($form->errors);
            if ($fields[0] == 'partner_id') {
                throw new PartnerNotFoundException();
            } else {
                throw new Exception($form->errors[$fields[0]][0], 400);
            }
        }
    }
}
