<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
/**
 * @ORM\Table(name="article")
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 */
class Article
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private int $id;

    /**
     * @Assert\NotBlank(message="Ce champ est obligatoire")
     * @ORM\Column(name="description", type="string", length=125, nullable=false)
     */
    private string $description;

    /**
     * @ORM\Column(name="image", type="string", length=125, nullable=false)
     */
    private string $image;

    /**
     * @var File|null
     */
    private ?File $imageFile = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
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

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): void
    {
        $this->imageFile = $imageFile;
        if ($imageFile) {
            // Generate a unique filename for the uploaded file
            $newFilename = uniqid().'.'.$imageFile->guessExtension();

            // Set the image property to the new filename
            $this->image = $newFilename;
        }
    }

    public function __toString(): string
    {
        return sprintf(
            "Article ID: %d\nDescription: %s\nImage: %s",
            $this->getId(),
            $this->getDescription(),
            $this->getImage()
        );
    }
}
