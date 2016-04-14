<?php

namespace tests\codeception\unit\models;

use Yii;
use yii\codeception\TestCase;
use app\exceptions\web\BadRequestHttpException;
use app\models\filter\FreeNumberFilter;
use app\tests\codeception\fixtures\NumberFixture;

class FreeNumbersTest extends TestCase
{

    private $filter;

    protected function setUp()
    {
        parent::setUp();
        $this->filter = new FreeNumberFilter;

        (new NumberFixture)->load();
    }

    public function testAllNumbers()
    {
        $numbers = new FreeNumberFilter;
        $this->assertEquals(22, count($numbers->result(null)));
    }

    public function testRegularNubmers()
    {
        $numbers = new FreeNumberFilter;
        $this->assertEquals(20, count($numbers->numbers->result(null)));
    }

    public function testNumbers7800()
    {
        $numbers = new FreeNumberFilter;
        $this->assertEquals(2, count($numbers->numbers7800->result(null)));
    }

    public function testNumbersByRegions()
    {
        // Москва
        $numbers = new FreeNumberFilter;
        $numbers->regions = [99];
        $this->assertEquals(19, count($numbers->result(null)));

        // Санкт-Петербург
        $numbers = new FreeNumberFilter;
        $numbers->regions = [81];
        $this->assertEquals(3, count($numbers->result(null)));

        // Оба
        $numbers = new FreeNumberFilter;
        $numbers->regions = [99, 81];
        $this->assertEquals(22, count($numbers->result(null)));
    }

    public function testNumbersByCost()
    {
        // Between
        $numbers = new FreeNumberFilter;
        $numbers->minCost = 0;
        $numbers->maxCost = 1999;
        $this->assertEquals(22, count($numbers->result(null)));

        // Between
        $numbers = new FreeNumberFilter;
        $numbers->minCost = 999;
        $numbers->maxCost = 1999;
        $this->assertEquals(9, count($numbers->result(null)));

        // Greater than minCost
        $numbers = new FreeNumberFilter;
        $numbers->minCost = 999;
        $this->assertEquals(9, count($numbers->result(null)));

        // Less than maxCost
        $numbers = new FreeNumberFilter;
        $numbers->maxCost = 999;
        $this->assertEquals(20, count($numbers->result(null)));
    }

    public function testNumbersByBeautyLvl()
    {
        // Стандартные номера
        $numbers = new FreeNumberFilter;
        $numbers->beautyLvl = 0;
        $this->assertEquals(20, count($numbers->result(null)));

        // Бронзовые номера
        $numbers = new FreeNumberFilter;
        $numbers->beautyLvl = 4;
        $this->assertEquals(2, count($numbers->result(null)));

        // Negative
        try {
            $numbers = new FreeNumberFilter;
            $numbers->beautyLvl = 10;
        }
        catch (BadRequestHttpException $e) {
            $this->assertFalse(false);
        }
    }

    public function testNumbersByMask()
    {
        $numbers = new FreeNumberFilter;
        $numbers->numberMask = '%9213%';
        $this->assertEquals(20, count($numbers->result(null)));

        // Negative
        try {
            $numbers = new FreeNumberFilter;
            $numbers->numberMask = '%92%';
        }
        catch (BadRequestHttpException $e) {
            $this->assertFalse(false);
        }

        $numbers = new FreeNumberFilter;
        $numbers->numberMask = '%9213000%';
        $this->assertEquals(9, count($numbers->result(null)));

        $numbers = new FreeNumberFilter;
        $numbers->numberMask = '%92130003%';
        $this->assertEquals(1, count($numbers->result(null)));
    }

    public function testNumbersOffset()
    {
        $numbers = new FreeNumberFilter;
        $firstPage = $numbers->result();

        $numbers = new FreeNumberFilter;
        $secondPage = $numbers->setOffset(FreeNumberFilter::FREE_NUMBERS_LIMIT)->result();

        $this->assertEquals(FreeNumberFilter::FREE_NUMBERS_LIMIT, count($firstPage));
        $this->assertLessThan(FreeNumberFilter::FREE_NUMBERS_LIMIT, count($secondPage));
    }

    public function testNumbersLimit()
    {
        // Лимит 5
        $numbers = new FreeNumberFilter;
        $this->assertEquals(5, count($numbers->result(5)));

        // Сколько установлено в FREE_NUMBERS_LIMIT
        $numbers = new FreeNumberFilter;
        $this->assertEquals(FreeNumberFilter::FREE_NUMBERS_LIMIT, count($numbers->result()));

        // Всё
        $numbers = new FreeNumberFilter;
        $this->assertEquals(22, count($numbers->result(null)));
    }

}