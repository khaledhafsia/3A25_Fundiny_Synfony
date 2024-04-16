<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User
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
     * @ORM\Column(name="nom", type="string", length=255, nullable=false)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=255, nullable=false)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var string|null
     *
     * @ORM\Column(name="role", type="string", length=255, nullable=true)
     */
    private $role;

    /**
     * @var float|null
     *
     * @ORM\Column(name="capital", type="float", precision=10, scale=0, nullable=true)
     */
    private $capital;

    /**
     * @var float|null
     *
     * @ORM\Column(name="montant", type="float", precision=10, scale=0, nullable=true)
     */
    private $montant;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="ban_state", type="boolean", nullable=true)
     */
    private $banState;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getCapital(): ?float
    {
        return $this->capital;
    }

    public function setCapital(?float $capital): static
    {
        $this->capital = $capital;

        return $this;
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

    public function isBanState(): ?bool
    {
        return $this->banState;
    }

    public function setBanState(?bool $banState): static
    {
        $this->banState = $banState;

        return $this;
    }

    public function __toString(): string
{
    $banState = $this->banState ? 'Banned' : 'Active';
    $role = $this->role ?? 'No role specified';
    $capital = $this->capital ?? 'Not specified';
    $montant = $this->montant ?? 'Not specified';

    return sprintf(
        "User Nom: %s, Prenom: %s, Email: %s, Role: %s, Capital: %s, Montant: %s, Ban State: %s",
        #"User ID: %d, Nom: %s, Prenom: %s, Email: %s, Role: %s, Capital: %s, Montant: %s, Ban State: %s",
        #$this->id,
        $this->nom,
        $this->prenom,
        $this->email,
        $role,
        $capital,
        $montant,
        $banState
    );
}


}
