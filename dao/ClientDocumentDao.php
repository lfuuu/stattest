<?php
namespace app\dao;

use app\models\ClientDocument;
use Yii;
use app\classes\Singleton;
use app\classes\Company;
use app\models\Contract;
use yii\base\Exception;

class ClientDocumentDao extends Singleton
{
    /**
     * @var ClientDocument
     */
    protected $model = null;

    public static $folders = [
        'mcn' => 'MCN',

        'mcntelefonija' => 'MCN Телефония',
        'mcninternet' => 'MCN Интернет',
        'mcndatacenter' => 'MCN Дата-центр',

        'interop' => 'Межоператорка',
        'partners' => 'Партнеры',
        'internetshop' => 'Интернет-магазин',

        'welltime' => 'WellTime',
        'arhiv' => 'Arhiv',
    ];

    protected function __construct($config)
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
        if($this->model === null)
            new Exception('Parent document does not exist');
    }

    public static function templateList($isWithType = false)
    {
        $R = array();
        foreach (glob(Yii::$app->params['STORE_PATH'] . 'contracts/template_*.html') as $s) {
            $t = str_replace(array('template_', '.html'), array('', ''), basename($s));

            list($group,) = explode('_', $t);

            if ($isWithType) {
                $R[$group][] = $t;
            } else {
                $R[$group][] = substr($t, strlen($group) + 1);
            }
        }

        foreach (static::$folders as $key => $folderName)
            $_R[$key] = isset($R[$key]) ? $R[$key] : array();

        if ($isWithType) {
            $R = ['contract' => [], 'blank' => [], 'agreement' => []];

            foreach ($_R as $folder => $rr) {
                foreach ($rr as $k => $r) {
                    $contract = Contract::findOne(['name' => $r]);

                    if ($contract) {
                        $type = $contract->type;
                    } else {
                        $type = 'contract';
                    }
                    list($group,) = explode('_', $r);
                    $R[$type][$folder][] = substr($r, strlen($group) + 1);
                }
            }
        } else {
            $R = $_R;
        }

        return $R;
    }

    public function delete()
    {
        return $this->deleteFile();
    }

    public function create()
    {
        $contractGroup = $this->model->group;
        $contractTemplate = $this->model->template;
        $group = static::$folders[$contractGroup];
        $content = $this->getTemplate('template_' . $group . "_" . $contractTemplate);
        $this->addContract($content);
        return $this->generateDefault();
    }

    public function setContent()
    {
        $content = $this->model->content;
        return $this->addContract($content);
    }

    public function getContent()
    {
        $file = $this->getFilePath();

        if (file_exists($file)) {
            return file_get_contents($file);
        }

        return '';
    }

    private function addContract($content)
    {
        return file_put_contents($this->getFilePath(), $content);
    }

    private function generateDefault()
    {
        $contractDate = $this->model->contract_date;
        $file = $this->getFilePath();

        $document = $this->model;
        $account = $document->getAccount();

        $design = \app\classes\Smarty::init();
        $design->assign('client', $account);
        $design->assign('contract', $document);
        $design->assign('firm_detail', Company::getDetail($account->contract->organization, $contractDate));
        $design->assign('firm', Company::getProperty($account->contract->organization, $contractDate));

        $content = $this->contract_fix_static_parts_of_template($design, file_get_contents($file));
        file_put_contents($file, $content);
        $newDocument = $design->fetch($file);
        return file_put_contents($file, $newDocument);
    }

    private function deleteFile()
    {
        $file = $this->getFilePath();

        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    private function getTemplate($name)
    {
        $name = preg_replace('[^\w\d\-\_]', '', $name);

        if (file_exists(Yii::$app->params['STORE_PATH'] . 'contracts/' . $name . '.html')) {
            $data = file_get_contents(Yii::$app->params['STORE_PATH'] . 'contracts/' . $name . '.html');
        } else {
            $data = file_get_contents(Yii::$app->params['STORE_PATH'] . 'contracts/template_mcn_default.html');
        }

        $this->fix_style($data);

        return $data;
    }

    private function getFilePath()
    {
        $contractId = $this->model->contract_id;
        $documentId = $this->model->id;
        return Yii::$app->params['STORE_PATH'] . 'contracts/' . $contractId . '-' . $documentId . '.html';
    }

    private function fix_style(&$content)
    {
        if (strpos($content, '{/literal}</style>') === false) {
            $content = preg_replace('/<style([^>]*)>(.*?)<\/style>/six', '<style\\1>{literal}\\2{/literal}</style>', $content);
        }
    }

    private function contract_fix_static_parts_of_template(&$design, $content)
    {
        if (($pos = strpos($content, '{\$include_')) !== false) {
            $c = substr($content, $pos);
            $templateName = substr($c, 10, strpos($c, '}') - 10);

            $fname = Yii::$app->params['STORE_PATH'] . 'contracts/template_' . $templateName . '.html';

            if (file_exists($fname)) {
                $c = file_get_contents($fname);
                $design->assign('include_' . $templateName, $c);
            }

            $fname = Yii::$app->params['STORE_PATH'] . 'contracts/' . $templateName . '.html';
            if (file_exists($fname)) {
                $c = file_get_contents($fname);
                $design->assign('include_' . $templateName, $c);
            }
        }


        if (strpos($content, '{*#voip_moscow_tarifs_mob#*}') !== false) {
            $repl = ''; // москва(моб.)
            $content = str_replace('{*#voip_moscow_tarifs_mob#*}', $repl, $content);
        }

        $this->fix_style($content);

        return $content;
    }
}
