<?php

namespace app\forms\templates\uu;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Country;
use app\models\dictionary\PublicSite;
use app\models\document\PaymentTemplate;
use app\models\document\PaymentTemplateType;
use DateTime;
use DateTimeZone;
use Yii;
use app\classes\Form;
use yii\base\InvalidArgumentException;
use yii\db\Exception;
use yii\web\UploadedFile;

class PaymentForm extends Form
{
    const STORE_PATH = 'files/payment_templates';
    const FILENAME_PATTERN = 'type-%d-country-%s-version-%d.%s';
    const TEMPLATE_EXTENSION = 'html';

    public $id;

    /** @var PaymentTemplateType */
    protected $type;
    /** @var Country */
    protected $country;

    /** @var PaymentTemplate */
    protected $templateDefault;

    /** @var PaymentTemplate */
    protected $template;

    /**
     * PaymentForm constructor.
     * @param int $typeId
     * @param int $countryCode
     */
    public function __construct($typeId, $countryCode = Country::RUSSIA)
    {
        parent::__construct();

        $this->type = PaymentTemplateType::findOne(['id' => $typeId]);
        $this->country = Country::findOne([Country::$primaryField => $countryCode]);
    }

    /**
     * @return int
     */
    public function getTypeId()
    {
        return $this->type->id;
    }

    /**
     * @return int
     */
    public function getCountryCode()
    {
        return $this->country->code;
    }

    /**
     * Get current template
     *
     * @return PaymentTemplate|null
     */
    public function getTemplate()
    {
        if (is_null($this->template)) {
            if (!is_null($this->id)) {
                if ($this->template = PaymentTemplate::findOne(['id' => $this->id]) ) {
                    $this->type = $this->template->type;
                    $this->country = $this->template->country;
                }
            }
        }

        return $this->template;
    }

    /**
     * Get default template
     *
     * @return PaymentTemplate|null
     */
    public function getTemplateDefault()
    {
        if (is_null($this->templateDefault)) {
            $this->templateDefault = PaymentTemplate::getDefaultByTypeIdAndCountryCode($this->getTypeId(), $this->getCountryCode());
        }

        return $this->templateDefault;
    }

    /**
     * Получить полный путь к файлу
     *
     * @param PaymentTemplate $newTemplate
     * @param string $fileExtension
     * @return string
     */
    public function getFullFileName(PaymentTemplate $newTemplate, $fileExtension = null)
    {
        if (is_null($fileExtension)) {
            $fileExtension = self::TEMPLATE_EXTENSION;
        }

        $name = self:: getFileName($newTemplate->type_id, $newTemplate->country->alpha_3, $newTemplate->version, $fileExtension);

        return self::getPath() . $name;
    }

    /**
     * Получить имя файла
     *
     * @param int $typeId
     * @param string $countryCode
     * @param int $version
     * @param string $fileExtension
     * @return string
     */
    public static function getFileName($typeId, $countryCode, $version, $fileExtension)
    {
        return sprintf(self::FILENAME_PATTERN, $typeId, $countryCode, $version, $fileExtension);
    }

    /**
     * @return string
     */
    public static function getPath()
    {
        return Yii::$app->params['STORE_PATH'] . self::STORE_PATH . DIRECTORY_SEPARATOR;
    }

    /**
     * Set template default
     *
     * @param int $id
     * @return int
     * @throws Exception
     */
    public static function setDefault($id)
    {
        if (!($model = PaymentTemplate::findOne(['id' => $id]))) {
            throw new InvalidArgumentException('Неверный шаблон');
        }

        $transaction = PaymentTemplate::getDb()->beginTransaction();
        try {
            PaymentTemplate::updateAll(
                ['is_default' => 0],
                'type_id = ' . $model->type_id .
                ' AND country_code = ' . $model->country_code .
                ' AND id <>' . $id
            );

            /** @var PaymentTemplate $model */
            $model->is_default = 1;
            if (!$model->save()) {
                throw new ModelValidationException($model);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $id;
    }

    /**
     * Disable template
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     */
    public static function delete($id)
    {
        if (!($model = PaymentTemplate::findOne(['id' => $id]))) {
            throw new InvalidArgumentException('Неверный шаблон');
        }

        /** @var PaymentTemplate $model */
        if ($model->is_default) {
            throw new InvalidArgumentException(
                'Шаблон для страны ' . $model->country->name . ' не может быть удалён, так он выставлен по умолчанию.'
            );
        }

        $model->is_active = 0;
        if (!$model->save()) {
            throw new ModelValidationException($model);
        }

        $defaultTemplate = PaymentTemplate::getDefaultByTypeIdAndCountryCode($model->type_id, $model->country_code);

        return $defaultTemplate->id;
    }

    /**
     * Restore template
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     */
    public static function restore($id)
    {
        if (!($model = PaymentTemplate::findOne(['id' => $id]))) {
            throw new InvalidArgumentException('Неверный шаблон');
        }

        /** @var PaymentTemplate $model */
        $model->is_active = 1;
        if (!$model->save()) {
            throw new ModelValidationException($model);
        }

        return $id;
    }

    /**
     * Save all templates
     *
     * @throws ModelValidationException
     */
    public function save()
    {
        // Сохранение шаблонов для стран
        foreach (PublicSite::getAllWithCountries() as $publicSite) {
            $this->storeTemplate($publicSite->countryFirst);
        }

        return true;
    }

    /**
     * Store template
     *
     * @param Country $country
     * @return bool
     * @throws ModelValidationException
     */
    protected function storeTemplate(Country $country)
    {
        $typeId = $this->getTypeId();
        $countryCode = $country->code;

        /** @var UploadedFile $file */
        $file = UploadedFile::getInstance($this, 'filename[' . $countryCode . ']');

        if (is_null($file)) {
            return false;
        }

        if ($file->getExtension() !== self::TEMPLATE_EXTENSION) {
            Yii::$app->session->addFlash('error', 'Шаблон для страны ' . $country->name . ' должен быть с расширением ' . self::TEMPLATE_EXTENSION . '<br />');
            return false;
        }

        try {
            $newTemplate = $this->prepareNewTemplate($typeId, $countryCode);
            $content = $this->getTemplateContent($file, $newTemplate);
            $this->saveNewTemplate($newTemplate, $content);

            Yii::$app->session->addFlash('success', 'Шаблон для страны ' . $country->name . ' сохранён');
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', 'Ошибка при сохранении шаблона для страны ' . $country->name . '<br />' . $e->getMessage());
            return false;
        }

        $this->template = $newTemplate;
        return true;
    }

    /**
     * Prepare new template
     *
     * @param int $typeId
     * @param int $countryCode
     * @return PaymentTemplate
     */
    protected function prepareNewTemplate($typeId, $countryCode)
    {
        $lastTemplate = PaymentTemplate::getLastVersionByTypeIdAndCountryCode($typeId, $countryCode);

        $newTemplate = new PaymentTemplate();
        $newTemplate->type_id = $typeId;
        $newTemplate->country_code = $countryCode;
        $newTemplate->is_active = 1;
        $newTemplate->is_default = 0;
        if ($lastTemplate) {
            // TODO: lock while reading version
            $newTemplate->version = $lastTemplate->version + 1;
        } else {
            $newTemplate->version = 1;
            $newTemplate->is_default = 1;
        }

        return $newTemplate;
    }

    /**
     *
     *
     * @param UploadedFile $file
     * @param PaymentTemplate $newTemplate
     * @return string
     */
    protected function getTemplateContent(UploadedFile $file, PaymentTemplate $newTemplate)
    {
        $content = '';

        $fileName = $this->getFullFileName($newTemplate, $file->extension);
        if ($file->saveAs($fileName, $deleteTempFile = true)) {
            $content = preg_replace_callback(
                '#\{[^\}]+\}#',
                function ($matches) {
                    return preg_replace('#&[^;]+;#', '', strip_tags($matches[0]));
                },
                file_get_contents($fileName)
            );
        }

        return $content;
    }

    /**
     * Save new template
     *
     * @param PaymentTemplate $newTemplate
     * @param $content
     * @throws ModelValidationException
     */
    protected function saveNewTemplate(PaymentTemplate $newTemplate, $content)
    {
        $newTemplate->content = $content;
        $newTemplate->created_at = new DateTime('now', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW));
        if (!$newTemplate->save()) {
            throw new ModelValidationException($newTemplate);
        }
    }

}