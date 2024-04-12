<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Evenement
 *
 * @ORM\Table(name="evenement")
 * @ORM\Entity
 */
class Evenement
{
    /**
     * @var int
     *
     * @ORM\Column(name="idEvent", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idevent;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nom", type="text", length=65535, nullable=true)
     */
    private $nom;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="dateDebut", type="text", length=65535, nullable=true)
     */
    private $datedebut;

    /**
     * @var string|null
     *
     * @ORM\Column(name="dateFin", type="text", length=65535, nullable=true)
     */
    private $datefin;

    /**
     * @var int|null
     *
     * @ORM\Column(name="objectifFinancement", type="integer", nullable=true)
     */
    private $objectiffinancement;

    /**
     * @var int|null
     *
     * @ORM\Column(name="montantCollecte", type="integer", nullable=true)
     */
    private $montantcollecte;

    /**
     * @var string|null
     *
     * @ORM\Column(name="statut", type="text", length=65535, nullable=true)
     */
    private $statut;

    /**
     * @var string|null
     *
     * @ORM\Column(name="categorie", type="text", length=65535, nullable=true)
     */
    private $categorie;

    /**
     * @var string|null
     *
     * @ORM\Column(name="organisateur", type="text", length=65535, nullable=true)
     */
    private $organisateur;

    /**
     * @var string|null
     *
     * @ORM\Column(name="localisation", type="text", length=65535, nullable=true)
     */
    private $localisation;

    /**
     * @var string|null
     *
     * @ORM\Column(name="image", type="text", length=65535, nullable=true)
     */
    private $image;

    public function getIdevent(): ?int
    {
        return $this->idevent;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDatedebut(): ?string
    {
        return $this->datedebut;
    }

    public function setDatedebut(?string $datedebut): static
    {
        $this->datedebut = $datedebut;

        return $this;
    }

    public function getDatefin(): ?string
    {
        return $this->datefin;
    }

    public function setDatefin(?string $datefin): static
    {
        $this->datefin = $datefin;

        return $this;
    }

    public function getObjectiffinancement(): ?int
    {
        return $this->objectiffinancement;
    }

    public function setObjectiffinancement(?int $objectiffinancement): static
    {
        $this->objectiffinancement = $objectiffinancement;

        return $this;
    }

    public function getMontantcollecte(): ?int
    {
        return $this->montantcollecte;
    }

    public function setMontantcollecte(?int $montantcollecte): static
    {
        $this->montantcollecte = $montantcollecte;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getOrganisateur(): ?string
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?string $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(?string $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }


}
