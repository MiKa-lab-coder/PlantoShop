<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:read', 'order:write', 'plant:read', 'cart:read', 'user:read'])]
    private ?int $id = null;

    // Un client a plusieurs commandes
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['order:read', 'order:write'])]
    private ?User $client = null;

    // Une commande correspond a un panier
    #[ORM\OneToOne(inversedBy: 'order', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['order:read', 'order:write'])]
    private ?Cart $cart = null;

    #[ORM\OneToOne(mappedBy: 'orderRef', cascade: ['persist', 'remove'])]
    #[Groups(['order:read', 'order:write'])]
    private ?OrderDetails $orderDetails = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): static
    {
        $this->cart = $cart;

        return $this;
    }

    public function getOrderDetails(): ?OrderDetails
    {
        return $this->orderDetails;
    }

    public function setOrderDetails(OrderDetails $orderDetails): static
    {
        // set the owning side of the relation if necessary
        if ($orderDetails->getOrderRef() !== $this) {
            $orderDetails->setOrderRef($this);
        }

        $this->orderDetails = $orderDetails;

        return $this;
    }
}
