<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\classes\Event;
use app\classes\Html;
use app\modules\nnp\filter\CountryFilter;
use app\modules\nnp\media\ImportServiceUploaded;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\CountryFile;
use app\modules\nnp\models\NumberRange;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\Url;

/**
 * Импорт
 */
class ImportController extends BaseController
{
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

        if (NumberRange::isTriggerEnabled()) {
            Yii::$app->session->addFlash('error', $this->_getTriggerErrorMessage());
        }

        return $this->render('index', ['country' => $country]);
    }

    /**
     * Шаг 2. Загрузка или выбор файла
     *
     * @param int $countryCode
     * @return string
     * @throws \yii\db\Exception
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidParamException
     */
    public function actionStep2($countryCode)
    {
        $country = Country::findOne(['code' => $countryCode]);
        if (!$country || $countryCode == Country::RUSSIA) {
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
            return $this->redirect(Url::to(['/nnp/import/step3', 'countryCode' => $country->code, 'fileId' => $countryFile->id]));
        }

        if (NumberRange::isTriggerEnabled()) {
            Yii::$app->session->addFlash('error', $this->_getTriggerErrorMessage());
        }

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
        $countryFile = CountryFile::findOne(['id' => $fileId]);
        if (!$countryFile) {
            throw new InvalidParamException('Неправильный файл');
        }

        if ($countryFile->country_code != $countryCode || $countryCode == Country::RUSSIA) {
            throw new InvalidParamException('Неправильная страна');
        }

        return $countryFile;
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
        if (NumberRange::isTriggerEnabled()) {
            Yii::$app->session->addFlash('error', $this->_getTriggerErrorMessage());
        }

        $countryFile = $this->_getCountryFile($countryCode, $fileId);
        return $this->render('step3', [
            'countryFile' => $countryFile,
            'offset' => $offset,
            'limit' => $limit,
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
     * @return string
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidParamException
     */
    public function actionStep4($countryCode, $fileId)
    {
        $countryFile = $this->_getCountryFile($countryCode, $fileId);

        if (NumberRange::isTriggerEnabled()) {
            Yii::$app->session->addFlash('error', $this->_getTriggerErrorMessage());
            return $this->redirect(Url::to(['/nnp/import/step3', 'countryCode' => $countryFile->country->code, 'fileId' => $countryFile->id]));
        }

        $country = $countryFile->country;
        $mediaManager = $country->getMediaManager();
        if ($mediaManager->isSmall($countryFile)) {

            // файл маленький - загрузить сразу
            $filePath = $mediaManager->getUnzippedFilePath($countryFile);
            $importServiceUploaded = new ImportServiceUploaded($countryCode);
            $isOk = $importServiceUploaded->run($filePath);
            $log = $importServiceUploaded->getLogAsString();
            if ($isOk) {
                $log .= PHP_EOL . PHP_EOL . Html::a(
                        'Посмотреть диапазоны номеров',
                        ['/nnp/number-range', 'NumberRangeFilter[country_code]' => $country->code, 'NumberRangeFilter[is_active]' => 1],
                        ['target' => '_blank']
                    );
                Yii::$app->session->addFlash('success', 'Файл успешно импортирован.' . nl2br(PHP_EOL . $log));
            } else {
                Yii::$app->session->addFlash('error', 'Ошибка импорта файла.' . nl2br(PHP_EOL . $log));
            }
        } else {

            // файл большой - поставить в очередь
            $eventQueue = Event::go(ImportServiceUploaded::EVENT, ['fileId' => $fileId]);
            $message = sprintf(
                'Файл поставлен в очередь на загрузку. <a href="%s" target="_blank">Проверить его статус</a>',
                Url::to(['/monitoring/event-queue', 'EventQueueFilter[id]' => $eventQueue->id])
            );
            Yii::$app->session->addFlash('success', $message);
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
