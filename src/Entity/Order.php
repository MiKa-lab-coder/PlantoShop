<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:read', 'order:write','plant:read', 'cart:read', 'user:read'])] // ID souvent utile dans les relations
    private ?int $id = null;

    // Un client a plusieurs commandes
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['order:read', 'order:write'])]
    private ?User $client = null;

    // Des commandes contiennent des plantes
    #[ORM\ManyToMany(targetEntity: Plant::class, inversedBy: 'orders')]
    #[Groups(['order:read', 'order:write'])]
    private Collection $plants;

    // Une commande correspond a un panier
    #[ORM\OneToOne(inversedBy: 'order', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['order:read', 'order:write'])]
    private ?Cart $cart = null;

    #[ORM\OneToOne(mappedBy: 'order', cascade: ['persist', 'remove'])]
    #[Groups(['order:read', 'order:write'])]
    private ?OrderDetails $orderDetails = null;

    public function __construct()
    {
        $this->plants = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Plant>
     */
    public function getPlants(): Collection
    {
        return $this->plants;
    }

    public function addPlant(Plant $plant): static
    {
        if (!$this->plants->contains($plant)) {
            $this->plants->add($plant);
        }

        return $this;
    }

    public function removePlant(Plant $plant): static
    {
        $this->plants->removeElement($plant);

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
        if ($orderDetails->getOrder() !== $this) {
            $orderDetails->setOrder($this);
        }

        $this->orderDetails = $orderDetails;

        return $this;
    }
}
