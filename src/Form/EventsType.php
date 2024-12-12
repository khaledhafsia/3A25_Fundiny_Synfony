<?php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

date_default_timezone_set('Africa/Tunis');

class EventsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('dateDebut')
            ->add('dateFin');

       
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $investissement = $event->getData();
            if ($investissement instanceof Evenement) {
                $investissement->setDateDebut(new \DateTime()); 
            }
        });
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
