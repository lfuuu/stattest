<?php

namespace app\modules\sbisTenzor\classes\SBISProcessor;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\modules\sbisTenzor\classes\SBISDocumentStatus;
use app\modules\sbisTenzor\classes\SBISProcessor;
use app\modules\sbisTenzor\classes\SBISTensorAPI;
use app\modules\sbisTenzor\classes\SBISTensorAPI\SBISDocumentInfo;
use app\modules\sbisTenzor\exceptions\SBISTensorException;
use app\modules\sbisTenzor\models\SBISDocument;
use app\modules\sbisTenzor\models\SBISOrganization;
use DateTime;
use Yii;
use yii\db\Expression;
use yii\web\BadRequestHttpException;

/**
 * Обработчик проверки статуса пакетов документов
 */
class SBISFetcher extends SBISProcessor
{
    // Информация по измененным документам организации
    /** @var SBISDocumentInfo[] */
    protected $documentsInfo = [];

    /**
     * Основа выборки
     *
     * @return \yii\db\ActiveQuery
     */
    protected function getBaseFetchQuery()
    {
        return SBISDocument::find()
            ->andWhere(['>=', 'state', SBISDocumentStatus::SENT])
            ->andWhere(['!=', 'state', SBISDocumentStatus::ACCEPTED]);
    }

    /**
     * Получаем количество документов для опроса для каждой организации
     *
     * @return array
     */
    protected function getDocumentsToProcessCount()
    {
        return $this->getBaseFetchQuery()
            ->select([
                'documents' => new Expression('COUNT(*)'),
            ])
            ->groupBy('sbis_organization_id')
            ->indexBy('sbis_organization_id')
            ->column();
    }

    /**
     * Получаем измененные пакеты документов
     *
     * @return SBISDocument[]
     */
    protected function getDocumentsToProcess()
    {
        $externalIds = array_keys($this->documentsInfo);

        return $this->getBaseFetchQuery()
            ->with('sbisOrganization')
            ->with('attachments')
            ->andWhere(['external_id' => $externalIds])
            ->all();
    }

    /**
     * Проверяем и обновляем данные по пакету документов
     *
     * @param SBISDocument $document
     * @return bool
     */
    protected function checkDocument(SBISDocument $document)
    {
        $success = false;

        $docExternalId = $document->external_id;
        if (array_key_exists($docExternalId, $this->documentsInfo)) {
            $documentInfo = $this->documentsInfo[$docExternalId];

            $success = $document->applyDocumentInfo($documentInfo);
        }

        return $success;
    }

    /**
     * Предварительные вычисления перед обработкой пакетов документов
     *
     * @param SBISTensorAPI $api
     * @return SBISOrganization
     * @throws BadRequestHttpException
     * @throws SBISTensorException
     * @throws \yii\base\Exception
     * @throws \Exception
     */
    protected function preLaunchActions(SBISTensorAPI $api)
    {
        $dateNow = new DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));

        // fetch changes
        $this->documentsInfo = $api->fetchDocumentsInfo();

        $sbisOrganization = $api->sbisOrganization;
        $sbisOrganization->last_fetched_at = $dateNow->format(DateTimeZoneHelper::DATETIME_FORMAT);

        if ($lastEvent = end($this->documentsInfo)) {
            $sbisOrganization->last_event_id = $lastEvent->lastEventId;
        }

        return $sbisOrganization;
    }

    /**
     * Вычисления после обработки пакетов документов
     *
     * @param SBISOrganization $sbisOrganization
     * @throws ModelValidationException
     */
    protected function afterLaunchAction(SBISOrganization $sbisOrganization)
    {
        if (!$sbisOrganization->save()) {
            throw new ModelValidationException($sbisOrganization);
        }
    }

    /**
     * Точка входа обработчика
     *
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function run()
    {
        $docsCount = $this->getDocumentsToProcessCount();
        if (empty($docsCount)) {
            return 0 ;
        }

        $processed = 0;
        foreach ($this->apiPool as $sbisOrganizationId => $api) {
            if (empty($docsCount[$api->sbisOrganization->id])) {
                continue;
            }

            $sbisOrganization = $this->preLaunchActions($api);

            $processed += count($this->documentsInfo);
            $transaction = SBISDocument::getDb()->beginTransaction();
            try {
                foreach ($this->getDocumentsToProcess() as $document) {
                    if ($this->beforeProcess($document)) {
                        $success = $this->process($document);
                        $this->afterProcess($document, $success);
                    }
                }

                $this->afterLaunchAction($sbisOrganization);

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();

                Yii::error($e);
                $errorText = sprintf(
                    'Ошибка обработчика опроса документов (sbisOrganizationId: %s): %s',
                    $sbisOrganizationId,
                    $e->getMessage()
                );
                Yii::error($errorText, SBISDocument::LOG_CATEGORY);
            }
        }

        return $processed;
    }

    /**
     * Предобработка пакета документов
     *
     * @param SBISDocument $document
     * @return bool
     */
    protected function beforeProcess(SBISDocument $document)
    {
        return true;
    }

    /**
     * Обработка пакета документов
     *
     * @param SBISDocument $document
     * @return bool
     */
    protected function process(SBISDocument $document)
    {
        return $this->checkDocument($document);
    }

    /**
     * Постобработка пакета документов
     *
     * @param SBISDocument $document
     * @param bool $success
     * @throws ModelValidationException
     * @throws \Exception
     */
    protected function afterProcess(SBISDocument $document, $success)
    {
        $dateNow = new DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));

        if ($success) {
            $newState = $document->state;
            switch ($document->state) {
                case SBISDocumentStatus::SENT:
                case SBISDocumentStatus::DELIVERED:
                    switch ($document->external_state) {
                        case SBISDocumentStatus::EXTERNAL_DELIVERED:
                            $newState = SBISDocumentStatus::DELIVERED;
                            break;

                        case SBISDocumentStatus::EXTERNAL_ERROR:
                            $newState = SBISDocumentStatus::SENT_ERROR;
                            break;

                        case SBISDocumentStatus::EXTERNAL_SUCCESS:
                            $newState = SBISDocumentStatus::ACCEPTED;
                            break;

                        case SBISDocumentStatus::EXTERNAL_NEGOTIATED:
                            $newState = SBISDocumentStatus::NEGOTIATED;
                            break;

                        case SBISDocumentStatus::EXTERNAL_ERASED:
                            $newState = SBISDocumentStatus::ERASED;
                            break;
                    }
                    break;

                default:
                    $document->addErrorText(
                        sprintf('Unknown state while fetching: %s', $document->state)
                    );
            }

            $document->setState($newState, $dateNow);
        }

        $document->last_fetched_at = $dateNow->format(DateTimeZoneHelper::DATETIME_FORMAT);

        if (!$document->save()) {
            throw new ModelValidationException($document);
        }
    }
}