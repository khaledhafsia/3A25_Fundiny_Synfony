<?php

namespace App\Entity;
use App\Entity\Projet;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass=App\Repository\UserRepository::class)
 */

class User implements UserInterface ,PasswordAuthenticatedUserInterface
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

        /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $resetToken = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $tokenExpiration = null;
    /**
     * @ORM\OneToMany(targetEntity=Investissements::class, mappedBy="userid")
     */
    private Collection $investissementsList;

    /**
     * @ORM\OneToMany(targetEntity=Projet::class, mappedBy="user")
     */
    private Collection $projetList;

    public function __construct()
    {
        $this->investissementsList = new ArrayCollection();
        $this->projetList = new ArrayCollection();
    }

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

    public function getParticipation(): ?float
    {
        return $this->montant;
    }

    public function setParticipation(?float $montant): static
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


    /**
     * @return Collection<int, Investissements>
     */
    public function getInvestissementsList(): Collection
    {
        return $this->investissementsList;
    }


    /**
     * @return Collection<int, Projet>
     */
    public function getProjetList(): Collection
    {
        return $this->projetList;
    }



    public function getRoles()
    {
    }

    public function getSalt ()
    {
    }

    public function eraseCredentials()
    {
    }

    public function getUsername()
    {
    }

    public function __call(string $name, array $arguments)
    {
    }
    public function getUserIdentifier()
    {
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getTokenExpiration(): ?\DateTimeInterface
    {
        return $this->tokenExpiration;
    }

    public function setTokenExpiration(?\DateTimeInterface $tokenExpiration): static
    {
        $this->tokenExpiration = $tokenExpiration;

        return $this;
    }

    public function addProjetList(Projet $projetList): static
    {
        if (!$this->projetList->contains($projetList)) {
            $this->projetList->add($projetList);
            $projetList->setUser($this);
        }

        return $this;
    }

    public function removeProjetList(Projet $projetList): static
    {
        if ($this->projetList->removeElement($projetList)) {
            // set the owning side to null (unless already changed)
            if ($projetList->getUser() === $this) {
                $projetList->setUser(null);
            }
        }

        return $this;
    }
    public function addInvestissementsList(Investissements $investissementsList): static
    {
        if (!$this->investissementsList->contains($investissementsList)) {
            $this->investissementsList->add($investissementsList);
            $investissementsList->setUserID($this);
        }

        return $this;
    }

    public function removeInvestissementsList(Investissements $investissementsList): static
    {
        if ($this->investissementsList->removeElement($investissementsList)) {
            // set the owning side to null (unless already changed)
            if ($investissementsList->getUserID() === $this) {
                $investissementsList->setUserID(null);
            }
        }

        return $this;
    }

    public function __toString(): string
{
    $banState = $this->banState ? 'Banned' : 'Active';
    $role = $this->role ?? 'No role specified';
    $capital = $this->capital ?? 'Not specified';
    $montant = $this->montant ?? 'Not specified';

    return sprintf(
        "User ID: %d, Nom: %s, Prenom: %s, Email: %s, Role: %s, Capital: %s, Montant: %s, Ban State: %s",
        $this->id,
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
