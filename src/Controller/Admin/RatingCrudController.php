<?php

namespace App\Controller\Admin;

use App\Entity\Rating;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class RatingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Rating::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm(); // Ocultamos ID en el formulario

        yield AssociationField::new('owner', 'Usuario')
            ->setFormTypeOption('disabled', true); // Bloqueamos editar el usuario para evitar líos

        yield AssociationField::new('element', 'Jugador Votado');

        yield ChoiceField::new('value', 'Nota')
            ->setChoices([
                'MVP (5)' => 5,
                'All-Star (4)' => 4,
                'Titular (3)' => 3,
                'Suplente (2)' => 2,
                'Rookie (1)' => 1,
            ])
            ->renderAsBadges([
                5 => 'success', // Verde
                4 => 'primary', // Azul
                3 => 'info',    // Azul claro
                2 => 'warning', // Naranja
                1 => 'danger',  // Rojo
            ]);

        yield TextareaField::new('comment', 'Comentario');
    }
}
