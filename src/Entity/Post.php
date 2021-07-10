<?php

namespace App\Entity;

use DateTime;
use App\Entity\User;
use Cocur\Slugify\Slugify;
use App\Helpers\StringFormat;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Post
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *  min = 3,
     *  max = 20,
     *  minMessage = "Le titre doit faire au moins 3 caractères",
     *  maxMessage = "Le titre ne doit pas dépasser 20 caractères"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(
     *  min = 30,
     *  max = 255,
     *  minMessage = "L'introduction doit-faire au moins 30 caractères",
     *  maxMessage = "L'introduction ne doit pas dépasser 255 caractères"
     * )
     */
    private $introdution;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(
     *  min = 1000,
     *  minMessage = "La description doit-faire au moins 1000 caractères"
     * )
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $video;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="post", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="post", orphanRemoval=true)
     * @Assert\Valid
     */
    private $images;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PostLike", mappedBy="postLike", orphanRemoval=true)
     */
    private $postLikes;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->postLikes = new ArrayCollection();
    }

    /**
     * Permet de savoir que l'utilisateur ait déja fait un commentaire pour le poste
     *
     * @param User $user
     * @return boolean
     */
    public function hasCommentPost(User $user): bool 
    {
        foreach ($this->comments as $comment):
            if ($comment->getAuthor() === $user) {
                return true;
                exit();
            } 
        endforeach;

        return false;       
    }

    /**
     * Permet de calculer et de récupérer le rating
     *
     * @return float|null
     */
    public function getRating(): ?int
    {
        $count = count($this->comments);

        if ($count > 0):
            $ratings = array_reduce($this->comments->toArray(), function($totalRating, $comment){
                return $totalRating += $comment->getRating();
            }, 0);
    
            return $ratings / $count;
        endif;

        return 0;
    }

    /**
     * Permet de savoir que l'utilisateur ait déja fait un like ou non
     *
     * @param User $user
     * @return boolean
     */
    public function hasLike(User $user): bool
    {
        foreach ($this->postLikes as $like) {
            if ($like->getUserLike() === $user) {
                return true;
            }
        }

        return false;
    }

    /**
     * Permet d'initialiser le slug
     * 
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @return void
     */
    public function initializeSlug(): void {
        if (!$this->slug):
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->title);
        endif;
    }

    /**
     * Permet d'initialiser la date de création de poste
     * 
     * @ORM\PrePersist
     *
     * @return void
     */
    public function initilizeCreatedAt(): void {
        if (!$this->createdAt):
            $this->createdAt = new DateTime();
        endif;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Permet de retourner l'extrait de titre
     *
     * @return string|null
     */
    public function getExtractTitle(): ?string {
        return StringFormat::extractString($this->title, 15); 
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getIntrodution(): ?string
    {
        return $this->introdution;
    }

    /**
     * Permet de retourner un extrait de l'introduction
     *
     * @return string|null
     */
    public function getExtractIntroduction(): ?string 
    {
        return StringFormat::extractString($this->introdution, 46);
    }

    public function setIntrodution(string $introdution): self
    {
        $this->introdution = $introdution;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getExtractDescription(): ?string
    {
        return StringFormat::extractString($this->description, 300);
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(string $video): self
    {
        $this->video = $video;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setPost($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getPost() === $this) {
                $image->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PostLike[]
     */
    public function getPostLikes(): Collection
    {
        return $this->postLikes;
    }

    public function addPostLike(PostLike $postLike): self
    {
        if (!$this->postLikes->contains($postLike)) {
            $this->postLikes[] = $postLike;
            $postLike->setPostLike($this);
        }

        return $this;
    }

    public function removePostLike(PostLike $postLike): self
    {
        if ($this->postLikes->contains($postLike)) {
            $this->postLikes->removeElement($postLike);
            // set the owning side to null (unless already changed)
            if ($postLike->getPostLike() === $this) {
                $postLike->setPostLike(null);
            }
        }

        return $this;
    }

}
