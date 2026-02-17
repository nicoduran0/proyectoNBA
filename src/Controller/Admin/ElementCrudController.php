<?php

namespace App\Controller\Admin;

use App\Entity\Element;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

class ElementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Element::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nombre');
        yield TextField::new('team', 'Equipo');
        yield TextField::new('position', 'Posición');
        yield IntegerField::new('number', 'Dorsal');

        yield AssociationField::new('categories', 'Categorías')
            ->setFormTypeOption('by_reference', false)
            ->autocomplete();

        yield ImageField::new('image', 'Foto')
            ->setBasePath('uploads/images')
            ->setUploadDir('public/uploads/images')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false);

        yield TextEditorField::new('description', 'Descripción')->hideOnIndex();
    }
}
