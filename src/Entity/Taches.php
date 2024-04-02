<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Taches
 *
 * @ORM\Table(name="taches", indexes={@ORM\Index(name="IDX_3BF2CD9818AEA7A1", columns={"invID"})})
 * @ORM\Entity
 */
class Taches
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
     * @var string|null
     *
     * @ORM\Column(name="titre", type="string", length=30, nullable=true)
     */
    private $titre;

    /**
     * @var string|null
     *
     * @ORM\Column(name="priorite", type="string", length=50, nullable=true)
     */
    private $priorite;

    /**
     * @var string|null
     *
     * @ORM\Column(name="statut", type="string", length=50, nullable=true)
     */
    private $statut;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="echeanceD", type="date", nullable=true)
     */
    private $echeanced;

    /**
     * @var \Investissements
     *
     * @ORM\ManyToOne(targetEntity="Investissements")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="invID", referencedColumnName="id")
     * })
     */
    private $invid;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getPriorite(): ?string
    {
        return $this->priorite;
    }

    public function setPriorite(?string $priorite): self
    {
        $this->priorite = $priorite;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getEcheanced(): ?\DateTimeInterface
    {
        return $this->echeanced;
    }

    public function setEcheanced(?\DateTimeInterface $echeanced): self
    {
        $this->echeanced = $echeanced;

        return $this;
    }

    public function getInvid(): ?Investissements
    {
        return $this->invid;
    }

    public function setInvid(?Investissements $invid): self
    {
        $this->invid = $invid;

        return $this;
    }


}
