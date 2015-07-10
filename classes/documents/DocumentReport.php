<?php

namespace app\classes\documents;

use app\models\Organization;
use Yii;
use yii\base\Object;
use yii\db\ActiveRecord;
use app\classes\BillQRCode;
use app\classes\Html2Mhtml;
use app\models\Bill;

/**
 * @property Organization organization
 * @property
 */
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
     * @return ActiveRecord
     */
    public function getOrganization()
    {
        return $this->bill->clientAccount->getOrganization($this->bill->bill_date);
    }

    /**
     * @return array
     */
    public function getPayer()
    {
        return
            $this->bill->clientAccount->loadVersionOnDate(
                $this->bill->bill_date
            );
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

        $file_name = '/tmp/' . time() . Yii::$app->user->id;
        $file_html = $file_name . '.html';
        $file_pdf = $file_name . '.pdf';

        file_put_contents($file_name . '.html', $this->render());

        passthru("/usr/bin/wkhtmltopdf $options $file_html $file_pdf");

        Yii::$app->response->sendFile($file_pdf, basename($file_pdf), [
            'mimeType' => 'application/pdf'
        ]);

        unlink($file_html);
        unlink($file_pdf);

        Yii::$app->end();
    }

    public function renderAsMhtml()
    {
        $result = (new Html2Mhtml)
            ->addContents(
                'index.html',
                $this->render($inline_img = false),
                function($content) {
                    return preg_replace('#font\-size:\s?[0-7]{1,2}\%#', 'font-size:8pt', $content);
                }
            )
            ->addImages(function($image_src) {
                $file_path = '';
                $file_name = '';

                if (preg_match('#\/[a-z]+(?![\.a-z]+)\?.+?#i', $image_src)) {
                    $file_name = 'host_img_' . mt_rand(0, 50);
                    $file_path = Yii::$app->request->hostInfo . $image_src;
                }
                else if (strpos($image_src, 'http:\/\/') === false) {
                    $file_path = Yii::$app->basePath . '/web' . $image_src;
                    $file_name = basename($image_src);
                }

                return [$file_name, $file_path];
            })
            ->getFile();

        Yii::$app->response->sendContentAsFile($result, time() . Yii::$app->user->id . '.doc');
        Yii::$app->end();
    }

    /**
     * @return $this
     */
    protected function fetchLines()
    {
        $tax_rate = $this->bill->clientAccount->getTaxRate($original = true);

        $this->lines =
            Yii::$app->db->createCommand('
                select
                    l.*,
                    if(g.nds is null, ' . $tax_rate . ', g.nds) as nds,
                    g.art as art,
                    g.num_id as num_id,
                    g.store as in_store,
                    if(l.service="usage_extra",
                      (
                        select
                          okvd_code
                        from
                          usage_extra u, tarifs_extra t
                        where
                            u.id = l.id_service and
                            t.id = tarif_id
                      ),
                      if (l.type = "good",
                        (
                          select
                            okei
                          from
                            g_unit
                          where
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
            if (!$line['sum']) continue;

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