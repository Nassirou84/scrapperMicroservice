<?php

namespace App\Controller;

use App\Service\BrightDataService;
use App\Service\DataSyncService;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class ScrapperController extends AbstractController
{
    #[Route('/api/scrapper', name: 'app_home')]
    public function index(
        Request $request,
        BrightDataService $brightData,
        CacheInterface $cache,
        DataSyncService $dataSync,
        NormalizerInterface $normalizer,
    ): JsonResponse {

        $url = (string) $request->query->get('url');

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->json(['error' => 'Invalid url parameter'], 400);
        }

        try {
            $productData = $cache->get('scrape_' . md5($url), function (ItemInterface $item) use ($brightData, $url) {
                $data = $brightData->scrapeProduct($url);
                if (null === $data) {
                    return null;
                }
                $item->expiresAfter(null === $data ? 0 : 7200);
                return $data;
            });
        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }

        if (null === $productData) {
            return $this->json(['error' => 'Scrape failed, check the PHP error log'], 502);
        }

        $synchronizedProduct = $dataSync->synchronizeData($productData, parse_url($url, PHP_URL_HOST));

        if (false === $synchronizedProduct) {
            return $this->json(['error' => 'Synchronization failed'], 500);
        }

        $normalizedProduct = $normalizer->normalize($synchronizedProduct);

        return $this->json($normalizedProduct, 200);
    }

    #[Route('/api/batch', name: 'app_batch')]
    public function batch(Request $request, BrightDataService $brightData): JsonResponse
    {
        $urls = $request->query->all('urls');

        try {
            $products = $brightData->scrapeMultipleProducts($urls);
        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }

        if (null === $products) {
            return $this->json(['error' => 'Scrape failed, check the PHP error log'], 502);
        }

        return $this->json(['products' => $products]);
    }
}