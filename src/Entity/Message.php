<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 */
class Message
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Le contenu est obligatoire")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post", inversedBy="messages")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $post;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sendingUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="recievedMessages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $receivingUser;

    /**
     * @ORM\Column(type="datetime")
     */
    private $sendingDate;

    public function __construct()
    {
        $this->sendingDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getSendingUser(): ?User
    {
        return $this->sendingUser;
    }

    public function setSendingUser(?User $sendingUser): self
    {
        $this->sendingUser = $sendingUser;

        return $this;
    }

    public function getReceivingUser(): ?User
    {
        return $this->receivingUser;
    }

    public function setReceivingUser(?User $receivingUser): self
    {
        $this->receivingUser = $receivingUser;

        return $this;
    }

    public function getSendingDate(): ?\DateTimeInterface
    {
        return $this->sendingDate;
    }

    public function setSendingDate(\DateTimeInterface $sendingDate): self
    {
        $this->sendingDate = $sendingDate;

        return $this;
    }
}
