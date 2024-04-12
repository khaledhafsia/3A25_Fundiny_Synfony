<?php

namespace App\Form;

use App\Entity\Investissements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

date_default_timezone_set('Africa/Tunis');

class InvestissementsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant')
            ->add('description')
            ->add('projetid')
            ->add('userid');

       
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $investissement = $event->getData();
            if ($investissement instanceof Investissements) {
                $investissement->setDate(new \DateTime()); 
            }
        });
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Investissements::class,
        ]);
    }
}
