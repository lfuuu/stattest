<?php

namespace app\classes\excel;

use Yii;
use DateTime;
use DateTimeZone;
use yii\base\Component;

abstract class Excel extends Component
{

    /** @var \PHPExcel $document */
    public $document;
    private $writerType = [
        'type' => 'Excel5',
        'fileExt' => '.xls',
    ];

    /**
     * @param $filename
     * @return bool
     * @throws \PHPExcel_Reader_Exception
     */
    public function openFile($filename)
    {
        if (preg_match('/\.csv$/', $filename)) {
            $reader = \PHPExcel_IOFactory::createReader('CSV');
        }
        elseif (preg_match('/\.xls$/', $filename)) {
            $reader = \PHPExcel_IOFactory::createReader('Excel5');
        }
        elseif (preg_match('/\.xlsx$/', $filename)) {
            $reader = \PHPExcel_IOFactory::createReader('Excel2007');
        }
        else {
            return false;
        }

        $this->document = $reader->load($filename);
        /*
        $worksheet = $this->document->getActiveSheet();
        if (!$worksheet) {
            throw new \Exception('Excel: Can`t get active sheet into "' . $filename . '"');
        }
        */
    }

    /**
     * @param string $filename
     * @throws \Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \yii\base\ExitException
     * @throws \yii\web\HttpException
     */
    public function download($filename = '')
    {
        if (!($this->document instanceof \PHPExcel)) {
            throw new \Exception('Excel: Document not existing');
        }

        $documentDate = new DateTime('now', new DateTimeZone('UTC'));

        $writer = \PHPExcel_IOFactory::createWriter($this->document, $this->writerType['type']);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        Yii::$app->response->sendContentAsFile(
            $content,
            $filename . '_' . $documentDate->format('dmYHi') . $this->writerType['fileExt']
        );
        Yii::$app->end();
    }

    public function prepare()
    {

    }

}