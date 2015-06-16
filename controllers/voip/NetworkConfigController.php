<?php
namespace app\controllers\voip;

use app\classes\Assert;
use app\classes\voip\BasePricelistLoader;
use app\classes\voip\MgtsNetworkLoader;
use app\classes\voip\UniversalNetworkLoader;
use app\forms\billing\NetworkConfigAddForm;
use app\forms\billing\NetworkConfigEditForm;
use app\forms\billing\PricelistAddForm;
use app\models\billing\NetworkConfig;
use app\models\billing\NetworkFile;
use Yii;
use app\classes\BaseController;
use app\forms\billing\PricelistEditForm;
use app\models\billing\Pricelist;
use app\models\Region;
use yii\base\Exception;
use yii\filters\AccessControl;

class NetworkConfigController extends BaseController
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
                        'actions' => ['add', 'edit', 'geo-upload', 'file-upload', 'file-parse'],
                        'roles' => ['voip.admin'],
                    ],
                ],
            ],
        ];
    }

    public function actionList()
    {
        $query =
            NetworkConfig::find()
                ->orderBy('instance_id desc, name asc');

        $list = $query->all();
        $connectionPoints = Region::dao()->getList();
        $pricelists = Pricelist::dao()->getList();

        return $this->render("list", [
            'list' => $list,
            'connectionPoints' => $connectionPoints,
            'pricelists' => $pricelists,
        ]);
    }



    public function actionAdd()
    {
        $model = new NetworkConfigAddForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['edit', 'id' => $model->id]);
        }

        return $this->render("add", [
            'model' => $model,
        ]);
    }

    public function actionEdit($id)
    {
        $networkConfig = NetworkConfig::findOne($id);
        Assert::isObject($networkConfig);

        $model = new NetworkConfigEditForm();
        $model->setAttributes($networkConfig->getAttributes(), false);
        $model->connection_point_id = $networkConfig->instance_id;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['edit', 'id' => $model->id]);
        }

        return $this->render("edit", [
            'model' => $model,
            'networkConfig' => $networkConfig,
        ]);
    }

    public function actionFiles($networkConfigId)
    {
        $networkConfig = NetworkConfig::findOne($networkConfigId);
        Assert::isObject($networkConfig);

        $pricelist = NetworkConfig::findOne($networkConfigId);
        Assert::isObject($pricelist);

        $files =
            NetworkFile::find()
                ->andWhere(['network_config_id' => $networkConfig->id])
                ->orderBy('startdate desc, created_at desc')
                ->all();

        return $this->render("files", [
            'networkConfig' => $networkConfig,
            'pricelist' => $pricelist,
            'files' => $files,
        ]);
    }

    public function actionGeoUpload($networkConfigId)
    {
        $networkConfig = NetworkConfig::findOne($networkConfigId); /** @var NetworkConfig $networkConfig */
        Assert::isObject($networkConfig);

        Assert::isNotEmpty($networkConfig->geo_city_id, 'Не указан город');
        Assert::isNotEmpty($networkConfig->geo_operator_id, 'Не указан оператор');

        $parser = new UniversalNetworkLoader();
        $file = $parser->uploadFileByGeo($networkConfig);

        return $this->redirect('/index.php?module=voipnew&action=network_file_show&id=' . $file->id);
    }

    public function actionFileParse($fileId)
    {
        $file = NetworkFile::findOne($fileId);
        Assert::isObject($file); /** @var NetworkFile $file */

        Assert::isFalse($file->parsed);
        Assert::isFalse($file->active);

        $settings = [
            'loader' => UniversalNetworkLoader::className(),
            'prefix' => '',
            'skip_rows' => 1,
            'compress' => 0,
        ];

        $savedSettings = json_decode($file->pricelist->parser_settings, true);
        if ($savedSettings) {
            $settings = array_merge($settings, $savedSettings);
        }

        if (Yii::$app->request->isPost && Yii::$app->request->post('btn_set_loader') !== null) {
            $settings['loader'] = Yii::$app->request->post('loader', '');
            $file->config->pricelist->parser_settings = json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $file->config->pricelist->save();
        }

        if (Yii::$app->request->isPost && Yii::$app->request->post('btn_upload') !== null) {
            $file->startdate = Yii::$app->request->post('effective_date');

            $settings['prefix'] = Yii::$app->request->post('prefix', '');
            $settings['skip_rows'] = (int)Yii::$app->request->post('skip_rows', 0);
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
                $file->config->pricelist->parser_settings = json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $file->config->pricelist->save();
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

            return $this->redirect('/index.php?module=voipnew&action=network_file_show&id=' . $file->id);

        } else {
            $parser->load($file);

            $data = $parser->readRaw(50);
        }

        $loaders = [
            UniversalNetworkLoader::className() => UniversalNetworkLoader::getName(),
            MgtsNetworkLoader::className() => MgtsNetworkLoader::getName(),
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
        $file = NetworkFile::findOne($fileId);
        Assert::isObject($file); /** @var NetworkFile $file */

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