<?php
namespace app\classes\bill;

use app\models\Emails;
use Yii;

class EmailBiller extends Biller
{
    private static $service_data = [];

    protected function processPeriodical()
    {
        if (!isset(self::$service_data[$this->clientAccount->id])) {

            $email =
                Emails::find()
                    ->andWhere(['client' => $this->clientAccount->client])
                    ->andWhere('actual_from <= :from', [':from' => $this->billerActualFrom->format('Y-m-d')])
                    ->andWhere('actual_to >= :to', [':to' => $this->billerActualTo->format('Y-m-d')])
                    ->one();

            $virtualMailServer =
                Yii::$app->db->createCommand("
                    select
                        MAX(
                            1 +
                            LEAST(actual_to,DATE(:to)) -
                            GREATEST(actual_from,DATE(:from))
                        )/
                        (1 + DATE(:to)-DATE(:from)) as dt
                    from usage_extra as U
                    inner join
                        tarifs_extra as T
                    on T.id = U.tarif_id
                    where
                        T.code = 'mailserver'
                    and U.actual_from <= :to
                    and U.client= :client",
                    [
                        ':client' => $this->clientAccount->client,
                        ':from' => $this->billerActualFrom->format('Y-m-d'),
                        ':to' => $this->billerActualTo->format('Y-m-d'),
                    ]
                )
                    ->queryScalar();

            if ($virtualMailServer > 0.08) {
                $virtualMailServer += 0.05;
            } else {
                $virtualMailServer = 0;
            }

            self::$service_data[$this->clientAccount->id] = [
                'has_server' => $virtualMailServer,
                'email_id' => $email->id
            ];
        }

        $p = self::$service_data[$this->clientAccount->id];
        if ($p['has_server'] > 1) {
            return $this;
        }

        $price = $this->clientAccount->currency == 'RUB' ? 27 : 1;
        if ($p['email_id'] == $this->usage->id) {
            $price = 0;
        }

        if ($price > 0) {

            $template  = 'email_service';
            $template_data = [
                'local_part' => $this->usage->local_part,
                'domain' => $this->usage->domain,
                'by_agreement' => ''
            ];

            if ($this->clientAccount->bill_rename1 == 'yes') {
                $template_data['by_agreement'] = $this->getContractInfo();
            }

            $this->addPackage(
                BillerPackagePeriodical::create($this)
                    ->setPeriodType(self::PERIOD_MONTH)
                    ->setIsAlign(true)
                    ->setIsPartialWriteOff(false)
                    ->setPrice($price)
                    ->setTemplate($template)
                    ->setTemplateData($template_data)
            );
        }

        return $this;
    }

}