<?php

namespace App\Entity;

use App\Repository\PaymentDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentDetailsRepository::class)]
class PaymentDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $taxValue = null;

    #[ORM\Column]
    private ?int $productPrice = null;

    #[ORM\Column]
    private ?int $paymentId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaxValue(): ?int
    {
        return $this->taxValue;
    }

    public function setTaxValue(int $taxValue): static
    {
        $this->taxValue = $taxValue;

        return $this;
    }

    public function getProductPrice(): ?int
    {
        return $this->productPrice;
    }

    public function setProductPrice(int $productPrice): static
    {
        $this->productPrice = $productPrice;

        return $this;
    }

    public function getPaymentId(): ?int
    {
        return $this->paymentId;
    }

    public function setPaymentId(int $paymentId): static
    {
        $this->paymentId = $paymentId;

        return $this;
    }
}
