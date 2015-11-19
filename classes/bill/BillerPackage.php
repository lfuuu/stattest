<?php
namespace app\classes\bill;

use app\models\ClientAccount;
use app\models\Transaction;
use app\models\usages\UsageInterface;
use Yii;
use DateTime;

abstract class BillerPackage
{
    /** @var Biller */
    protected $biller;
    /** @var ClientAccount */
    protected $clientAccount;
    /** @var UsageInterface */
    protected $usage;

    /** @var DateTime */
    protected $currentFrom;
    /** @var DateTime */
    protected $currentTo;
    /** @var DateTime */
    protected $currentAlignFrom;
    /** @var DateTime */
    protected $currentAlignTo;

    protected $name;
    protected $template = '{name}';
    protected $amount = 1;
    protected $price;

    /** @var DateTime */
    protected $billerPeriodFrom;
    /** @var DateTime */
    protected $billerPeriodTo;
    /** @var DateTime */
    protected $billerActualFrom;
    /** @var DateTime */
    protected $billerActualTo;
    /** @var DateTime */
    protected $usageActualFrom;
    /** @var DateTime */
    protected $usageActualTo;

    protected $periodType;
    protected $templateData;

    public static function create(Biller $biller)
    {
        $package = new static();
        $package->biller = $biller;
        $package->clientAccount = $biller->clientAccount;
        $package->usage = $biller->usage;
        $package->billerPeriodFrom = $biller->billerPeriodFrom;
        $package->billerPeriodTo = $biller->billerPeriodTo;
        $package->billerActualFrom = $biller->billerActualFrom;
        $package->billerActualTo = $biller->billerActualTo;
        $package->usageActualFrom = $biller->usageActualFrom;
        $package->usageActualTo = $biller->usageActualTo;
        return $package;
    }

    public function setActualPeriod(DateTime $from, DateTime $to)
    {
        $this->billerActualFrom = $from;
        $this->billerActualTo = $to;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = round($amount, 6);
        return $this;
    }

    public function setPrice($price)
    {
        $this->price = round($price, 4);
        return $this;
    }

    public function setPeriodType($periodType)
    {
        $this->periodType = $periodType;
        return $this;
    }

    public function setTemplateData($data)
    {
        $this->templateData = $data;
        return $this;
    }

    abstract public function createTransaction();

    protected function calculateSum(Transaction $transaction, DateTime $periodFrom = null, DateTime $periodTo = null)
    {
        $transaction->tax_rate = $this->clientAccount->getTaxRate();

        list($transaction->sum, $transaction->sum_without_tax, $transaction->sum_tax) =
            $this->clientAccount->convertSum($transaction->price * $transaction->amount, $transaction->tax_rate);

        if ($transaction->is_partial_write_off && $periodFrom && $periodTo) {
            $date = $this->biller->billerDate->getTimestamp() + 86400 - 1;
            $periodFrom = $periodFrom->getTimestamp();
            $periodTo = $periodTo->getTimestamp();

            if ($date < $periodFrom) {
                $transaction->effective_amount = 0;
                $transaction->effective_sum = 0;
            } elseif ($date > $periodTo) {
                $transaction->effective_sum = -$transaction->sum;
            } elseif ($periodTo > $periodFrom) {
                $all = $periodTo - $periodFrom + 1;
                $done = $date - $periodFrom;

                $transaction->effective_amount = round($transaction->amount * $done / $all, 6);
                $transaction->effective_sum = -round($transaction->sum * $done / $all, 2);
            } else {
                $transaction->effective_amount = $transaction->amount;
                $transaction->effective_sum = -$transaction->sum;
            }
        } else {
            $transaction->effective_amount = $transaction->amount;
            $transaction->effective_sum = -$transaction->sum;
        }
    }

    protected function processTemplate(DateTime $from, DateTime $to, $template = null)
    {
        if ($template === null) {
            $template = $this->template;
        }

        $i18n_params = $this->templateData;

        if ($this->periodType) {
            $from2 = new DateTime();
            $from2->setDate($from->format('Y'), $from->format('m'), $from->format('d'));
            $from2->setTime($from->format('H'), $from->format('i'), $from->format('s'));

            $to2 = new DateTime();
            $to2->setDate($to->format('Y'), $to->format('m'), $to->format('d'));
            $to2->setTime($to->format('H'), $to->format('i'), $to->format('s'));

            $i18n_params['date_range'] = Yii::t(
                'biller',
                $this->biller->getPeriodTemplate($period, $from2, $to2),
                [
                    $from2->getTimestamp(),
                    $to2->getTimestamp()
                ],
                $this->clientAccount->contragent->country->lang
            );
            
        }

        $name = Yii::t(
            $this->biller->getTranslateFilename(),
            $template,
            $i18n_params,
            $this->clientAccount->contragent->country->lang
        );

        return $name;
    }

}
