<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * on ajoute , unique=true pour pas avoir
     * deux categories avec le meme nom
     *
     * Validations :
     * le champ ne doit pas être vide >>
     * @Assert\NotBlank(message="Le nom est obligatoire")
     * validation sur la taille
     * @Assert\Length(max="50",
     *     maxMessage="Le nom ne doit pas dépasser {{ limit }} caractères")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * validation pour l'image (File et Image - je peux decider quoi mettre ds mimeTypes)
     *  @Assert\Image(
     *     mimeTypesMessage="Le ficher doit être une image",
     *     maxSize="500k",
     *     maxSizeMessage="L'image ne doit pas dépasser {{ limit }}{{ suffix }}")
     */
    private $logo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="category")
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserCategory", mappedBy="category")
     */
    private $userCategories;


    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->userCategories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

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
            $post->setCategory($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getCategory() === $this) {
                $post->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserCategory[]
     */
    public function getUserCategories(): Collection
    {
        return $this->userCategories;
    }

    public function addUserCategory(UserCategory $userCategory): self
    {
        if (!$this->userCategories->contains($userCategory)) {
            $this->userCategories[] = $userCategory;
            $userCategory->setCategory($this);
        }

        return $this;
    }

    public function removeUserCategory(UserCategory $userCategory): self
    {
        if ($this->userCategories->contains($userCategory)) {
            $this->userCategories->removeElement($userCategory);
            // set the owning side to null (unless already changed)
            if ($userCategory->getCategory() === $this) {
                $userCategory->setCategory(null);
            }
        }

        return $this;
    }


}
