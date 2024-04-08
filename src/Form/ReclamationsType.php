<?php

namespace App\Form;

use App\Entity\Reclamations;
use App\Entity\Projet;
use App\Entity\Typesreclamation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface; // Import EntityManagerInterface
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ReclamationsType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',)

            ->add('idProjet', EntityType::class, [
                'class' => Projet::class,
                'choice_label' => 'nompr', // Assurez-vous que 'nomPr' est le champ contenant le nom du projet
                'label' => 'Projet', // Personnalisez le label selon vos besoins

            ])

            ->add('idTypeReclamation', EntityType::class, [
                'class' => Typesreclamation::class,
                'choice_label' => 'NomTypeReclamation', // Assurez-vous que 'nomPr' est le champ contenant le nom du projet
                'label' => 'Nom TypeReclamation', // Customize the label as needed
            ])

            ->add('idUtilisateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom', // Assurez-vous que 'nomPr' est le champ contenant le nom du projet
                'label' => 'nom utilisateur', // Customize the label as needed
            ])
            ->add('objet')
            ->add('texte');
    }

    private function getProjectChoices()
    {
        $projects = $this->entityManager->getRepository(Projet::class)->findAll();

        $choices = [];
        foreach ($projects as $project) {
            // Utiliser l'ID du projet comme clé et le nom du projet comme valeur
            $choices[$project->getId()] = $project->getNomPr();
        }

        return $choices;
    }

    private function getTypeReclamationChoices()
    {
        $typesReclamation = $this->entityManager->getRepository(Typesreclamation::class)->findAll();

        $choices = [];
        foreach ($typesReclamation as $typeReclamation) {
            // Assuming 'Nom_Type_Reclamation' returns the type name and 'getIdTypeReclamation' returns the type ID
            $choices[$typeReclamation->getIdTypeReclamation()] = $typeReclamation->getNomTypeReclamation();
        }

        return $choices;
    }

    private function getUserChoices()
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

        $choices = [];
        foreach ($users as $user) {
            // Assuming 'getNom' returns the user's name and 'getId' returns the user's ID
            $choices[$user->getId()] = $user->getNom();
        }

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamations::class,
        ]);
    }
}
