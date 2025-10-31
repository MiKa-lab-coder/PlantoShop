<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(repositoryClass=App\Repository\ReviewRepository::class)
 */
class Review
{
    /**
     * @ODM\Id(strategy="AUTO")
     * @Groups({"review:read"})
     */
    private ?string $id = null;

    /**
     * L'ID de la plante dans la base de données SQL.
     * @ODM\Field(type="int")
     * @Groups({"review:read", "review:write"})
     * @Assert\NotBlank
     */
    private ?int $plantId = null;

    /**
     * L'ID de l'utilisateur qui a posté l'avis.
     * @ODM\Field(type="int")
     * @Groups({"review:read"})
     */
    private ?int $userId = null;

    /**
     * Le nom de l'utilisateur, stocké pour éviter des requêtes complexes.
     * @ODM\Field(type="string")
     * @Groups({"review:read"})
     */
    private ?string $username = null;

    /**
     * @ODM\Field(type="int")
     * @Groups({"review:read", "review:write"})
     * @Assert\NotBlank
     * @Assert\Range(min=1, max=5)
     */
    private ?int $rating = null;

    /**
     * @ODM\Field(type="string")
     * @Groups({"review:read", "review:write"})
     * @Assert\NotBlank
     * @Assert\Length(min=10)
     */
    private ?string $comment = null;

    /**
     * @ODM\Field(type="date")
     * @Groups({"review:read"})
     */
    private ?\DateTime $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPlantId(): ?int
    {
        return $this->plantId;
    }

    public function setPlantId(int $plantId): self
    {
        $this->plantId = $plantId;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
