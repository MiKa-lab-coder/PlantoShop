<?php

namespace App\Entity;

use App\Repository\OrderDetailsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderDetailsRepository::class)]
class OrderDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:read'])]
    private ?int $id = null;

    // Renommage de la propriété et des méthodes pour la cohérence
    #[ORM\OneToOne(inversedBy: 'orderDetails', targetEntity: Order::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $theOrder = null;

    #[ORM\Column(length: 255)]
    #[Groups(['order:read'])]
    private ?string $clientFirstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['order:read'])]
    private ?string $clientLastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['order:read'])]
    private ?string $clientEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['order:read'])]
    private ?string $clientAddress = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['order:read'])]
    private ?string $clientPhoneNumber = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['order:read'])]
    private ?\DateTimeImmutable $orderDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['order:read'])]
    private ?string $totalPrice = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['order:read'])]
    private ?array $plantSummary = null;

    public function __construct()
    {
        $this->orderDate = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTheOrder(): ?Order
    {
        return $this->theOrder;
    }

    public function setTheOrder(Order $theOrder): static
    {
        $this->theOrder = $theOrder;

        return $this;
    }

    public function getClientFirstName(): ?string
    {
        return $this->clientFirstName;
    }

    public function setClientFirstName(string $clientFirstName): static
    {
        $this->clientFirstName = $clientFirstName;

        return $this;
    }

    public function getClientLastName(): ?string
    {
        return $this->clientLastName;
    }

    public function setClientLastName(string $clientLastName): static
    {
        $this->clientLastName = $clientLastName;

        return $this;
    }

    public function getClientEmail(): ?string
    {
        return $this->clientEmail;
    }

    public function setClientEmail(string $clientEmail): static
    {
        $this->clientEmail = $clientEmail;

        return $this;
    }

    public function getClientAddress(): ?string
    {
        return $this->clientAddress;
    }

    public function setClientAddress(?string $clientAddress): static
    {
        $this->clientAddress = $clientAddress;

        return $this;
    }

    public function getClientPhoneNumber(): ?string
    {
        return $this->clientPhoneNumber;
    }

    public function setClientPhoneNumber(?string $clientPhoneNumber): static
    {
        $this->clientPhoneNumber = $clientPhoneNumber;

        return $this;
    }

    public function getOrderDate(): ?\DateTimeImmutable
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTimeImmutable $orderDate): static
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    public function getTotalPrice(): ?string
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(string $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getPlantSummary(): ?array
    {
        return $this->plantSummary;
    }

    public function setPlantSummary(?array $plantSummary): static
    {
        $this->plantSummary = $plantSummary;

        return $this;
    }
}
