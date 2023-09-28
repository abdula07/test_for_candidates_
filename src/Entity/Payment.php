<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $productId = null;
    #[ORM\Column(unique: true, length: 255)]
    private ?string $txId = null;

    #[ORM\Column]
    private ?string $paymentProcessor = null;

    #[ORM\Column]
    private ?int $taxId = null;

    #[ORM\Column(nullable: true)]
    private ?int $couponId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): static
    {
        $this->productId = $productId;

        return $this;
    }
    public function setTxId($txId): static {
        $this->txId = $txId;
        return $this;
    }

    public function getPaymentProcessor(): ?string
    {
        return $this->paymentProcessor;
    }

    public function setPaymentProcessor(string $paymentProcessor): static
    {
        $this->paymentProcessor= $paymentProcessor;

        return $this;
    }

    public function getTaxId(): ?int
    {
        return $this->taxId;
    }

    public function setTaxId(int $taxId): static
    {
        $this->taxId = $taxId;

        return $this;
    }

    public function getCouponId(): ?int
    {
        return $this->couponId;
    }

    public function setCouponId(int $couponId): static
    {
        $this->couponId = $couponId;

        return $this;
    }
}
