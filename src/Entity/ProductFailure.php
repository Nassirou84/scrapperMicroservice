<?php

namespace App\Entity;

use App\Repository\ProductFailureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductFailureRepository::class)]
class ProductFailure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $errorType = null;

    #[ORM\Column]
    private array $payload = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isFix = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fixedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fixedBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isFix = false;
        $this->fixedAt = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getErrorType(): ?string
    {
        return $this->errorType;
    }

    public function setErrorType(string $errorType): static
    {
        $this->errorType = $errorType;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isFix(): ?bool
    {
        return $this->isFix;
    }

    public function setIsFix(?bool $isFix): static
    {
        $this->isFix = $isFix;

        return $this;
    }

    public function getFixedAt(): ?\DateTime
    {
        return $this->fixedAt;
    }

    public function setFixedAt(?\DateTime $fixedAt): static
    {
        $this->fixedAt = $fixedAt;

        return $this;
    }

    public function getFixedBy(): ?string
    {
        return $this->fixedBy;
    }

    public function setFixedBy(?string $fixedBy): static
    {
        $this->fixedBy = $fixedBy;

        return $this;
    }
}