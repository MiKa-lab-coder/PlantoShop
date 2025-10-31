<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cart:read', 'order:read', 'plant:read'])] // ID souvent utile dans les relations
    private ?int $id = null;

    // Un panier est associé à un seul utilisateur
    #[ORM\OneToOne(inversedBy: 'cart', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cart:read', 'cart:write'])]
    private ?User $owner = null;

    // Un panier contient plusieurs plantes
    #[ORM\ManyToMany(targetEntity: Plant::class, inversedBy: 'carts')]
    #[Groups(['cart:read', 'cart:write'])]
    private Collection $plants;

    #[ORM\OneToOne(mappedBy: 'cart', cascade: ['persist', 'remove'])]
    #[Groups(['cart:read', 'cart:write'])]
    private ?Order $order = null;

    public function __construct()
    {
        $this->plants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

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

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): static
    {
        // Met à jour l'association si nécessaire
        if ($order->getCart() !== $this) {
            $order->setCart($this);
        }

        $this->order = $order;

        return $this;
    }
}
