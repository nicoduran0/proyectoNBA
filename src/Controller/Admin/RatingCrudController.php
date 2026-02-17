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
        yield IdField::new('id')->hideOnForm();

        yield AssociationField::new('owner', 'Usuario')
            ->setFormTypeOption('disabled', true);

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
                5 => 'success',
                4 => 'primary',
                3 => 'info',
                2 => 'warning',
                1 => 'danger',
            ]);

        yield TextareaField::new('comment', 'Comentario');
    }
}
