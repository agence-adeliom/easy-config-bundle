<?php

namespace Adeliom\EasyConfigBundle\Controller\Admin;

use Adeliom\EasyConfigBundle\Enum\EasyConfigEnum;
use Adeliom\EasyFieldsBundle\Admin\Field\ChoiceMaskField;
use Adeliom\EasyMediaBundle\Admin\Field\EasyMediaField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class EasyConfigCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        $crud
            ->addFormTheme('@EasyFields/form/choice_mask_widget.html.twig')
            ->setPageTitle(Crud::PAGE_INDEX, 'easy_config.manage_configs')
            ->setPageTitle(Crud::PAGE_NEW, 'easy_config.new_config')
            ->setPageTitle(Crud::PAGE_EDIT, 'easy_config.edit_config')
            ->setPageTitle(Crud::PAGE_DETAIL, static fn ($entity) => $entity->getName())
            ->setEntityLabelInSingular('easy_config.config')
            ->setEntityLabelInPlural('easy_config.configs')
            ->setFormOptions([
                'validation_groups' => ['Default'],
            ])
            ->setEntityPermission('ROLE_ADMIN');

        if (class_exists(\Adeliom\EasyMediaBundle\Form\EasyMediaType::class)) {
            $crud->addFormTheme('@EasyMedia/form/easy-media.html.twig');
        }

        if (class_exists(\FOS\CKEditorBundle\Form\Type\CKEditorType::class)) {
            $crud->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig');
        }

        return $crud;
    }

    protected function getAvailableTypes() : array
    {
        $types = EasyConfigEnum::getValues();
        $choices = [];
        foreach ($types as $type) {
            $choices['easy_config.types.'. $type] = $type;
        }
        return $choices;
    }

    protected function getFieldMap() : array
    {
        $types = EasyConfigEnum::getValues();
        $choices = [];
        foreach ($types as $type) {
            $choices[$type] = [$type];
        }
        return $choices;
    }

    public function configureFilters(Filters $filters): Filters
    {
        $filters = parent::configureFilters($filters);

        $filters->add(TextFilter::new('key', 'easy_config.form.key'));
        $filters->add(TextFilter::new('name', 'easy_config.form.name'));
        $filters->add(ChoiceFilter::new('type', 'easy_config.form.type')
            ->setFormTypeOption('translation_domain', 'messages')
            ->setChoices($this->getAvailableTypes()));

        return $filters;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        $context = $this->container->get(AdminContextProvider::class)->getContext();
        $config = $context?->getEntity()->getInstance();

        if (Crud::PAGE_NEW == $pageName) {
            yield SlugField::new('key', 'easy_config.form.key')
                ->setTargetFieldName('name')
                ->setRequired(true)
                ->setColumns('col-12 col-sm-6');
        } else {
            yield TextField::new('key', 'easy_config.form.key')
                ->setRequired(true)
                ->setFormTypeOption('disabled', Crud::PAGE_EDIT == $pageName)
                ->setColumns('col-12 col-sm-6');
        }

        yield TextField::new('name', 'easy_config.form.name')->setColumns('col-12 col-sm-6');
        yield TextareaField::new('description', 'easy_config.form.description');
        yield ChoiceMaskField::new('type', 'easy_config.form.type')
            ->setFormTypeOption('disabled', Crud::PAGE_EDIT == $pageName)
            ->setChoices($this->getAvailableTypes())
            ->setMap($this->getFieldMap())
            ->renderAsBadges()
            ->setRequired(true)
            ->setColumns('col-12 col-sm-6');

        if ($config) {
            if (self::isEditable(EasyConfigEnum::CODE->value, $config, $pageName)) {
                yield CodeEditorField::new(EasyConfigEnum::CODE->value, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigEnum::IMAGE->value, $config, $pageName) && class_exists(\Adeliom\EasyMediaBundle\Form\EasyMediaType::class)) {
                yield EasyMediaField::new(EasyConfigEnum::IMAGE->value, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setFormTypeOption('restrictions_uploadTypes', ['image/*'])
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigEnum::FILE->value, $config, $pageName) && class_exists(\Adeliom\EasyMediaBundle\Form\EasyMediaType::class)) {
                yield EasyMediaField::new(EasyConfigEnum::FILE->value, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigEnum::COLOR->value, $config, $pageName)) {
                yield ColorField::new(EasyConfigEnum::COLOR->value, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigEnum::DATE->value, $config, $pageName)) {
                yield DateField::new(EasyConfigEnum::DATE->value, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigEnum::TIME->value, $config, $pageName)) {
                yield TimeField::new(EasyConfigEnum::TIME->value, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigEnum::DATETIME->value, $config, $pageName)) {
                yield DateTimeField::new(EasyConfigEnum::DATETIME->value, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigEnum::EMAIL->value, $config, $pageName)) {
                yield EmailField::new(EasyConfigEnum::EMAIL->value, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigEnum::NUMBER->value, $config, $pageName)) {
                yield NumberField::new(EasyConfigEnum::NUMBER->value, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigEnum::JSON->value, $config, $pageName)) {
                yield CodeEditorField::new(EasyConfigEnum::JSON->value, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigEnum::TEXT->value, $config, $pageName)) {
                yield TextField::new(EasyConfigEnum::TEXT->value, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigEnum::WYSIWYG->value, $config, $pageName) && class_exists(\FOS\CKEditorBundle\Form\Type\CKEditorType::class)) {
                yield TextareaField::new(EasyConfigEnum::WYSIWYG->value, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->renderAsHtml()
                    ->setFormType(CKEditorType::class)
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigEnum::TEXTAREA->value, $config, $pageName)) {
                yield TextareaField::new(EasyConfigEnum::TEXTAREA->value, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigEnum::BOOLEAN->value, $config, $pageName)) {
                yield BooleanField::new(EasyConfigEnum::BOOLEAN->value, 'easy_config.form.value_bool')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }
        }
    }

    protected static function isEditable(string $type, object $config, string $pageName): bool
    {
        return !$config->getId() || ($config->getId() && Crud::PAGE_EDIT == $pageName) || ($config->getId() && $config->getType() == $type && Crud::PAGE_DETAIL == $pageName);
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            ParameterBagInterface::class => '?'.ParameterBagInterface::class,
        ]);
    }
}
