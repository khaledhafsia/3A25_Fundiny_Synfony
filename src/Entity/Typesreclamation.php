<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Typesreclamation
 *
 * @ORM\Table(name="typesreclamation")
 * @ORM\Entity
 */
class Typesreclamation
{
    /**
     * @var int
     *
     * @ORM\Column(name="ID_Type_Reclamation", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTypeReclamation;

    /**
     * @var string|null
     *
     * @ORM\Column(name="Nom_Type_Reclamation", type="string", length=255, nullable=true)
     */
    private $nomTypeReclamation;

    public function getIdTypeReclamation(): ?int
    {
        return $this->idTypeReclamation;
    }

    public function getNomTypeReclamation(): ?string
    {
        return $this->nomTypeReclamation;
    }

    public function setNomTypeReclamation(?string $nomTypeReclamation): self
    {
        $this->nomTypeReclamation = $nomTypeReclamation;

        return $this;
    }


}
