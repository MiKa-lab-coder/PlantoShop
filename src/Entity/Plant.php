<?php

namespace App\Entity;

use App\Repository\PlantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: PlantRepository::class)]
class Plant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['plant:read', 'order:read', 'cart:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['plant:read', 'plant:write'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['plant:read', 'plant:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['plant:read', 'plant:write'])]
    private ?int $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['plant:read', 'plant:write'])]
    private ?string $imageUrl = null;

    #[ORM\ManyToOne(inversedBy: 'plants')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['plant:read'])]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'plants')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['plant:read'])]
    private ?User $owner = null;

    #[ORM\ManyToMany(targetEntity: Order::class, mappedBy: 'plants')]
    private Collection $orders;


    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->addPlant($this);
        }
        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            $order->removePlant($this);
        }
        return $this;
    }

    // Suppression des méthodes addCart et removeCart
    // /**
    //  * @return Collection<int, Cart>
    //  */
    // public function getCarts(): Collection
    // {
    //     return $this->carts;
    // }

    // public function addCart(Cart $cart): static
    // {
    //     if (!$this->carts->contains($cart)) {
    //         $this->carts->add($cart);
    //         $cart->addPlant($this);
    //     }
    //     return $this;
    // }

    // public function removeCart(Cart $cart): static
    // {
    //     if ($this->carts->removeElement($cart)) {
    //         $cart->removePlant($this);
    //     }
    //     return $this;
    // }
}
