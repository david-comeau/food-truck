<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\FoodTruckCommand;
use App\Entity\FoodTruck;
use App\Service\FoodTruckService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;

#[\PHPUnit\Framework\Attributes\CoversClass(FoodTruckCommand::class)]
class FoodTruckCommandTest extends TestCase
{
    private FoodTruckCommand $command;

    private FoodTruckService&MockObject $mockFoodTruckService;

    private LoggerInterface&MockObject $mockLogger;

    private string $fixtureFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockFoodTruckService = $this->createMock(FoodTruckService::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->command = new FoodTruckCommand($this->mockFoodTruckService, $this->mockLogger);
        $this->fixtureFile = __DIR__ . '/../Fixtures/food_trucks.json';
    }

    public function testExecute(): void
    {
        $foodTrucks = $this->createFoodTrucksFromFixture();

        $this->mockFoodTruckService->expects(self::once())
            ->method('getApiData')
            ->willReturn($foodTrucks);

        $this->mockFoodTruckService->expects(self::never())
            ->method('getLocalData');

        $this->mockFoodTruckService->expects(self::once())
            ->method('tokenizedSearch')
            ->with($foodTrucks, 'Catering')
            ->willReturn([$foodTrucks[0]]);

        $commandTester = new CommandTester($this->command);

        // Simulate user input
        $commandTester->setInputs(['yes', 'Catering']);

        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('1 result(s) found.', $output);
        self::assertStringContainsString('Park\'s Catering', $output);
        self::assertStringContainsString('Cold Truck: Hamburger', $output);
        self::assertStringContainsString('220 NEWHALL ST', $output);
        self::assertStringContainsString('http://maps.google.com/?q=37.743014249631514,-122.38446024493484', $output);
    }

    public function testExecuteWithError(): void
    {
        $this->mockFoodTruckService->expects(self::once())
            ->method('getApiData')
            ->willThrowException(new \Exception('API Error'));

        $this->mockLogger->expects(self::once())
            ->method('error')
            ->with(self::stringContains('Error fetching food truck data:'), self::anything());

        $commandTester = new CommandTester($this->command);

        // Simulate user input
        $commandTester->setInputs(['yes', '']);

        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('An error occurred', $output);
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
