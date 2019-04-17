<?php

namespace app\dao\reports;

use app\dao\reports\ReportUsage\Config;
use app\dao\reports\ReportUsage\Processor;
use app\models\billing\Hub;
use DateTimePickerValues;
use Yii;

class ReportUsage
{
    protected $attributes = [];
    protected $config;

    /**
     * ReportUsage constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        if (!$this->config) {
            throw new \LogicException('Usage report config not set!');
        }
    }

    /**
     * @param $fixClient
     * @param bool $isWithProfit
     * @return static
     * @throws \Exception
     */
    public static function createFromRequest($fixClient, $isWithProfit = false)
    {
        $attributes = [];

        if ($isWithProfit) {
            $fixClient = get_param_protected('client', $fixClient);
        }

        $config = new ReportUsage\Config($fixClient);
        $config->validateUsages();
        if ($config->isValid()) {

            $account = $config->account;

            $dateFrom = new DateTimePickerValues('date_from', 'first', false);
            $dateTo = new DateTimePickerValues('date_to', 'next first', false);

            $attributes['date_from'] = $dateFrom->getValueFormatted();
            $attributes['date_to'] = $dateTo->getValueFormatted();

//            $destination = get_param_raw('destination', 'all');
            $direction = get_param_raw('direction', 'both');

            $marketPlace = null;
            if ($isWithProfit) {
                $marketPlace = get_param_protected('marketPlace', Hub::MARKET_PLACE_ID_RUSSIA);
                $attributes['marketPlace'] = $marketPlace;
                $attributes['marketPlaces'] = Hub::$marketPlaces;
            }
            $attributes['phone'] = $phone = get_param_protected('phone', null);
            $attributes['phones'] = $config->getUsagesOptions();
            $attributes['phone_default'] = $phone ? : array_keys($config->getUsagesOptions())[0];

//            $attributes['destination'] = $destination;
//            $attributes['destinations'] = $config->getDestinations();

            $attributes['direction'] = $direction;
            $attributes['directions'] = $config->getDirections();

            $attributes['type'] = $type = get_param_protected('type', 'day');
            $attributes['types'] = $config->getTypes();
            $attributes['paidOnly'] = $paidOnly = get_param_integer('paidOnly', 0);
            $attributes['timezone'] = $timeZone = get_param_raw('timezone', $account->timezone_name);
            $attributes['timezones'] = $config->getTimezones();

            $attributes['stats'] = [];
            $attributes['action'] = get_param_protected('action');
            $attributes['priceIncludeVat'] = $account->price_include_vat;

            $config->from = $dateFrom->getValue();
            $config->to = $dateTo->getValue();

            $config->isWithProfit = $isWithProfit;
            $config->phone = $phone;
            $config->type = $type;
            $config->paidOnly = $paidOnly;
//            $config->destination = $destination;
            $config->direction = $direction;
            $config->isShowMax = false;
            $config->packages = [];
            $config->timeZone = $timeZone;
            $config->marketPlace = $marketPlace;
        }

        $report = new static($config);
        $report->setAttributes($attributes);

        return $report;
    }

    /**
     * Статистика по телефонии
     *
     * @throws \Exception
     */
    public function fetchStatistic()
    {
        $this->attributes['stats'] = [];
        if ($this->config->validate()) {
            $processor = Processor::createFromConfig($this->config);
            $this->attributes['stats'] = $processor->getResult();
        }

        $this->processErrors();
    }

    /**
     *
     */
    protected function processErrors()
    {
        foreach ($this->config->errors as $error) {
            if (Yii::$app instanceof \app\classes\WebApplication) {
                Yii::$app->session->addFlash($error['type'], $error['text']);
            } else {
                echo $error['text'] . "\n";
            }
        }
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return static
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    // Moved to UsageVoipPackage::getStatistic
    //public function getUsageVoipPackagesStatistic($usageId, $packageId = 0)
}