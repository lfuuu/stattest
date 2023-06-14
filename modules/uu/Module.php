<?php

namespace app\modules\uu;

use app\classes\Navigation;
use app\classes\NavigationBlock;
use app\models\mtt_raw\MttRaw;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use Yii;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * Универсальные услуги
 */
class Module extends \yii\base\Module
{
    const EVENT_ADD_DEFAULT_PACKAGES = 'uu_add_default_packages';
    const EVENT_VOIP_CALLS = 'uu_voip_calls';
    const EVENT_VOIP_BUNDLE = 'uu_voip_bundle';
    const EVENT_CLOSE_ALL_PACKAGE = 'uu_close_all_package';
    const EVENT_VPBX = 'uu_vpbx';
    const EVENT_CALL_CHAT_CREATE = 'uu_chat_create';
    const EVENT_CALL_CHAT_REMOVE = 'uu_chat_remove';
    const EVENT_RESOURCE_VOIP = 'uu_resource_voip';
    const EVENT_RESOURCE_VPBX = 'uu_resource_vpbx';
    const EVENT_RESOURCE_VPS = 'uu_resource_vps';
    const EVENT_RESOURCE_VPS_LICENCE = 'uu_resource_licence';
    const EVENT_RECALC_ACCOUNT = 'uu_recalc_account';
    const EVENT_RECALC_BALANCE = 'uu_recalc_balance';
    const EVENT_VPS_SYNC = 'uu_vps_sync';
    const EVENT_VPS_LICENSE = 'uu_vps_license';
    const EVENT_ADD_LIGHT = 'uu_add_light';
    const EVENT_DELETE_LIGHT = 'uu_delete_light';
    const EVENT_CLOSE_LIGHT = 'uu_close_light';
    const EVENT_UU_ANONCE_TARIFF = 'uu_anonce_tariff'; // Анонсировать изменение УУ-тарифа
    const EVENT_UU_ANONCE = 'uu_anonce'; // Анонсировать изменение УУ-услуги
    const EVENT_UU_SWITCHED_ON = 'uu_switched_on'; // УУ-услуга включена
    const EVENT_UU_SWITCHED_OFF = 'uu_switched_off'; // УУ-услуга выключена
    const EVENT_UU_UPDATE = 'uu_update'; // УУ-услуга сменила тариф
    const EVENT_SIPTRUNK_SYNC = 'uu_siptrunk_sync';
    const EVENT_CHAT_BOT_CREATE = 'uu_chat_bot_create';
    const EVENT_CHAT_BOT_REMOVE = 'uu_chat_bot_remove';
    const EVENT_ROBOCALL_INTERNAL_CREATE = 'uu_robocall_create';
    const EVENT_ROBOCALL_INTERNAL_UPDATE = 'uu_robocall_update';
    const EVENT_ROBOCALL_INTERNAL_REMOVE = 'uu_robocall_remove';

    const LOG_CATEGORY = 'uu';
    const LOG_CATEGORY_API = 'uu_api';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\uu\controllers';

    /**
     * Для корректного запуска из консоли
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\uu\commands';
        }
    }

    /**
     * @param Navigation $navigation
     * @throws \yii\base\InvalidParamException
     */
    public function getNavigation(Navigation $navigation)
    {
        // тарифы
        $block = NavigationBlock::create();
        $block->setTitle(Yii::t('tariff', 'Universal tariffs'));

        // услуги
        $block2 = NavigationBlock::create();
        $block2->setTitle(Yii::t('tariff', 'Universal services'));

        // тарифы и услуги
        $serviceTypes = ServiceType::find()
            ->orderBy(new Expression('COALESCE(parent_id, id), id'))// чтобы пакеты были рядом с базовым тарифом
            ->all();
        foreach ($serviceTypes as $serviceType) {

            $block->addItem($serviceType->name, Url::to([
                '/uu/tariff',
                'serviceTypeId' => $serviceType->id,
            ]), ['tarifs.read']);

            $block2->addItem($serviceType->name, Url::to([
                '/uu/account-tariff',
                'serviceTypeId' => $serviceType->id,
                // 'AccountTariffFilter[tariff_period_id]' => TariffPeriod::IS_SET,
            ]), ['tarifs.read']);

        }

        $navigation->addBlock($block);
        $navigation->addBlock($block2);

        // Универсальный тарификатор
        $navigation->addBlock(
            NavigationBlock::create()
                ->setTitle(Yii::t('tariff', 'Universal tarifficator'))
                ->addItem(Yii::t('tariff', 'Tariff statuses'), ['/uu/tariff-status'], ['tarifs.read'])
                ->addItem(Yii::t('tariff', 'Tariff VPS'), ['/uu/tariff-vm'], ['tarifs.read'])
                ->addItem(Yii::t('tariff', 'Service types'), ['/uu/service-type'], ['tarifs.read'])
                ->addItem(Yii::t('tariff', 'Setup tariffication'), ['/uu/account-log/setup'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Period tariffication'), ['/uu/account-log/period'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Resource tariffication'), ['/uu/account-log/resource'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Min resource tariffication'), ['/uu/account-log/min'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Account entries'), ['/uu/account-entry'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Bills'), ['/uu/bill'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Invoice'), ['/uu/invoice/view'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Balance'), ['/uu/balance/view'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Clear UU-calls'), ['/uu/resource/clear'], ['services_voip.edit'])
                ->addItem(Yii::t('tariff', 'Monitoring'), [
                    '/uu/monitor',
                    'AccountLogMonitorFilter[tariff_period_id]' => TariffPeriod::IS_SET,
                    'AccountLogMonitorFilter[month]' => date('Y-m'),
                ],
                    ['newaccounts_balance.read'])
                ->addItem('СМС', ['/uu/mtt?MttRawFilter[serviceid][0]=' . MttRaw::SERVICE_ID_SMS_IN_HOMENETWORK . '&MttRawFilter[serviceid][1]=' . MttRaw::SERVICE_ID_SMS_IN_ROAMING], ['services_voip.r'])
                ->addItem('Моб. Интернет', ['/uu/mtt?MttRawFilter[serviceid][0]=' . MttRaw::SERVICE_ID_INET_IN_HOMENETWORK . '&MttRawFilter[serviceid][1]=' . MttRaw::SERVICE_ID_INET_IN_ROAMING], ['services_voip.r'])
                ->addItem('CallTracking', ['/callTracking/log'], ['services_voip.r'])
                ->addItem('Метки тарифов', ['/uu/tags/'], ['dictionary.read'])
                ->addItem('Услуги. Уровни цен. Статусы.', ['/uu/service-folder/'], ['dictionary.read'])
        );
    }
}
