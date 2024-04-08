<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * Reponses
 *
 * @ORM\Table(name="reponses")
 * @ORM\Entity
 */
class Reponses
{
    /**
     * @var int
     *
     * @ORM\Column(name="ID_Reponse", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idReponse;

    /**
     * @ORM\Column(name="email", type="string", length=50, nullable=true)
     * @Assert\NotBlank(message="L'adresse email ne peut pas être vide.")
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/",
     *     message="L'adresse email '{{ value }}' n'est pas valide."
     * )
     */
    private $email;

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

    public function getIdReponse(): ?int
    {
        return $this->idReponse;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getIdUtilisateur(): ?User
    {
        return $this->idUtilisateur;
    }

    public function setIdUtilisateur(?User $user): self
    {
        $this->idUtilisateur = $user;

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


}
