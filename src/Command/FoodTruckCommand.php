<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\FoodTruckService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\FoodTruck;

class FoodTruckCommand extends Command
{
    protected static string $defaultName = 'app:food-truck';
    private FoodTruckService $foodTruckService;
    private SymfonyStyle $io;
    private LoggerInterface $logger;
    private const RESULTS_PER_GROUP = 5;

    public function __construct(FoodTruckService $foodTruckService, LoggerInterface $logger)
    {
        parent::__construct();
        $this->foodTruckService = $foodTruckService;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:food-truck')
            ->setDescription('Interact with the food truck API from data.sfgov.org.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        try {
            $useAPI = $this->io->choice('Use live API?', ['no', 'yes'], 'yes');
            $terms = $this->io->ask('Eearch search terms (space delimited).');

            $data = $useAPI == 'no' ? $this->foodTruckService->getLocalData() : $this->foodTruckService->getApiData();

            if ($terms != '') {
                $results = $this->foodTruckService->tokenizedSearch($data, $terms);
            } else {
                $results = $data;
            }

            $this->outputResults($results);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error('Error fetching food truck data: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            $this->io->error('An error occurred while fetching food truck data. Please try again later.');

            return Command::FAILURE;
        }
    }

    /**
     * @param array<FoodTruck> $results
     */
    private function outputResults(array $results): void
    {
        $total = count($results);

        if ($results) {
            $this->io->info(sprintf('%s result(s) found.', count($results)));

            $counter = 0;

            foreach ($results as $index => $truck) {
                $counter++;

                $googleMapsURL = ($truck->getLatitude() && $truck->getLongitude())
                    ? sprintf('http://maps.google.com/?q=%s,%s', $truck->getLatitude(), $truck->getLongitude())
                    : 'Unknown coordinates, see address.';

                $this->io->title($truck->getApplicant());

                $this->io->listing([
                    $truck->getFoodItems(),
                    $truck->getAddress(),
                    $googleMapsURL
                ]);

                $this->io->newLine(2);

                if ($counter % self::RESULTS_PER_GROUP == 0 && $counter != $total) {
                    $userInput = $this->io->ask(
                        sprintf(
                            '%s/%s result(s) displayed. | Press enter to continue OR q then enter to quit.',
                            $counter,
                            $total
                        )
                    );

                    if (strtolower(trim($userInput ?? '')) == 'q') {
                        break;
                    }
                }
            }

            if ($counter == $total) {
                $this->io->info(sprintf('%s/%s result(s) displayed.', $counter, $total));
            }
        } else {
            $this->io->info('No results found.');
        }
    }
}
