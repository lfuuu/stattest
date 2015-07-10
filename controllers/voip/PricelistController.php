<?php
namespace app\controllers\voip;

use app\classes\Assert;
use app\classes\voip\BasePricelistLoader;
use app\classes\voip\MegafonPricelistLoader;
use app\classes\voip\UniversalPricelistLoader;
use app\forms\billing\PricelistAddForm;
use app\models\billing\NetworkConfig;
use app\models\billing\PricelistFile;
use Yii;
use app\classes\BaseController;
use app\forms\billing\PricelistEditForm;
use app\models\billing\Pricelist;
use app\models\Region;
use yii\base\Exception;
use yii\filters\AccessControl;

class PricelistController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['list', 'files', 'file-download'],
                        'roles' => ['voip.access'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add', 'edit', 'file-upload', 'file-parse'],
                        'roles' => ['voip.admin'],
                    ],
                ],
            ],
        ];
    }

    public function actionList($type, $orig, $connectionPointId = 0)
    {
        $query =
            Pricelist::find()
                ->orderBy('region desc, name asc');

        $query->andWhere(['type' => $type]);
        $query->andWhere('orig = ' . ($orig > 0 ? 'true' : 'false'));
        if ($connectionPointId > 0) {
            $query->andWhere(['region' => $connectionPointId]);
        }

        $pricelists = $query->all();

        return $this->render("list", [
            'connectionPointId' => $connectionPointId,
            'pricelists' => $pricelists,
            'connectionPoints' => Region::dao()->getList(),
            'networkConfigs' => NetworkConfig::dao()->getList(),
            'orig' => (int)$orig,
            'type' => $type,
        ]);
    }


    public function actionAdd($type = null, $orig = 0, $connectionPointId = 0)
    {
        $model = new PricelistAddForm();
        $model->orig = $orig;
        $model->type = $type;
        $model->connection_point_id = $connectionPointId;
        $model->tariffication_by_minutes = 0;
        $model->tariffication_full_first_minute = 0;
        $model->initiate_mgmn_cost = 0;
        $model->initiate_zona_cost = 0;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['edit', 'id' => $model->id]);
        }

        return $this->render("add", [
            'model' => $model,
        ]);
    }

    public function actionEdit($id)
    {
        $pricelist = Pricelist::findOne($id);

        Assert::isObject($pricelist);

        $model = new PricelistEditForm();
        $model->setAttributes($pricelist->getAttributes(), false);
        $model->connection_point_id = $pricelist->region;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['edit', 'id' => $model->id]);
        }

        return $this->render("edit", [
            'model' => $model,
            'pricelist' => $pricelist,
        ]);
    }

    public function actionFiles($pricelistId)
    {
        $pricelist = Pricelist::findOne($pricelistId);
        Assert::isObject($pricelist);
        $files =
            PricelistFile::find()
                ->andWhere(['pricelist_id' => $pricelistId])
                ->orderBy('startdate desc, date desc')
                ->all();

        return $this->render("files", [
            'pricelist' => $pricelist,
            'files' => $files,
        ]);

    }

    public function actionFileUpload($pricelistId)
    {
        $pricelist = Pricelist::findOne($pricelistId);
        Assert::isObject($pricelist);

        if (!$_FILES['upfile'] && $_FILES['upfile']['error']) {
            throw new Exception('Файл не был загружен');
        }

        $parser = new UniversalPricelistLoader();
        $file = $parser->uploadFile($_FILES['upfile'], $pricelist->id);

        return $this->redirect(['file-parse', 'fileId' => $file->id]);
    }

    public function actionFileParse($fileId)
    {
        $file = PricelistFile::findOne($fileId);
        /** @var PricelistFile $file */
        Assert::isObject($file);

        Assert::isFalse($file->parsed);
        Assert::isFalse($file->active);

        $settings = [
            'loader' => UniversalPricelistLoader::className(),
            'prefix' => '',
            'skip_rows' => 1,
            'full' => false,
            'compress' => 0,
        ];

        $savedSettings = json_decode($file->pricelist->parser_settings, true);
        if ($savedSettings) {
            $settings = array_merge($settings, $savedSettings);
        }

        if (Yii::$app->request->isPost && Yii::$app->request->post('btn_set_loader') !== null) {
            $settings['loader'] = Yii::$app->request->post('loader', '');
            $file->pricelist->parser_settings = json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $file->pricelist->save();
        }

        if (Yii::$app->request->isPost && Yii::$app->request->post('btn_upload') !== null) {
            $file->full = Yii::$app->request->post('load_type') == 'full';
            $file->startdate = Yii::$app->request->post('effective_date');

            $settings['prefix'] = Yii::$app->request->post('prefix', '');
            $settings['skip_rows'] = (int)Yii::$app->request->post('skip_rows', 0);
            $settings['full'] = $file->full;
            $settings['compress'] = (int)Yii::$app->request->post('compress', 0);

            $settings['cols'] = [];
            $n = 1;
            while (Yii::$app->request->post('col_' . $n) !== null) {
                $fType = Yii::$app->request->post('col_' . $n);
                if ($fType) {
                    $settings['cols'][$fType] = $n;
                }
                $n++;
            }

            if (Yii::$app->request->post('save_settings', 0) > 0) {
                $file->pricelist->parser_settings = json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $file->pricelist->save();
            }
            $file->save();
        }

        $loaderClass = $settings['loader'];
        $parser = new $loaderClass(); /** @var BasePricelistLoader $parser */

        if (Yii::$app->request->isPost && Yii::$app->request->post('btn_upload') !== null) {

            set_time_limit(0);
            Yii::$app->session->close();

            $parser->load($file);

            $data = $parser->read($settings);
            if ($settings['compress']) {
                $data = $parser->compress($data);
            }
            $parser->savePrices($file, $data);

            return $this->redirect('/index.php?module=voipnew&action=view_raw_file&id=' . $file->id);

        } else {
            $parser->load($file);

            $data = $parser->readRaw(50);
        }

        $loaders = [
            UniversalPricelistLoader::className() => UniversalPricelistLoader::getName(),
            MegafonPricelistLoader::className() => MegafonPricelistLoader::getName(),
        ];

        return $this->render("file_parse", [
            'parser' => $parser,
            'loaders' => $loaders,
            'pricelist' => $file->pricelist,
            'file' => $file,
            'settings' => $settings,
            'data' => $data,
        ]);
    }

    public function actionFileDownload($fileId)
    {
        $file = PricelistFile::findOne($fileId); /** @var PricelistFile $file */
        Assert::isObject($file);

        while (ob_get_level()) ob_end_clean();

        if (preg_match('/\.csv$/', $file->filename)) {
            header("Content-Type: application/csv");
        } elseif (preg_match('/\.xls$/', $file->filename)) {
            header("Content-Type: application/vnd.ms-excel");
        } elseif (preg_match('/\.xlsx$/', $file->filename)) {
            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        }

        header("Content-Disposition: attachment; filename=" . $file->filename);
        header('Content-Transfer-Encoding: binary');
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);

        readfile($file->getStorageFilePath());

        exit;
    }
}