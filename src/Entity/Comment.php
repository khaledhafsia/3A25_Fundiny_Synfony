<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\CommentRepository")]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "commentid", type: "integer", nullable: false)]
    private int $commentid;

    #[ORM\Column(name: "comment", type: "string", length: 100, nullable: false)]
    private string $comment;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(name: "postid", referencedColumnName: "id")]
    private ?Article $postid;

    public function getCommentid(): ?int
    {
        return $this->commentid;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getPostid(): ?Article
    {
        return $this->postid;
    }

    public function setPostid(?Article $postid): static
    {
        $this->postid = $postid;

        return $this;
    }


}
