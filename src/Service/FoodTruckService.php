<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use App\Entity\FoodTruck;

class FoodTruckService
{
    private HttpClientInterface $client;
    private string $apiUrl;
    private string $localFile;
    private SerializerInterface $serializer;

    public function __construct(
        HttpClientInterface $client,
        string $apiUrl,
        string $localFile,
        SerializerInterface $serializer,
    ) {
        $this->client = $client;
        $this->apiUrl = $apiUrl;
        $this->localFile = $localFile;
        $this->serializer = $serializer;
    }

    /**
     * @return array<FoodTruck>
     */
    public function getApiData(): array
    {
        try {
            $response = $this->client->request('GET', $this->apiUrl, [
                'timeout' => 5,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $content = $response->getContent();

            /** @var array<FoodTruck> $data */
            $data = $this->serializer->deserialize($content, FoodTruck::class . '[]', 'json');

            return $data;
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException('Network error while fetching API data: ' . $e->getMessage(), 0, $e);
        } catch (HttpExceptionInterface $e) {
            throw new \RuntimeException('HTTP error while fetching API data: ' . $e->getMessage(), 0, $e);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Invalid JSON received from API: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            throw new \RuntimeException('Unexpected error while fetching API data: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @return array<FoodTruck>
     */
    public function getLocalData(): array
    {
        try {
            /** @var array<FoodTruck> $data */
            $data = $this->serializer->deserialize(
                file_get_contents($this->localFile),
                FoodTruck::class . '[]',
                'json'
            );

            return $data;
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Invalid JSON format in local file: ' . $e->getMessage(), 0, $e);
        } catch (\RuntimeException $e) {
            throw new \RuntimeException('Failed to read local file: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            throw new \RuntimeException('Unexpected error while loading local data: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array<FoodTruck> $data
     * @return array<FoodTruck>
     */
    public function tokenizedSearch(array $data, string $searchTerm): array
    {
        //  find quoted phrases and treat as complete serach tokens
        preg_match_all('/"([^"]+)"|\'([^\']+)\'|\S+/', $searchTerm, $matches);

        //  clean up token strings
        $searchWords = array_map(function ($match) {
            return strtolower(trim($match, "'\""));
        }, $matches[0]);

        return array_filter($data, function ($item) use ($searchWords) {
            $applicant = strtolower($item->getApplicant() ?? '');
            $foodItems = strtolower($item->getFoodItems() ?? '');
            $combinedText = $applicant . ' ' . $foodItems;

            //  match all tokens
            foreach ($searchWords as $word) {
                if (stripos($combinedText, $word) === false) {
                    return false;
                }
            }
            return true;
        });
    }
}
