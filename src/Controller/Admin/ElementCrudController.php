<?php

namespace App\Controller\Admin;

use App\Entity\Element;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField; // He añadido esto por si quieres subir fotos
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField; // Para la descripción

class ElementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Element::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // 1. ID (Oculto al editar, visible en la lista)
        yield IdField::new('id')->hideOnForm();

        // 2. Datos principales del Jugador
        yield TextField::new('name', 'Nombre');
        yield TextField::new('team', 'Equipo');
        yield TextField::new('position', 'Posición');
        yield IntegerField::new('number', 'Dorsal');

        // 3. ESTO ES LO QUE NECESITAS (CAMBIO REALIZADO):
        // Fíjate que ahora pone 'categories' (en PLURAL)
        yield AssociationField::new('categories', 'Categorías')
            ->setFormTypeOption('by_reference', false) // Obligatorio para guardar bien ManyToMany
            ->autocomplete(); // Añade un buscador para seleccionar VARIAS categorías

        // 4. Campos extra (Opcionales, pero útiles para tu web)
        yield ImageField::new('image', 'Foto')
            ->setBasePath('uploads/images')
            ->setUploadDir('public/uploads/images')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false);

        yield TextEditorField::new('description', 'Descripción')->hideOnIndex();
    }
}
