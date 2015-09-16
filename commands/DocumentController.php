<?php
namespace app\commands;

use app\models\document\DocumentFolder;
use app\models\document\DocumentTemplate;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use Yii;

class DocumentController extends Controller
{
    private $folderName = [
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
    private $folders = [];
    private $templates = [];
    private $types = [];

    public function actionMigrate()
    {
        $tmplDir = Yii::$app->params['SMARTY_TEMPLATE_DIR'];
        echo "Check dir '$tmplDir'\n";
        if(!file_exists($tmplDir)){
            echo "Dir not exists\n";
            return false;
        }
        echo "Dir exists\n\n";

        $files = $this->getTree($tmplDir);
        echo 'Найдено '.count($files)." файла(ов)\n\n";

        DocumentTemplate::deleteAll();
        DocumentFolder::deleteAll();

        foreach($files as $file){
            $folderId = $this->getFolderIdByName($file['folder']);
            $type = $this->getDocumentTypeByName($file['folder'].'_'.$file['name']);
            $this->createTemplate($file['name'], $file['content'], $type, $folderId);
        }

        echo "Создано всего:\n";
        echo "\t-папок: ".count($this->folders)."\n";
        echo "\t-шаблонов: ".count($this->templates)."\n";
        return true;
    }

    private function getTree($dir)
    {
        $res = [];
        $files = FileHelper::findFiles($dir, ['only' => ['template_*.html']]);
        foreach($files as $file){
            $tmp = [];
            $arr = explode('_', basename($file, '.html'));
            $tmp['folder'] = $arr[1];
            unset($arr[0], $arr[1]);
            $tmp['name'] = implode('_', $arr);
            $tmp['content'] = file_get_contents($file);
            $res[] = $tmp;
        }

        return $res;
    }

    private function getFolderIdByName($name)
    {
        $name = $this->folderName[$name];
        $id = array_search($name, $this->folders, true);
        if($id==false) {
            $f = new DocumentFolder();
            $f->name = $name;
            $f->save();
            $id = $f->id;
            $this->folders[$id] = $f->name;
        }
        return $id;
    }

    private function createTemplate($name, $content, $type, $folderId)
    {
        $t = new DocumentTemplate();
        $t->content = $content;
        $t->name = $name;
        $t->type = $type;
        $t->folder_id = $folderId;
        $t->save();
        $this->templates[$t->id] = $t->name;
        return $t->name;
    }

    private function getDocumentTypeByName($name)
    {
        if(isset($this->types[$name]))
            return $this->types[$name];

        $arr = Yii::$app->db
            ->createCommand("SELECT * FROM contract;")
            ->queryAll(\PDO::FETCH_ASSOC);
        $this->types = ArrayHelper::map($arr, 'name', 'type');

        return isset($this->types[$name]) ? $this->types[$name] : 'contract';
    }
}
