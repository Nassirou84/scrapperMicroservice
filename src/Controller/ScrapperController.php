<?php

namespace App\Controller;

use App\Service\BrightDataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ScrapperController extends AbstractController
{
    #[Route('/api/home', name: 'app_home')]
    public function index(
        BrightDataService $brightDataAmazonScraper,
        CacheInterface $cacheInterface,
    ): JsonResponse {
        // This scrapes a fixed demo URL; caching avoids paying for a fresh
        // Bright Data scrape on every single homepage hit.
        $productData = $cacheInterface->get('home_demo_product', function (ItemInterface $item) use ($brightDataAmazonScraper) {
            $item->expiresAfter(3600);

            return $brightDataAmazonScraper->scrapeProduct('https://www.amazon.com/Google-Pixel-Smartphone-Assistant-Fast-Charging/dp/B0FFTRK635/ref=sr_1_1_sspa?crid=2XUZK83JRAF8A&dib=eyJ2IjoiMSJ9.J2DHLcRIkKP6zLt74HGwdbzEvhuQRVyH13B9JaSC3_61QNJu3eLChmkuCLBe8nmyn7Xoysb9jo_MUCMQe9c6XZ2QxjLXvdtNzSyACIEVwMSmo2ZaqPZMP9-CqYncEyfP4-peyrcArUdT2CLEYrt18iRyu4hzTCP6DbtUsBJyZmol-Cvt4r5L67F92bMnPBuu-wI649QUa8uzB0b40vANjpIhmFTjPM210SYk4KR-9zY.LI1vqNBgOYUvE_076UHOof7tx_48JtHywwGRz48fV2s&dib_tag=se&keywords=google%2Bpixel&qid=1783688970&sprefix=google%2Bpixel%2Caps%2C181&sr=8-1-spons&ufe=app_do%3Aamzn1.fos.0f891610-7b8b-4b91-8ba6-963fbc5b64cf&sp_csd=d2lkZ2V0TmFtZT1zcF9hdGY&th=1');
        });

        return $this->json([
            'product_data' => $productData,
        ]);
    }
}