<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
  public function __construct(
    private EntityManagerInterface $entityManagerInterface,
  ) {
  }

  public function addNewProduct(array $productData)
  {
    $product = new \App\Entity\Product();
    $product->hydrate($productData);
    if (!$product->isGoodForAdd()) {
      throw new \InvalidArgumentException('Product data is incomplete or invalid.');
    }
    $this->entityManagerInterface->persist($product);
    $this->entityManagerInterface->flush();
    return $product;
  }
}