<?php
namespace app\dao;

use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\ClientDocument;
use app\models\InvoiceSettings;
use app\models\Organization;
use yii\db\Query;
use Yii;

/**
 * Class ClientContractDao
 */
class ClientContractDao extends Singleton
{

    /**
     * Получить транковые контракты с типом контракта в скобках
     *
     * @param array $params
     * @param bool $isWithEmpty
     *
     * @return string[]
     */
    public static function getListWithType(array $params = [], $isWithEmpty = false)
    {
        $query = (new Query)
            ->select(
                [
                    'name' => "COALESCE(st.contract_number || ' (' || cct.name || ')', st.contract_number)",
                    'id' => 'st.contract_id',
                ]
            )
            ->from('billing.service_trunk AS st')
            ->leftJoin('stat.client_contract_type AS cct', 'cct.id = st.contract_type_id')
            ->orderBy('name DESC');

        if (isset($params['serverIds']) && $params['serverIds']) {
            $query->andWhere(['st.server_id' => $params['serverIds']]);
        }

        if (isset($params['serviceTrunkIds']) && $params['serviceTrunkIds']) {
            $query->andWhere(['st.id' => $params['serviceTrunkIds']]);
        }

        if (isset($params['trunkIds']) && $params['trunkIds']) {
            $query->andWhere(['st.trunk_id' => $params['trunkIds']]);
        }

        $list = $query->indexBy('id')->column(Yii::$app->dbPgSlave);

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }

    /**
     * @param ClientContract $contract
     * @param \DateTime|null $date
     * @return null|\app\models\ClientDocument
     */
    public function getContractInfo(ClientContract $contract, \DateTime $date = null)
    {
        if (!$date) {
            $date = new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT));
        }

        $contractDoc = ClientDocument::find()
            ->active()
            ->contract()
            ->andWhere(['contract_id' => $contract->id])
            ->andWhere(['<=', 'contract_date', $date->format(DateTimeZoneHelper::DATETIME_FORMAT)])
            ->last();

        if (!$contractDoc) {
            $contractDoc = ClientDocument::find()
                ->contract()
                ->andWhere(['contract_id' => $contract->id])
                ->andWhere(['<=', 'contract_date', $date->format(DateTimeZoneHelper::DATETIME_FORMAT)])
                ->last();
        }

        return $contractDoc;
    }

    /**
     * Обновить эффективную ставку НДС во всех договорах
     *
     * @param bool $isWithTrace
     * @return array
     */
    public function resetAllEffectiveVATRate($isWithTrace = true)
    {
        $countAll = $countSet = $countFromOrganization = $iteration = 0;

        $contractQuery = ClientContract::find();

        echo PHP_EOL . date("r") . PHP_EOL . PHP_EOL;

        $transaction = Yii::$app->db->beginTransaction();

        /** @var ClientContract $contract */
        foreach ($contractQuery->each() as $contract) {
            $info = $contract->resetEffectiveVATRate($isWithTrace);
            $countAll += $info['countAll'];
            $countSet += $info['countSet'];
            $countFromOrganization += $info['countFromOrganization'];

            if ($iteration++ % 100 == 0) {
                echo "\r" . date("r") . ": iteration: " .$iteration . "; all: " . $countAll . "; set: " . $countSet . "; from organization: " . $countFromOrganization;
            }

            if ($iteration % 100 == 0) {
                $transaction->commit();
                $transaction = Yii::$app->db->beginTransaction();
            }
        }

        $transaction->commit();

        return [
            'countAll' => $countAll,
            'countSet' => $countSet,
            'countFromOrganization' => $countFromOrganization
        ];

    }

    /**
     * Устанавливаем эффективную ставку НДС на подчиненные ЛС
     *
     * @param ClientContract $contract
     * @param bool $isWithTrace
     * @return array
     * @throws ModelValidationException
     */
    public function resetEffectiveVATRate(ClientContract $contract, $isWithTrace = true)
    {
        $countAll = $countSet = $countFromOrganization = 0;

        $vatRate = $this->getEffectiveVATRate($contract, $isOrganizationValue);

        if (!$isWithTrace) {
            $contract->detachBehaviors();
            $contract->isHistoryVersioning = false;
        }

        foreach ($contract->accounts as $account) {

            $countAll++;

            if ($account->effective_vat_rate == $vatRate) {
                continue;
            }

            $account->effective_vat_rate = $vatRate;

            if (!$isWithTrace) {
                $account->detachBehaviors();
                $account->isHistoryVersioning = false;
            }

            if (!$account->save()) {
                throw new ModelValidationException($account);
            }

            $countSet++;

            if ($isOrganizationValue) {
                $countFromOrganization++;
            }
        }

        return [
            'countAll' => $countAll,
            'countSet' => $countSet,
            'countFromOrganization' => $countFromOrganization
        ];
    }

    /**
     * Расчитывает эффективную ставку НДС для данного договора
     *
     * @param ClientContract $contract
     * @param bool $isOrganizationValue
     * @return int
     */
    public function getEffectiveVATRate(ClientContract $contract, &$isOrganizationValue)
    {
        static $cash = [];

        if (!$cash) {
            /** @var InvoiceSettings $settings */
            foreach (InvoiceSettings::find()->all() as $settings) {
                $cash[$settings->doer_organization_id][$settings->customer_country_code ?: 'any'][$settings->vat_apply_scheme] = $settings->vat_rate;
            }
        }

        $isOrganizationValue = false;
        $organizationId = $contract->organization_id;
        $countryId = $contract->contragent->country_id;

        $countrySettings = null;
        if (isset($cash[$organizationId][$countryId])) { // настройки компания+страна
            $countrySettings = $cash[$organizationId][$countryId];
        } elseif (isset($cash[$organizationId]['any'])) { // настройки компания+любая страна
            $countrySettings = $cash[$organizationId]['any'];
        } else {
            Yii::warning('[contract_vat_not_found] Не найдена эффективная ставка НДС для договора ' . $contract->id . '. Нет настроек страны для организации id:' . $organizationId);
            $isOrganizationValue = true;
            return $this->getOrganizationVATRateByContract($contract);
        }

        if ($contract->contragent->tax_regime == ClientContragent::TAX_REGTIME_YCH_VAT0 && isset($countrySettings[InvoiceSettings::VAT_SCHEME_NONVAT])) {
            return $countrySettings[InvoiceSettings::VAT_SCHEME_NONVAT];
        } elseif ($contract->contragent->tax_regime == ClientContragent::TAX_REGTIME_OCH_VAT18 && isset($countrySettings[InvoiceSettings::VAT_SCHEME_VAT])) {
            return $countrySettings[InvoiceSettings::VAT_SCHEME_VAT];
        } elseif (isset($countrySettings[InvoiceSettings::VAT_SCHEME_ANY])) {
            return $countrySettings[InvoiceSettings::VAT_SCHEME_ANY];
        }

        Yii::warning('[contract_vat_not_found] Не найдена эффективная ставка НДС для договора ' . $contract->id . '. Нет настроек по режиму.');
        $isOrganizationValue = true;
        return $this->getOrganizationVATRateByContract($contract);
    }

    /**
     * Получение ставки НДС в организации по договору
     *
     * @param ClientContract $contract
     * @return int
     */
    public function getOrganizationVATRateByContract(ClientContract $contract)
    {
        static $cash = [];

        if (!array_key_exists($contract->organization_id, $cash)) {
            $organization = Organization::find()->byId($contract->organization_id)->actual()->one();

            $cash[$contract->organization_id] = $organization ? $organization->vat_rate : null;
        }

        if ($cash[$contract->organization_id] === null) {
            throw new \LogicException(sprintf('[contract_organization_not_found] Не найдена организация (%d) в договоре %d', $contract->organization_id, $contract->id));
        }

        return $cash[$contract->organization_id];
    }
}
