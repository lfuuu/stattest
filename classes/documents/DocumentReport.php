<?php

namespace app\classes\documents;

use Yii;
use yii\base\Object;
use app\classes\Company;
use app\classes\BillQRCode;
use app\models\Bill;

abstract class DocumentReport extends Object
{

    const TEMPLATE_PATH = '@app/views/documents/';

    const BILL_DOC_TYPE = 'bill';

    /**
     * @var Bill
     */
    public $bill;
    public $sendEmail;
    public $lines = [];

    public
        $sum,
        $sum_without_tax,
        $sum_with_tax,
        $sum_discount = 0;

    protected $optionsPDF = ' --quiet -L 10 -R 10 -T 10 -B 10';

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return Company::getProperty($this->bill->clientAccount->firma, $this->bill->bill_date);
    }

    /**
     * @return string
     */
    public function getCompanyDetails()
    {
        return Company::getDetail($this->bill->clientAccount->firma, $this->bill->bill_date);
    }

    /**
     * @return array
     */
    public function getCompanyResidents()
    {
        return Company::setResidents($this->bill->clientAccount->firma, $this->bill->bill_date);
    }

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return self::TEMPLATE_PATH . $this->getLanguage() . '/' . $this->getDocType() . '_' . mb_strtolower($this->getCurrency(), 'UTF-8');
    }

    /**
     * @return string
     */
    public function getHeaderTemplate()
    {
        return self::TEMPLATE_PATH . $this->getLanguage() . '/header_base';
    }

    /**
     * @return array
     */
    public function getQrCode()
    {
        $result = BillQRCode::getNo($this->bill->bill_no);
        return $result['bill'];
    }

    /**
     * @return $this
     */
    public function setBill(Bill $bill = null)
    {
        $this->bill = $bill;
        return $this;
    }

    /**
     * @return $this
     */
    public function setSendEmail($sendEmail)
    {
        $this->sendEmail = $sendEmail;
        return $this;
    }

    /**
     * @return $this
     */
    public function prepare()
    {
        return $this
            ->fetchLines()
            ->filterLines()
            ->postFilterLines()
            ->calculateSummary();
    }

    public function render($inline_img = true)
    {
        return Yii::$app->view->renderFile($this->getTemplateFile() . '.php', [
            'document' => $this,
            'inline_img' => $inline_img
        ]);
    }

    /*wkhtmltopdf*/
    public function renderAsPDF()
    {
        $options = $this->optionsPDF;
        // Может быть когда-нибудь доп. параметры уйдут в свой класс
        /*
        switch ($this->getDocType()) {
            case 'upd':
                $options .= ' --orientation Landscape ';
                break;
            case 'invoice':
                $options .= ' --orientation Landscape ';
                break;
        }
        */

        ob_start();
        echo $this->render();
        $content = ob_get_contents();
        ob_end_clean();

        $file_name = '/tmp/' . time() . Yii::$app->user->id;
        $file_html = $file_name . '.html';
        $file_pdf = $file_name . '.pdf';

        file_put_contents($file_name . '.html', $content);

        passthru("/usr/bin/wkhtmltopdf $options $file_html $file_pdf");
        $pdf = file_get_contents($file_pdf);
        unlink($file_html);
        unlink($file_pdf);

        Header('Content-Type: application/pdf');
        ob_clean();
        flush();
        echo $pdf;
        exit;
    }

    /**
     * @return $this
     */
    protected function fetchLines()
    {
        $this->lines =
            Yii::$app->db->createCommand('
                select
					l.*,
					if(g.nds is null, 18, g.nds) as nds,
					g.art as art,
					g.num_id as num_id,
					g.store as in_store,
                    if(l.service="usage_extra",
						(select
							okvd_code
						from
							usage_extra u, tarifs_extra t
						where
							u.id = l.id_service and
							t.id = tarif_id
						),
						if (l.type = "good",
							(select
								okei
							FROM
								g_unit
							WHERE
								id = g.unit_id
							), "")
					) okvd_code
				from newbill_lines l
				left join g_goods g on (l.item_id = g.id)
				left join g_unit as gu ON g.unit_id = gu.id
				where l.bill_no=:billNo
				order by sort
            ', [
                ':billNo' => $this->bill->bill_no,
            ])->queryAll();

        return $this;
    }

    /**
     * @return $this
     */
    protected function filterLines()
    {
        $filtered_lines = [];

        foreach ($this->lines as $line) {
            if (!(int) $line['sum']) continue;

            $filtered_lines[] = $line;
        }

        $this->lines = $filtered_lines;

        return $this;
    }

    /**
     * @return $this
     */
    protected function postFilterLines()
    {
        return $this;
    }

    /**
     * @return $this
     */
    protected function calculateSummary()
    {
        $this->sum              =
        $this->sum_without_tax  =
        $this->sum_with_tax     =
        $this->sum_discount     = 0;

        foreach ($this->lines as $line) {
            $this->sum              += $line['sum'];
            $this->sum_without_tax  += $line['sum_without_tax'];
            $this->sum_with_tax     += $line['sum_tax'];
            $this->sum_discount     += $line['discount_auto'] + $line['discount_set'];
        }

        return $this;
    }

    abstract public function getLanguage();

    abstract public function getCurrency();

    abstract public function getDocType();

    abstract public function getName();

}