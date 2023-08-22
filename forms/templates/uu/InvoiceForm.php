<?php
namespace app\forms\templates\uu;

use app\models\Invoice;
use Yii;
use app\classes\Form;
use app\classes\Smarty;
use app\models\Language;
use app\models\Country;
use yii\web\UploadedFile;
use app\models\document\PaymentTemplate;
use app\models\Bill;
use app\models\document\PaymentTemplateType;
use Exception;
use InvalidArgumentException;
use yii\base\InvalidConfigException;

class InvoiceForm extends Form
{

    const STORE_PATH = 'files/invoice_content';
    const TEMPLATE_EXTENSION = 'html';
    const UNIVERSAL_INVOICE_KEY = 'en-EN-universal';

    private $_langCode = Language::LANGUAGE_DEFAULT;
    private $_invoice = null;

    /** @var Bill */
    public $_invoiceProformaBill = null;

    /**
     * @param array|string $langCode
     * @param Invoice $invoice
     * @param Bill $invoice
     */
    public function __construct($langCode = Language::LANGUAGE_DEFAULT, $invoice = null, $_invoiceProformaBill = null)
    {
        parent::__construct();

        $this->_langCode = $langCode;
        $this->_invoice = $invoice;
        $this->_invoiceProformaBill = $_invoiceProformaBill;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->_langCode;
    }

    /**
     * Получить имя файла
     *
     * @return string
     */
    public function getFileName()
    {
        if ($this->_invoiceProformaBill) {
            $template = PaymentTemplate::getDefaultByTypeIdAndCountryCode(PaymentTemplateType::TYPE_INVOICE_PROFORMA, $this->_invoiceProformaBill->clientAccount->contragent->country->code);

            return PaymentForm::getPath() . PaymentForm::getFileName($template->type_id, $template->country->alpha_3, $template->version, self::TEMPLATE_EXTENSION);
        }elseif ($this->_invoice && $this->_invoice->is_reversal) {
            if ($this->_invoice->bill->clientAccount->contragent->lang_code == Language::LANGUAGE_ENGLISH) {
                $template = PaymentTemplate::getDefaultByTypeIdAndCountryCode(PaymentTemplateType::TYPE_INVOICE_STORNO, Country::UNITED_KINGDOM);
            } else {
                $template = PaymentTemplate::getDefaultByTypeIdAndCountryCode(PaymentTemplateType::TYPE_INVOICE_STORNO, $this->_invoice->bill->clientAccount->contragent->country_id);
            }

            if (!$template) {
                if ($this->_invoice->bill->clientAccount->country_id == Country::RUSSIA) {
                    throw new InvalidArgumentException('Шаблон не найден, либо не включен по-умолчанию.');
                }
                throw new InvalidArgumentException('Template either not found or not selected as default.');
            }

            return  PaymentForm::getPath() . PaymentForm::getFileName($template->type_id, $template->country->alpha_3, $template->version, self::TEMPLATE_EXTENSION);
        }

        return self::getPath() . $this->_langCode . '.' . self::TEMPLATE_EXTENSION;
    }

    /**
     * @return bool
     */
    public function fileExists()
    {
        return file_exists($this->getFileName());
    }

    /**
     * @return bool|string
     */
    public function getFile()
    {
        if ($this->fileExists()) {
            return file_get_contents($this->getFileName());
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        // Сохранение универсального шаблона
        $this->_storeFile(self::UNIVERSAL_INVOICE_KEY);

        // Сохранение языковых шаблонов
        foreach (Language::getList() as $languageCode => $languageTitle) {
            $this->_storeFile($languageCode);
        }
    }

    /**
     * @return string
     */
    public static function getPath()
    {
        return Yii::$app->params['STORE_PATH'] . self::STORE_PATH . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $key
     */
    private function _storeFile($key)
    {
        /** @var UploadedFile $file */
        $file = UploadedFile::getInstance($this, 'filename[' . $key . ']');

        if (!is_null($file) && $file->getExtension() !== self::TEMPLATE_EXTENSION) {
            Yii::$app->session->addFlash('error', 'Шаблон ' . $key . ' должен быть с расширением ' . self::TEMPLATE_EXTENSION . '<br />');
            return;
        }

        if ($file && $file->saveAs(self::getPath() . $key . '.' . $file->extension, $deleteTempFile = true)) {
            $content = preg_replace_callback(
                '#\{[^\}]+\}#',
                function ($matches) {
                    return preg_replace('#&[^;]+;#', '', strip_tags($matches[0]));
                },
                (new self($key))->getFile()
            );

            try {
                $smarty = Smarty::init();
                $smarty->fetch('string:' . $content);
            } catch (\Exception $e) {
                Yii::$app->session->addFlash('error', 'Шаблон ' . $key . ' не может быть преобразован<br />' . $e->getMessage());
            }
        }
    }
}
