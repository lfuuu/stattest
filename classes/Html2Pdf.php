<?php

namespace app\classes;


use yii\base\Object;

/**
 * Класс генерации PDF из HTML
 *
 * Class Html2Pdf
 *
 * @property string html
 * @property string pdf
 *
 * @package app\classes
 */
class Html2Pdf extends Object
{
    private
        $html = '',
        $pdf = ''
    ;

    private $execTool = "/usr/bin/wkhtmltopdf";

    public function __construct(array $config = [])
    {
        if (!is_file($this->execTool)) {
            throw new \Exception("wkhtmltopdf not found");
        }

        if (!is_executable($this->execTool)) {
            throw new \Exception("wkhtmltopdf not executable");
        }

        parent::__construct($config);
    }

    public function setHtml($html = "")
    {
        $this->html = $html;
        $this->pdf = "";
    }

    public function getPdf()
    {
        if ($this->pdf) {
            return $this->pdf;
        }

        $this->pdf = $this->generatePdf();

        return $this->pdf;
    }

    /**
     * Генерация PDF'а
     * @return string
     */
    private function generatePdf()
    {
        $tmp_dir = sys_get_temp_dir();
        $file_html = tempnam($tmp_dir, "html_to_pdf");
        $file_pdf = tempnam($tmp_dir, "html_to_pdf");

        unlink($file_html);
        unlink($file_pdf);

        $file_html = $file_html . ".html";
        $file_pdf = $file_pdf . ".pdf";

        /** wkhtmltopdf */
        $options = ' --quiet -L 15 -R 15 -T 15 -B 15';

        file_put_contents($file_html, $this->html);

        exec($this->execTool . " " . $options . " " . $file_html . " " . $file_pdf);

        $content = file_get_contents($file_pdf);

        unlink($file_html);
        unlink($file_pdf);

        return $content;
    }
}