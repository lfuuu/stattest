<?php

namespace app\modules\uu\models\traits;

use app\classes\Html;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use Yii;
use yii\helpers\Url;

trait AccountTariffLinkTrait
{
    /**
     * @param bool $isWithAccount
     * @return string
     */
    public function getName($isWithAccount = true)
    {
        $names = [];

        if ($isWithAccount) {
            $names[] = $this->clientAccount->client;
        }

        if ($this->service_type_id == ServiceType::ID_VOIP && $this->voip_number) {

            // телефония
            $names[] = Yii::t('uu', 'Number {number}', ['number' => $this->voip_number]);

        } elseif ($this->service_type_id == ServiceType::ID_VOIP_PACKAGE_CALLS) {

            // пакет телефонии. Номер взять от телефонии
            /** @var AccountTariff $prevAccountTariff */
            $prevAccountTariff = $this->prevAccountTariff;
            if ($prevAccountTariff->voip_number) {
                $names[] = Yii::t('uu', 'Number {number}', ['number' => $prevAccountTariff->voip_number]);
            }
        }

        /** @var TariffPeriod $tariffPeriod */
        $tariffPeriod = $this->tariffPeriod;
        $names[] = $tariffPeriod ? $tariffPeriod->getName() : Yii::t('common', 'Switched off');

        return implode('. ', $names);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUrl()
    {
        return Url::to(['/uu/account-tariff/edit', 'id' => $this->id]);
    }

    /**
     * Вернуть html: имя + ссылка на услугу
     *
     * @param bool $isWithAccount
     * @return string
     */
    public function getAccountTariffLink($isWithAccount = true)
    {
        return $this->getLink($isWithAccount, false);
    }

    /**
     * Вернуть html: имя + ссылка на тариф
     *
     * @param bool $isWithAccount
     * @return string
     */
    public function getTariffPeriodLink($isWithAccount = true)
    {
        return $this->getLink($isWithAccount, true);
    }

    /**
     * Вернуть html: имя + ссылка на тариф
     *
     * @param bool $isWithAccount
     * @param bool $isTariffPeriodLink
     * @return string
     */
    public function getLink($isWithAccount = true, $isTariffPeriodLink = false)
    {
        /** @var TariffPeriod $tariffPeriod */
        $tariffPeriod = $this->tariffPeriod;
        return $tariffPeriod ?
            Html::a(
                Html::encode($this->getName($isWithAccount)),
                $isTariffPeriodLink ? $tariffPeriod->getUrl() : $this->getUrl()
            ) :
            Yii::t('common', 'Switched off');
    }

    /**
     * @param int $serviceTypeId
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public static function getUrlNew($serviceTypeId)
    {
        return Url::to(['/uu/account-tariff/new', 'serviceTypeId' => $serviceTypeId]);
    }
}