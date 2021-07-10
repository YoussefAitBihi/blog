<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostLikeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class PostLike
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post", inversedBy="postLikes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $postLike;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userLikes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userLike;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Permet d'initialiser la date de création
     * 
     * @ORM\PrePersist
     * 
     * @return void
     */
    public function initilizeCreatedAt(): void
    {
        if (!$this->createdAt):
            $this->createdAt = new DateTime();
        endif;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostLike(): ?Post
    {
        return $this->postLike;
    }

    public function setPostLike(?Post $postLike): self
    {
        $this->postLike = $postLike;

        return $this;
    }

    public function getUserLike(): ?User
    {
        return $this->userLike;
    }

    public function setUserLike(?User $userLike): self
    {
        $this->userLike = $userLike;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
