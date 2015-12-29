<?php
namespace app\classes\bill;

use Yii;

class VoipTrunkBiller extends Biller
{

    public function getTranslateFilename()
    {
        return 'biller-voip';
    }

    public function beforeProcess()
    {
    }

    protected function processConnecting()
    {
    }

    protected function processPeriodical()
    {
    }

    protected function processResource()
    {
        $prices = $this->calc();

        $template_data = [
            'service' => $this->usage->description,
        ];

        $this->addPackage(
            BillerPackageResource::create($this)
                ->setPrice($prices['price_orig'])
                ->setPeriodType(self::PERIOD_MONTH) // Need for localization
                ->setTemplate('voip_operator_trunk_orig')
                ->setTemplateData($template_data)
        );

        $this->addPackage(
            BillerPackageResource::create($this)
                ->setPrice($prices['price_term'])
                ->setPeriodType(self::PERIOD_MONTH) // Need for localization
                ->setTemplate('voip_operator_trunk_term')
                ->setTemplateData($template_data)
        );
    }

    private function calc()
    {
        $from = clone $this->billerActualFrom;
        $from->setTimezone(new \DateTimeZone('UTC'));
        $to = clone $this->billerActualTo;
        $to->setTimezone(new \DateTimeZone('UTC'));

        return
            Yii::$app->get('dbPg')
                ->createCommand('
                    SELECT
                        CAST(- SUM(CASE WHEN cost > 0 THEN cost ELSE 0 END) AS NUMERIC(10,2)) AS price_orig,
                        CAST(- SUM(CASE WHEN cost < 0 THEN cost ELSE 0 END) AS NUMERIC(10,2)) AS price_term
                    FROM calls_aggr.calls_aggr
                    WHERE
                        trunk_service_id = :trunk_service_id
                        AND aggr_time >= :from
                        AND aggr_time <= :to
                        AND ABS(cost) > 0.00001
                    GROUP BY trunk_service_id
                ', [
                    ':trunk_service_id' => $this->usage->id,
                    ':from' => $from->format('Y-m-d H:i:s'),
                    ':to' => $to->format('Y-m-d H:i:s'),
                ])->queryOne();
    }

}
