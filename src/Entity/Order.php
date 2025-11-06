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
    #[Groups(['order:read', 'user:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['order:read'])]
    private ?User $client = null;

    #[ORM\OneToOne(mappedBy: 'theOrder', cascade: ['persist', 'remove'])]
    #[Groups(['order:read'])]
    private ?OrderDetails $orderDetails = null;

    #[ORM\ManyToMany(targetEntity: Plant::class, inversedBy: 'orders')]
    #[Groups(['order:read'])]
    private Collection $plants;

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

    public function getOrderDetails(): ?OrderDetails
    {
        return $this->orderDetails;
    }

    public function setOrderDetails(OrderDetails $orderDetails): static
    {
        if ($orderDetails->getTheOrder() !== $this) {
            $orderDetails->setTheOrder($this);
        }
        $this->orderDetails = $orderDetails;
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
}
