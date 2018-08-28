<?php

namespace app\dao;

use app\classes\Singleton;
use app\models\DidGroup;

/**
 * @method static NumberBeautyDao me($args = null)
 */
class NumberBeautyDao extends Singleton
{
    const DEFAULT_POSTFIX_LENGTH = 7;

    public static $beautyLvls = [
        DidGroup::BEAUTY_LEVEL_EXCLUSIVE => [
            6 => [
                /**
                 * @example XX-XX-XX 22-22-22
                 * @example XX-XX-YX 66-66-26
                 * @example XX-YY-YY 66-22-22
                 * @example XX-XX-YY 66-66-22
                 * @example XX-XY-YY 66-62-22
                 */
                '/^(\d)\1{5}$/',
                '/^(\d)\1{3}\d\1{1}$/',
                '/^(\d)\1{1}(\d)\2{3}$/',
                '/^(\d)\1{3}(\d)\2{1}$/',
                '/^(\d)\1{2}(\d)\2{2}$/',
                /**
                 * @example Z-XXXXX 28-88-88
                 * @example YYYYY-X 77-77-70
                 */
                '/^\d(\d)\1{4}$/',
                '/^(\d)\1{4}\d$/',
            ],
            7 => [
                /**
                 * @example XXXXXXX 222-22-22
                 */
                '/^(\d)\1{6}$/',
                /**
                 * @example Z-XXXXXX 288-88-88
                 * @example YYYYYY-X 7-777-770
                 */
                '/^(\d)\1{5}\d$/',
                '/^\d(\d)\1{5}$/',
                /**
                 * @example XXX-Y-XXX 666-2-666
                 */
                '/^(\d)\1{2}\d\1{3}$/',
                /**
                 * @example XYX-XX-XX 939-99-99
                 * @example XX-Y-XXXX 44-3-4444
                 */
                '/^(\d)\d\1{5}$/',
                '/^(\d)\1\d\1{3}$/',
                /**
                 * @example XX-YYYYY 225-55-55
                 * @example XXXXX-YY 333-33-44
                 * @example X-YYYYY-X 7-00000-7
                 */
                '/^(\d)\1(\d)\2{4}$/',
                '/^(\d)\1{4}(\d)\2$/',
                '/^(\d)(\d)\2{4}\1$/',
                /**
                 * @example XXX-YYYY 444-22-22
                 * @example XXXX-YYY 22-22-444
                 */
                '/^(\d)\1{2}(\d)\2{3}$/',
                '/^(\d)\1{3}(\d)\2{2}$/',
                /**
                 * @example X-YY-XXXX 2-33-2222
                 */
                '/^(\d)(\d)\2\1{4}$/',
            ],
        ],
        DidGroup::BEAUTY_LEVEL_PLATINUM => [
            6 => [
                /**
                 * @example XX-YX-XX 66-26-66
                 * @example XX-XY-XX 66-62-66
                 */
                '/^(\d)\1{1}\d\1{3}$/',
                '/^(\d)\1{2}\d\1{2}$/',
                /**
                 * @example XX-YY-XX 22-66-22
                 * @example XX-XX-NUM2 22-22-NUM2
                 * @example XY-XY-XY 62-62-62
                 * @example XY-XY-YY 62-62-22
                 * @example XX-YX-XY 662-662
                 * @example XY-ZX-YZ 621-621
                 */
                '/^(\d)\1{1}(\d)\2{1}\1{2}$/',
                '/^(\d)\1{3}\d\d$/',
                '/^(\d{2})\1{2}$/',
                '/^(\d)(\d)\1{1}\2{3}$/',
                '/^(\d)\1{1}(\d)\1{2}\2{1}$/',
                '/^(\d)(\d)(\d)\1{1}\2{1}\3{1}$/',
            ],
            7 => [
                /**
                 * @example XXXX-Y-XX 2222-3-22
                 * @example XXXXX-Y-X 55555-6-5
                 */
                '/^(\d)\1{3}\d\1{2}$/',
                '/^(\d)\1{4}\d\1$/',
                /**
                 * @example XX-YYY-XX 22-555-22
                 */
                '/^(\d)\1(\d)\2{2}\1{2}$/',
                /**
                 * @example XXXX-YY-X 222-23-32
                 * @example XX-YY-XXX 22-33-222
                 * @example XXX-YY-XX 222-33-22
                 */
                '/^(\d)\1{3}(\d)\2\1$/',
                '/^(\d)\1(\d)\2\1{3}$/',
                '/^(\d)\1{2}(\d)\2\1{2}$/',
                /**
                 * @example XY-ZZZZZ 245-55-55
                 * @example XXXXX-YZ 555-55-32
                 * @example YXXXXXZ 3-55555-2
                 */
                '/^\d\d(\d)\1{4}$/',
                '/^(\d)\1{4}\d\d$/',
                '/^\d(\d)\1{4}\d$/',
                /**
                 * @example XYX-YX-YX 242-42-42
                 * @example XYZ-YZ-YZ 246-46-46
                 * @example XY-XY-XYZ 24-24-246
                 */
                '/^(\d)(\d)\1\2\1\2\1$/',
                '/^\d(\d)(\d)\1\2\1\2$/',
                '/^(\d)(\d)\1\2\1\2\d$/',
                /**
                 * @example X-YYY-XXX 2-555-222
                 * @example X-YYY-ZZZ 2-555-666
                 * @example XXX-YYY-X 222-111-2
                 * @example XXX-YYY-Z 555-666-7
                 * @example XXX-Y-ZZZ 444-1-777
                 */
                '/^\d(\d)\1{2}(\d)\2{2}$/',
                '/^(\d)\1{2}(\d)\2{2}\d$/',
                '/^(\d)\1{2}\d(\d)\2{2}$/',
                /**
                 * @example XXXX-YXY 4444-242
                 * @example XXXX-YYX 4444-224
                 * @example XYY-XXXX 422-4444
                 * @example YXY-XXXX 242-4444
                 */
                '/^(\d)\1{3}(\d)\1\2$/',
                '/^(\d)\1{3}(\d)\2\1$/',
                '/^(\d)(\d)\2\1{4}$/',
                '/^(\d)(\d)\1\2{4}$/',
                /**
                 * @example NUM-ZZ-ZZ 245-88-88
                 * @example ZZ-ZZ-NUM 88-88-245
                 */
                '/^\d\d\d(\d)\1{3}$/',
                '/^(\d)\1{3}\d\d\d$/',
                /**
                 * @example XYYYYXX 244-44-22
                 * @example XXYYYYX 22-444-42
                 */
                '/^(\d)(\d)\2{3}\1{2}$/',
                '/^(\d)\1(\d)\2{3}\1$/',
                /**
                 * @example XXX-YZ-YZ 222-45-45
                 * @example XXX-YX-YX 222-42-42
                 * @example XY-ZZZ-XY 71-999-71
                 * @example XY-XY-ZZZ 45-45-222
                 */
                '/^(\d)\1{2}(\d)(\d)\2\3$/',
                '/^(\d)\1{2}(\d)\1\2\1$/',
                '/^(\d)(\d)(\d)\3{2}\1\2$/',
                '/^(\d)(\d)\1\2(\d)\3{2}$/',
                /**
                 * @example XXY-Z-XXY 556-7-556
                 * @example XYY-Z-XYY 566-7-566
                 * @example XYX-Z-XYX 565-7-565
                 * @example XYZ-A-XYZ 576-9-576
                 */
                '/^(\d)\1(\d)\d\1{2}\2$/',
                '/^(\d)(\d)\2\d\1\2{2}$/',
                '/^(\d)(\d)\1\d\1\2\1$/',
                '/^(\d)(\d)(\d)\d\1\2\3$/',
                /**
                 * @example ABX-XY-XY 247-75-75
                 * @example ABY-XY-XY 375-25-25
                 * @example NUM-XY-XY 245-16-16
                 */
                '/^\d\d\d(\d)(\d)\1\2$/',
            ],
        ],
        DidGroup::BEAUTY_LEVEL_GOLD => [
            6 => [
                /**
                 * @example XX-XY-YX 22-26-62
                 */
                '/^(\d)\1{2}(\d)\2{1}\1{1}$/',
                /**
                 * @example XY-YY-YX 26-66-62
                 */
                '/^(\d)(\d)\2{2}\2{1}\1{1}$/',
                /**
                 * @example XX-YZ-YZ 66-21-21
                 * @example XX-YX-YX 66-26-26
                 * @example XY-ZZ-XY 62-11-62
                 * @example XY-XY-ZZ 62-62-11
                 */
                '/^(\d)\1{1}(\d{2})\2{1}$/',
                '/^(\d)\1{1}(\1{1}\d)\2{1}$/',
                '/^(\d{2})(\d)\2{1}\1{1}$/',
                '/^(\d{2})\1{1}(\d)\2{1}$/',
                /**
                 * @example XX-YZ-XX 66-21-66
                 * @example XY-YZ-XY 62-21-62
                 * @example XY-XZ-XY 62-61-62
                 * @example XY-NUM2-XY 62-17-62
                 */
                '/^(\d)\1{1}\d{2}\1{2}$/',
                '/^(\d)(\d)\2{1}\d\1{1}\2{1}$/',
                '/^(\d)(\d)\1{1}\d\1{1}\2{1}$/',
                '/^(\d{2})\d\d\1{1}$/',
                /**
                 * @example XZ-YX-XX 61-26-66
                 */
                '/^(\d)\d\d\1{3}$/',
                /**
                 * @example ZY-YX-YX 12-26-26
                 */
                '/^\d(\d)\1{1}(\d)\1{1}\2{1}$/',
                /**
                 * @example XY-YX-XY 62-26-62
                 * @example XY-XY-YX 62-62-26
                 * @example XY-XX-XY 62-66-62
                 */
                '/^(\d)(\d)\2{1}\1{1}\1{1}\2{1}$/',
                '/^(\d)(\d)\1{1}\2{2}\1{1}$/',
                '/^(\d)(\d)\1{3}\2{1}$/',
                /**
                 * @example NUM2-XY-XY 78-62-62
                 * @example XY-XY-NUM2 62-62-78
                 */
                '/^\d\d(\d)(\d)\1{1}\2{1}$/',
                '/^(\d)(\d)\1{1}\2{1}\d\d$/',
                /**
                 * @example XX-YY-ZZ 66-22-11
                 */
                '/^(\d)\1{1}(\d)\2{1}(\d)\3{1}$/',
                /**
                 * @example ZX-XX-XY 1-6666-2
                 */
                '/^\d(\d)\1{3}\d$/',
                /**
                 * @example NUM2-XXXX 51-66-66
                 * @example XX-NUM2-XX 66-51-66
                 */
                '/^\d\d(\d)\1{3}$/',
                '/^(\d)\1{1}\d\d\1{2}$/',
                /**
                 * @example YX-XX-ZX 26-66-16
                 * @example YХ-ZХ-ХХ 26-06-66
                 * @example
                 */
                '/^\d(\d)\1{2}\d\1{1}$/',
                '/^\d(\d)\d\1{3}$/',
                /**
                 * @example XY-ZY-XY 62-12-62
                 */
                '/^(\d)(\d)\d\2{1}\1{1}\2{1}$/',
                /**
                 * @example XX-ZY-YA 66-12-20
                 */
                '/^(\d)\1{1}\d(\d)\2{1}\d$/',
                /**
                 * @example XX-YY-YZ 66-222-1
                 * @example ZX-XX-YY 16-66-22
                 */
                '/^(\d)\1{1}(\d)\2{2}\d$/',
                '/^\d(\d)\1{2}(\d)\2{1}$/',

            ],
            7 => [
                /**
                 * @example XX-ZY-XXX 22-40-222
                 * @example XXX-ZY-XX 222-40-22
                 */
                '/^(\d)\1\d\d\1{3}$/',
                '/^(\d)\1{2}\d\d\1{2}$/',
                /**
                 * @example XXY-XY-YY 225-25-55
                 */
                '/^(\d)\1(\d)\1\2{3}$/',
                /**
                 * @example XYZ-XYZ-A 283-283-0
                 * @example XYY-XYY-X 288-288-2
                 * @example XYX-XYX-Z 282-282-9
                 * @example XYY-XYY-Z 288-288-9
                 */
                '/^(\d)(\d)(\d)\1\2\3\d$/',
                '/^(\d)(\d)\2\1\2{2}\1$/',
                '/^(\d)(\d)\1{2}\2\1\d$/',
                '/^(\d)(\d)\2\1\2{2}\d$/',
                /**
                 * @example A-XYZ-XYZ 7-235-235
                 * @example A-XXY-XXY 7-225-225
                 * @example XXY-XXY-A 228-228-5
                 * @example A-XYY-XYY 7-455-455
                 * @example A-XYX-XYX 7-454-454
                 */
                '/^\d(\d)(\d)(\d)\1\2\3$/',
                '/^\d(\d)\1(\d)\1{2}\2$/',
                '/^(\d)\1(\d)\1{2}\2\d$/',
                '/^\d(\d)(\d)\2\1\2{2}$/',
                '/^\d(\d)(\d)\1{2}\2\1$/',
                /**
                 * @example AXX-XY-XY 233-34-34
                 * @example AYY-XY-XY 233-43-43
                 */
                '/^\d(\d)\1{2}(\d)\1\2$/',
                '/^\d(\d)\1(\d)\1\2\1$/',
                /**
                 * @example XYY-XX-YY 655-66-55
                 * @example XXY-XX-YY 665-66-55
                 * @example XYX-YY-XX 656-55-66
                 * @example XYX-XX-YY 656-66-55
                 */
                '/^(\d)(\d)\2\1{2}\2{2}$/',
                '/^(\d)\1(\d)\1{2}\2{2}$/',
                '/^(\d)(\d)\1\2{2}\1{2}$/',
                '/^(\d)(\d)\1{3}\2{2}$/',
                /**
                 * @example XYY-ZZ-BB 244-66-77
                 * @example XYY-ZZ-XX 233-55-22
                 * @example XYY-ZZ-YY 455-66-55
                 * @example XXA-YY-ZZ 663-55-77
                 */
                '/^\d(\d)\1(\d)\2(\d)\3$/',
                '/^(\d)\1\d(\d)\2(\d)\3$/',
                /**
                 * @example XY-ZZZ-YX 24-555-42
                 */
                '/^(\d)(\d)(\d)\3{2}\2\1$/',
                /**
                 * @example XYZ-BB-YZ 245-33-45
                 */
                '/^\d(\d)(\d)(\d)\3\1\2$/',
                /**
                 * @example XY-ZAZ-XY 25-303-25
                 */
                '/^(\d)(\d)(\d)\d\3\1\2$/',
                /**
                 * @example A-XXX-YYB 7-999-552
                 * @example AXX-YYY-B 799-555-2
                 * @example AB-XX-YYY 79-22-555
                 * @example AB-XXX-YY 79-222-55
                 */
                '/^\d(\d)\1{2}(\d)\2\d$/',
                '/^\d(\d)\1(\d)\2{2}\d$/',
                '/^\d\d(\d)\1(\d)\2{2}$/',
                '/^\d\d(\d)\1{2}(\d)\2$/',
                /**
                 * @example XXX-YY-ZX 222-44-02
                 * @example XXX-YY-ZY 222-44-04
                 */
                '/^(\d)\1{2}(\d)\2\d\1$/',
                '/^(\d)\1{2}(\d)\2\d\2$/',
                /**
                 * @example XYY-Z-YYY 277-5-777
                 * @example XXX-YX-ZX 222-42-52
                 */
                '/^\d(\d)\1\d\1{3}$/',
                '/^(\d)\1{2}\d\1\d\1$/',
                /**
                 * @example XXX-AZ-BZ 222-31-51
                 */
                '/^(\d)\1{2}\d(\d)\d\2$/',
                /**
                 * @example AB-XXXX-C 246-66-67
                 * @example A-XXXX-BC 244-44-71
                 * @example AXX-BC-XX 244-58-44
                 * @example XYZZ-A-ZZ 245-57-55
                 * @example XY-ZZZ-AZ 245-55-75
                 */
                '/^\d\d(\d)\1{3}\d$/',
                '/^\d(\d)\1{3}\d\d$/',
                '/^\d(\d)\1\d\d\1{2}$/',
                '/^\d\d(\d)\1\d\1{2}$/',
                '/^\d\d(\d)\1{2}\d\1$/',
                /**
                 * @example NUM-XY-YX 792-34-43
                 */
                '/^\d\d\d(\d)(\d)\2\1$/',
            ],
        ],
        DidGroup::BEAUTY_LEVEL_SILVER => [
            6 => [
                /**
                 * @example XY-YY-XX 26-66-22
                 */
                '/^(\d)(\d)\2{2}\1{2}$/',
                /**
                 * @example XY-YX-XX 26-62-22
                 */
                '/^(\d)(\d)\2{1}\1{3}$/',
                /**
                 * @example XX-XZ-YX 66-61-26
                 */
                '/^(\d)\1{2}\d\d\1{1}$/',
                /**
                 * @example ZX-XX-YX 16-66-26
                 */
                '/^\d(\d)\1{2}\d\1{1}$/',
                /**
                 * @example XX-YY-NUM2 66-22-53
                 * @example XX-NUM2-YY 66-53-22
                 * @example NUM2-XX-YY 53-66-22
                 */
                '/^(\d)\1{1}(\d)\2{1}\d\d$/',
                '/^(\d)\1{1}\d\d(\d)\2{1}$/',
                '/^\d\d(\d)\1{1}(\d)\2{1}$/',
                /**
                 * @example XY-ZZ-YX 62-11-26
                 */
                '/^(\d)(\d)(\d)\3{1}\2{1}\1{1}$/',
                /**
                 * @example XX-XY-YZ 666-221
                 */
                '/^(\d)\1{2}(\d)\2{1}\d$/',
                /**
                 * @example ZX-XY-YY 1-66-222
                 */
                '/^\d(\d)\1{1}(\d)\2{2}$/',
                /**
                 * @example XX-YX-ZX 66-26-16
                 */
                '/^(\d)\1{1}\d\1{1}\d\1{1}$/',
                /**
                 * @example XY-YZ-YY 62-21-22
                 */
                '/^(\d)(\d)\2{1}\d\2{2}$/',
                /**
                 * @example XXX-NUM3 666-NUM3
                 * @example NUM3-XXX NUM3-666
                 * @example YX-XX-NUM2 26-66-NUM2
                 * @example NUM2-XX-XY NUM2-66-62
                 * @example NUM2-XY-YX NUM2-62-26
                 */
                '/^(\d)\1{2}\d\d\d$/',
                '/^\d\d\d(\d)\1{2}$/',
                '/^\d(\d)\1{2}\d\d$/',
                '/^\d\d(\d)\1{2}\d$/',
                '/^\d\d(\d)(\d)\2{1}\1{1}$/',
            ],
            7 => [
                /**
                 * @example XYZ-A-ZZZ 245-3-555
                 */
                '/^\d\d(\d)\d\1{3}$/',
                /**
                 * @example XYZ-AZ-YZ 245-35-45
                 */
                '/^\d(\d)(\d)\d\2\1\2$/',
                /**
                 * @example NUM-Y-XXX 275-8-000
                 * @example AB-XXX-CD 28-000-34
                 * @example NUM-XXX-Y 245-333-9
                 * @example Y-XXX-NUM 2-333-845
                 */
                '/^\d\d\d\d(\d)\1{2}$/',
                '/^\d\d(\d)\1{2}\d\d$/',
                '/^\d\d\d(\d)\1{2}\d$/',
                '/^\d(\d)\1{2}\d\d\d$/',
                /**
                 * @example XY-XY-NUM 64-64-875
                 */
                '/^(\d)(\d)\1\2\d\d\d$/',
                /**
                 * @example XY-NUM-XY 24-351-24
                 */
                '/^(\d)(\d)\d\d\d\1\2$/',
                /**
                 * @example XYZ-AB-YZ 243-75-43
                 */
                '/^\d(\d)(\d)\d\d\1\2$/',
                /**
                 * @example XX-YY-NUM 22-33-875
                 * @example XXX-A-YYB 222-6-778
                 * @example XYY-A-ZZB 266-8-449
                 * @example AXX-BC-YY 266-39-55
                 * @example XX-NUM-YY 33-521-88
                 */
                '/^(\d)\1(\d)\2\d\d\d$/',
                '/^(\d)\1{2}\d(\d)\2\d$/',
                '/^\d(\d)\1\d(\d)\2\d$/',
                '/^\d(\d)\1\d\d(\d)\2$/',
                '/^(\d)\1\d\d\d(\d)\2$/',
                '/^(\d)(\d)(\d)\d\3\2\1$/',
                '/^\d\d(\d)(\d)\2\1\d$/',
                '/^\d(\d)(\d)\1\2\d\d$/',
                /**
                 * @example NUM-XX-YY 283-22-00
                 */
                '/^\d\d\d(\d)\1(\d)\2$/',
                /**
                 * @example XXX-YY-NUM2 666-44-97
                 */
                '/^(\d)\1{2}(\d)\2{1}\d\d$/',
            ],
        ],
        DidGroup::BEAUTY_LEVEL_BRONZE => [
            6 => [
                /**
                 * @example XY-NUM2-YX 62-78-26
                 * @example XX-ZY-AY 66-12-02
                 * @example NUM4-XX NUM4-66
                 * @example NUM2-XX-YX NUM-66-26
                 * @example ZX-YY-XY 16-22-62
                 */
                '/^(\d)(\d)\d\d\2{1}\1{1}$/',
                '/^(\d)\1{1}\d(\d)\d\2{1}$/',
                '/^\d{4}(\d)\1{1}$/',
                '/^\d\d(\d)\1{1}\d\1{1}$/',
                '/^\d(\d)(\d)\2{1}\1{1}\2{1}$/',
            ],
            7 => [
                /**
                 * @example NUM-XX-ZX 231-44-54
                 * @example NUM-XY-ZZ 231-54-77
                 */
                '/^\d\d\d(\d)\1\d\1$/',
                '/^\d\d\d\d\d(\d)\1$/',
            ],
        ],
    ];

    /**
     * Определение красивости номера
     *
     * @param string $number
     * @param int $postfixLength
     * @return int
     */
    public static function getNumberBeautyLvl($number, $postfixLength = self::DEFAULT_POSTFIX_LENGTH)
    {
        $number = substr($number, -$postfixLength);

        foreach (self::$beautyLvls as $key => $value) {
            if (!isset($value[$postfixLength])) {
                continue;
            }
            foreach ($value[$postfixLength] as $expression) {
                if (preg_match($expression, $number)) {
                    return $key;
                }
            }
        }

        // Стандартные номера
        return DidGroup::BEAUTY_LEVEL_STANDART;
    }
}