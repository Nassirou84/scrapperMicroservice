<?php

declare(strict_types=1);

namespace App\Service;

use InvalidArgumentException;

use function count;
use function is_array;
use function str_contains;

use const CURLINFO_HTTP_CODE;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_TIMEOUT;
use const CURLOPT_URL;
use const JSON_THROW_ON_ERROR;
use const PHP_URL_HOST;

class BrightDataService
{
    private string $baseUrl = 'https://api.brightdata.com/datasets/v3';

    /**
     * @param array<string, string> $datasets Bright Data dataset ID per marketplace, keyed by a host fragment (e.g. "ebay.")
     */
    public function __construct(
        private readonly string $apiKey,
        private readonly array $datasets,
    ) {
    }

    /**
     * Scrape a single Amazon product by URL.
     *
     * @return array|null Product data or null on failure
     */
    public function scrapeProduct(string $url): ?array
    {
        return $this->scrape($this->datasetFor($url), [['url' => $url]])[0] ?? null;
    }

    /**
     * Scrape multiple products in one request (up to 20 URLs).
     *
     * @return array|null Array of product data or null on failure
     */
    public function scrapeMultipleProducts(array $urls): ?array
    {
        if (count($urls) > 20) {
            throw new InvalidArgumentException('Maximum 20 URLs per request for synchronous scraping');
        }

        if ([] === $urls) {
            return [];
        }

        $urls = array_values($urls);

        // Each dataset only understands its own site, so the batch must not mix sites.
        return $this->scrape(
            $this->datasetFor($urls[0]),
            array_map(static fn ($url) => ['url' => $url], $urls),
        );
    }

    private function datasetFor(string $url): string
    {
        $host = (string) parse_url($url, PHP_URL_HOST);

        foreach ($this->datasets as $hostFragment => $datasetId) {
            if (str_contains($host, (string) $hostFragment)) {
                if ('' === $datasetId) {
                    throw new InvalidArgumentException("No Bright Data dataset ID configured for {$host}");
                }

                return $datasetId;
            }
        }

        throw new InvalidArgumentException("Unsupported marketplace URL: {$url}");
    }

    /**
     * @param list<array<string, mixed>> $inputs
     *
     * @return list<array<string, mixed>>|null
     */
    private function scrape(string $datasetId, array $inputs): ?array
    {
        // Sync scrapes can take well over php.ini's 30s max_execution_time under php-fpm,
        // which kills the worker mid-request (proxy reports "unexpected EOF").
        set_time_limit(120);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/scrape?' . http_build_query([
                'dataset_id' => $datasetId,
                'format' => 'json',
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            // The synchronous /scrape endpoint expects {"input": [...]}, not a bare array.
            CURLOPT_POSTFIELDS => json_encode(['input' => $inputs], JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 100,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if (false === $response) {
            error_log("Bright Data request failed: {$curlError}");

            return null;
        }

        // 202 means the scrape took too long and was turned into an async snapshot.
        if (200 !== $httpCode) {
            error_log("Bright Data API error: HTTP {$httpCode} - " . substr((string) $response, 0, 300));

            return null;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            return null;
        }

        // A single input may come back as one object rather than a list.
        return array_is_list($data) ? $data : [$data];
    }
}
