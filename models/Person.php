<?php

namespace app\models;

use app\classes\DynamicModel;
use app\classes\model\ActiveRecord;
use app\classes\traits\I18NGetTrait;
use app\classes\validators\ArrayValidator;
use app\exceptions\ModelValidationException;
use ReflectionClass;
use Yii;

/**
 * @property int $id
 * @property string $signature_file_name         Имя файла с оттиском подписи ответственного лица
 * @property string $name_nominative             ФИО в им. п. (виртуальное свойство, зависит от I18N)
 * @property string $name_genitive               ФИО в род. п. (виртуальное свойство, зависит от I18N)
 * @property string $post_nominative             Наименование должности в им. п. (виртуальное свойство, зависит от I18N)
 * @property string $post_genitive               Наименование должности в род. п. (виртуальное свойство, зависит от I18N)
 */
class Person extends ActiveRecord
{

    use I18NGetTrait;

    public $canDelete = true;

    private $langCode = Language::LANGUAGE_DEFAULT;

    // Виртуальные поля для локализации
    private static $_virtualPropertiesI18N = [
        'name_nominative',
        'name_genitive',
        'post_nominative',
        'post_genitive',
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'person';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];
    }


    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['id', 'integer'],
            ['signature_file_name', 'string'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name_nominative' => 'ФИО',
            'name_genitive' => 'ФИО (род. п.)',
            'post_nominative' => 'Должность',
            'post_genitive' => 'Должность (род. п.)',
        ];
    }

    /**
     * @param string $langCode
     * @return array
     */
    public function getI18N($langCode = Language::LANGUAGE_DEFAULT)
    {
        return
            $this->hasMany(PersonI18N::class, ['person_id' => 'id'])
                ->andWhere(['lang_code' => $langCode])
                ->indexBy('field')
                ->all();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name_nominative;
    }

    /**
     * @param string $langCode
     * @return $this
     */
    public function setLanguage($langCode = Language::LANGUAGE_DEFAULT)
    {
        $this->langCode = $langCode;
        return $this;
    }

    /**
     * @param bool|true $runValidation
     * @param string[] $attributesName
     * @return bool
     * @throws \Exception
     */
    public function save($runValidation = true, $attributesName = null)
    {
        parent::save($runValidation, $attributesName);

        $personI18N = DynamicModel::validateData(
            Yii::$app->request->post((new ReflectionClass($this))->getShortName()),
            [
                [self::$_virtualPropertiesI18N, ArrayValidator::class],
            ]
        );

        if ($personI18N->hasErrors()) {
            throw new ModelValidationException($personI18N);
        }

        foreach ($personI18N->attributes as $attribute => $i18nData) {
            foreach ($i18nData as $lang => $value) {
                $transaction = PersonI18N::getDb()->beginTransaction();
                try {
                    $i18n = PersonI18N::findOne([
                        'person_id' => $this->id,
                        'lang_code' => $lang,
                        'field' => $attribute,
                    ]);
                    if (!$i18n) {
                        $i18n = new PersonI18N;
                        $i18n->person_id = $this->id;
                        $i18n->lang_code = $lang;
                        $i18n->field = $attribute;
                    }
                    $i18n->value = $value;
                    $i18n->save();

                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getOldModeInfo()
    {
        return [
            'name' => $this->name_nominative,
            'name_' => $this->name_genitive,
            'position' => $this->post_nominative,
            'position_' => $this->post_genitive,
            'sign' => [
                'src' => str_replace('/images/', '', \Yii::$app->params['SIGNATURE_DIR']) . $this->signature_file_name,
            ]
        ];
    }

}