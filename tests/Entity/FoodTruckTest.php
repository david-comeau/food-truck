<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\FoodTruck;
use PHPUnit\Framework\TestCase;

class FoodTruckTest extends TestCase
{
    private FoodTruck $foodTruck;

    protected function setUp(): void
    {
        $this->foodTruck = new FoodTruck();
    }

    public function testGetSetApplicant(): void
    {
        $this->foodTruck->setApplicant('Test Applicant');
        $this->assertEquals('Test Applicant', $this->foodTruck->getApplicant());
    }

    public function testGetSetFoodItems(): void
    {
        $this->foodTruck->setFoodItems('Tacos, Burritos');
        $this->assertEquals('Tacos, Burritos', $this->foodTruck->getFoodItems());
    }

    public function testGetSetAddress(): void
    {
        $this->foodTruck->setAddress('123 Test St');
        $this->assertEquals('123 Test St', $this->foodTruck->getAddress());
    }

    public function testGetSetLatitude(): void
    {
        $this->foodTruck->setLatitude('37.7749');
        $this->assertEquals('37.7749', $this->foodTruck->getLatitude());
    }

    public function testGetSetLongitude(): void
    {
        $this->foodTruck->setLongitude('-122.4194');
        $this->assertEquals('-122.4194', $this->foodTruck->getLongitude());
    }
}
