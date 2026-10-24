<?php

namespace App\Service;

use App\Entity\Product;

final class DataSyncService
{
  // Add your data synchronization methods here.
  // Make sure have the same structure for all synchronized data. accross all sources.

  public function AmazonDataSync(array $data): Product
  {

    $product = new Product();

    $isAvailable = $data['availability'] === 'In Stock';

    $collectedCondition = strtolower($data['variant_condition'] ?? '');

    $condition = str_contains($collectedCondition, 'used') ? 'Used' : 'New';
    $condition = str_contains($collectedCondition, 'refurbished') ? 'Refurbished' : $condition;

    $details = $data['product_details'];

    foreach ($data['product_details'] ?? [] as $value) {
      $details[] = [
        'name' => $value['type'],
        'value' => $value['value'] ?? null,
      ];
    }

    $product->setTitle($data['title'] ?? null);
    $product->setScrappingUrl($data['url'] ?? null);
    $product->setUsdPrice($data['final_price'] ?? null);
    $product->setDetails($details);
    $product->setIsAvailable($isAvailable);
    $product->setBrand($data['brand'] ?? null);
    $product->setDescription($data['description'] ?? null);
    $product->setSeller('amazon');
    $product->setProductCondition($condition);
    $product->setOverallRating($data['rating'] ?? null);
    $product->setOverallRatingsCounts($data['reviews_count'] ?? null);
    $product->setReturnPolicy($data['return_policy'] ?? null);
    $product->setAcceptReturn($data['is_returnable'] ?? null);
    $product->setVariants($data['variations'] ?? null);
    $product->setFeatures($data['features'] ?? null);
    $product->setPrimeDelivery($data['amazon_prime'] ?? null);
    $product->setSellerChoice($data['amazon_choice'] ?? null);
    $product->setCustomerSays($data['customer_says'] ?? null);

    return $product;
  }

  public function BestBuyDataSync(array $data): Product
  {
    $productSpecifications = $data['product_specifications'] ?? null;
    $actualSpecs = [];

    foreach ($productSpecifications ?? [] as $spec) {
      if (!$spec['specification_name'] || !$spec['specification_value']) {
        continue;
      }
      if (str_contains(strtolower($spec['specification_name']), 'warranty')) {
        continue;
      }
      $actualSpecs[] = [
        'type' => $spec['specification_name'],
        'value' => $spec['specification_value'],
      ];
    }

    $isAvailable = isset($data['availability']) && count($data['availability']) > 0;

    $collectedCondition = strtolower($data['variant_condition'] ?? '');

    $condition = str_contains($collectedCondition, 'used') ? 'Used' : 'New';
    $condition = str_contains($collectedCondition, 'refurbished') ? 'Refurbished' : $condition;
    if ($data['final_price'] === null) {
      $price = null;
    }
    $price = $data['final_price'] ?? null;
    if (is_string($price)) {
      $price = floatval(str_replace('$', '', $price));
    }

    $returnPolicy = null;

    if (isset($data['return_window']) && $data['return_window']) {
      $returnPolicy = 'Available for return in ' . $data['return_window'] . ' days';
    }

    $product = new Product();
    $product->setTitle($data['title'] ?? null);
    $product->setScrappingUrl($data['url'] ?? null);
    $product->setUsdPrice($price);
    $product->setDetails($actualSpecs);
    $product->setIsAvailable($isAvailable);
    $product->setBrand($data['brand'] ?? null);
    $product->setDescription($data['product_description'] ?? null);
    $product->setSeller('bestbuy');
    $product->setProductCondition($condition);
    $product->setOverallRating($data['rating'] ?? null);
    $product->setOverallRatingsCounts($data['reviews_count'] ?? null);
    $product->setReturnPolicy($returnPolicy);
    $product->setAcceptReturn($returnPolicy !== null);
    $product->setVariants([]);
    $product->setFeatures($data['features'] ?? null);
    $product->setPrimeDelivery(null);
    $product->setSellerChoice(null);
    $product->setCustomerSays(null);
    return $product;
  }

  public function WalmartDataSync(array $data): Product
  {
    $product = new Product();

    $collectedCondition = strtolower($data['condition'] ?? '');

    $condition = str_contains($collectedCondition, 'used') ? 'Used' : 'New';
    $condition = str_contains($collectedCondition, 'refurbished') ? 'Refurbished' : $condition;

    $condition = str_contains($collectedCondition, 'restored') ? 'Refurbished' : $condition;

    $isAvailable = $data['is_available'];

    $allVariants = $data['variants_details'] ?? null;
    $actualVariants = [];

    foreach ($allVariants ?? [] as $variant) {
      if (isset($variant['variant_type'])) {
        foreach ($variant['variant_options'] ?? [] as $option) {
          $actualVariants[] = [
            'name' => $variant['variant_type'] . ' ' . $option['option_name'],
            'price' => $option['option_price'] ?? null,
          ];
        }
      }
    }

    $productSpecifications = $data['specifications'] ?? null;
    $actualSpecs = [];

    foreach ($productSpecifications ?? [] as $spec) {
      if (!$spec['name'] || !$spec['value']) {
        continue;
      }
      if (str_contains(strtolower($spec['name']), 'warranty')) {
        continue;
      }
      $actualSpecs[] = [
        'type' => $spec['name'],
        'value' => $spec['value'],
      ];
    }

    $product->setTitle($data['product_name'] ?? null);
    $product->setScrappingUrl($data['url'] ?? null);
    $product->setUsdPrice($data['initial_price'] ?? null);
    $product->setDetails($actualSpecs);
    $product->setIsAvailable($isAvailable);
    $product->setBrand($data['brand'] ?? null);
    $product->setDescription($data['description'] ?? null);
    $product->setSeller('walmart');
    $product->setProductCondition($condition);
    $product->setOverallRating($data['rating'] ?? null);
    $product->setOverallRatingsCounts($data['rating_count'] ?? null);
    $product->setReturnPolicy($data['free_returns'] ?? null);
    $product->setAcceptReturn($data['is_returnable'] ?? null);
    $product->setVariants($actualVariants);
    $product->setFeatures($data['product_details'] ?? null);
    $product->setPrimeDelivery(null);
    $product->setSellerChoice(null);
    $product->setCustomerSays(null);

    return $product;
  }

  public function synchronizeData(array $data, string $source): Product|bool
  {
    // Add the data into Product entity
    // Implement your data synchronization logic here.
    switch ($source) {
      case str_contains($source, 'amazon') === true:
        return $this->AmazonDataSync($data);
      case str_contains($source, 'bestbuy') === true:
        return $this->BestBuyDataSync($data);
      case str_contains($source, 'walmart') === true:
        return $this->WalmartDataSync($data);
      default:
        return false;
    }
  }
}