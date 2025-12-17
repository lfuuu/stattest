<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\exceptions\ModelValidationException;
use app\models\EventQueue;
use app\classes\Html;
use app\modules\nnp\filters\CountryFilter;
use app\modules\nnp2\forms\import\Form;
use app\modules\nnp\media\ImportServiceUploaded;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\CountryFile;
use app\modules\nnp\Module;
use app\modules\nnp2\media\ImportServiceUploadedNew;
use app\modules\nnp2\models\ImportHistory;
use Yii;
use yii\db\Expression;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\data\ActiveDataProvider;

/**
 * Импорт
 */
class ImportController extends BaseController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'step2', 'step3', 'step4', 'unlink', 'download', 'approve', 'delete'],
                        'roles' => ['nnp.write'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Шаг 1. Выбор страны
     *
     * @return string
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $country = new CountryFilter();
        $post = Yii::$app->request->post();
        $country->load($post);
        if ($country->code) {
            return $this->redirect(Url::to(['/nnp/import/step2', 'countryCode' => $country->code]));
        }

//        if (NumberRange::isTriggerEnabled()) {
//            Yii::$app->session->addFlash('error', $this->_getTriggerErrorMessage());
//        }

        $query = ImportHistory::find()->orderBy(['id' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
        ]);

        return $this->render('index', [
            'country' => $country,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Подвердить всё по стране
     *
     * @param int $countryCode
     * @return string
     * @throws \yii\db\Exception
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidParamException
     */
    public function actionApprove($countryCode)
    {
        try {
            /** @var Form $form */
            $formModel = new Form(['countryCode' => $countryCode]);
            $formModel->approve();

            return $this->redirect(Url::to(['/nnp/import/step2', 'countryCode' => $formModel->getCountry()->code]));
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', 'Ошибка: ' . $e->getMessage());
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Удалить всё по стране
     *
     * @param int $countryCode
     * @return string
     * @throws \yii\db\Exception
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidParamException
     */
    public function actionDelete($countryCode)
    {
        try {
            /** @var Form $form */
            $formModel = new Form(['countryCode' => $countryCode]);
            $formModel->delete();

            return $this->redirect(Url::to(['/nnp/import/step2', 'countryCode' => $formModel->getCountry()->code]));
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', 'Ошибка: ' . $e->getMessage());
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Шаг 2. Загрузка или выбор файла
     *
     * @param int $countryCode
     * @return string
     * @throws ModelValidationException
     */
    public function actionStep2($countryCode)
    {
        $country = Country::findOne(['code' => $countryCode]);
        if (!$country) {
            throw new InvalidParamException('Неправильная страна');
        }

        if (isset($_FILES['file'])) {
            // загрузить файл
            /** @var CountryFile $countryFile */
            $countryFile = $country->getMediaManager()->addFile($_FILES['file']);
            if (!$countryFile) {
                Yii::$app->session->addFlash('error', 'Ошибка загрузки файла');
                return $this->redirect(Url::to(['/nnp/import/step2', 'countryCode' => $country->code]));
            }

            Yii::$app->session->addFlash('success', 'Файл успешно загружен');
            $previewEvent = EventQueue::go(Module::EVENT_IMPORT_PREVIEW, ['fileId' => $countryFile->id, 'notified_user_id' => Yii::$app->user->id]);
            $countryFile->rememberPreviewEventId($previewEvent->id);
            return $this->redirect(Url::to(['/nnp/import/step3', 'countryCode' => $country->code, 'fileId' => $countryFile->id]));
        }

//        if (NumberRange::isTriggerEnabled()) {
//            Yii::$app->session->addFlash('error', $this->_getTriggerErrorMessage());
//        }

        return $this->render('step2', ['country' => $country]);
    }

    /**
     * Шаг 3. Получить файл
     *
     * @param int $countryCode
     * @param int $fileId
     * @return CountryFile
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidParamException
     */
    private function _getCountryFile($countryCode, $fileId)
    {
        $countryFile = CountryFile::findOne([
            'id' => $fileId,
            'is_active' => 1,
        ]);
        if (!$countryFile) {
            throw new InvalidParamException('Неправильный файл');
        }

        if ($countryFile->country_code != $countryCode) {
            throw new InvalidParamException('Неправильная страна');
        }

        return $countryFile;
    }

    /**
     * Получить очередь предпросмотра файла.
     */
    private function getPreviewEvent(CountryFile $countryFile): ?EventQueue
    {
        $eventId = $countryFile->getCachedPreviewEventId();
        if ($eventId) {
            return EventQueue::findOne($eventId);
        }

        $event = EventQueue::find()
            ->where(['event' => Module::EVENT_IMPORT_PREVIEW])
            ->andWhere(new Expression(
                "JSON_UNQUOTE(JSON_EXTRACT(param, '$.fileId')) = :fileId",
                [':fileId' => (string)$countryFile->id]
            ))
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($event) {
            $countryFile->rememberPreviewEventId($event->id);
        }

        return $event;
    }

    /**
     * Шаг 3. Предпросмотр файла
     *
     * @param int $countryCode
     * @param int $fileId
     * @param int $offset
     * @param int $limit
     * @return string
     * @throws \yii\db\Exception
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidParamException
     */
    public function actionStep3($countryCode, $fileId, $offset = 0, $limit = 10)
    {
//        if (NumberRange::isTriggerEnabled()) {
//            Yii::$app->session->addFlash('error', $this->_getTriggerErrorMessage());
//        }

        try {
            /** @var Form $form */
            $formModel = new Form(['countryCode' => $countryCode]);
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', 'Ошибка: ' . $e->getMessage());
        }

        $countryFile = $this->_getCountryFile($countryCode, $fileId);
        $previewEvent = $this->getPreviewEvent($countryFile);

        if (Yii::$app->request->get('startQueue')) {
            $previewEvent = EventQueue::go(Module::EVENT_IMPORT_PREVIEW, ['fileId' => $countryFile->id, 'notified_user_id' => Yii::$app->user->id], true);
            $countryFile->rememberPreviewEventId($previewEvent->id);

            return $this->redirect(Url::to(['/nnp/import/step3', 'countryCode' => $countryCode, 'fileId' => $fileId]));
        }

        $runCheck = boolval(Yii::$app->request->get('runCheck'));
        $checkFull = boolval(Yii::$app->request->get('check'));
        $country = $countryFile->country;
        $mediaManager = $country->getMediaManager();
        $isSmall = $mediaManager->isSmall($countryFile);

        if (!$checkFull) {
            $checkFull = $isSmall;
        }

        if ($previewEvent && $previewEvent->status === EventQueue::STATUS_OK) {
            $runCheck = true;
            $checkFull = true;
        }

        return $this->render('step3', [
            'countryFile' => $countryFile,
            'clear' => boolval(Yii::$app->request->get('clear')),
            'checkFull' => $checkFull,
            'runCheck' => $runCheck,
            'isSmall' => $isSmall,
            'offset' => $offset,
            'limit' => $limit,
            'formModel' => $formModel,
            'previewEvent' => $previewEvent,
        ]);
    }

    /**
     * Шаг 3. Скачивание файла
     *
     * @param int $countryCode
     * @param int $fileId
     * @return string
     * @throws \yii\web\HttpException
     * @throws \yii\base\ExitException
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidParamException
     */
    public function actionDownload($countryCode, $fileId)
    {
        $countryFile = $this->_getCountryFile($countryCode, $fileId);
        $countryFile
            ->country
            ->getMediaManager()
            ->getContent($countryFile, $isDownload = true);
    }

    /**
     * Шаг 3. Удаление файла
     *
     * @param int $countryCode
     * @param int $fileId
     * @return string
     * @throws \yii\web\HttpException
     * @throws \yii\base\ExitException
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidParamException
     */
    public function actionUnlink($countryCode, $fileId)
    {
        $countryFile = $this->_getCountryFile($countryCode, $fileId);
        $country = $countryFile->country;

        $post = Yii::$app->request->post();
        if (isset($post['dropButton'])) {
            $country
                ->getMediaManager()
                ->removeFile($countryFile);
            Yii::$app->session->addFlash('success', 'Файл успешно удален');
        } else {
            Yii::$app->session->addFlash('error', 'Ошибка удаления файла');
        }

        return $this->redirect(Url::to(['/nnp/import/step2', 'countryCode' => $country->code]));
    }

    /**
     * Шаг 4. Импортировать файла
     *
     * @param int $countryCode
     * @param int $fileId
     * @param int|null $version
     * @param int|null $queue
     * @return string
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionStep4($countryCode, $fileId, $version = null, $queue = null)
    {
        try {
            /** @var Form $form */
            $formModel = new Form(['countryCode' => $countryCode]);
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', 'Ошибка: ' . $e->getMessage());
            return $this->redirect(Url::to(['/nnp/import/step3', 'countryCode' => $countryCode, 'fileId' => $fileId]));
        }

        $countryFile = $this->_getCountryFile($countryCode, $fileId);

        if (is_null($version)) {
            Yii::$app->session->addFlash('error', 'Не выбрана версия импорта');
            return $this->redirect(Url::to(['/nnp/import/step3', 'countryCode' => $countryFile->country->code, 'fileId' => $countryFile->id]));
        }

        $isProcessedOld = $formModel->isProcessedOld($version);
        $isProcessed = $formModel->isProcessed($version);

//        if (NumberRange::isTriggerEnabled()) {
//            Yii::$app->session->addFlash('error', $this->_getTriggerErrorMessage());
//            return $this->redirect(Url::to(['/nnp/import/step3', 'countryCode' => $countryFile->country->code, 'fileId' => $countryFile->id]));
//        }

        $country = $countryFile->country;
        $mediaManager = $country->getMediaManager();
        $isSmall = $mediaManager->isSmall($countryFile);
        $forceQueue = boolval($queue);

        if (!$forceQueue && $isSmall) {
            // файл маленький - загрузить сразу

            // import version 1
            if ($isProcessedOld) {
                $importHistoryOld = ImportHistory::startFile($countryFile, false);
                $importOld = new ImportServiceUploaded([
                    'countryCode' => $countryCode,
                    'countryFileId' => $countryFile->id,
                    'url' => $mediaManager->getUnzippedFilePath($countryFile),
                    'delimiter' => ';',
                ]);
                $doneOld = $importOld->run($importHistoryOld);
                $importHistoryOld->finish($doneOld);
                $logOld = $importOld->getLogAsString();

                if ($doneOld) {
                    $logOld .= PHP_EOL . PHP_EOL . Html::a(
                            'Посмотреть диапазоны номеров v1',
                            ['/nnp/number-range', 'NumberRangeFilter[country_code]' => $country->code, 'NumberRangeFilter[is_active]' => 1],
                            ['target' => '_blank']
                        ) . PHP_EOL;

                    // поставить в очередь для пересчета операторов, регионов и городов
                    $eventQueue = EventQueue::go(Module::EVENT_LINKER, [
                        'notified_user_id' => Yii::$app->user->id,
                        'country_code' => $country->code,
                    ]);
                    Yii::$app->session->addFlash('success', 'Файл успешно импортирован v1.' . nl2br(PHP_EOL . $logOld) .
                        'Пересчет операторов, регионов и городов будет через несколько минут. ' . Html::a('Проверить', $eventQueue->getUrl()));

                } else {
                    Yii::$app->session->addFlash('error', 'Ошибка импорта v1 файла.' . nl2br(PHP_EOL . $logOld));
                }
            }

            // import version 2
            if ($isProcessed) {
                $importHistory = ImportHistory::startFile($countryFile, false, 2);
                $import = new ImportServiceUploadedNew([
                    'countryCode' => $countryCode,
                    'url' => $mediaManager->getUnzippedFilePath($countryFile),
                    'delimiter' => ';',
                ]);

                $done = $import->run($importHistory);
                $importHistory->finish($done);
                $log = $import->getLogAsString();
                $log = PHP_EOL . PHP_EOL . '******************************************' . PHP_EOL . $log;

                if ($done) {
                    $log .= PHP_EOL . PHP_EOL . Html::a(
                            'Посмотреть диапазоны номеров v2',
                            ['/nnp2/number-range', 'NumberRangeFilter[country_code]' => $country->code, 'NumberRangeFilter[is_active]' => 1],
                            ['target' => '_blank']
                        );

                    Yii::$app->session->addFlash('success', 'Файл ' . Html::a(
                            $countryFile->country->name_rus,
                            Url::to([
                                '/nnp/import/step2',
                                'countryCode' => $countryFile->country_code,
                            ])
                        ) . ' / ' .
                        Html::a(
                            $countryFile->name,
                            Url::to([
                                '/nnp/import/step3',
                                'countryCode' => $countryFile->country_code,
                                'fileId' => $countryFile->id,
                            ])
                        ) . ' успешно импортирован v2.' . nl2br(PHP_EOL . $log));

                } else {
                    Yii::$app->session->addFlash('error', 'Ошибка импорта v2 файла ' . Html::a(
                            $countryFile->name,
                            Url::to([
                                '/nnp/import/step3',
                                'countryCode' => $countryFile->country_code,
                                'fileId' => $countryFile->id,
                            ])
                        ) . '.' . nl2br(PHP_EOL . $log));
                }
            }
        } else {
            // файл большой - поставить в очередь
            $eventQueue = EventQueue::go(Module::EVENT_IMPORT, ['fileId' => $fileId, 'old' => $isProcessedOld, 'new' => $isProcessed]);
            Yii::$app->session->setFlash('success', 'Файл поставлен в очередь на загрузку. ' . Html::a('Проверить', $eventQueue->getUrl()));
        }

        return $this->redirect(Url::to(['/nnp/import/step2', 'countryCode' => $country->code]));
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    private function _getTriggerErrorMessage()
    {
        return sprintf(
            'Импорт невозможен, потому что триггер включен. <a href="%s" target="_blank">Выключите его</a> и обновите эту страницу.',
            Url::to(['/nnp/number-range/', 'NumberRangeFilter[country_code]' => Country::RUSSIA, 'NumberRangeFilter[is_active]' => 1])
        );
    }
}