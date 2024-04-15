<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Collaboration
 *
 * @ORM\Table(name="collaboration", indexes={@ORM\Index(name="id_projet", columns={"id_projet"})})
 * @ORM\Entity
 */
class Collaboration
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    #[Assert\NotBlank(message:'description obligatoire')]
    #[Assert\Regex(pattern: '/^[a-zA-Z]+$/', message: 'The project name can only contain letters.')]
    /**
     * @var string
     *
     * @ORM\Column(name="nomColl", type="string", length=50, nullable=false)
     */
    private $nomcoll;

    #[Assert\NotBlank(message:'description obligatoire')]
    #[Assert\Regex(pattern: '/^[a-zA-Z]+$/', message: 'The project name can only contain letters.')]
    /**
     * @var string
     *
     * @ORM\Column(name="TypeColl", type="string", length=50, nullable=false)
     */
    private $typecoll;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateColl", type="date", nullable=false)
     */
    private $datecoll;

    /**
     * @var \Projet
     *
     * @ORM\ManyToOne(targetEntity="Projet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_projet", referencedColumnName="id")
     * })
     */
    private $idProjet;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomcoll(): ?string
    {
        return $this->nomcoll;
    }

    public function setNomcoll(string $nomcoll): static
    {
        $this->nomcoll = $nomcoll;

        return $this;
    }

    public function getTypecoll(): ?string
    {
        return $this->typecoll;
    }

    public function setTypecoll(string $typecoll): static
    {
        $this->typecoll = $typecoll;

        return $this;
    }

    public function getDatecoll(): ?\DateTimeInterface
    {
        return $this->datecoll;
    }

    public function setDatecoll(\DateTimeInterface $datecoll): static
    {
        $this->datecoll = $datecoll;

        return $this;
    }

    public function getIdProjet(): ?Projet
    {
        return $this->idProjet;
    }

    public function setIdProjet(?Projet $idProjet): static
    {
        $this->idProjet = $idProjet;

        return $this;
    }


}
