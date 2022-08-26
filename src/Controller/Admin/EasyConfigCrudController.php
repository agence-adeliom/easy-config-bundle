<?php

namespace Adeliom\EasyConfigBundle\Controller\Admin;

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

    public function configureFilters(Filters $filters): Filters
    {
        $filters = parent::configureFilters($filters);

        $filters->add(TextFilter::new('key', 'easy_config.form.key'));
        $filters->add(TextFilter::new('name', 'easy_config.form.name'));
        $filters->add(ChoiceFilter::new('type', 'easy_config.form.type')
            ->setFormTypeOption('translation_domain', 'messages')
            ->setChoices([
                'easy_config.types.code' => 'code',
                'easy_config.types.email' => 'email',
                'easy_config.types.number' => 'number',
                'easy_config.types.json' => 'json',
                'easy_config.types.text' => 'text',
                'easy_config.types.textarea' => 'textarea',
                'easy_config.types.wysiwyg' => 'wysiwyg',
                'easy_config.types.boolean' => 'boolean',
                'easy_config.types.image' => 'image',
                'easy_config.types.file' => 'file',
                'easy_config.types.color' => 'color',
                'easy_config.types.date' => 'date',
                'easy_config.types.time' => 'time',
                'easy_config.types.datetime' => 'datetime',
            ]));

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
        $config = $context->getEntity()->getInstance();

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
            ->setChoices([
                'easy_config.types.code' => 'code',
                'easy_config.types.email' => 'email',
                'easy_config.types.number' => 'number',
                'easy_config.types.json' => 'json',
                'easy_config.types.text' => 'text',
                'easy_config.types.textarea' => 'textarea',
                'easy_config.types.wysiwyg' => 'wysiwyg',
                'easy_config.types.boolean' => 'boolean',
                'easy_config.types.image' => 'image',
                'easy_config.types.file' => 'file',
                'easy_config.types.color' => 'color',
                'easy_config.types.date' => 'date',
                'easy_config.types.time' => 'time',
                'easy_config.types.datetime' => 'datetime',
            ])
            ->setMap([
                'code' => ['code'],
                'number' => ['number'],
                'email' => ['email'],
                'json' => ['json'],
                'text' => ['text'],
                'boolean' => ['boolean'],
                'wysiwyg' => ['wysiwyg'],
                'textarea' => ['textarea'],
                'file' => ['file'],
                'image' => ['image'],
                'color' => ['color'],
                'date' => ['date'],
                'time' => ['time'],
                'datetime' => ['datetime'],
            ])
            ->renderAsBadges()
            ->setRequired(true)
            ->setColumns('col-12 col-sm-6');

        if ($config) {
            if (self::isEditable('code', $config, $pageName)) {
                yield CodeEditorField::new('code', 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable('image', $config, $pageName) && class_exists(\Adeliom\EasyMediaBundle\Form\EasyMediaType::class)) {
                yield EasyMediaField::new('image', 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setFormTypeOption('restrictions_uploadTypes', ['image/*'])
                    ->setColumns('col-12');
            }

            if (self::isEditable('file', $config, $pageName) && class_exists(\Adeliom\EasyMediaBundle\Form\EasyMediaType::class)) {
                yield EasyMediaField::new('file', 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable('color', $config, $pageName)) {
                yield ColorField::new('color', 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable('date', $config, $pageName)) {
                yield DateField::new('date', 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable('time', $config, $pageName)) {
                yield TimeField::new('time', 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable('datetime', $config, $pageName)) {
                yield DateTimeField::new('datetime', 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable('email', $config, $pageName)) {
                yield EmailField::new('email', 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable('number', $config, $pageName)) {
                yield NumberField::new('number', 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable('json', $config, $pageName)) {
                yield CodeEditorField::new('json', 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable('text', $config, $pageName)) {
                yield TextField::new('text', 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable('wysiwyg', $config, $pageName) && class_exists(\FOS\CKEditorBundle\Form\Type\CKEditorType::class)) {
                yield TextareaField::new('wysiwyg', 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->renderAsHtml()
                    ->setFormType(CKEditorType::class)
                    ->setColumns('col-12');
            }

            if (self::isEditable('textarea', $config, $pageName)) {
                yield TextareaField::new('textarea', 'easy_config.form.value')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }

            if (self::isEditable('boolean', $config, $pageName)) {
                yield BooleanField::new('boolean', 'easy_config.form.value_bool')
                    ->setVirtual(true)
                    ->hideOnIndex()
                    ->setColumns('col-12');
            }
        }
    }

    protected static function isEditable($type, $config, $pageName): bool
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
