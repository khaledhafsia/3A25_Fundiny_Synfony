<?php

namespace App\Form;

use App\Entity\Projet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\DateType;


class ProjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nompr')
            ->add('nompo')
            ->add('dated', null, [
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => new \DateTime('today'),
                        'message' => 'The project date cannot be in the past.',
                    ]),
                ],
            ])
            ->add('ca')
            ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'data_class' => Projet::class,
        
    ]);
 }
}
