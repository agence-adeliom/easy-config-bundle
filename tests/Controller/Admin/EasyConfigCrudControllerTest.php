<?php

namespace Adeliom\EasyConfigBundle\Tests\Controller\Admin;

use Adeliom\EasyConfigBundle\Controller\Admin\EasyConfigCrudController;
use Adeliom\EasyConfigBundle\Controller\Admin\EasyConfigTrait;
use Adeliom\EasyConfigBundle\EasyConfigBundle;
use Adeliom\EasyConfigBundle\Enum\EasyConfigEnum;
use Adeliom\EasyConfigBundle\Tests\Fixtures\Entity\TestConfig;
use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\CrudContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\RequestContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Controller\DashboardControllerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Factory\MenuFactoryInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\AssetsDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Registry\CrudControllerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(\Adeliom\EasyConfigBundle\Controller\Admin\EasyConfigCrudController::class)]
#[CoversClass(\Adeliom\EasyConfigBundle\Controller\Admin\EasyConfigTrait::class)]
#[CoversClass(\Adeliom\EasyConfigBundle\EasyConfigBundle::class)]
final class EasyConfigCrudControllerTest extends TestCase
{
    public function testConfigureCrudAddsExpectedThemesAndPermission(): void
    {
        $controller = new TestEasyConfigCrudController();
        $crudDto = $controller->configureCrud(Crud::new())->getAsDto();

        self::assertSame('easy_config.config', $crudDto->getEntityLabelInSingular());
        self::assertSame('easy_config.configs', $crudDto->getEntityLabelInPlural());
        self::assertSame('ROLE_ADMIN', $crudDto->getEntityPermission());
        self::assertContains('@EasyFields/form/choice_mask_widget.html.twig', $crudDto->getFormThemes());
    }

    public function testProtectedHelpersExposeAvailableTypesAndFieldMap(): void
    {
        $controller = new TestEasyConfigCrudController();

        self::assertCount(count(EasyConfigEnum::getValues()), $controller->exposedAvailableTypes());
        self::assertSame(EasyConfigEnum::TEXT->value, $controller->exposedAvailableTypes()['easy_config.types.'.EasyConfigEnum::TEXT->value]);
        self::assertSame([EasyConfigEnum::JSON->value], $controller->exposedFieldMap()[EasyConfigEnum::JSON->value]);
    }

    public function testConfigureFiltersAndActions(): void
    {
        $controller = new TestEasyConfigCrudController();

        $filtersDto = $controller->configureFilters(Filters::new())->getAsDto();
        self::assertArrayHasKey('key', $filtersDto->all());
        self::assertArrayHasKey('name', $filtersDto->all());
        self::assertArrayHasKey('type', $filtersDto->all());

        $actionsDto = $controller->configureActions(Actions::new())->getAsDto(Crud::PAGE_INDEX);
        self::assertNotNull($actionsDto->getAction(Crud::PAGE_INDEX, Action::DETAIL));
    }

    public function testConfigureFieldsForNewPageContainsBaseFields(): void
    {
        $controller = $this->createControllerWithConfig(null);

        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_NEW));

        self::assertCount(4, $fields);
        self::assertSame('key', $fields[0]->getAsDto()->getProperty());
        self::assertSame('type', $fields[3]->getAsDto()->getProperty());
    }

    public function testConfigureFieldsWithConfigExposesDynamicValueFields(): void
    {
        $this->ensureOptionalFormClassesExist();

        $config = (new TestConfig())
            ->setName('Homepage title')
            ->setType(EasyConfigEnum::TEXT->value);

        $controller = $this->createControllerWithConfig($config);
        $properties = array_map(static fn ($field) => $field->getAsDto()->getProperty(), iterator_to_array($controller->configureFields(Crud::PAGE_DETAIL)));

        self::assertContains(EasyConfigEnum::CODE->value, $properties);
        self::assertContains(EasyConfigEnum::TEXT->value, $properties);
        self::assertContains(EasyConfigEnum::BOOLEAN->value, $properties);
        self::assertContains(EasyConfigEnum::DATETIME->value, $properties);
        self::assertContains(EasyConfigEnum::IMAGE->value, $properties);
        self::assertContains(EasyConfigEnum::WYSIWYG->value, $properties);
    }

    public function testEditableRulesAndSubscribedServices(): void
    {
        $config = (new TestConfig())->setType(EasyConfigEnum::TEXT->value);
        $reflection = new \ReflectionProperty(TestConfig::class, 'id');
        $reflection->setValue($config, 1);

        self::assertTrue(TestEasyConfigCrudController::exposedIsEditable(EasyConfigEnum::TEXT->value, $config, Crud::PAGE_EDIT));
        self::assertTrue(TestEasyConfigCrudController::exposedIsEditable(EasyConfigEnum::TEXT->value, $config, Crud::PAGE_DETAIL));
        self::assertFalse(TestEasyConfigCrudController::exposedIsEditable(EasyConfigEnum::BOOLEAN->value, $config, Crud::PAGE_DETAIL));
        self::assertArrayHasKey(\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface::class, TestEasyConfigCrudController::getSubscribedServices());
    }

    public function testTraitBuildsMenuEntryAndBundleCreatesExtension(): void
    {
        $container = new Container();
        $container->set('parameter_bag', new ParameterBag([
            'easy_config.config_class' => TestConfig::class,
        ]));
        $dashboard = new TestEasyConfigDashboardController();
        $dashboard->setContainer($container);

        $items = iterator_to_array($dashboard->configMenuEntry());

        self::assertCount(1, $items);
        self::assertSame('easy_config.configs', (string) $items[0]->getAsDto()->getLabel());
        self::assertInstanceOf(\Adeliom\EasyConfigBundle\DependencyInjection\EasyConfigExtension::class, (new EasyConfigBundle())->getContainerExtension());
    }

    private function createControllerWithConfig(?TestConfig $config): TestEasyConfigCrudController
    {
        $controller = new TestEasyConfigCrudController();
        $container = new Container();
        $container->set(AdminContextProvider::class, new AdminContextProvider($this->createRequestStackWithContext($config)));
        $controller->setContainer($container);

        return $controller;
    }

    private function createRequestStackWithContext(?TestConfig $config): RequestStack
    {
        $request = Request::create('https://example.com/admin');
        $request->attributes->set(EA::CONTEXT_REQUEST_ATTRIBUTE, $this->createAdminContext($request, $config));

        $requestStack = new RequestStack();
        $requestStack->push($request);

        return $requestStack;
    }

    private function createAdminContext(Request $request, ?TestConfig $config): AdminContext
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']);

        return AdminContext::forTesting(
            RequestContext::forTesting($request),
            CrudContext::forTesting(
                entityDto: new EntityDto(TestConfig::class, $metadata, null, $config),
                crudControllers: new CrudControllerRegistry([], [], [TestConfig::class => TestEasyConfigCrudController::class], [])
            )
        );
    }

    private function ensureOptionalFormClassesExist(): void
    {
        if (!class_exists(\Adeliom\EasyMediaBundle\Form\EasyMediaType::class)) {
            class_alias(\stdClass::class, \Adeliom\EasyMediaBundle\Form\EasyMediaType::class);
        }

        if (!class_exists(\FOS\CKEditorBundle\Form\Type\CKEditorType::class)) {
            class_alias(\stdClass::class, \FOS\CKEditorBundle\Form\Type\CKEditorType::class);
        }
    }
}

final class TestEasyConfigCrudController extends EasyConfigCrudController
{
    public static function getEntityFqcn(): string
    {
        return TestConfig::class;
    }

    public function exposedAvailableTypes(): array
    {
        return $this->getAvailableTypes();
    }

    public function exposedFieldMap(): array
    {
        return $this->getFieldMap();
    }

    public static function exposedIsEditable(string $type, object $config, string $pageName): bool
    {
        return parent::isEditable($type, $config, $pageName);
    }
}

final class TestEasyConfigDashboardController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    use EasyConfigTrait;
}
