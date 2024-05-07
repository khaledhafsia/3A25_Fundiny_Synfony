<?php

namespace App\Form;

use App\Entity\Reclamations;
use App\Entity\Projet;
use App\Entity\Typesreclamation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface; // Import EntityManagerInterface
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType; // Import correct
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; // Import correct
use Symfony\Component\Form\Extension\Core\Type\TextareaType; // Import correct
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // Import correct pour les entités

class ReclamationsType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idProjet', EntityType::class, [
                'class' => Projet::class,
                'choice_label' => 'nompr', // Afficher le nom du projet
            ])
            ->add('idTypeReclamation', EntityType::class, [
                'class' => Typesreclamation::class,
                'choice_label' => 'NomTypeReclamation', // Afficher le type de réclamation
            ])
            
            ->add('objet', TextType::class) // Champ de texte pour l'objet
            ->add('texte', TextareaType::class); // Champ de texte plus grand pour le contenu de la réclamation

        if ($options['disable_etat']) {
            $builder->add('etat', null, [
                'disabled' => true, // Désactiver le champ 'etat'
            ]);
        } else {
            $builder->add('etat'); // Activer le champ 'etat'
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamations::class,
            'disable_etat' => false, // Par défaut, le champ 'etat' est activé
        ]);
    }
}
