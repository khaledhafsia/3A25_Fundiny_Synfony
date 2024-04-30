<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\CommentRepository")]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "commentid", type: "integer", nullable: false)]
    private int $id;

    #[ORM\Column(name: "comment", type: "string", length: 100, nullable: false)]
    private string $comment;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(name: "postid", referencedColumnName: "id")]
    private ?Article $postid;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPostid(): ?Article
    {
        return $this->postid;
    }
    
    public function setPostid(?Article $postid): self
    {
        $this->postid = $postid;
        return $this;
    }
}
