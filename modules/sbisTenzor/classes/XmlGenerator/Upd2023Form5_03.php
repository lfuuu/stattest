<?php

namespace app\modules\sbisTenzor\classes\XmlGenerator;

class Upd2023Form5_03 extends Invoice2025Form5_03
{
    /** @var string */
    protected $xsdFile = 'upd_2023-1115131_5_03.xsd';


    /**
     * Создает свойство Файл.Документ
     *
     * @param \DOMDocument $dom
     * @return \DOMElement
     */
    protected function createElementDocument(\DOMDocument $dom)
    {
        /*
        <Документ
            ВремИнфПр="08.50.38"
            ДатаИнфПр="10.06.2024"
            КНД="1115131"
            НаимЭконСубСост="ООО "Наша компания""

            НаимДокОпр="Документ об отгрузке товаров (выполнении работ), передаче имущественных прав (документ об оказании услуг)"
            ПоФактХЖ="Документ об отгрузке товаров (выполнении работ), передаче имущественных прав (документ об оказании услуг)"
            Функция="ДОП"
        >
        */

        $docName = "Документ об отгрузке товаров (выполнении работ), передаче имущественных прав (документ об оказании услуг)";
        $elDoc = parent::createElementDocument($dom);
        $elDoc->setAttribute('НаимДокОпр', $docName);
        $elDoc->setAttribute('ПоФактХЖ', $docName);
        $elDoc->setAttribute('Функция', 'ДОП');

        return $elDoc;
    }
}