<?php

namespace app\modules\sorm\controllers;

use app\classes\Assert;
use app\exceptions\ModelValidationException;
use app\exceptions\web\NotImplementedHttpException;
use app\models\Bik;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\ClientContragent;
use app\models\EquipmentUser;
use app\models\filter\SormClientFilter;
use app\models\UsageVoip;
use app\modules\sorm\filters\StateServiceVoipFilter;
use app\modules\uu\models\AccountTariff;
use Yii;
use app\classes\BaseController;

class NumbersController extends BaseController
{
    /**
     * Номера
     *
     * @return string
     */
    public function actionNumbersB2c()
    {
        $params = Yii::$app->request->get() ?: ['StateServiceVoipFilter' => ['is_b2c' => 1]];

        $filter = new StateServiceVoipFilter();
        $filter->load($params);

        return $this->render('numbers', [
            'filter' => $filter,
        ]);
    }
    /**
     * Номера
     *
     * @return string
     */
    public function actionNumbers()
    {
        $params = Yii::$app->request->get() ?: ['StateServiceVoipFilter' => ['is_b2c' => 0]];

        $filter = new StateServiceVoipFilter();
        $filter->load($params);

        return $this->render('numbers', [
            'filter' => $filter,
        ]);
    }

    /**
     * СОРМ: Клиенты
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionSormClients()
    {
        $filterModel = new SormClientFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('sormClients', [
            'filterModel' => $filterModel,
        ]);
    }

    public function actionSormClientsSave($accountId, $field, $value)
    {
        Assert::isInArray($field, ['inn', 'bik', 'bank', 'pay_acc', 'contact_fio', 'contact_phone', 'address_jur', 'address_post', 'equ']);

        $account = ClientAccount::findOne(['id' => $accountId]);

        Assert::isObject($account);

        $value = trim(strip_tags($value));
        Assert::isNotEmpty($value);

        switch ($field) {
            case 'inn':
                $contragent = $account->contragent;

                if ($contragent->inn == $value) {
                    break;
                }

                $contragent->inn = $value;
                if (!$contragent->save()) {
                    throw new ModelValidationException($contragent);
                }
                break;

            case 'bik':
                $bikModel = Bik::findOne(['bik' => $value]);

                Assert::isObject($bikModel);

                $account->bik = $bikModel->bik;
                $account->bank_name = $bikModel->bank_name;
                $account->corr_acc = $bikModel->corr_acc;
                $account->bank_city = $bikModel->bank_city;

                if (!$account->save()) {
                    throw new ModelValidationException($account);
                }
                break;

            case 'bank':
                $account->bank_name = $value;
                if (!$account->save()) {
                    throw new ModelValidationException($account);
                }
                break;

            case 'pay_acc':
                $account->pay_acc = $value;
                if (!$account->save()) {
                    throw new ModelValidationException($account);
                }
                break;

            case 'contact_fio':
            case 'contact_phone':
                $contact = SormClientFilter::getContactByAccount($account);

                if (!$contact) {
                    $contact = new ClientContact();
                    $contact->client_id = $account->id;
                    $contact->type = ClientContact::TYPE_PHONE;
                }

                $contact->{$field == 'contact_fio' ? 'comment' : 'data'} = $value;

                if (!$contact->save()) {
                    throw new ModelValidationException($contact);
                }
                break;

            case 'address_jur':
                $contragent = $account->contragent;

                if ($contragent->legal_type == ClientContragent::PERSON_TYPE) {
                    $model = $contragent->person;
                    $model->registration_address = $value;
                    if ($model->registration_address == $value) {
                        break;
                    }
                } else {
                    $model = $contragent;
                    $model->address_jur = $value;

                    if ($model->address_jur == $value) {
                        break;
                    }
                }

                if (!$model->save()) {
                    throw new ModelValidationException($model);
                }
                break;

            case 'address_post':
                if ($account->address_post == $value) {
                    break;
                }

                $account->address_post = $value;

                if (!$account->save()) {
                    throw new ModelValidationException($account);
                }
                break;

            case 'equ':
                $equ = new EquipmentUser();
                $equ->isStrongCheck = false;
                $equ->client_account_id = $account->id;
                $equ->full_name = $value;
                $equ->birth_date = '2000-01-01';
                if (!$equ->save()) {
                    throw new ModelValidationException($equ);
                }
                break;
            default:
                throw new NotImplementedHttpException();
        }

        return 'ok';
    }


    /**
     * Сохраняет адрес в соответствующей модели
     *
     * @throws ModelValidationException
     * @throws \yii\base\Exception
     */
    public function actionSaveAddress()
    {
        throw new NotImplementedHttpException('Адреса редактируются только в ЛК');

        $id = Yii::$app->request->post('id');
        $address = Yii::$app->request->post('text');

        Assert::isNotEmpty($id);
        Assert::isNotEmpty($address);

        if ($id >= AccountTariff::DELTA) {
            $accountTariff = AccountTariff::findOne(['id' => $id]);
            Assert::isObject($accountTariff);

            $accountTariff->device_address = $address;
        } else {
            $accountTariff = UsageVoip::findOne(['id' => $id]);
            Assert::isObject($accountTariff);

            $accountTariff->address = $address;
        }

        if (!$accountTariff->save()) {
            throw new ModelValidationException($accountTariff);
        }
    }

}