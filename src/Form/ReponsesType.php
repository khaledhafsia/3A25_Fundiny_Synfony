<?php

namespace App\Form;

use App\Entity\Reponses;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReponsesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('idUtilisateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom', // Assurez-vous que 'nomPr' est le champ contenant le nom du projet
                'label' => 'nom utilisateur', // Customize the label as needed
            ])
            ->add('objet')
            ->add('texte')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponses::class,
        ]);
    }
}
