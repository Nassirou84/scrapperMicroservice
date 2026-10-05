<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private ?DateTime $createdAt = null;


    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $scrappingUrl = null;

    #[ORM\Column(length: 255)]
    private ?string $seller = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $brand = null;

    #[ORM\Column(nullable: true)]
    private ?float $usdPrice = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isAvailable = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $customerSays = null;

    #[ORM\Column(nullable: true)]
    private ?array $features = null;

    #[ORM\Column(nullable: true)]
    private ?array $variants = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $lastScrappingAt = null;

    #[ORM\Column(nullable: true)]
    private ?array $details = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 10)]
    private ?string $uid = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    public function __construct(
    ) {
        $this->createdAt = new DateTime();
        $this->lastScrappingAt = new DateTime();
        $this->status = 'new';
    }

    public function hydrate(array $data): static
    {

        $this->setTitle($data['title'] ?? null);
        $this->setScrappingUrl($data['scrappingUrl'] ?? null);
        $this->setSeller($data['seller'] ?? null);
        $this->setBrand($data['brand'] ?? null);
        $this->setUsdPrice($data['usdPrice'] ?? null);
        $this->setIsAvailable($data['isAvailable'] ?? null);
        $this->setDescription($data['description'] ?? null);
        $this->setCustomerSays($data['customerSays'] ?? null);
        $this->setFeatures($data['features'] ?? null);
        $this->setVariants($data['variants'] ?? null);
        $this->setLastScrappingAt($data['lastScrappingAt'] ?? null);
        $this->setDetails($data['details'] ?? null);
        $this->setSlug($data['slug'] ?? null);
        $this->setUid($data['uid'] ?? null);
        return $this;
    }

    public function isGoodForAdd(): bool
    {
        if ($this->scrappingUrl === null) {
            return false;
        }
        if ($this->uid === null) {
            return false;
        }
        if ($this->title === null) {
            return false;
        }
        if ($this->seller === null) {
            return false;
        }

        return true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getScrappingUrl(): ?string
    {
        return $this->scrappingUrl;
    }

    public function setScrappingUrl(?string $scrappingUrl): static
    {
        $this->scrappingUrl = $scrappingUrl;

        return $this;
    }

    public function getSeller(): ?string
    {
        return $this->seller;
    }

    public function setSeller(string $seller): static
    {
        $this->seller = $seller;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getUsdPrice(): ?float
    {
        return $this->usdPrice;
    }

    public function setUsdPrice(?float $usdPrice): static
    {
        $this->usdPrice = $usdPrice;

        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(?bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCustomerSays(): ?string
    {
        return $this->customerSays;
    }

    public function setCustomerSays(?string $customerSays): static
    {
        $this->customerSays = $customerSays;

        return $this;
    }

    public function getFeatures(): ?array
    {
        return $this->features;
    }

    public function setFeatures(?array $features): static
    {
        $this->features = $features;

        return $this;
    }

    public function getVariants(): ?array
    {
        return $this->variants;
    }

    public function setVariants(?array $variants): static
    {
        $this->variants = $variants;

        return $this;
    }

    public function getLastScrappingAt(): ?DateTime
    {
        return $this->lastScrappingAt;
    }

    public function setLastScrappingAt(?DateTime $lastScrappingAt): static
    {
        $this->lastScrappingAt = $lastScrappingAt;

        return $this;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function setDetails(?array $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}