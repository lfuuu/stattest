<?php

namespace app\modules\sorm\commands;

use app\exceptions\ModelValidationException;
use app\modules\sorm\models\pg\Address;
use yii\console\Controller;

class AddressRecognitionController extends Controller
{
    /**
     * Распознование адресов для SORM
     *
     * @return void
     * @throws ModelValidationException
     */
    public function actionIndex($isReset = 0)
    {
        $token = getenv('DADATA_TOKEN');
        $secret = getenv('DADATA_SECRET');

        if (!$token || !$secret) {
            throw new \Exception('Empty auth parameters');
        }

        $dadata = new \Dadata\DadataClient($token, $secret);

        /** @var Address $address */
        foreach (Address::find()->where(['state' => 'added'])->all() as $address) {

            if (!$address->address) {
                $address->state = 'need_check';
                $address->save();
                continue;
            }

            if (stripos($address->address, 'а/я') !== false) {
                $address->is_struct = false;
                $address->state = 'need_check';
                $address->save();
                continue;
            }


            $result = $dadata->clean("address", $address->address);
            if ($result) {
                $address->json = json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $address->save();
            }

//            continue;
//            $result = json_decode($address->json, true);

            print_r($result);

            $d = [
                'post_code' => $result['postal_code'],
                'country' => $result['country'],
                'district_type' => $result['district_type'],
                'district' => $result['district'],
                'region_type' => $result['region_type'],
                'region' => $result['region'],
                'city_type' => $result['city_type'] ?? $result['settlement_type'] ?? $result['region_type'],
                'city' => $result['city'] ?? $result['settlement'] ?? $result['region'],
                'street_type' => $result['street_type'],
                'street' => $result['street'],
                'house' => $result['house'],
                'housing' => $result['block'],
                'flat_type' => $result['flat_type'],
                'flat' => $result['flat'],
                'unparsed_parts' => $result['unparsed_parts'],
                'state' => 'checked',
            ];

            if ($result['unparsed_parts'] || !$result['postal_code'] || !$d['street'] || !$d['house']) {
                $d['state'] = 'need_check';
            }

            $address->setAttributes($d, false);
            if (!$address->save()) {
                throw new ModelValidationException($address);
            }

            print_r($d);
        }

    }
}
