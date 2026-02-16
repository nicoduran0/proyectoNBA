<?php

namespace App\Controller\Admin;

use App\Entity\Element;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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

        // 3. ESTO ES LO QUE NECESITAS:
        // Te mostrará la categoría actual y te dejará cambiarla
        yield AssociationField::new('category', 'Categoría')
            ->setFormTypeOption('by_reference', false) // Obligatorio para guardar bien la relación
            ->autocomplete(); // Añade un buscador para encontrar la categoría rápido
    }
}
