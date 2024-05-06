<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="reclamations")
 */
class Reclamations
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="ID_Reclamation", type="integer", nullable=false)
     */
    private $id;

    /**
     * @ORM\Column(name="email", type="string", length=50, nullable=true)
     * 
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/",
     *     message="L'adresse email '{{ value }}' n'est pas valide."
     * )
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Projet")
     * @ORM\JoinColumn(name="ID_Projet", referencedColumnName="id", nullable=true)
     */
    private $idProjet;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Typesreclamation")
     * @ORM\JoinColumn(name="ID_Type_Reclamation", referencedColumnName="ID_Type_Reclamation", nullable=true)
     */
    private $idTypeReclamation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="ID_utilisateur", referencedColumnName="id", nullable=true)
     */
    private $idUtilisateur;

    /**
     * @ORM\Column(name="objet", type="string", length=50, nullable=true)
     * @Assert\NotBlank(message="Le champ objet ne peut pas être vide.")
     * @Assert\Length(max=50, maxMessage="Le champ objet ne peut pas dépasser {{ limit }} caractères.")
     */
    private $objet;

    /**
     * @ORM\Column(name="texte", type="text", length=65535, nullable=true)
     * @Assert\NotBlank(message="Le champ texte ne peut pas être vide.")
     * @Assert\Length(min=10, minMessage="Le texte doit contenir au moins {{ limit }} caractères.")
     */
    private $texte;

    /**
     * @ORM\Column(name="date_creation", type="datetime", nullable=false, options={"default": "CURRENT_TIMESTAMP"})
     */
    private $dateCreation;

    /**
     * @ORM\Column(name="etat", type="boolean", nullable=false, options={"default" : 0})
     */
    private $etat;

    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getIdReclamation(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getIdProjet(): ?Projet
    {
        return $this->idProjet;
    }

    public function setIdProjet(?Projet $projet): self
    {
        $this->idProjet = $projet;
        return $this;
    }

    public function getIdTypeReclamation(): ?Typesreclamation
    {
        return $this->idTypeReclamation;
    }

    public function setIdTypeReclamation(?Typesreclamation $idTypeReclamation): self
    {
        $this->idTypeReclamation = $idTypeReclamation;
        return $this;
    }

    public function getIdUtilisateur(): ?User
    {
        return $this->idUtilisateur;
    }

    public function setIdUtilisateur(?User $idUtilisateur): self
    {
        $this->idUtilisateur = $idUtilisateur;
        return $this;
    }

    public function getObjet(): ?string
    {
        return $this->objet;
    }

    public function setObjet(?string $objet): self
    {
        $this->objet = $objet;
        return $this;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(?string $texte): self
    {
        $this->texte = $texte;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getEtat(): ?bool
    {
        return $this->etat;
    }

    public function setEtat(bool $etat): self
    {
        $this->etat = $etat;
        return $this;
    }
}