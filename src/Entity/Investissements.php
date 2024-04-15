<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\repository\InvestissementsRepository;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Investissements
 *
 * @ORM\Table(name="investissements", indexes={@ORM\Index(name="userID", columns={"userID"}), @ORM\Index(name="projetID", columns={"projetID"})})
 * @ORM\Entity(repositoryClass=App\Repository\InvestissementsRepository::class)
 */

class Investissements
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
     * @var float|null
     *
     * @ORM\Column(name="montant", type="float", precision=10, scale=0, nullable=true)
     */

     #[Assert\NotBlank(message:'montant obligatoire')]
     #[Assert\Regex(
        pattern: '/^\d+(\.\d+)?$/',
        message: 'Le montant doit être un nombre.'
    )]
        private $montant;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    #[Assert\NotBlank(message:'description obligatoire')]
    private $description;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @var \Projet
     *
     * @ORM\ManyToOne(targetEntity="Projet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="projetID", referencedColumnName="id")
     * })
     */
    #[Assert\NotBlank(message:'projet ID obligatoire')]

    private $projetid;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="userID", referencedColumnName="id")
     * })
     */
    #[Assert\NotBlank(message:'user ID obligatoire')]

    private $userid;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): static
    {
        $this->montant = $montant;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getProjetid(): ?Projet
    {
        return $this->projetid;
    }

    public function setProjetid(?Projet $projetid): static
    {
        $this->projetid = $projetid;

        return $this;
    }

    public function getUserid(): ?User
    {
        return $this->userid;
    }

    public function setUserid(?User $userid): static
    {
        $this->userid = $userid;

        return $this;
    }

    public function __toString(): string
    {
        return "Investissement #" . $this->id . " - Montant: " . $this->montant . ", Description: " . $this->description . ", Date: " . ($this->date ? $this->date->format('Y-m-d H:i:s') : 'N/A');
    }


}
