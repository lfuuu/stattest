<?php

namespace app\controllers\api\internal;

use Yii;
use Exception;
use app\classes\ApiInternalController;
use app\models\ClientSuper;
use app\models\ClientContragent;
use app\models\ClientAccount;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\api\internal\PartnerNotFoundException;
use app\forms\client\ClientCreateExternalForm;

class ClientController extends ApiInternalController
{
    /**
     * @SWG\Definition(
     *   definition="contract",
     *   type="object",
     *   required={"id","number","accounts"},
     *   @SWG\Property(
     *     property="id",
     *     type="integer",
     *     description="Идентификатор договора"
     *   ),
     *   @SWG\Property(
     *     property="number",
     *     type="string",
     *     description="Номер договора"
     *   ),
     *   @SWG\Property(
     *     property="accounts",
     *     type="array",
     *     description="Массив идентификаторов лицевых счетов",
     *     @SWG\Items(
     *       type="integer"
     *     )
     *   )
     * ),
     * @SWG\Definition(
     *   definition="contragent",
     *   type="object",
     *   required={"id","name","contracts"},
     *   @SWG\Property(
     *     property="id",
     *     type="integer",
     *     description="Идентификатор контрагента"
     *   ),
     *   @SWG\Property(
     *     property="name",
     *     type="string",
     *     description="Название контрагента"
     *   ),
     *   @SWG\Property(
     *     property="contracts",
     *     type="array",
     *     description="Массив договоров",
     *     @SWG\Items(
     *       ref="#/definitions/contract"
     *     )
     *   )
     * ),
     * @SWG\Definition(
     *   definition="client",
     *   type="object",
     *   required={"id","name","contragents"},
     *   @SWG\Property(
     *     property="id",
     *     type="integer",
     *     description="Идентификатор супер-клиента"
     *   ),
     *   @SWG\Property(
     *     property="name",
     *     type="string",
     *     description="Название супер-клиента"
     *   ),
     *   @SWG\Property(
     *     property="contragents",
     *     type="array",
     *     description="Массив контрагентов",
     *     @SWG\Items(
     *       ref="#/definitions/contragent"
     *     )
     *   )
     * ),
     * @SWG\Post(
     *   tags={"Работа с клиентами"},
     *   path="/internal/client/",
     *   summary="Получение данных по клиенту",
     *   operationId="Получение данных по клиенту",
     *   @SWG\Parameter(name="client_id",type="integer",description="идентификатор супер-клиента",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="данные о клиенте",
     *     @SWG\Schema(
     *       ref="#/definitions/client"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
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

    /**
     * @SWG\Definition(
     *   definition="get-client-struct-applications",
     *   type="object",
     *   required={"id","name","enabled"},
     *   @SWG\Property(
     *     property="id",
     *     type="integer",
     *     description="Идентификатор"
     *   ),
     *   @SWG\Property(
     *     property="name",
     *     type="string",
     *     description="Название"
     *   ),
     *   @SWG\Property(
     *     property="enabled",
     *     type="boolean",
     *     description="Признак отключенного"
     *   )
     * ),
     * @SWG\Definition(
     *   definition="get-client-struct-account",
     *   type="object",
     *   required={"id","is_partner","is_disabled","applications"},
     *   @SWG\Property(
     *     property="id",
     *     type="integer",
     *     description="Идентификатор ЛС"
     *   ),
     *   @SWG\Property(
     *     property="is_partner",
     *     type="boolean",
     *     description="Признак партнера"
     *   ),
     *   @SWG\Property(
     *     property="is_disabled",
     *     type="boolean",
     *     description="Признак отключенного"
     *   ),
     *   @SWG\Property(
     *     property="applications",
     *     type="array",
     *     description="Массив приложений",
     *     @SWG\Items(
     *       ref="#/definitions/get-client-struct-applications"
     *     )
     *   )
     * ),
     * @SWG\Definition(
     *   definition="get-client-struct-contragent",
     *   type="object",
     *   required={"id","name","country","accounts"},
     *   @SWG\Property(
     *     property="id",
     *     type="integer",
     *     description="Идентификатор контрагента"
     *   ),
     *   @SWG\Property(
     *     property="name",
     *     type="string",
     *     description="Имя контагента"
     *   ),
     *   @SWG\Property(
     *     property="country",
     *     type="string",
     *     description="Страна"
     *   ),
     *   @SWG\Property(
     *     property="accounts",
     *     type="array",
     *     description="Массив ЛС",
     *     @SWG\Items(
     *       ref="#/definitions/get-client-struct-account"
     *     )
     *   )
     * ),
     * @SWG\Definition(
     *   definition="get-client-struct",
     *   type="object",
     *   required={"id","name","timezone","contragents"},
     *   @SWG\Property(
     *     property="id",
     *     type="integer",
     *     description="Идентификатор супер-клиента"
     *   ),
     *   @SWG\Property(
     *     property="name",
     *     type="string",
     *     description="Название супер-клиента"
     *   ),
     *   @SWG\Property(
     *     property="timezone",
     *     type="string",
     *     description="Таймзона"
     *   ),
     *   @SWG\Property(
     *     property="contragents",
     *     type="array",
     *     description="Массив контрагентов",
     *     @SWG\Items(
     *       ref="#/definitions/get-client-struct-contragent"
     *     )
     *   )
     * ),
     * @SWG\Get(
     *   tags={"Работа с клиентами"},
     *   path="/internal/client/get-client-struct/",
     *   summary="Получение цельной структуры клиента",
     *   operationId="Получение цельной структуры клиента",
     *   @SWG\Parameter(name="id",type="integer",description="идентификатор супер-клиента",in="formData"),
     *   @SWG\Parameter(name="name",type="string",description="имя супер-клиента",in="formData"),
     *   @SWG\Parameter(name="contragent_id",type="integer",description="идентификатор контрагента",in="formData"),
     *   @SWG\Parameter(name="contragent_name",type="string",description="имя контрагента",in="formData"),
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор ЛС",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="данные о клиенте",
     *     @SWG\Schema(
     *       ref="#/definitions/get-client-struct"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */

    public function actionGetClientStruct($id=null, $name=null, $contragent_id=null, $contragent_name=null, $account_id=null)
    {
	$ids = [];
	if (empty($id)) {
            if (empty($name)) {
		if (empty($contragent_id)) {
		    if (empty($contragent_name)) {
			if (empty($account_id)) {
			    return false;
			} else {
			    $account = ClientAccount::findOne($account_id);
			    $ids []= $account->super_id;
			}
		    } else {
		        $ids = array_keys(ClientContragent::find()->andWhere([ 'name' => $contragent_name])->indexBy('super_id')->all());
		    }
		} else {
		    $contragent = ClientContragent::findOne($contragent_id);
		    $ids []= $contragent->super_id;
		}
	    } else {
		$ids = array_keys(ClientSuper::find()->andWhere([ 'name' => $name])->indexBy('id')->all());
	    }
	} else {
	    $ids []= $id;
	}
	
	$fullResult = [];
	foreach ($ids as $id) {
	    $response = Yii::$app->db->createCommand("select * from view_client_struct_ro WHERE id=".$id.";")->queryAll();
	    $flip_by = [ 'id' => [ 'name', 'timezone', 'contragents_id' => [ 'contragents_name', 'contragents_country_id', 'contragents_accounts_id' => [ 'contragents_accounts_is_partner' ] ] ] ];
	    $preresult = [];
	    $result = [];
	    //map
	    foreach ($response as $minimal_row) {
	        $result['id'] = (int)$minimal_row['id'];
	        $result['timezone'] = $minimal_row['timezone'];
	        $result['name'] = $minimal_row['name'];
	        $preresult['contragents'][$minimal_row['contragents_id']]['id'] = (int)$minimal_row['contragents_id'];
	        $preresult['contragents'][$minimal_row['contragents_id']]['name'] = $minimal_row['contragents_name'];
	        $preresult['contragents'][$minimal_row['contragents_id']]['country'] = $minimal_row['contragents_country'];
	        $preresult['contragents'][$minimal_row['contragents_id']]['accounts'][$minimal_row['contragents_accounts_id']]['is_disabled'] = (bool)$minimal_row['is_disabled'];
	        $preresult['contragents'][$minimal_row['contragents_id']]['accounts'][$minimal_row['contragents_accounts_id']]['is_partner'] = (bool)$minimal_row['contragents_accounts_is_partner'];
	        $preresult['contragents'][$minimal_row['contragents_id']]['accounts'][$minimal_row['contragents_accounts_id']]['id'] = (int)$minimal_row['contragents_accounts_id'];
		if (empty($result['contragents'][$minimal_row['contragents_id']]['accounts'][$minimal_row['contragents_accounts_id']]['applications'])) {
		    $clientIdent = $minimal_row['clientIdent'];
		    $applications = Yii::$app->db->createCommand("select id,name, enabled from view_platforma_services_ro WHERE client='".$clientIdent."';")->queryAll();
		    $preresult['contragents'][$minimal_row['contragents_id']]['accounts'][$minimal_row['contragents_accounts_id']]['applications'] = $applications;
		}
	    }
	    //clean
	    $result['contragents'] = [];
	    foreach ($preresult['contragents'] as $precontragent) {
	        $contragent = $precontragent;
	        $contragent['accounts'] = [];
	        foreach ($precontragent['accounts'] as $account) {
		    $contragent['accounts'] []= $account;
		}
		$result['contragents'] []= $contragent;
	    }
	    $fullResult []= $result;
	}
	return $result;
    }

    /**
     * @SWG\Post(
     *   tags={"Работа с клиентами"},
     *   path="/internal/client/create/",
     *   summary="Создание клиента",
     *   operationId="Создание клиента",
     *   @SWG\Parameter(name="company",type="string",description="название клиента",in="formData",default="Клиент без названия"),
     *   @SWG\Parameter(name="address",type="string",description="адрес",in="formData"),
     *   @SWG\Parameter(name="partner_id",type="integer",description="идентификатор партнёра",in="formData"),
     *   @SWG\Parameter(name="contact_phone",type="string",description="контактный телефон",in="formData"),
     *   @SWG\Parameter(name="official_phone",type="string",description="телефон организации",in="formData"),
     *   @SWG\Parameter(name="fio",type="string",description="Ф.И.О. контактного лица",in="formData"),
     *   @SWG\Parameter(name="fax",type="string",description="факс",in="formData"),
     *   @SWG\Parameter(name="email",type="string",description="email",in="formData",required=true),
     *   @SWG\Parameter(name="comment",type="string",description="комментарий к заказу",in="formData"),
     *   @SWG\Parameter(name="timezone",type="string",description="Таймзона лицевого счета",in="formData",default="Europe/Moscow"),
     *   @SWG\Parameter(name="country_id",type="integer",description="Код страны подключения (ISO)",in="formData",default="643"),
     *   @SWG\Parameter(name="site_name",type="string",description="С какого сайта пришел клиент",in="formData"),
     *   @SWG\Parameter(name="vats_tariff_id",type="integer",description="идентификатор тарифа для ВАТС",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="данные о созданном клиенте",
     *     @SWG\Schema(
     *       type="object",
     *       required={"id","name","contragents"},
     *       @SWG\Property(
     *         property="client_id",
     *         type="integer",
     *         description="Идентификатор супер-клиента"
     *       ),
     *       @SWG\Property(
     *         property="is_created",
     *         type="boolean",
     *         description="Создан ли клиент"
     *       )
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
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
