<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\FoodTruck;
use App\Service\FoodTruckService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Serializer\SerializerInterface;

class FoodTruckServiceTest extends TestCase
{
    private FoodTruckService $foodTruckService;
    private MockHttpClient $mockHttpClient;
    /** @var SerializerInterface&MockObject */
    private $mockSerializer;
    private string $fixtureFile;

    protected function setUp(): void
    {
        $this->mockHttpClient = new MockHttpClient();
        $this->mockSerializer = $this->createMock(SerializerInterface::class);
        $this->fixtureFile = __DIR__ . '/../Fixtures/food_trucks.json';

        $this->foodTruckService = new FoodTruckService(
            $this->mockHttpClient,
            'https://api.example.com/food-trucks',
            $this->fixtureFile,
            $this->mockSerializer
        );
    }

    public function testGetApiData(): void
    {
        $fixtureData = file_get_contents($this->fixtureFile);
        $mockResponse = new MockResponse($fixtureData);
        $this->mockHttpClient->setResponseFactory($mockResponse);

        $expectedData = $this->createFoodTrucksFromFixture();

        $this->mockSerializer->expects($this->once())
            ->method('deserialize')
            ->willReturn($expectedData);

        $result = $this->foodTruckService->getApiData();

        $this->assertEquals($expectedData, $result);
    }

    public function testGetLocalData(): void
    {
        $expectedData = $this->createFoodTrucksFromFixture();

        $this->mockSerializer->expects($this->once())
            ->method('deserialize')
            ->willReturn($expectedData);

        $result = $this->foodTruckService->getLocalData();

        $this->assertEquals($expectedData, $result);
    }

    public function testTokenizedSearch(): void
    {
        $foodTrucks = $this->createFoodTrucksFromFixture();

        $searchTerm = 'hamburger';
        $result = $this->foodTruckService->tokenizedSearch($foodTrucks, $searchTerm);
        $this->assertNotEmpty($result, "Term '$searchTerm' should be found.");

        $found = false;
        foreach ($result as $foodTruck) {
            if (
                stripos($foodTruck->getFoodItems(), $searchTerm) !== false ||
                stripos($foodTruck->getApplicant(), $searchTerm) !== false
            ) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Term '$searchTerm' should be found.");

        $searchTerms = 'hot dog soda';
        $result = $this->foodTruckService->tokenizedSearch($foodTrucks, $searchTerms);
        $this->assertNotEmpty($result, "Terms '$searchTerms' should be found.");

        $result = $this->foodTruckService->tokenizedSearch($foodTrucks, 'nonexistent food item');
        $this->assertEmpty($result, 'Search for nonexistent food item should return no results');
    }

    /**
     * @return FoodTruck[]
     */
    private function createFoodTrucksFromFixture(): array
    {
        $fixtureData = json_decode(file_get_contents($this->fixtureFile), true);
        $foodTrucks = [];

        foreach ($fixtureData as $data) {
            $foodTruck = new FoodTruck();
            $foodTruck->setApplicant($data['applicant'] ?? '');
            $foodTruck->setFoodItems($data['fooditems'] ?? '');
            $foodTruck->setAddress($data['address'] ?? '');
            $foodTruck->setLatitude($data['latitude'] ?? '');
            $foodTruck->setLongitude($data['longitude'] ?? '');
            $foodTrucks[] = $foodTruck;
        }

        return $foodTrucks;
    }
}
