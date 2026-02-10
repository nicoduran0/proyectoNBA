<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // El ID no lo tocamos, lo ocultamos al crear
        yield IdField::new('id')->hideOnForm();

        // El nombre de la categoría (ej: "Conferencia Este")
        yield TextField::new('name', 'Nombre de la Categoría');

        // AQUÍ ESTÁ LO QUE PEDISTE:
        // Te permite buscar y seleccionar jugadores para meterlos en esta categoría.
        yield AssociationField::new('elements', 'Jugadores en esta categoría')
            ->setFormTypeOption('by_reference', false) // ¡IMPORTANTE! Esto hace que se guarde la relación
            ->autocomplete(); // Añade un buscador para que sea más cómodo si tienes muchos jugadores
    }
}
