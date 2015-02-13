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
                            86400 +
                            LEAST(UNIX_TIMESTAMP(actual_to),UNIX_TIMESTAMP(:to)) -
                            GREATEST(UNIX_TIMESTAMP(actual_from),UNIX_TIMESTAMP(:from))
                        )/
                        (86400 + UNIX_TIMESTAMP(:to)-UNIX_TIMESTAMP(:from)) as dt
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
            $template = 'Поддержка почтового ящика {name} ' . $this->getPeriodTemplate(self::PERIOD_YEAR);

            if ($this->clientAccount->bill_rename1 == 'yes') {
                $template .= $this->getContractInfo();
            }

            $this->addPackage(
                BillerPackagePeriodical::create($this)
                    ->setPeriodType(self::PERIOD_MONTH)
                    ->setIsAlign(true)
                    ->setIsPartialWriteOff(false)
                    ->setTemplate($template)
                    ->setPrice($price)
                    ->setName($this->usage->local_part . '@' . $this->usage->domain)
            );
        }

        return $this;
    }

}