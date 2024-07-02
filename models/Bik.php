<?php

namespace app\models;

use app\classes\HandlerLogger;
use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use yii\base\InvalidConfigException;

/**
 * Class Bik
 *
 * @property string $bik
 * @property string $corr_acc
 * @property string $bank_name
 * @property string $bank_city
 * @property string $bank_address
 * @property string $bank
 * @property string $dadata
 */
class Bik extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'bik';
    }

    public static function updateDadata($bik)
    {
        $token = \Yii::$app->params['DADATA']['TOKEN'] ?? getenv('DADATA_TOKEN') ;
        $secret = \Yii::$app->params['DADATA']['SECRET'] ?? getenv('DADATA_SECRET');

        if (!$token || !$secret) {
            throw new InvalidConfigException('DaData Token or Secret not set');
        }

        $dadata = new \Dadata\DadataClient($token, $secret);

        $data = $dadata->findById('bank', $bik);

        $bikModel = Bik::findOne(['bik' => $bik]);

        if (!$data) {
            $bikWithDd = Bik::find()->where(['AND', ['bank_address' => $bikModel->bank_address], ['NOT', ['dadata' => null]], ['NOT', ['dadata' => false]]])->one();

            if ($bikWithDd) {
                $data = [['data' => [
                    'bic' => $bik,
                    'address' => $bikWithDd->dadata['data']['address'],
                    'data_from' => 'other_bik_with_address'
                ]]];
            } else {
                $result = $dadata->clean("address", $bikModel->bank_city . ', ' . $bikModel->bank_address);
                if ($result) {
                    $data = [['data' => [
                        'bic' => $bik,
                        'address' => $result,
                        'data_from' => 'by_address_clean',
                    ]]];
                }
            }
        }


        if ($data) {
            $_data = reset($data);
            HandlerLogger::me()->add(' - ' . ($_data['data_from'] ?? ' + '));
            $bikModel->dadata = $_data;
            if (!$bikModel->save()) {
                throw new ModelValidationException($bikModel);
            }
        } else {
            HandlerLogger::me()->add(' ---- ');
        }

    }
}
