<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Projet
 *
 * @ORM\Table(name="projet", indexes={@ORM\Index(name="IDX_50159CA9A76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class Projet
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nomPr", type="string", length=255, nullable=false)
     */
    private $nompr;

    /**
     * @var string
     *
     * @ORM\Column(name="nomPo", type="string", length=50, nullable=false)
     */
    private $nompo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateD", type="date", nullable=false)
     */
    private $dated;

    /**
     * @var float
     *
     * @ORM\Column(name="CA", type="float", precision=10, scale=0, nullable=false)
     */
    private $ca;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

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

    public function __toString(): string
{
    return sprintf(
        "Projet ID: %d, NomPr: %s, NomPo: %s, DateD: %s, CA: %f",
        $this->id,
        $this->nompr,
        $this->nompo,
        $this->dated->format('Y-m-d'),
        $this->ca
    );
}


}
