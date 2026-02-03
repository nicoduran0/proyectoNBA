<?php

namespace App\Form;

use App\Entity\Rating;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RatingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('score', ChoiceType::class, [
                'label' => 'Tu Puntuación',
                'choices'  => [
                    '⭐⭐⭐⭐⭐ (MVP)' => 5,
                    '⭐⭐⭐⭐ (All-Star)' => 4,
                    '⭐⭐⭐ (Titular)' => 3,
                    '⭐⭐ (Suplente)' => 2,
                    '⭐ (Rookie)' => 1,
                ],
                'expanded' => true, // Esto hace que salgan botones (radio) en vez de lista desplegable
                'multiple' => false,
                'attr' => ['class' => 'd-flex flex-wrap gap-3 justify-content-center mb-3'],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Tu Opinión',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => '¿Por qué le das esa nota?'
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enviar Valoración',
                'attr' => ['class' => 'btn btn-primary mt-3 w-100']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rating::class,
        ]);
    }
}
