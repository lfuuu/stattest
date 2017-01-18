<?php
use app\models\Country;

/**
 * Class m170116_163202_alter_country
 */
class m170116_163202_alter_country extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $countryTableName = Country::tableName();
        $this->addColumn($countryTableName, 'name_rus', $this->string(255));
        $this->addColumn($countryTableName, 'name_rus_full', $this->string(255));

        $this->_fillNameRus();
        $this->_fillPrefix();
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $countryTableName = Country::tableName();
        $this->dropColumn($countryTableName, 'name_rus');
        $this->dropColumn($countryTableName, 'name_rus_full');
    }

    /**
     * Заполнить name
     */
    private function _fillNameRus()
    {
        /**
         * @link http://www.classbase.ru/download
         *
         * UPDATE class_country SET name = CONCAT(UPPER(SUBSTRING(name, 1, 1)), LOWER(SUBSTRING(name FROM 2)));
         *
         * UPDATE class_country SET name = 'Американское Самоа' WHERE id = 5;
         * UPDATE class_country SET name = 'Антигуа и Барбуда' WHERE id = 8;
         * UPDATE class_country SET name = 'Боливия' WHERE id = 21;
         * UPDATE class_country SET name = 'Босния и Герцеговина' WHERE id = 22;
         * UPDATE class_country SET name = 'Остров Буве' WHERE id = 24;
         * UPDATE class_country SET name = 'Британская территория в Индийском океане' WHERE id = 27;
         * UPDATE class_country SET name = 'Британские Виргинские острова' WHERE id = 29;
         * UPDATE class_country SET name = 'Бруней-Даруссалам' WHERE id = 30;
         * UPDATE class_country SET name = 'Кабо-Верде' WHERE id = 38;
         * UPDATE class_country SET name = 'Острова Кайман' WHERE id = 39;
         * UPDATE class_country SET name = 'ЦАР', full_name = 'Центрально-африканская республика' WHERE id = 40;
         * UPDATE class_country SET name = 'Шри-Ланка' WHERE id = 41;
         * UPDATE class_country SET name = 'Тайвань' WHERE id = 45;
         * UPDATE class_country SET name = 'Остров Рождества' WHERE id = 46;
         * UPDATE class_country SET name = 'Кокосовые (Килинг) острова' WHERE id = 47;
         * UPDATE class_country SET name = 'Острова Кука' WHERE id = 53;
         * UPDATE class_country SET name = 'Коста-Рика' WHERE id = 54;
         * UPDATE class_country SET name = 'Чехия', full_name = 'Чешская республика' WHERE id = 58;
         * UPDATE class_country SET name = 'Доминикана', full_name = 'Доминиканская республика' WHERE id = 62;
         * UPDATE class_country SET name = 'Эль-Сальвадор' WHERE id = 64;
         * UPDATE class_country SET name = 'Экваториальная Гвинея' WHERE id = 65;
         * UPDATE class_country SET name = 'Фолклендские острова (Мальвинские)' WHERE id = 70;
         * UPDATE class_country SET name = 'Южная Джорджия и Южные Сандвичевы острова' WHERE id = 71;
         * UPDATE class_country SET name = 'Французская Гвиана' WHERE id = 76;
         * UPDATE class_country SET name = 'Французская Полинезия' WHERE id = 77;
         * UPDATE class_country SET name = 'Палестина' WHERE id = 83;
         * UPDATE class_country SET name = 'Остров Херд и острова Макдональд' WHERE id = 97;
         * UPDATE class_country SET name = 'Ватикан' WHERE id = 98;
         * UPDATE class_country SET name = 'Иран' WHERE id = 105;
         * UPDATE class_country SET name = "Кот д Ивуар" WHERE id = 110;
         * UPDATE class_country SET name = 'КНДР' WHERE id = 116;
         * UPDATE class_country SET name = 'Южная Корея' WHERE id = 117;
         * UPDATE class_country SET name = 'Лаос', full_name = 'Лаосская народно-демократическая республика' WHERE id = 120;
         * UPDATE class_country SET name = 'Молдова' WHERE id = 142;
         * UPDATE class_country SET name = 'Сен-Мартен', full_name = 'Сен-Мартен (Нидерландская часть)' WHERE id = 154;
         * UPDATE class_country SET name = 'Бонэйр, Синт-Эстатиус и Саба' WHERE id = 155;
         * UPDATE class_country SET name = 'Новая Каледония' WHERE id = 156;
         * UPDATE class_country SET name = 'Новая Зеландия' WHERE id = 158;
         * UPDATE class_country SET name = 'Остров Норфолк' WHERE id = 163;
         * UPDATE class_country SET name = 'Северные Марианские острова' WHERE id = 165;
         * UPDATE class_country SET name = 'Малые Тихоокеанские отдаленные острова США' WHERE id = 166;
         * UPDATE class_country SET name = 'Микронезия' WHERE id = 167;
         * UPDATE class_country SET name = 'Папуа Новая Гвинея' WHERE id = 172;
         * UPDATE class_country SET name = 'Гвинея-Бисау' WHERE id = 179;
         * UPDATE class_country SET name = 'Тимор-Лесте' WHERE id = 180;
         * UPDATE class_country SET name = 'Пуэрто-Рико' WHERE id = 181;
         * UPDATE class_country SET name = 'Сен-Бартелеми' WHERE id = 187;
         * UPDATE class_country SET name = 'Святая Елена, остров Вознесения, Тристан-Да-Кунья' WHERE id = 188;
         * UPDATE class_country SET name = 'Сент-Китс и Невис' WHERE id = 189;
         * UPDATE class_country SET name = 'Сент-Люсия' WHERE id = 191;
         * UPDATE class_country SET name = 'Сен-Мартен' WHERE id = 192;
         * UPDATE class_country SET name = 'Сен-Пьер и Микелон' WHERE id = 193;
         * UPDATE class_country SET name = 'Сент-Винсент и Гренадины' WHERE id = 194;
         * UPDATE class_country SET name = 'Сан-Марино' WHERE id = 195;
         * UPDATE class_country SET name = 'Сан-Томе и Принсипи' WHERE id = 196;
         * UPDATE class_country SET name = 'Саудовская Аравия' WHERE id = 197;
         * UPDATE class_country SET name = 'Сьерра-Леоне' WHERE id = 201;
         * UPDATE class_country SET name = 'ЮАР' WHERE id = 207;
         * UPDATE class_country SET name = 'Южный Судан' WHERE id = 210;
         * UPDATE class_country SET name = 'Западная Сахара' WHERE id = 212;
         * UPDATE class_country SET name = 'Шпицберген и Ян Майен' WHERE id = 214;
         * UPDATE class_country SET name = 'Сирия', full_name = 'Сирийская арабская республика' WHERE id = 218;
         * UPDATE class_country SET name = 'Тринидад и Тобаго' WHERE id = 224;
         * UPDATE class_country SET name = 'ОАЭ', full_name = 'Объединенные Арабские Эмираты' WHERE id = 225;
         * UPDATE class_country SET name = 'Острова Теркс и Кайкос' WHERE id = 229;
         * UPDATE class_country SET name = 'Македония', full_name = 'Республика Македония' WHERE id = 233;
         * UPDATE class_country SET name = 'Великобритания' WHERE id = 235;
         * UPDATE class_country SET name = 'Остров Мэн' WHERE id = 238;
         * UPDATE class_country SET name = 'Танзания' WHERE id = 239;
         * UPDATE class_country SET name = 'США' WHERE id = 240;
         * UPDATE class_country SET name = 'Виргинские острова, США' WHERE id = 241;
         * UPDATE class_country SET name = 'Буркина-Фасо' WHERE id = 242;
         * UPDATE class_country SET name = 'Венесуэла' WHERE id = 245;
         * UPDATE class_country SET name = 'Уоллис и Футуна' WHERE id = 246;
         * UPDATE class_country SET name = 'Южная Осетия' WHERE id = 251;
         *
         * UPDATE class_country SET full_name = name WHERE full_name is null;
         *
         * SELECT CONCAT('UPDATE country SET name_rus = "' , name, '", name_rus_full = "', full_name, '" WHERE code = ', number_code, ';') FROM class_country;
         */
        $sqls = [
            'UPDATE country SET name_rus = "Афганистан", name_rus_full = "Переходное Исламское Государство Афганистан" WHERE code = 004',
            'UPDATE country SET name_rus = "Албания", name_rus_full = "Республика Албания" WHERE code = 008',
            'UPDATE country SET name_rus = "Антарктида", name_rus_full = "Антарктида" WHERE code = 010',
            'UPDATE country SET name_rus = "Алжир", name_rus_full = "Алжирская Народная Демократическая Республика" WHERE code = 012',
            'UPDATE country SET name_rus = "Американское Самоа", name_rus_full = "Американское Самоа" WHERE code = 016',
            'UPDATE country SET name_rus = "Андорра", name_rus_full = "Княжество Андорра" WHERE code = 020',
            'UPDATE country SET name_rus = "Ангола", name_rus_full = "Республика Ангола" WHERE code = 024',
            'UPDATE country SET name_rus = "Антигуа и Барбуда", name_rus_full = "Антигуа и Барбуда" WHERE code = 028',
            'UPDATE country SET name_rus = "Азербайджан", name_rus_full = "Республика Азербайджан" WHERE code = 031',
            'UPDATE country SET name_rus = "Аргентина", name_rus_full = "Аргентинская Республика" WHERE code = 032',
            'UPDATE country SET name_rus = "Австралия", name_rus_full = "Австралия" WHERE code = 036',
            'UPDATE country SET name_rus = "Австрия", name_rus_full = "Австрийская Республика" WHERE code = 040',
            'UPDATE country SET name_rus = "Багамы", name_rus_full = "Содружество Багамы" WHERE code = 044',
            'UPDATE country SET name_rus = "Бахрейн", name_rus_full = "Королевство Бахрейн" WHERE code = 048',
            'UPDATE country SET name_rus = "Бангладеш", name_rus_full = "Народная Республика Бангладеш" WHERE code = 050',
            'UPDATE country SET name_rus = "Армения", name_rus_full = "Республика Армения" WHERE code = 051',
            'UPDATE country SET name_rus = "Барбадос", name_rus_full = "Барбадос" WHERE code = 052',
            'UPDATE country SET name_rus = "Бельгия", name_rus_full = "Королевство Бельгии" WHERE code = 056',
            'UPDATE country SET name_rus = "Бермуды", name_rus_full = "Бермуды" WHERE code = 060',
            'UPDATE country SET name_rus = "Бутан", name_rus_full = "Королевство Бутан" WHERE code = 064',
            'UPDATE country SET name_rus = "Боливия", name_rus_full = "Многонациональное Государство Боливия" WHERE code = 068',
            'UPDATE country SET name_rus = "Босния и Герцеговина", name_rus_full = "Босния и Герцеговина" WHERE code = 070',
            'UPDATE country SET name_rus = "Ботсвана", name_rus_full = "Республика Ботсвана" WHERE code = 072',
            'UPDATE country SET name_rus = "Остров Буве", name_rus_full = "Остров Буве" WHERE code = 074',
            'UPDATE country SET name_rus = "Бразилия", name_rus_full = "Федеративная Республика Бразилия" WHERE code = 076',
            'UPDATE country SET name_rus = "Белиз", name_rus_full = "Белиз" WHERE code = 084',
            'UPDATE country SET name_rus = "Британская территория в Индийском океане", name_rus_full = "Британская территория в Индийском океане" WHERE code = 086',
            'UPDATE country SET name_rus = "Соломоновы острова", name_rus_full = "Соломоновы острова" WHERE code = 090',
            'UPDATE country SET name_rus = "Британские Виргинские острова", name_rus_full = "Британские Виргинские острова" WHERE code = 092',
            'UPDATE country SET name_rus = "Бруней-Даруссалам", name_rus_full = "Бруней-Даруссалам" WHERE code = 096',
            'UPDATE country SET name_rus = "Болгария", name_rus_full = "Республика Болгария" WHERE code = 100',
            'UPDATE country SET name_rus = "Мьянма", name_rus_full = "Республика Союза Мьянма" WHERE code = 104',
            'UPDATE country SET name_rus = "Бурунди", name_rus_full = "Республика Бурунди" WHERE code = 108',
            'UPDATE country SET name_rus = "Беларусь", name_rus_full = "Республика Беларусь" WHERE code = 112',
            'UPDATE country SET name_rus = "Камбоджа", name_rus_full = "Королевство Камбоджа" WHERE code = 116',
            'UPDATE country SET name_rus = "Камерун", name_rus_full = "Республика Камерун" WHERE code = 120',
            'UPDATE country SET name_rus = "Канада", name_rus_full = "Канада" WHERE code = 124',
            'UPDATE country SET name_rus = "Кабо-Верде", name_rus_full = "Республика Кабо-Верде" WHERE code = 132',
            'UPDATE country SET name_rus = "Острова Кайман", name_rus_full = "Острова Кайман" WHERE code = 136',
            'UPDATE country SET name_rus = "ЦАР", name_rus_full = "Центрально-африканская республика" WHERE code = 140',
            'UPDATE country SET name_rus = "Шри-Ланка", name_rus_full = "Демократическая Социалистическая Республика Шри-Ланка" WHERE code = 144',
            'UPDATE country SET name_rus = "Чад", name_rus_full = "Республика Чад" WHERE code = 148',
            'UPDATE country SET name_rus = "Чили", name_rus_full = "Республика Чили" WHERE code = 152',
            'UPDATE country SET name_rus = "Китай", name_rus_full = "Китайская Народная Республика" WHERE code = 156',
            'UPDATE country SET name_rus = "Тайвань", name_rus_full = "Тайвань" WHERE code = 158',
            'UPDATE country SET name_rus = "Остров Рождества", name_rus_full = "Остров Рождества" WHERE code = 162',
            'UPDATE country SET name_rus = "Кокосовые (Килинг) острова", name_rus_full = "Кокосовые (Килинг) острова" WHERE code = 166',
            'UPDATE country SET name_rus = "Колумбия", name_rus_full = "Республика Колумбия" WHERE code = 170',
            'UPDATE country SET name_rus = "Коморы", name_rus_full = "Союз Коморы" WHERE code = 174',
            'UPDATE country SET name_rus = "Майотта", name_rus_full = "Майотта" WHERE code = 175',
            'UPDATE country SET name_rus = "Конго", name_rus_full = "Республика Конго" WHERE code = 178',
            'UPDATE country SET name_rus = "Конго, демократическая республика", name_rus_full = "Демократическая Республика Конго" WHERE code = 180',
            'UPDATE country SET name_rus = "Острова Кука", name_rus_full = "Острова Кука" WHERE code = 184',
            'UPDATE country SET name_rus = "Коста-Рика", name_rus_full = "Республика Коста-Рика" WHERE code = 188',
            'UPDATE country SET name_rus = "Хорватия", name_rus_full = "Республика Хорватия" WHERE code = 191',
            'UPDATE country SET name_rus = "Куба", name_rus_full = "Республика Куба" WHERE code = 192',
            'UPDATE country SET name_rus = "Кипр", name_rus_full = "Республика Кипр" WHERE code = 196',
            'UPDATE country SET name_rus = "Чехия", name_rus_full = "Чешская республика" WHERE code = 203',
            'UPDATE country SET name_rus = "Бенин", name_rus_full = "Республика Бенин" WHERE code = 204',
            'UPDATE country SET name_rus = "Дания", name_rus_full = "Королевство Дания" WHERE code = 208',
            'UPDATE country SET name_rus = "Доминика", name_rus_full = "Содружество Доминики" WHERE code = 212',
            'UPDATE country SET name_rus = "Доминикана", name_rus_full = "Доминиканская республика" WHERE code = 214',
            'UPDATE country SET name_rus = "Эквадор", name_rus_full = "Республика Эквадор" WHERE code = 218',
            'UPDATE country SET name_rus = "Эль-Сальвадор", name_rus_full = "Республика Эль-Сальвадор" WHERE code = 222',
            'UPDATE country SET name_rus = "Экваториальная Гвинея", name_rus_full = "Республика Экваториальная Гвинея" WHERE code = 226',
            'UPDATE country SET name_rus = "Эфиопия", name_rus_full = "Федеративная Демократическая Республика Эфиопия" WHERE code = 231',
            'UPDATE country SET name_rus = "Эритрея", name_rus_full = "Государство Эритрея" WHERE code = 232',
            'UPDATE country SET name_rus = "Эстония", name_rus_full = "Эстонская Республика" WHERE code = 233',
            'UPDATE country SET name_rus = "Фарерские острова", name_rus_full = "Фарерские острова" WHERE code = 234',
            'UPDATE country SET name_rus = "Фолклендские острова (Мальвинские)", name_rus_full = "Фолклендские острова (Мальвинские)" WHERE code = 238',
            'UPDATE country SET name_rus = "Южная Джорджия и Южные Сандвичевы острова", name_rus_full = "Южная Джорджия и Южные Сандвичевы острова" WHERE code = 239',
            'UPDATE country SET name_rus = "Фиджи", name_rus_full = "Республика Фиджи" WHERE code = 242',
            'UPDATE country SET name_rus = "Финляндия", name_rus_full = "Финляндская Республика" WHERE code = 246',
            'UPDATE country SET name_rus = "Эландские острова", name_rus_full = "Эландские острова" WHERE code = 248',
            'UPDATE country SET name_rus = "Франция", name_rus_full = "Французская Республика" WHERE code = 250',
            'UPDATE country SET name_rus = "Французская Гвиана", name_rus_full = "Французская Гвиана" WHERE code = 254',
            'UPDATE country SET name_rus = "Французская Полинезия", name_rus_full = "Французская Полинезия" WHERE code = 258',
            'UPDATE country SET name_rus = "Французские южные территории", name_rus_full = "Французские южные территории" WHERE code = 260',
            'UPDATE country SET name_rus = "Джибути", name_rus_full = "Республика Джибути" WHERE code = 262',
            'UPDATE country SET name_rus = "Габон", name_rus_full = "Габонская Республика" WHERE code = 266',
            'UPDATE country SET name_rus = "Грузия", name_rus_full = "Грузия" WHERE code = 268',
            'UPDATE country SET name_rus = "Гамбия", name_rus_full = "Республика Гамбия" WHERE code = 270',
            'UPDATE country SET name_rus = "Палестина", name_rus_full = "Государство Палестина" WHERE code = 275',
            'UPDATE country SET name_rus = "Германия", name_rus_full = "Федеративная Республика Германия" WHERE code = 276',
            'UPDATE country SET name_rus = "Гана", name_rus_full = "Республика Гана" WHERE code = 288',
            'UPDATE country SET name_rus = "Гибралтар", name_rus_full = "Гибралтар" WHERE code = 292',
            'UPDATE country SET name_rus = "Кирибати", name_rus_full = "Республика Кирибати" WHERE code = 296',
            'UPDATE country SET name_rus = "Греция", name_rus_full = "Греческая Республика" WHERE code = 300',
            'UPDATE country SET name_rus = "Гренландия", name_rus_full = "Гренландия" WHERE code = 304',
            'UPDATE country SET name_rus = "Гренада", name_rus_full = "Гренада" WHERE code = 308',
            'UPDATE country SET name_rus = "Гваделупа", name_rus_full = "Гваделупа" WHERE code = 312',
            'UPDATE country SET name_rus = "Гуам", name_rus_full = "Гуам" WHERE code = 316',
            'UPDATE country SET name_rus = "Гватемала", name_rus_full = "Республика Гватемала" WHERE code = 320',
            'UPDATE country SET name_rus = "Гвинея", name_rus_full = "Гвинейская Республика" WHERE code = 324',
            'UPDATE country SET name_rus = "Гайана", name_rus_full = "Республика Гайана" WHERE code = 328',
            'UPDATE country SET name_rus = "Гаити", name_rus_full = "Республика Гаити" WHERE code = 332',
            'UPDATE country SET name_rus = "Остров Херд и острова Макдональд", name_rus_full = "Остров Херд и острова Макдональд" WHERE code = 334',
            'UPDATE country SET name_rus = "Ватикан", name_rus_full = "Ватикан" WHERE code = 336',
            'UPDATE country SET name_rus = "Гондурас", name_rus_full = "Республика Гондурас" WHERE code = 340',
            'UPDATE country SET name_rus = "Гонконг", name_rus_full = "Специальный административный регион Китая Гонконг" WHERE code = 344',
            'UPDATE country SET name_rus = "Венгрия", name_rus_full = "Венгрия" WHERE code = 348',
            'UPDATE country SET name_rus = "Исландия", name_rus_full = "Республика Исландия" WHERE code = 352',
            'UPDATE country SET name_rus = "Индия", name_rus_full = "Республика Индия" WHERE code = 356',
            'UPDATE country SET name_rus = "Индонезия", name_rus_full = "Республика Индонезия" WHERE code = 360',
            'UPDATE country SET name_rus = "Иран", name_rus_full = "Исламская Республика Иран" WHERE code = 364',
            'UPDATE country SET name_rus = "Ирак", name_rus_full = "Республика Ирак" WHERE code = 368',
            'UPDATE country SET name_rus = "Ирландия", name_rus_full = "Ирландия" WHERE code = 372',
            'UPDATE country SET name_rus = "Израиль", name_rus_full = "Государство Израиль" WHERE code = 376',
            'UPDATE country SET name_rus = "Италия", name_rus_full = "Итальянская Республика" WHERE code = 380',
            'UPDATE country SET name_rus = "Кот д Ивуар", name_rus_full = "Республика Кот д Ивуар" WHERE code = 384',
            'UPDATE country SET name_rus = "Ямайка", name_rus_full = "Ямайка" WHERE code = 388',
            'UPDATE country SET name_rus = "Япония", name_rus_full = "Япония" WHERE code = 392',
            'UPDATE country SET name_rus = "Казахстан", name_rus_full = "Республика Казахстан" WHERE code = 398',
            'UPDATE country SET name_rus = "Иордания", name_rus_full = "Иорданское Хашимитское Королевство" WHERE code = 400',
            'UPDATE country SET name_rus = "Кения", name_rus_full = "Республика Кения" WHERE code = 404',
            'UPDATE country SET name_rus = "КНДР", name_rus_full = "Корейская Народно-Демократическая Республика" WHERE code = 408',
            'UPDATE country SET name_rus = "Южная Корея", name_rus_full = "Республика Корея" WHERE code = 410',
            'UPDATE country SET name_rus = "Кувейт", name_rus_full = "Государство Кувейт" WHERE code = 414',
            'UPDATE country SET name_rus = "Киргизия", name_rus_full = "Киргизская Республика" WHERE code = 417',
            'UPDATE country SET name_rus = "Лаос", name_rus_full = "Лаосская народно-демократическая республика" WHERE code = 418',
            'UPDATE country SET name_rus = "Ливан", name_rus_full = "Ливанская Республика" WHERE code = 422',
            'UPDATE country SET name_rus = "Лесото", name_rus_full = "Королевство Лесото" WHERE code = 426',
            'UPDATE country SET name_rus = "Латвия", name_rus_full = "Латвийская Республика" WHERE code = 428',
            'UPDATE country SET name_rus = "Либерия", name_rus_full = "Республика Либерия" WHERE code = 430',
            'UPDATE country SET name_rus = "Ливия", name_rus_full = "Ливия" WHERE code = 434',
            'UPDATE country SET name_rus = "Лихтенштейн", name_rus_full = "Княжество Лихтенштейн" WHERE code = 438',
            'UPDATE country SET name_rus = "Литва", name_rus_full = "Литовская Республика" WHERE code = 440',
            'UPDATE country SET name_rus = "Люксембург", name_rus_full = "Великое Герцогство Люксембург" WHERE code = 442',
            'UPDATE country SET name_rus = "Макао", name_rus_full = "Специальный административный регион Китая Макао" WHERE code = 446',
            'UPDATE country SET name_rus = "Мадагаскар", name_rus_full = "Республика Мадагаскар" WHERE code = 450',
            'UPDATE country SET name_rus = "Малави", name_rus_full = "Республика Малави" WHERE code = 454',
            'UPDATE country SET name_rus = "Малайзия", name_rus_full = "Малайзия" WHERE code = 458',
            'UPDATE country SET name_rus = "Мальдивы", name_rus_full = "Мальдивская Республика" WHERE code = 462',
            'UPDATE country SET name_rus = "Мали", name_rus_full = "Республика Мали" WHERE code = 466',
            'UPDATE country SET name_rus = "Мальта", name_rus_full = "Республика Мальта" WHERE code = 470',
            'UPDATE country SET name_rus = "Мартиника", name_rus_full = "Мартиника" WHERE code = 474',
            'UPDATE country SET name_rus = "Мавритания", name_rus_full = "Исламская Республика Мавритания" WHERE code = 478',
            'UPDATE country SET name_rus = "Маврикий", name_rus_full = "Республика Маврикий" WHERE code = 480',
            'UPDATE country SET name_rus = "Мексика", name_rus_full = "Мексиканские Соединенные Штаты" WHERE code = 484',
            'UPDATE country SET name_rus = "Монако", name_rus_full = "Княжество Монако" WHERE code = 492',
            'UPDATE country SET name_rus = "Монголия", name_rus_full = "Монголия" WHERE code = 496',
            'UPDATE country SET name_rus = "Молдова", name_rus_full = "Республика Молдова" WHERE code = 498',
            'UPDATE country SET name_rus = "Черногория", name_rus_full = "Черногория" WHERE code = 499',
            'UPDATE country SET name_rus = "Монтсеррат", name_rus_full = "Монтсеррат" WHERE code = 500',
            'UPDATE country SET name_rus = "Марокко", name_rus_full = "Королевство Марокко" WHERE code = 504',
            'UPDATE country SET name_rus = "Мозамбик", name_rus_full = "Республика Мозамбик" WHERE code = 508',
            'UPDATE country SET name_rus = "Оман", name_rus_full = "Султанат Оман" WHERE code = 512',
            'UPDATE country SET name_rus = "Намибия", name_rus_full = "Республика Намибия" WHERE code = 516',
            'UPDATE country SET name_rus = "Науру", name_rus_full = "Республика Науру" WHERE code = 520',
            'UPDATE country SET name_rus = "Непал", name_rus_full = "Федеративная Демократическая Республика Непал" WHERE code = 524',
            'UPDATE country SET name_rus = "Нидерланды", name_rus_full = "Королевство Нидерландов" WHERE code = 528',
            'UPDATE country SET name_rus = "Кюрасао", name_rus_full = "Кюрасао" WHERE code = 531',
            'UPDATE country SET name_rus = "Аруба", name_rus_full = "Аруба" WHERE code = 533',
            'UPDATE country SET name_rus = "Сен-Мартен", name_rus_full = "Сен-Мартен (Нидерландская часть)" WHERE code = 534',
            'UPDATE country SET name_rus = "Бонэйр, Синт-Эстатиус и Саба", name_rus_full = "Бонэйр, Синт-Эстатиус и Саба" WHERE code = 535',
            'UPDATE country SET name_rus = "Новая Каледония", name_rus_full = "Новая Каледония" WHERE code = 540',
            'UPDATE country SET name_rus = "Вануату", name_rus_full = "Республика Вануату" WHERE code = 548',
            'UPDATE country SET name_rus = "Новая Зеландия", name_rus_full = "Новая Зеландия" WHERE code = 554',
            'UPDATE country SET name_rus = "Никарагуа", name_rus_full = "Республика Никарагуа" WHERE code = 558',
            'UPDATE country SET name_rus = "Нигер", name_rus_full = "Республика Нигер" WHERE code = 562',
            'UPDATE country SET name_rus = "Нигерия", name_rus_full = "Федеративная Республика Нигерия" WHERE code = 566',
            'UPDATE country SET name_rus = "Ниуэ", name_rus_full = "Ниуэ" WHERE code = 570',
            'UPDATE country SET name_rus = "Остров Норфолк", name_rus_full = "Остров Норфолк" WHERE code = 574',
            'UPDATE country SET name_rus = "Норвегия", name_rus_full = "Королевство Норвегия" WHERE code = 578',
            'UPDATE country SET name_rus = "Северные Марианские острова", name_rus_full = "Содружество Северных Марианских островов" WHERE code = 580',
            'UPDATE country SET name_rus = "Малые Тихоокеанские отдаленные острова США", name_rus_full = "Малые Тихоокеанские отдаленные острова США" WHERE code = 581',
            'UPDATE country SET name_rus = "Микронезия", name_rus_full = "Федеративные штаты Микронезии" WHERE code = 583',
            'UPDATE country SET name_rus = "Маршалловы острова", name_rus_full = "Республика Маршалловы Острова" WHERE code = 584',
            'UPDATE country SET name_rus = "Палау", name_rus_full = "Республика Палау" WHERE code = 585',
            'UPDATE country SET name_rus = "Пакистан", name_rus_full = "Исламская Республика Пакистан" WHERE code = 586',
            'UPDATE country SET name_rus = "Панама", name_rus_full = "Республика Панама" WHERE code = 591',
            'UPDATE country SET name_rus = "Папуа Новая Гвинея", name_rus_full = "Независимое Государство Папуа Новая Гвинея" WHERE code = 598',
            'UPDATE country SET name_rus = "Парагвай", name_rus_full = "Республика Парагвай" WHERE code = 600',
            'UPDATE country SET name_rus = "Перу", name_rus_full = "Республика Перу" WHERE code = 604',
            'UPDATE country SET name_rus = "Филиппины", name_rus_full = "Республика Филиппины" WHERE code = 608',
            'UPDATE country SET name_rus = "Питкерн", name_rus_full = "Питкерн" WHERE code = 612',
            'UPDATE country SET name_rus = "Польша", name_rus_full = "Республика Польша" WHERE code = 616',
            'UPDATE country SET name_rus = "Португалия", name_rus_full = "Португальская Республика" WHERE code = 620',
            'UPDATE country SET name_rus = "Гвинея-Бисау", name_rus_full = "Республика Гвинея-Бисау" WHERE code = 624',
            'UPDATE country SET name_rus = "Тимор-Лесте", name_rus_full = "Демократическая Республика Тимор-Лесте" WHERE code = 626',
            'UPDATE country SET name_rus = "Пуэрто-Рико", name_rus_full = "Пуэрто-Рико" WHERE code = 630',
            'UPDATE country SET name_rus = "Катар", name_rus_full = "Государство Катар" WHERE code = 634',
            'UPDATE country SET name_rus = "Реюньон", name_rus_full = "Реюньон" WHERE code = 638',
            'UPDATE country SET name_rus = "Румыния", name_rus_full = "Румыния" WHERE code = 642',
            'UPDATE country SET name_rus = "Россия", name_rus_full = "Российская Федерация" WHERE code = 643',
            'UPDATE country SET name_rus = "Руанда", name_rus_full = "Руандийская Республика" WHERE code = 646',
            'UPDATE country SET name_rus = "Сен-Бартелеми", name_rus_full = "Сен-Бартелеми" WHERE code = 652',
            'UPDATE country SET name_rus = "Святая Елена, остров Вознесения, Тристан-Да-Кунья", name_rus_full = "Святая Елена, остров Вознесения, Тристан-Да-Кунья" WHERE code = 654',
            'UPDATE country SET name_rus = "Сент-Китс и Невис", name_rus_full = "Сент-Китс и Невис" WHERE code = 659',
            'UPDATE country SET name_rus = "Ангилья", name_rus_full = "Ангилья" WHERE code = 660',
            'UPDATE country SET name_rus = "Сент-Люсия", name_rus_full = "Сент-Люсия" WHERE code = 662',
            'UPDATE country SET name_rus = "Сен-Мартен", name_rus_full = "Сен-Мартен" WHERE code = 663',
            'UPDATE country SET name_rus = "Сен-Пьер и Микелон", name_rus_full = "Сен-Пьер и Микелон" WHERE code = 666',
            'UPDATE country SET name_rus = "Сент-Винсент и Гренадины", name_rus_full = "Сент-Винсент и Гренадины" WHERE code = 670',
            'UPDATE country SET name_rus = "Сан-Марино", name_rus_full = "Республика Сан-Марино" WHERE code = 674',
            'UPDATE country SET name_rus = "Сан-Томе и Принсипи", name_rus_full = "Демократическая Республика Сан-Томе и Принсипи" WHERE code = 678',
            'UPDATE country SET name_rus = "Саудовская Аравия", name_rus_full = "Королевство Саудовская Аравия" WHERE code = 682',
            'UPDATE country SET name_rus = "Сенегал", name_rus_full = "Республика Сенегал" WHERE code = 686',
            'UPDATE country SET name_rus = "Сербия", name_rus_full = "Республика Сербия" WHERE code = 688',
            'UPDATE country SET name_rus = "Сейшелы", name_rus_full = "Республика Сейшелы" WHERE code = 690',
            'UPDATE country SET name_rus = "Сьерра-Леоне", name_rus_full = "Республика Сьерра-Леоне" WHERE code = 694',
            'UPDATE country SET name_rus = "Сингапур", name_rus_full = "Республика Сингапур" WHERE code = 702',
            'UPDATE country SET name_rus = "Словакия", name_rus_full = "Словацкая Республика" WHERE code = 703',
            'UPDATE country SET name_rus = "Вьетнам", name_rus_full = "Социалистическая Республика Вьетнам" WHERE code = 704',
            'UPDATE country SET name_rus = "Словения", name_rus_full = "Республика Словения" WHERE code = 705',
            'UPDATE country SET name_rus = "Сомали", name_rus_full = "Федеративная Республика Сомали" WHERE code = 706',
            'UPDATE country SET name_rus = "ЮАР", name_rus_full = "Южно-Африканская Республика" WHERE code = 710',
            'UPDATE country SET name_rus = "Зимбабве", name_rus_full = "Республика Зимбабве" WHERE code = 716',
            'UPDATE country SET name_rus = "Испания", name_rus_full = "Королевство Испания" WHERE code = 724',
            'UPDATE country SET name_rus = "Южный Судан", name_rus_full = "Республика Южный Судан" WHERE code = 728',
            'UPDATE country SET name_rus = "Судан", name_rus_full = "Республика Судан" WHERE code = 729',
            'UPDATE country SET name_rus = "Западная Сахара", name_rus_full = "Западная Сахара" WHERE code = 732',
            'UPDATE country SET name_rus = "Суринам", name_rus_full = "Республика Суринам" WHERE code = 740',
            'UPDATE country SET name_rus = "Шпицберген и Ян Майен", name_rus_full = "Шпицберген и Ян Майен" WHERE code = 744',
            'UPDATE country SET name_rus = "Свазиленд", name_rus_full = "Королевство Свазиленд" WHERE code = 748',
            'UPDATE country SET name_rus = "Швеция", name_rus_full = "Королевство Швеция" WHERE code = 752',
            'UPDATE country SET name_rus = "Швейцария", name_rus_full = "Швейцарская Конфедерация" WHERE code = 756',
            'UPDATE country SET name_rus = "Сирия", name_rus_full = "Сирийская арабская республика" WHERE code = 760',
            'UPDATE country SET name_rus = "Таджикистан", name_rus_full = "Республика Таджикистан" WHERE code = 762',
            'UPDATE country SET name_rus = "Таиланд", name_rus_full = "Королевство Таиланд" WHERE code = 764',
            'UPDATE country SET name_rus = "Того", name_rus_full = "Тоголезская Республика" WHERE code = 768',
            'UPDATE country SET name_rus = "Токелау", name_rus_full = "Токелау" WHERE code = 772',
            'UPDATE country SET name_rus = "Тонга", name_rus_full = "Королевство Тонга" WHERE code = 776',
            'UPDATE country SET name_rus = "Тринидад и Тобаго", name_rus_full = "Республика Тринидад и Тобаго" WHERE code = 780',
            'UPDATE country SET name_rus = "ОАЭ", name_rus_full = "Объединенные арабские эмираты" WHERE code = 784',
            'UPDATE country SET name_rus = "Тунис", name_rus_full = "Тунисская Республика" WHERE code = 788',
            'UPDATE country SET name_rus = "Турция", name_rus_full = "Турецкая Республика" WHERE code = 792',
            'UPDATE country SET name_rus = "Туркмения", name_rus_full = "Туркменистан" WHERE code = 795',
            'UPDATE country SET name_rus = "Острова Теркс и Кайкос", name_rus_full = "Острова Теркс и Кайкос" WHERE code = 796',
            'UPDATE country SET name_rus = "Тувалу", name_rus_full = "Тувалу" WHERE code = 798',
            'UPDATE country SET name_rus = "Уганда", name_rus_full = "Республика Уганда" WHERE code = 800',
            'UPDATE country SET name_rus = "Украина", name_rus_full = "Украина" WHERE code = 804',
            'UPDATE country SET name_rus = "Македония", name_rus_full = "Республика Македония" WHERE code = 807',
            'UPDATE country SET name_rus = "Египет", name_rus_full = "Арабская Республика Египет" WHERE code = 818',
            'UPDATE country SET name_rus = "Великобритания", name_rus_full = "Соединенное Королевство Великобритании и Северной Ирландии" WHERE code = 826',
            'UPDATE country SET name_rus = "Гернси", name_rus_full = "Гернси" WHERE code = 831',
            'UPDATE country SET name_rus = "Джерси", name_rus_full = "Джерси" WHERE code = 832',
            'UPDATE country SET name_rus = "Остров Мэн", name_rus_full = "Остров Мэн" WHERE code = 833',
            'UPDATE country SET name_rus = "Танзания", name_rus_full = "Объединенная Республика Танзания" WHERE code = 834',
            'UPDATE country SET name_rus = "США", name_rus_full = "Соединенные Штаты Америки" WHERE code = 840',
            'UPDATE country SET name_rus = "Виргинские острова, США", name_rus_full = "Виргинские острова Соединенных Штатов" WHERE code = 850',
            'UPDATE country SET name_rus = "Буркина-Фасо", name_rus_full = "Буркина-Фасо" WHERE code = 854',
            'UPDATE country SET name_rus = "Уругвай", name_rus_full = "Восточная Республика Уругвай" WHERE code = 858',
            'UPDATE country SET name_rus = "Узбекистан", name_rus_full = "Республика Узбекистан" WHERE code = 860',
            'UPDATE country SET name_rus = "Венесуэла", name_rus_full = "Боливарианская Республика Венесуэла" WHERE code = 862',
            'UPDATE country SET name_rus = "Уоллис и Футуна", name_rus_full = "Уоллис и Футуна" WHERE code = 876',
            'UPDATE country SET name_rus = "Самоа", name_rus_full = "Независимое Государство Самоа" WHERE code = 882',
            'UPDATE country SET name_rus = "Йемен", name_rus_full = "Йеменская Республика" WHERE code = 887',
            'UPDATE country SET name_rus = "Замбия", name_rus_full = "Республика Замбия" WHERE code = 894',
            'UPDATE country SET name_rus = "Абхазия", name_rus_full = "Республика Абхазия" WHERE code = 895',
            'UPDATE country SET name_rus = "Южная Осетия", name_rus_full = "Республика Южная Осетия" WHERE code = 896',

            'UPDATE country SET name_rus = "Нидерландские Антиллы", name_rus_full = "Нидерландские Антильские острова" WHERE code = 530',
            'UPDATE country SET name = "Κύπρος", name_rus = "Кипр", name_rus_full = "Республика Кипр" WHERE code = 196',

            'DELETE FROM country WHERE code = 891', // Югославия

            'UPDATE country SET name = name_rus WHERE in_use = 0',
        ];
        foreach ($sqls as $sql) {
            $this->execute($sql);
        }
    }

    /**
     * Заполнить prefix
     */
    private function _fillPrefix()
    {
        /**
         * @link https://countrycode.org/
         */
        $sqls = [
            'UPDATE country SET name = "Afghanistan", prefix = REPLACE("93", "-", "") WHERE alpha_3 = SUBSTR("AF / AFG", 6) AND in_use = 0',
            'UPDATE country SET name = "Albania", prefix = REPLACE("355", "-", "") WHERE alpha_3 = SUBSTR("AL / ALB", 6) AND in_use = 0',
            'UPDATE country SET name = "Algeria", prefix = REPLACE("213", "-", "") WHERE alpha_3 = SUBSTR("DZ / DZA", 6) AND in_use = 0',
            'UPDATE country SET name = "American Samoa", prefix = REPLACE("1-684", "-", "") WHERE alpha_3 = SUBSTR("AS / ASM", 6) AND in_use = 0',
            'UPDATE country SET name = "Andorra", prefix = REPLACE("376", "-", "") WHERE alpha_3 = SUBSTR("AD / AND", 6) AND in_use = 0',
            'UPDATE country SET name = "Angola", prefix = REPLACE("244", "-", "") WHERE alpha_3 = SUBSTR("AO / AGO", 6) AND in_use = 0',
            'UPDATE country SET name = "Anguilla", prefix = REPLACE("1-264", "-", "") WHERE alpha_3 = SUBSTR("AI / AIA", 6) AND in_use = 0',
            'UPDATE country SET name = "Antarctica", prefix = REPLACE("672", "-", "") WHERE alpha_3 = SUBSTR("AQ / ATA", 6) AND in_use = 0',
            'UPDATE country SET name = "Antigua and Barbuda", prefix = REPLACE("1-268", "-", "") WHERE alpha_3 = SUBSTR("AG / ATG", 6) AND in_use = 0',
            'UPDATE country SET name = "Argentina", prefix = REPLACE("54", "-", "") WHERE alpha_3 = SUBSTR("AR / ARG", 6) AND in_use = 0',
            'UPDATE country SET name = "Armenia", prefix = REPLACE("374", "-", "") WHERE alpha_3 = SUBSTR("AM / ARM", 6) AND in_use = 0',
            'UPDATE country SET name = "Aruba", prefix = REPLACE("297", "-", "") WHERE alpha_3 = SUBSTR("AW / ABW", 6) AND in_use = 0',
            'UPDATE country SET name = "Australia", prefix = REPLACE("61", "-", "") WHERE alpha_3 = SUBSTR("AU / AUS", 6) AND in_use = 0',
            'UPDATE country SET name = "Austria", prefix = REPLACE("43", "-", "") WHERE alpha_3 = SUBSTR("AT / AUT", 6) AND in_use = 0',
            'UPDATE country SET name = "Azerbaijan", prefix = REPLACE("994", "-", "") WHERE alpha_3 = SUBSTR("AZ / AZE", 6) AND in_use = 0',
            'UPDATE country SET name = "Bahamas", prefix = REPLACE("1-242", "-", "") WHERE alpha_3 = SUBSTR("BS / BHS", 6) AND in_use = 0',
            'UPDATE country SET name = "Bahrain", prefix = REPLACE("973", "-", "") WHERE alpha_3 = SUBSTR("BH / BHR", 6) AND in_use = 0',
            'UPDATE country SET name = "Bangladesh", prefix = REPLACE("880", "-", "") WHERE alpha_3 = SUBSTR("BD / BGD", 6) AND in_use = 0',
            'UPDATE country SET name = "Barbados", prefix = REPLACE("1-246", "-", "") WHERE alpha_3 = SUBSTR("BB / BRB", 6) AND in_use = 0',
            'UPDATE country SET name = "Belarus", prefix = REPLACE("375", "-", "") WHERE alpha_3 = SUBSTR("BY / BLR", 6) AND in_use = 0',
            'UPDATE country SET name = "Belgium", prefix = REPLACE("32", "-", "") WHERE alpha_3 = SUBSTR("BE / BEL", 6) AND in_use = 0',
            'UPDATE country SET name = "Belize", prefix = REPLACE("501", "-", "") WHERE alpha_3 = SUBSTR("BZ / BLZ", 6) AND in_use = 0',
            'UPDATE country SET name = "Benin", prefix = REPLACE("229", "-", "") WHERE alpha_3 = SUBSTR("BJ / BEN", 6) AND in_use = 0',
            'UPDATE country SET name = "Bermuda", prefix = REPLACE("1-441", "-", "") WHERE alpha_3 = SUBSTR("BM / BMU", 6) AND in_use = 0',
            'UPDATE country SET name = "Bhutan", prefix = REPLACE("975", "-", "") WHERE alpha_3 = SUBSTR("BT / BTN", 6) AND in_use = 0',
            'UPDATE country SET name = "Bolivia", prefix = REPLACE("591", "-", "") WHERE alpha_3 = SUBSTR("BO / BOL", 6) AND in_use = 0',
            'UPDATE country SET name = "Bosnia and Herzegovina", prefix = REPLACE("387", "-", "") WHERE alpha_3 = SUBSTR("BA / BIH", 6) AND in_use = 0',
            'UPDATE country SET name = "Botswana", prefix = REPLACE("267", "-", "") WHERE alpha_3 = SUBSTR("BW / BWA", 6) AND in_use = 0',
            'UPDATE country SET name = "Brazil", prefix = REPLACE("55", "-", "") WHERE alpha_3 = SUBSTR("BR / BRA", 6) AND in_use = 0',
            'UPDATE country SET name = "British Indian Ocean Territory", prefix = REPLACE("246", "-", "") WHERE alpha_3 = SUBSTR("IO / IOT", 6) AND in_use = 0',
            'UPDATE country SET name = "British Virgin Islands", prefix = REPLACE("1-284", "-", "") WHERE alpha_3 = SUBSTR("VG / VGB", 6) AND in_use = 0',
            'UPDATE country SET name = "Brunei", prefix = REPLACE("673", "-", "") WHERE alpha_3 = SUBSTR("BN / BRN", 6) AND in_use = 0',
            'UPDATE country SET name = "Bulgaria", prefix = REPLACE("359", "-", "") WHERE alpha_3 = SUBSTR("BG / BGR", 6) AND in_use = 0',
            'UPDATE country SET name = "Burkina Faso", prefix = REPLACE("226", "-", "") WHERE alpha_3 = SUBSTR("BF / BFA", 6) AND in_use = 0',
            'UPDATE country SET name = "Burundi", prefix = REPLACE("257", "-", "") WHERE alpha_3 = SUBSTR("BI / BDI", 6) AND in_use = 0',
            'UPDATE country SET name = "Cambodia", prefix = REPLACE("855", "-", "") WHERE alpha_3 = SUBSTR("KH / KHM", 6) AND in_use = 0',
            'UPDATE country SET name = "Cameroon", prefix = REPLACE("237", "-", "") WHERE alpha_3 = SUBSTR("CM / CMR", 6) AND in_use = 0',
            'UPDATE country SET name = "Canada", prefix = REPLACE("1", "-", "") WHERE alpha_3 = SUBSTR("CA / CAN", 6) AND in_use = 0',
            'UPDATE country SET name = "Cape Verde", prefix = REPLACE("238", "-", "") WHERE alpha_3 = SUBSTR("CV / CPV", 6) AND in_use = 0',
            'UPDATE country SET name = "Cayman Islands", prefix = REPLACE("1-345", "-", "") WHERE alpha_3 = SUBSTR("KY / CYM", 6) AND in_use = 0',
            'UPDATE country SET name = "Central African Republic", prefix = REPLACE("236", "-", "") WHERE alpha_3 = SUBSTR("CF / CAF", 6) AND in_use = 0',
            'UPDATE country SET name = "Chad", prefix = REPLACE("235", "-", "") WHERE alpha_3 = SUBSTR("TD / TCD", 6) AND in_use = 0',
            'UPDATE country SET name = "Chile", prefix = REPLACE("56", "-", "") WHERE alpha_3 = SUBSTR("CL / CHL", 6) AND in_use = 0',
            'UPDATE country SET name = "China", prefix = REPLACE("86", "-", "") WHERE alpha_3 = SUBSTR("CN / CHN", 6) AND in_use = 0',
            'UPDATE country SET name = "Christmas Island", prefix = REPLACE("61", "-", "") WHERE alpha_3 = SUBSTR("CX / CXR", 6) AND in_use = 0',
            'UPDATE country SET name = "Cocos Islands", prefix = REPLACE("61", "-", "") WHERE alpha_3 = SUBSTR("CC / CCK", 6) AND in_use = 0',
            'UPDATE country SET name = "Colombia", prefix = REPLACE("57", "-", "") WHERE alpha_3 = SUBSTR("CO / COL", 6) AND in_use = 0',
            'UPDATE country SET name = "Comoros", prefix = REPLACE("269", "-", "") WHERE alpha_3 = SUBSTR("KM / COM", 6) AND in_use = 0',
            'UPDATE country SET name = "Cook Islands", prefix = REPLACE("682", "-", "") WHERE alpha_3 = SUBSTR("CK / COK", 6) AND in_use = 0',
            'UPDATE country SET name = "Costa Rica", prefix = REPLACE("506", "-", "") WHERE alpha_3 = SUBSTR("CR / CRI", 6) AND in_use = 0',
            'UPDATE country SET name = "Croatia", prefix = REPLACE("385", "-", "") WHERE alpha_3 = SUBSTR("HR / HRV", 6) AND in_use = 0',
            'UPDATE country SET name = "Cuba", prefix = REPLACE("53", "-", "") WHERE alpha_3 = SUBSTR("CU / CUB", 6) AND in_use = 0',
            'UPDATE country SET name = "Curacao", prefix = REPLACE("599", "-", "") WHERE alpha_3 = SUBSTR("CW / CUW", 6) AND in_use = 0',
            'UPDATE country SET name = "Cyprus", prefix = REPLACE("357", "-", "") WHERE alpha_3 = SUBSTR("CY / CYP", 6) AND in_use = 0',
            'UPDATE country SET name = "Czech Republic", prefix = REPLACE("420", "-", "") WHERE alpha_3 = SUBSTR("CZ / CZE", 6) AND in_use = 0',
            'UPDATE country SET name = "Democratic Republic of the Congo", prefix = REPLACE("243", "-", "") WHERE alpha_3 = SUBSTR("CD / COD", 6) AND in_use = 0',
            'UPDATE country SET name = "Denmark", prefix = REPLACE("45", "-", "") WHERE alpha_3 = SUBSTR("DK / DNK", 6) AND in_use = 0',
            'UPDATE country SET name = "Djibouti", prefix = REPLACE("253", "-", "") WHERE alpha_3 = SUBSTR("DJ / DJI", 6) AND in_use = 0',
            'UPDATE country SET name = "Dominica", prefix = REPLACE("1-767", "-", "") WHERE alpha_3 = SUBSTR("DM / DMA", 6) AND in_use = 0',
            'UPDATE country SET name = "Dominican Republic", prefix = REPLACE("1-809, 1-829, 1-849", "-", "") WHERE alpha_3 = SUBSTR("DO / DOM", 6) AND in_use = 0',
            'UPDATE country SET name = "East Timor", prefix = REPLACE("670", "-", "") WHERE alpha_3 = SUBSTR("TL / TLS", 6) AND in_use = 0',
            'UPDATE country SET name = "Ecuador", prefix = REPLACE("593", "-", "") WHERE alpha_3 = SUBSTR("EC / ECU", 6) AND in_use = 0',
            'UPDATE country SET name = "Egypt", prefix = REPLACE("20", "-", "") WHERE alpha_3 = SUBSTR("EG / EGY", 6) AND in_use = 0',
            'UPDATE country SET name = "El Salvador", prefix = REPLACE("503", "-", "") WHERE alpha_3 = SUBSTR("SV / SLV", 6) AND in_use = 0',
            'UPDATE country SET name = "Equatorial Guinea", prefix = REPLACE("240", "-", "") WHERE alpha_3 = SUBSTR("GQ / GNQ", 6) AND in_use = 0',
            'UPDATE country SET name = "Eritrea", prefix = REPLACE("291", "-", "") WHERE alpha_3 = SUBSTR("ER / ERI", 6) AND in_use = 0',
            'UPDATE country SET name = "Estonia", prefix = REPLACE("372", "-", "") WHERE alpha_3 = SUBSTR("EE / EST", 6) AND in_use = 0',
            'UPDATE country SET name = "Ethiopia", prefix = REPLACE("251", "-", "") WHERE alpha_3 = SUBSTR("ET / ETH", 6) AND in_use = 0',
            'UPDATE country SET name = "Falkland Islands", prefix = REPLACE("500", "-", "") WHERE alpha_3 = SUBSTR("FK / FLK", 6) AND in_use = 0',
            'UPDATE country SET name = "Faroe Islands", prefix = REPLACE("298", "-", "") WHERE alpha_3 = SUBSTR("FO / FRO", 6) AND in_use = 0',
            'UPDATE country SET name = "Fiji", prefix = REPLACE("679", "-", "") WHERE alpha_3 = SUBSTR("FJ / FJI", 6) AND in_use = 0',
            'UPDATE country SET name = "Finland", prefix = REPLACE("358", "-", "") WHERE alpha_3 = SUBSTR("FI / FIN", 6) AND in_use = 0',
            'UPDATE country SET name = "France", prefix = REPLACE("33", "-", "") WHERE alpha_3 = SUBSTR("FR / FRA", 6) AND in_use = 0',
            'UPDATE country SET name = "French Polynesia", prefix = REPLACE("689", "-", "") WHERE alpha_3 = SUBSTR("PF / PYF", 6) AND in_use = 0',
            'UPDATE country SET name = "Gabon", prefix = REPLACE("241", "-", "") WHERE alpha_3 = SUBSTR("GA / GAB", 6) AND in_use = 0',
            'UPDATE country SET name = "Gambia", prefix = REPLACE("220", "-", "") WHERE alpha_3 = SUBSTR("GM / GMB", 6) AND in_use = 0',
            'UPDATE country SET name = "Georgia", prefix = REPLACE("995", "-", "") WHERE alpha_3 = SUBSTR("GE / GEO", 6) AND in_use = 0',
            'UPDATE country SET name = "Germany", prefix = REPLACE("49", "-", "") WHERE alpha_3 = SUBSTR("DE / DEU", 6) AND in_use = 0',
            'UPDATE country SET name = "Ghana", prefix = REPLACE("233", "-", "") WHERE alpha_3 = SUBSTR("GH / GHA", 6) AND in_use = 0',
            'UPDATE country SET name = "Gibraltar", prefix = REPLACE("350", "-", "") WHERE alpha_3 = SUBSTR("GI / GIB", 6) AND in_use = 0',
            'UPDATE country SET name = "Greece", prefix = REPLACE("30", "-", "") WHERE alpha_3 = SUBSTR("GR / GRC", 6) AND in_use = 0',
            'UPDATE country SET name = "Greenland", prefix = REPLACE("299", "-", "") WHERE alpha_3 = SUBSTR("GL / GRL", 6) AND in_use = 0',
            'UPDATE country SET name = "Grenada", prefix = REPLACE("1-473", "-", "") WHERE alpha_3 = SUBSTR("GD / GRD", 6) AND in_use = 0',
            'UPDATE country SET name = "Guam", prefix = REPLACE("1-671", "-", "") WHERE alpha_3 = SUBSTR("GU / GUM", 6) AND in_use = 0',
            'UPDATE country SET name = "Guatemala", prefix = REPLACE("502", "-", "") WHERE alpha_3 = SUBSTR("GT / GTM", 6) AND in_use = 0',
            'UPDATE country SET name = "Guernsey", prefix = REPLACE("44-1481", "-", "") WHERE alpha_3 = SUBSTR("GG / GGY", 6) AND in_use = 0',
            'UPDATE country SET name = "Guinea", prefix = REPLACE("224", "-", "") WHERE alpha_3 = SUBSTR("GN / GIN", 6) AND in_use = 0',
            'UPDATE country SET name = "Guinea-Bissau", prefix = REPLACE("245", "-", "") WHERE alpha_3 = SUBSTR("GW / GNB", 6) AND in_use = 0',
            'UPDATE country SET name = "Guyana", prefix = REPLACE("592", "-", "") WHERE alpha_3 = SUBSTR("GY / GUY", 6) AND in_use = 0',
            'UPDATE country SET name = "Haiti", prefix = REPLACE("509", "-", "") WHERE alpha_3 = SUBSTR("HT / HTI", 6) AND in_use = 0',
            'UPDATE country SET name = "Honduras", prefix = REPLACE("504", "-", "") WHERE alpha_3 = SUBSTR("HN / HND", 6) AND in_use = 0',
            'UPDATE country SET name = "Hong Kong", prefix = REPLACE("852", "-", "") WHERE alpha_3 = SUBSTR("HK / HKG", 6) AND in_use = 0',
            'UPDATE country SET name = "Hungary", prefix = REPLACE("36", "-", "") WHERE alpha_3 = SUBSTR("HU / HUN", 6) AND in_use = 0',
            'UPDATE country SET name = "Iceland", prefix = REPLACE("354", "-", "") WHERE alpha_3 = SUBSTR("IS / ISL", 6) AND in_use = 0',
            'UPDATE country SET name = "India", prefix = REPLACE("91", "-", "") WHERE alpha_3 = SUBSTR("IN / IND", 6) AND in_use = 0',
            'UPDATE country SET name = "Indonesia", prefix = REPLACE("62", "-", "") WHERE alpha_3 = SUBSTR("ID / IDN", 6) AND in_use = 0',
            'UPDATE country SET name = "Iran", prefix = REPLACE("98", "-", "") WHERE alpha_3 = SUBSTR("IR / IRN", 6) AND in_use = 0',
            'UPDATE country SET name = "Iraq", prefix = REPLACE("964", "-", "") WHERE alpha_3 = SUBSTR("IQ / IRQ", 6) AND in_use = 0',
            'UPDATE country SET name = "Ireland", prefix = REPLACE("353", "-", "") WHERE alpha_3 = SUBSTR("IE / IRL", 6) AND in_use = 0',
            'UPDATE country SET name = "Isle of Man", prefix = REPLACE("44-1624", "-", "") WHERE alpha_3 = SUBSTR("IM / IMN", 6) AND in_use = 0',
            'UPDATE country SET name = "Israel", prefix = REPLACE("972", "-", "") WHERE alpha_3 = SUBSTR("IL / ISR", 6) AND in_use = 0',
            'UPDATE country SET name = "Italy", prefix = REPLACE("39", "-", "") WHERE alpha_3 = SUBSTR("IT / ITA", 6) AND in_use = 0',
            'UPDATE country SET name = "Ivory Coast", prefix = REPLACE("225", "-", "") WHERE alpha_3 = SUBSTR("CI / CIV", 6) AND in_use = 0',
            'UPDATE country SET name = "Jamaica", prefix = REPLACE("1-876", "-", "") WHERE alpha_3 = SUBSTR("JM / JAM", 6) AND in_use = 0',
            'UPDATE country SET name = "Japan", prefix = REPLACE("81", "-", "") WHERE alpha_3 = SUBSTR("JP / JPN", 6) AND in_use = 0',
            'UPDATE country SET name = "Jersey", prefix = REPLACE("44-1534", "-", "") WHERE alpha_3 = SUBSTR("JE / JEY", 6) AND in_use = 0',
            'UPDATE country SET name = "Jordan", prefix = REPLACE("962", "-", "") WHERE alpha_3 = SUBSTR("JO / JOR", 6) AND in_use = 0',
            'UPDATE country SET name = "Kazakhstan", prefix = REPLACE("7", "-", "") WHERE alpha_3 = SUBSTR("KZ / KAZ", 6) AND in_use = 0',
            'UPDATE country SET name = "Kenya", prefix = REPLACE("254", "-", "") WHERE alpha_3 = SUBSTR("KE / KEN", 6) AND in_use = 0',
            'UPDATE country SET name = "Kiribati", prefix = REPLACE("686", "-", "") WHERE alpha_3 = SUBSTR("KI / KIR", 6) AND in_use = 0',
            'UPDATE country SET name = "Kosovo", prefix = REPLACE("383", "-", "") WHERE alpha_3 = SUBSTR("XK / XKX", 6) AND in_use = 0',
            'UPDATE country SET name = "Kuwait", prefix = REPLACE("965", "-", "") WHERE alpha_3 = SUBSTR("KW / KWT", 6) AND in_use = 0',
            'UPDATE country SET name = "Kyrgyzstan", prefix = REPLACE("996", "-", "") WHERE alpha_3 = SUBSTR("KG / KGZ", 6) AND in_use = 0',
            'UPDATE country SET name = "Laos", prefix = REPLACE("856", "-", "") WHERE alpha_3 = SUBSTR("LA / LAO", 6) AND in_use = 0',
            'UPDATE country SET name = "Latvia", prefix = REPLACE("371", "-", "") WHERE alpha_3 = SUBSTR("LV / LVA", 6) AND in_use = 0',
            'UPDATE country SET name = "Lebanon", prefix = REPLACE("961", "-", "") WHERE alpha_3 = SUBSTR("LB / LBN", 6) AND in_use = 0',
            'UPDATE country SET name = "Lesotho", prefix = REPLACE("266", "-", "") WHERE alpha_3 = SUBSTR("LS / LSO", 6) AND in_use = 0',
            'UPDATE country SET name = "Liberia", prefix = REPLACE("231", "-", "") WHERE alpha_3 = SUBSTR("LR / LBR", 6) AND in_use = 0',
            'UPDATE country SET name = "Libya", prefix = REPLACE("218", "-", "") WHERE alpha_3 = SUBSTR("LY / LBY", 6) AND in_use = 0',
            'UPDATE country SET name = "Liechtenstein", prefix = REPLACE("423", "-", "") WHERE alpha_3 = SUBSTR("LI / LIE", 6) AND in_use = 0',
            'UPDATE country SET name = "Lithuania", prefix = REPLACE("370", "-", "") WHERE alpha_3 = SUBSTR("LT / LTU", 6) AND in_use = 0',
            'UPDATE country SET name = "Luxembourg", prefix = REPLACE("352", "-", "") WHERE alpha_3 = SUBSTR("LU / LUX", 6) AND in_use = 0',
            'UPDATE country SET name = "Macau", prefix = REPLACE("853", "-", "") WHERE alpha_3 = SUBSTR("MO / MAC", 6) AND in_use = 0',
            'UPDATE country SET name = "Macedonia", prefix = REPLACE("389", "-", "") WHERE alpha_3 = SUBSTR("MK / MKD", 6) AND in_use = 0',
            'UPDATE country SET name = "Madagascar", prefix = REPLACE("261", "-", "") WHERE alpha_3 = SUBSTR("MG / MDG", 6) AND in_use = 0',
            'UPDATE country SET name = "Malawi", prefix = REPLACE("265", "-", "") WHERE alpha_3 = SUBSTR("MW / MWI", 6) AND in_use = 0',
            'UPDATE country SET name = "Malaysia", prefix = REPLACE("60", "-", "") WHERE alpha_3 = SUBSTR("MY / MYS", 6) AND in_use = 0',
            'UPDATE country SET name = "Maldives", prefix = REPLACE("960", "-", "") WHERE alpha_3 = SUBSTR("MV / MDV", 6) AND in_use = 0',
            'UPDATE country SET name = "Mali", prefix = REPLACE("223", "-", "") WHERE alpha_3 = SUBSTR("ML / MLI", 6) AND in_use = 0',
            'UPDATE country SET name = "Malta", prefix = REPLACE("356", "-", "") WHERE alpha_3 = SUBSTR("MT / MLT", 6) AND in_use = 0',
            'UPDATE country SET name = "Marshall Islands", prefix = REPLACE("692", "-", "") WHERE alpha_3 = SUBSTR("MH / MHL", 6) AND in_use = 0',
            'UPDATE country SET name = "Mauritania", prefix = REPLACE("222", "-", "") WHERE alpha_3 = SUBSTR("MR / MRT", 6) AND in_use = 0',
            'UPDATE country SET name = "Mauritius", prefix = REPLACE("230", "-", "") WHERE alpha_3 = SUBSTR("MU / MUS", 6) AND in_use = 0',
            'UPDATE country SET name = "Mayotte", prefix = REPLACE("262", "-", "") WHERE alpha_3 = SUBSTR("YT / MYT", 6) AND in_use = 0',
            'UPDATE country SET name = "Mexico", prefix = REPLACE("52", "-", "") WHERE alpha_3 = SUBSTR("MX / MEX", 6) AND in_use = 0',
            'UPDATE country SET name = "Micronesia", prefix = REPLACE("691", "-", "") WHERE alpha_3 = SUBSTR("FM / FSM", 6) AND in_use = 0',
            'UPDATE country SET name = "Moldova", prefix = REPLACE("373", "-", "") WHERE alpha_3 = SUBSTR("MD / MDA", 6) AND in_use = 0',
            'UPDATE country SET name = "Monaco", prefix = REPLACE("377", "-", "") WHERE alpha_3 = SUBSTR("MC / MCO", 6) AND in_use = 0',
            'UPDATE country SET name = "Mongolia", prefix = REPLACE("976", "-", "") WHERE alpha_3 = SUBSTR("MN / MNG", 6) AND in_use = 0',
            'UPDATE country SET name = "Montenegro", prefix = REPLACE("382", "-", "") WHERE alpha_3 = SUBSTR("ME / MNE", 6) AND in_use = 0',
            'UPDATE country SET name = "Montserrat", prefix = REPLACE("1-664", "-", "") WHERE alpha_3 = SUBSTR("MS / MSR", 6) AND in_use = 0',
            'UPDATE country SET name = "Morocco", prefix = REPLACE("212", "-", "") WHERE alpha_3 = SUBSTR("MA / MAR", 6) AND in_use = 0',
            'UPDATE country SET name = "Mozambique", prefix = REPLACE("258", "-", "") WHERE alpha_3 = SUBSTR("MZ / MOZ", 6) AND in_use = 0',
            'UPDATE country SET name = "Myanmar", prefix = REPLACE("95", "-", "") WHERE alpha_3 = SUBSTR("MM / MMR", 6) AND in_use = 0',
            'UPDATE country SET name = "Namibia", prefix = REPLACE("264", "-", "") WHERE alpha_3 = SUBSTR("NA / NAM", 6) AND in_use = 0',
            'UPDATE country SET name = "Nauru", prefix = REPLACE("674", "-", "") WHERE alpha_3 = SUBSTR("NR / NRU", 6) AND in_use = 0',
            'UPDATE country SET name = "Nepal", prefix = REPLACE("977", "-", "") WHERE alpha_3 = SUBSTR("NP / NPL", 6) AND in_use = 0',
            'UPDATE country SET name = "Netherlands", prefix = REPLACE("31", "-", "") WHERE alpha_3 = SUBSTR("NL / NLD", 6) AND in_use = 0',
            'UPDATE country SET name = "Netherlands Antilles", prefix = REPLACE("599", "-", "") WHERE alpha_3 = SUBSTR("AN / ANT", 6) AND in_use = 0',
            'UPDATE country SET name = "New Caledonia", prefix = REPLACE("687", "-", "") WHERE alpha_3 = SUBSTR("NC / NCL", 6) AND in_use = 0',
            'UPDATE country SET name = "New Zealand", prefix = REPLACE("64", "-", "") WHERE alpha_3 = SUBSTR("NZ / NZL", 6) AND in_use = 0',
            'UPDATE country SET name = "Nicaragua", prefix = REPLACE("505", "-", "") WHERE alpha_3 = SUBSTR("NI / NIC", 6) AND in_use = 0',
            'UPDATE country SET name = "Niger", prefix = REPLACE("227", "-", "") WHERE alpha_3 = SUBSTR("NE / NER", 6) AND in_use = 0',
            'UPDATE country SET name = "Nigeria", prefix = REPLACE("234", "-", "") WHERE alpha_3 = SUBSTR("NG / NGA", 6) AND in_use = 0',
            'UPDATE country SET name = "Niue", prefix = REPLACE("683", "-", "") WHERE alpha_3 = SUBSTR("NU / NIU", 6) AND in_use = 0',
            'UPDATE country SET name = "North Korea", prefix = REPLACE("850", "-", "") WHERE alpha_3 = SUBSTR("KP / PRK", 6) AND in_use = 0',
            'UPDATE country SET name = "Northern Mariana Islands", prefix = REPLACE("1-670", "-", "") WHERE alpha_3 = SUBSTR("MP / MNP", 6) AND in_use = 0',
            'UPDATE country SET name = "Norway", prefix = REPLACE("47", "-", "") WHERE alpha_3 = SUBSTR("NO / NOR", 6) AND in_use = 0',
            'UPDATE country SET name = "Oman", prefix = REPLACE("968", "-", "") WHERE alpha_3 = SUBSTR("OM / OMN", 6) AND in_use = 0',
            'UPDATE country SET name = "Pakistan", prefix = REPLACE("92", "-", "") WHERE alpha_3 = SUBSTR("PK / PAK", 6) AND in_use = 0',
            'UPDATE country SET name = "Palau", prefix = REPLACE("680", "-", "") WHERE alpha_3 = SUBSTR("PW / PLW", 6) AND in_use = 0',
            'UPDATE country SET name = "Palestine", prefix = REPLACE("970", "-", "") WHERE alpha_3 = SUBSTR("PS / PSE", 6) AND in_use = 0',
            'UPDATE country SET name = "Panama", prefix = REPLACE("507", "-", "") WHERE alpha_3 = SUBSTR("PA / PAN", 6) AND in_use = 0',
            'UPDATE country SET name = "Papua New Guinea", prefix = REPLACE("675", "-", "") WHERE alpha_3 = SUBSTR("PG / PNG", 6) AND in_use = 0',
            'UPDATE country SET name = "Paraguay", prefix = REPLACE("595", "-", "") WHERE alpha_3 = SUBSTR("PY / PRY", 6) AND in_use = 0',
            'UPDATE country SET name = "Peru", prefix = REPLACE("51", "-", "") WHERE alpha_3 = SUBSTR("PE / PER", 6) AND in_use = 0',
            'UPDATE country SET name = "Philippines", prefix = REPLACE("63", "-", "") WHERE alpha_3 = SUBSTR("PH / PHL", 6) AND in_use = 0',
            'UPDATE country SET name = "Pitcairn", prefix = REPLACE("64", "-", "") WHERE alpha_3 = SUBSTR("PN / PCN", 6) AND in_use = 0',
            'UPDATE country SET name = "Poland", prefix = REPLACE("48", "-", "") WHERE alpha_3 = SUBSTR("PL / POL", 6) AND in_use = 0',
            'UPDATE country SET name = "Portugal", prefix = REPLACE("351", "-", "") WHERE alpha_3 = SUBSTR("PT / PRT", 6) AND in_use = 0',
            'UPDATE country SET name = "Puerto Rico", prefix = REPLACE("1-787, 1-939", "-", "") WHERE alpha_3 = SUBSTR("PR / PRI", 6) AND in_use = 0',
            'UPDATE country SET name = "Qatar", prefix = REPLACE("974", "-", "") WHERE alpha_3 = SUBSTR("QA / QAT", 6) AND in_use = 0',
            'UPDATE country SET name = "Republic of the Congo", prefix = REPLACE("242", "-", "") WHERE alpha_3 = SUBSTR("CG / COG", 6) AND in_use = 0',
            'UPDATE country SET name = "Reunion", prefix = REPLACE("262", "-", "") WHERE alpha_3 = SUBSTR("RE / REU", 6) AND in_use = 0',
            'UPDATE country SET name = "Romania", prefix = REPLACE("40", "-", "") WHERE alpha_3 = SUBSTR("RO / ROU", 6) AND in_use = 0',
            'UPDATE country SET name = "Russia", prefix = REPLACE("7", "-", "") WHERE alpha_3 = SUBSTR("RU / RUS", 6) AND in_use = 0',
            'UPDATE country SET name = "Rwanda", prefix = REPLACE("250", "-", "") WHERE alpha_3 = SUBSTR("RW / RWA", 6) AND in_use = 0',
            'UPDATE country SET name = "Saint Barthelemy", prefix = REPLACE("590", "-", "") WHERE alpha_3 = SUBSTR("BL / BLM", 6) AND in_use = 0',
            'UPDATE country SET name = "Saint Helena", prefix = REPLACE("290", "-", "") WHERE alpha_3 = SUBSTR("SH / SHN", 6) AND in_use = 0',
            'UPDATE country SET name = "Saint Kitts and Nevis", prefix = REPLACE("1-869", "-", "") WHERE alpha_3 = SUBSTR("KN / KNA", 6) AND in_use = 0',
            'UPDATE country SET name = "Saint Lucia", prefix = REPLACE("1-758", "-", "") WHERE alpha_3 = SUBSTR("LC / LCA", 6) AND in_use = 0',
            'UPDATE country SET name = "Saint Martin", prefix = REPLACE("590", "-", "") WHERE alpha_3 = SUBSTR("MF / MAF", 6) AND in_use = 0',
            'UPDATE country SET name = "Saint Pierre and Miquelon", prefix = REPLACE("508", "-", "") WHERE alpha_3 = SUBSTR("PM / SPM", 6) AND in_use = 0',
            'UPDATE country SET name = "Saint Vincent and the Grenadines", prefix = REPLACE("1-784", "-", "") WHERE alpha_3 = SUBSTR("VC / VCT", 6) AND in_use = 0',
            'UPDATE country SET name = "Samoa", prefix = REPLACE("685", "-", "") WHERE alpha_3 = SUBSTR("WS / WSM", 6) AND in_use = 0',
            'UPDATE country SET name = "San Marino", prefix = REPLACE("378", "-", "") WHERE alpha_3 = SUBSTR("SM / SMR", 6) AND in_use = 0',
            'UPDATE country SET name = "Sao Tome and Principe", prefix = REPLACE("239", "-", "") WHERE alpha_3 = SUBSTR("ST / STP", 6) AND in_use = 0',
            'UPDATE country SET name = "Saudi Arabia", prefix = REPLACE("966", "-", "") WHERE alpha_3 = SUBSTR("SA / SAU", 6) AND in_use = 0',
            'UPDATE country SET name = "Senegal", prefix = REPLACE("221", "-", "") WHERE alpha_3 = SUBSTR("SN / SEN", 6) AND in_use = 0',
            'UPDATE country SET name = "Serbia", prefix = REPLACE("381", "-", "") WHERE alpha_3 = SUBSTR("RS / SRB", 6) AND in_use = 0',
            'UPDATE country SET name = "Seychelles", prefix = REPLACE("248", "-", "") WHERE alpha_3 = SUBSTR("SC / SYC", 6) AND in_use = 0',
            'UPDATE country SET name = "Sierra Leone", prefix = REPLACE("232", "-", "") WHERE alpha_3 = SUBSTR("SL / SLE", 6) AND in_use = 0',
            'UPDATE country SET name = "Singapore", prefix = REPLACE("65", "-", "") WHERE alpha_3 = SUBSTR("SG / SGP", 6) AND in_use = 0',
            'UPDATE country SET name = "Sint Maarten", prefix = REPLACE("1-721", "-", "") WHERE alpha_3 = SUBSTR("SX / SXM", 6) AND in_use = 0',
            'UPDATE country SET name = "Slovakia", prefix = REPLACE("421", "-", "") WHERE alpha_3 = SUBSTR("SK / SVK", 6) AND in_use = 0',
            'UPDATE country SET name = "Slovenia", prefix = REPLACE("386", "-", "") WHERE alpha_3 = SUBSTR("SI / SVN", 6) AND in_use = 0',
            'UPDATE country SET name = "Solomon Islands", prefix = REPLACE("677", "-", "") WHERE alpha_3 = SUBSTR("SB / SLB", 6) AND in_use = 0',
            'UPDATE country SET name = "Somalia", prefix = REPLACE("252", "-", "") WHERE alpha_3 = SUBSTR("SO / SOM", 6) AND in_use = 0',
            'UPDATE country SET name = "South Africa", prefix = REPLACE("27", "-", "") WHERE alpha_3 = SUBSTR("ZA / ZAF", 6) AND in_use = 0',
            'UPDATE country SET name = "South Korea", prefix = REPLACE("82", "-", "") WHERE alpha_3 = SUBSTR("KR / KOR", 6) AND in_use = 0',
            'UPDATE country SET name = "South Sudan", prefix = REPLACE("211", "-", "") WHERE alpha_3 = SUBSTR("SS / SSD", 6) AND in_use = 0',
            'UPDATE country SET name = "Spain", prefix = REPLACE("34", "-", "") WHERE alpha_3 = SUBSTR("ES / ESP", 6) AND in_use = 0',
            'UPDATE country SET name = "Sri Lanka", prefix = REPLACE("94", "-", "") WHERE alpha_3 = SUBSTR("LK / LKA", 6) AND in_use = 0',
            'UPDATE country SET name = "Sudan", prefix = REPLACE("249", "-", "") WHERE alpha_3 = SUBSTR("SD / SDN", 6) AND in_use = 0',
            'UPDATE country SET name = "Suriname", prefix = REPLACE("597", "-", "") WHERE alpha_3 = SUBSTR("SR / SUR", 6) AND in_use = 0',
            'UPDATE country SET name = "Svalbard and Jan Mayen", prefix = REPLACE("47", "-", "") WHERE alpha_3 = SUBSTR("SJ / SJM", 6) AND in_use = 0',
            'UPDATE country SET name = "Swaziland", prefix = REPLACE("268", "-", "") WHERE alpha_3 = SUBSTR("SZ / SWZ", 6) AND in_use = 0',
            'UPDATE country SET name = "Sweden", prefix = REPLACE("46", "-", "") WHERE alpha_3 = SUBSTR("SE / SWE", 6) AND in_use = 0',
            'UPDATE country SET name = "Switzerland", prefix = REPLACE("41", "-", "") WHERE alpha_3 = SUBSTR("CH / CHE", 6) AND in_use = 0',
            'UPDATE country SET name = "Syria", prefix = REPLACE("963", "-", "") WHERE alpha_3 = SUBSTR("SY / SYR", 6) AND in_use = 0',
            'UPDATE country SET name = "Taiwan", prefix = REPLACE("886", "-", "") WHERE alpha_3 = SUBSTR("TW / TWN", 6) AND in_use = 0',
            'UPDATE country SET name = "Tajikistan", prefix = REPLACE("992", "-", "") WHERE alpha_3 = SUBSTR("TJ / TJK", 6) AND in_use = 0',
            'UPDATE country SET name = "Tanzania", prefix = REPLACE("255", "-", "") WHERE alpha_3 = SUBSTR("TZ / TZA", 6) AND in_use = 0',
            'UPDATE country SET name = "Thailand", prefix = REPLACE("66", "-", "") WHERE alpha_3 = SUBSTR("TH / THA", 6) AND in_use = 0',
            'UPDATE country SET name = "Togo", prefix = REPLACE("228", "-", "") WHERE alpha_3 = SUBSTR("TG / TGO", 6) AND in_use = 0',
            'UPDATE country SET name = "Tokelau", prefix = REPLACE("690", "-", "") WHERE alpha_3 = SUBSTR("TK / TKL", 6) AND in_use = 0',
            'UPDATE country SET name = "Tonga", prefix = REPLACE("676", "-", "") WHERE alpha_3 = SUBSTR("TO / TON", 6) AND in_use = 0',
            'UPDATE country SET name = "Trinidad and Tobago", prefix = REPLACE("1-868", "-", "") WHERE alpha_3 = SUBSTR("TT / TTO", 6) AND in_use = 0',
            'UPDATE country SET name = "Tunisia", prefix = REPLACE("216", "-", "") WHERE alpha_3 = SUBSTR("TN / TUN", 6) AND in_use = 0',
            'UPDATE country SET name = "Turkey", prefix = REPLACE("90", "-", "") WHERE alpha_3 = SUBSTR("TR / TUR", 6) AND in_use = 0',
            'UPDATE country SET name = "Turkmenistan", prefix = REPLACE("993", "-", "") WHERE alpha_3 = SUBSTR("TM / TKM", 6) AND in_use = 0',
            'UPDATE country SET name = "Turks and Caicos Islands", prefix = REPLACE("1-649", "-", "") WHERE alpha_3 = SUBSTR("TC / TCA", 6) AND in_use = 0',
            'UPDATE country SET name = "Tuvalu", prefix = REPLACE("688", "-", "") WHERE alpha_3 = SUBSTR("TV / TUV", 6) AND in_use = 0',
            'UPDATE country SET name = "U.S. Virgin Islands", prefix = REPLACE("1-340", "-", "") WHERE alpha_3 = SUBSTR("VI / VIR", 6) AND in_use = 0',
            'UPDATE country SET name = "Uganda", prefix = REPLACE("256", "-", "") WHERE alpha_3 = SUBSTR("UG / UGA", 6) AND in_use = 0',
            'UPDATE country SET name = "Ukraine", prefix = REPLACE("380", "-", "") WHERE alpha_3 = SUBSTR("UA / UKR", 6) AND in_use = 0',
            'UPDATE country SET name = "United Arab Emirates", prefix = REPLACE("971", "-", "") WHERE alpha_3 = SUBSTR("AE / ARE", 6) AND in_use = 0',
            'UPDATE country SET name = "United Kingdom", prefix = REPLACE("44", "-", "") WHERE alpha_3 = SUBSTR("GB / GBR", 6) AND in_use = 0',
            'UPDATE country SET name = "United States", prefix = REPLACE("1", "-", "") WHERE alpha_3 = SUBSTR("US / USA", 6) AND in_use = 0',
            'UPDATE country SET name = "Uruguay", prefix = REPLACE("598", "-", "") WHERE alpha_3 = SUBSTR("UY / URY", 6) AND in_use = 0',
            'UPDATE country SET name = "Uzbekistan", prefix = REPLACE("998", "-", "") WHERE alpha_3 = SUBSTR("UZ / UZB", 6) AND in_use = 0',
            'UPDATE country SET name = "Vanuatu", prefix = REPLACE("678", "-", "") WHERE alpha_3 = SUBSTR("VU / VUT", 6) AND in_use = 0',
            'UPDATE country SET name = "Vatican", prefix = REPLACE("379", "-", "") WHERE alpha_3 = SUBSTR("VA / VAT", 6) AND in_use = 0',
            'UPDATE country SET name = "Venezuela", prefix = REPLACE("58", "-", "") WHERE alpha_3 = SUBSTR("VE / VEN", 6) AND in_use = 0',
            'UPDATE country SET name = "Vietnam", prefix = REPLACE("84", "-", "") WHERE alpha_3 = SUBSTR("VN / VNM", 6) AND in_use = 0',
            'UPDATE country SET name = "Wallis and Futuna", prefix = REPLACE("681", "-", "") WHERE alpha_3 = SUBSTR("WF / WLF", 6) AND in_use = 0',
            'UPDATE country SET name = "Western Sahara", prefix = REPLACE("212", "-", "") WHERE alpha_3 = SUBSTR("EH / ESH", 6) AND in_use = 0',
            'UPDATE country SET name = "Yemen", prefix = REPLACE("967", "-", "") WHERE alpha_3 = SUBSTR("YE / YEM", 6) AND in_use = 0',
            'UPDATE country SET name = "Zambia", prefix = REPLACE("260", "-", "") WHERE alpha_3 = SUBSTR("ZM / ZMB", 6) AND in_use = 0',
            'UPDATE country SET name = "Zimbabwe", prefix = REPLACE("263", "-", "") WHERE alpha_3 = SUBSTR("ZW / ZWE", 6) AND in_use = 0',
        ];
        foreach ($sqls as $sql) {
            $this->execute($sql);
        }
    }
}
