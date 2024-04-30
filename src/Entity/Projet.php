<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
class Projet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nompr = null;

    #[ORM\Column(length: 255)]
    private ?string $nompo = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dated = null;

    #[ORM\Column]
    private ?float $ca = null;

    #[ORM\ManyToOne(inversedBy: 'projetList')]
    private ?User $user = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNompr(): ?string
    {
        return $this->nompr;
    }

    public function setNompr(string $nompr): static
    {
        $this->nompr = $nompr;

        return $this;
    }

    public function getNompo(): ?string
    {
        return $this->nompo;
    }

    public function setNompo(string $nompo): static
    {
        $this->nompo = $nompo;

        return $this;
    }

    public function getDated(): ?\DateTimeInterface
    {
        return $this->dated;
    }

    public function setDated(\DateTimeInterface $dated): static
    {
        $this->dated = $dated;

        return $this;
    }

    public function getCa(): ?float
    {
        return $this->ca;
    }

    public function setCa(float $ca): static
    {
        $this->ca = $ca;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

}
