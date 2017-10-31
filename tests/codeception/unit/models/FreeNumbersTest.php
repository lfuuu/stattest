<?php

namespace tests\codeception\unit\models;

use app\models\filter\FreeNumberFilter;
use app\modules\nnp\models\NdcType;
use yii\codeception\TestCase;

class FreeNumbersTest extends TestCase
{
    // Фикстуры использовать нельзя, ибо unload() удаляет все данные из таблицы и веб-тесты падают
//    protected function setUp()
//    {
//        parent::setUp();
//        (new NumberFixture)->load();
//    }
//
//    protected function tearDown()
//    {
//        (new NumberFixture)->unload();
//        parent::tearDown();
//    }

    public function testAllFreeNumbers()
    {
        $numbers = new FreeNumberFilter;
        $this->assertEquals(126, count($numbers->setLimit(200)->result()));
    }

    public function testRegularFreeNumbers()
    {
        $numbers = new FreeNumberFilter;
        $this->assertEquals(23, count($numbers->setNdcType(NdcType::ID_GEOGRAPHIC)->setLimit(100)->result()));
    }

    public function test7800FreeNumbers()
    {
        $numbers = new FreeNumberFilter;
        $this->assertEquals(103, count($numbers->setNdcType(NdcType::ID_FREEPHONE)->setLimit(200)->result()));
    }

    public function testFreeNumbersByRegions()
    {
        // Москва
        $numbers = new FreeNumberFilter;
        $numbers->regions = [99];
        $this->assertEquals(123, count($numbers->setLimit(200)->result()));

        // Санкт-Петербург
        $numbers = new FreeNumberFilter;
        $numbers->regions = [81];
        $this->assertEquals(3, count($numbers->setLimit(100)->result()));

        // Оба
        $numbers = new FreeNumberFilter;
        $numbers->regions = [99, 81];
        $this->assertEquals(126, count($numbers->setLimit(200)->result()));
    }

    public function testFreeNumbersByCost()
    {
        // Between
        $numbers =
            (new FreeNumberFilter)
                ->setMinCost(0)
                ->setMaxCost(1999);
        $this->assertEquals(20, count($numbers->setLimit(100)->result()));

        // Between
        $numbers =
            (new FreeNumberFilter)
                ->setMinCost(999)
                ->setMaxCost(1999);
        $this->assertEquals(2, count($numbers->setLimit(100)->result()));

        // Greater than minCost
        $numbers =
            (new FreeNumberFilter)
                ->setMinCost(999);
        $this->assertEquals(2, count($numbers->setLimit(100)->result()));

        // Less than maxCost
        $numbers =
            (new FreeNumberFilter)
                ->setMaxCost(999);
        $this->assertEquals(18, count($numbers->setLimit(100)->result()));
    }

    public function testFreeNumbersByBeautyLvl()
    {
        // Стандартные номера
        $numbers = new FreeNumberFilter;
        $numbers->beautyLvl = [0];
        $this->assertEquals(121, count($numbers->setLimit(200)->result()));

        // Бронзовые номера
        $numbers = new FreeNumberFilter;
        $numbers->beautyLvl = [4];
        $this->assertEquals(2, count($numbers->setLimit(100)->result()));

        // Несколько
        $numbers = new FreeNumberFilter;
        $numbers->beautyLvl = [0, 4];
        $this->assertEquals(123, count($numbers->setLimit(200)->result()));

        // Negative
        $numbers = new FreeNumberFilter;
        $numbers->beautyLvl = [10];
        $this->assertEquals(0, count($numbers->setLimit(100)->result()));
    }

    public function testFreeNumbersByMask()
    {
        $numbers = new FreeNumberFilter;
        $numbers->numberMask = 'XYZABYZ';

        $this->assertEquals(1, count($numbers->setLimit(100)->result()));

        $numbers = new FreeNumberFilter;
        $numbers->numberMask = 'XYY';

        $this->assertEquals(3, count($numbers->setLimit(100)->result()));

        $numbers = new FreeNumberFilter;
        $numbers->numberMask = '000';

        $this->assertEquals(12, count($numbers->setLimit(100)->result()));

        $numbers = new FreeNumberFilter;
        $numbers->numberMask = '13000';

        $this->assertEquals(9, count($numbers->setLimit(100)->result()));

        $numbers = new FreeNumberFilter;
        $numbers->numberMask = '*002';

        $this->assertEquals(3, count($numbers->setLimit(100)->result()));

        $numbers = new FreeNumberFilter;
        $numbers->numberMask = '*XXA';

        $this->assertEquals(28, count($numbers->setLimit(100)->result()));

        $numbers = new FreeNumberFilter;
        $numbers->numberMask = '*XY00XY';

        $this->assertEquals(1, count($numbers->setLimit(100)->result()));
    }

    public function testFreeNumbersOffset()
    {
        $numbers = new FreeNumberFilter;
        $firstPage = $numbers->setLimit(21)->result();

        $numbers = new FreeNumberFilter;
        $secondPage = $numbers->setOffset(20)->setLimit(20)->result();

        $this->assertEquals(21, count($firstPage));
        $this->assertLessThanOrEqual(20, count($secondPage));
        $this->assertNotEquals(reset($firstPage)->number, reset($secondPage)->number); // offset работает
    }

    public function testFreeNumbersSimilar()
    {
        $numbers = new FreeNumberFilter;
        $result = $numbers->setSimilar(2130003)->setLimit(100)->result();

        $firstNumber = array_shift($result);
        $secondNumber = array_shift($result);
        $thirdNumber = array_shift($result);

        $this->assertGreaterThanOrEqual($firstNumber->levenshtein, $secondNumber->levenshtein);
        $this->assertGreaterThanOrEqual($secondNumber->levenshtein, $thirdNumber->levenshtein);
    }

}