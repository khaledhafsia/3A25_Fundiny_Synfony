<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
/**
 * @method string getUserIdentifier()
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface ,PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;



    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Choice(choices: ['Funder', 'Owner', 'Admin'])]
    #[Groups("User")]
    private ?string $role = null;

    #[ORM\Column(nullable: true)]
    private ?float $capital = null;

    #[ORM\Column(nullable: true)]
    public ?float $montant = null;

    #[ORM\Column(nullable: true)]
    private ?bool $banState = null;
/*
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Investissements::class)]
    private Collection $investissementsList;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Projet::class)]
    private Collection $projetList;





    public function __construct()
    {
        $this->investissementsList = new ArrayCollection();
        $this->projetList = new ArrayCollection();
        $this->User = new ArrayCollection();
    }


*/




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

    /**
     * @return Collection<int, Projet>
     */
    public function getProjetList(): Collection
    {
        return $this->projetList;
    }

    public function addProjetList(Projet $projetList): static
    {
        if (!$this->projetList->contains($projetList)) {
            $this->projetList->add($projetList);
            $projetList->setUserId($this);
        }

        return $this;
    }

    public function removeProjetList(Projet $projetList): static
    {
        if ($this->projetList->removeElement($projetList)) {
            // set the owning side to null (unless already changed)
            if ($projetList->getUserId() === $this) {
                $projetList->setUserId(null);
            }
        }

        return $this;
    }


    public function getRoles()
    {
        // TODO: Implement getRoles() method.
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUsername()
    {
        // TODO: Implement getUsername() method.
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement @method string getUserIdentifier()
    }
}
