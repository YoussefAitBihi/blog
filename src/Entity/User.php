<?php

namespace App\Entity;

use DateTime;
use App\Helpers\StringFormat;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *  fields = {"email"},
 *  message = "Cette adresse est déja utilisé, veuillez la modifier"
 * )
 */
class User implements UserInterface
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
     *  minMessage = "Le premier nom doit-faire au minimum 3 caractères",
     *  maxMessage = "Le premier nom ne doit pas dépasser 20 caractères",
     *  groups = {"user_edit"}
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *  min = 3,
     *  max = 20,
     *  minMessage = "Le deuxième nom doit-faire au minimum 3 caractères",
     *  maxMessage = "Le deuxième nom ne doit pas dépasser 20 caractères",
     *  groups = {"user_edit"}
     * )
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(
     *  message="Veuillez saisir correctement un email valide",
     *  groups = {"user_edit"}
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *  min = 8,
     *  max = 30,
     *  minMessage = "Le mot de passe doit-faire au moins 8 caractères",
     *  maxMessage = "Le mot de passe ne doit dépasser 30 caractères"
     * )
     */
    private $hash;

    /**
     * @Assert\EqualTo(
     *  propertyPath = "hash",
     *  message = "Veuillez confirmer correctement le mot de passe"
     * )
     */
    private $confirmPassword;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(
     *  min = 1000,
     *  minMessage = "La description doit-faire au moins 1000 caractères",
     *  groups = {"user_edit"}
     * )
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255),
     *  groups = {"user_edit"}
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $picture;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="author", orphanRemoval=true)
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Role", mappedBy="user")
     */
    private $userRoles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PostLike", mappedBy="userLike", orphanRemoval=true)
     */
    private $userLikes;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
        $this->userLikes = new ArrayCollection();
    }
    
    /**
     * Permet d'initialiser le slug
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @return void
     */
    public function initilizeSlug(): void {
        if (!$this->slug):
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->firstName.' '.$this->lastName);
        endif;
    }

    /**
     * Permet d'initialiser la date de création
     * @ORM\PrePersist
     *
     * @return void
     */
    public function initilizeCreatedAt(): void {
        if (!$this->createdAt):
            $this->createdAt = new DateTime();
        endif;
    }

    /**
     * Permet de créer et récupérer le nom complet de l'utilisateur
     *
     * @return string|null
     */
    public function getFullName(): ?string {
        return $this->firstName.' '.$this->lastName; 
    } 
    
    /**
     * Permet de calculer et de récupérer le rating
     *
     * @return float
     */
    public function getRating(): float
    {   
        $ratings = 0;
        $count = 0;
    
        foreach ($this->posts as $post) {
            foreach ($post->getComments() as $comment) {
                $ratings += $comment->getRating();
            }
            $count += count($post->getComments());
        } 

        if ($count === 0):
            return 0;
        endif;
                
        return $ratings / $count;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Sert à renvoyer un extrait de la description
     *
     * @return string
     */
    public function getExtractDescription(): string
    {
        return StringFormat::extractString($this->description, 200);
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    /**
     * Sert à récupérer un ou plusieurs rôles qui possède ou qui possèdent l'utilisateur
     *
     * @return array
     */
    public function getRoles(): array 
    {
        // Dans le cas de l'administrateur
        $roleAdmin = $this->userRoles->map(function($role){
            return $role->getName();
        })->toArray();

        if (isset($roleAdmin) && !empty($roleAdmin)):  

            $roleAdmin = $roleAdmin[0];
            if (is_string($roleAdmin)):
                return ['ROLE_USER', $roleAdmin];
            endif;

        endif;

        // Dans le cas de l'utilisateur
        return ['ROLE_USER'];
    }

    /**
     * Permet de récupérer le mot de passe hashé
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->hash;
    }

    public function getSalt() {}
    
    /**
     * Permet de récupérer le nom d'utilisateur
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function eraseCredentials() { }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): self
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

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
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Role[]
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(Role $userRole): self
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles[] = $userRole;
            $userRole->addUser($this);
        }

        return $this;
    }

    public function removeUserRole(Role $userRole): self
    {
        if ($this->userRoles->contains($userRole)) {
            $this->userRoles->removeElement($userRole);
            $userRole->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|PostLike[]
     */
    public function getUserLikes(): Collection
    {
        return $this->userLikes;
    }

    public function addUserLike(PostLike $userLike): self
    {
        if (!$this->userLikes->contains($userLike)) {
            $this->userLikes[] = $userLike;
            $userLike->setUserLike($this);
        }

        return $this;
    }

    public function removeUserLike(PostLike $userLike): self
    {
        if ($this->userLikes->contains($userLike)) {
            $this->userLikes->removeElement($userLike);
            // set the owning side to null (unless already changed)
            if ($userLike->getUserLike() === $this) {
                $userLike->setUserLike(null);
            }
        }

        return $this;
    }

}
