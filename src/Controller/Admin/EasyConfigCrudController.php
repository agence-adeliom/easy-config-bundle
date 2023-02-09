<?php

namespace Adeliom\EasyConfigBundle\Controller\Admin;

use Adeliom\EasyConfigBundle\Enum\EasyConfigType;
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
        $types = EasyConfigType::getValues();
        $choices = [];
        foreach ($types as $type) {
            $choices['easy_config.types.'. $type] = $type;
        }
        return $choices;
    }

    protected function getFieldMap() : array
    {
        $types = EasyConfigType::getValues();
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
            if (self::isEditable(EasyConfigType::CODE, $config, $pageName)) {
                yield CodeEditorField::new(EasyConfigType::CODE, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigType::IMAGE, $config, $pageName) && class_exists(\Adeliom\EasyMediaBundle\Form\EasyMediaType::class)) {
                yield EasyMediaField::new(EasyConfigType::IMAGE, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setFormTypeOption('restrictions_uploadTypes', ['image/*'])
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigType::FILE, $config, $pageName) && class_exists(\Adeliom\EasyMediaBundle\Form\EasyMediaType::class)) {
                yield EasyMediaField::new(EasyConfigType::FILE, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigType::COLOR, $config, $pageName)) {
                yield ColorField::new(EasyConfigType::COLOR, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigType::DATE, $config, $pageName)) {
                yield DateField::new(EasyConfigType::DATE, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigType::TIME, $config, $pageName)) {
                yield TimeField::new(EasyConfigType::TIME, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigType::DATETIME, $config, $pageName)) {
                yield DateTimeField::new(EasyConfigType::DATETIME, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigType::EMAIL, $config, $pageName)) {
                yield EmailField::new(EasyConfigType::EMAIL, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigType::NUMBER, $config, $pageName)) {
                yield NumberField::new(EasyConfigType::NUMBER, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigType::JSON, $config, $pageName)) {
                yield CodeEditorField::new(EasyConfigType::JSON, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigType::TEXT, $config, $pageName)) {
                yield TextField::new(EasyConfigType::TEXT, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigType::WYSIWYG, $config, $pageName) && class_exists(\FOS\CKEditorBundle\Form\Type\CKEditorType::class)) {
                yield TextareaField::new(EasyConfigType::WYSIWYG, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->renderAsHtml()
                    ->setFormType(CKEditorType::class)
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigType::TEXTAREA, $config, $pageName)) {
                yield TextareaField::new(EasyConfigType::TEXTAREA, 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable(EasyConfigType::BOOLEAN, $config, $pageName)) {
                yield BooleanField::new(EasyConfigType::BOOLEAN, 'easy_config.form.value_bool')
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
