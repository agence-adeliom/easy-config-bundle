
![Adeliom](https://adeliom.com/public/uploads/2017/09/Adeliom_logo.png)
[![Quality gate](https://sonarcloud.io/api/project_badges/quality_gate?project=agence-adeliom_easy-config-bundle)](https://sonarcloud.io/dashboard?id=agence-adeliom_easy-config-bundle)

# Easy Common Bundle

Provide basic configuration manager for Easyadmin.

## Installation

Install with composer

```bash
composer require agence-adeliom/easy-config-bundle
```

### Setup database

#### Using doctrine migrations

```bash
php bin/console doctrine:migration:diff
php bin/console doctrine:migration:migrate
```

#### Without

```bash
php bin/console doctrine:schema:update --force
```

## Documentation

### Manage configs in your Easyadmin dashboard

Go to your dashboard controller, example : `src/Controller/Admin/DashboardController.php`

```php
<?php

namespace App\Controller\Admin;

...
use Adeliom\EasyConfigBundle\Controller\Admin\EasyConfigTrait;

class DashboardController extends AbstractDashboardController
{
    ...
    use EasyConfigTrait;

    ...
    public function configureMenuItems(): iterable
    {
        ...
        yield from $this->configMenuEntry();

        ...
```

### Usage

```php
# Get value or null
{{- easy_config('key') -}}

# Get infos or null
{{- easy_config('key', false) -}}

# Result :
{
    type,
    value,
    raw_value
}
```


## License

[MIT](https://choosealicense.com/licenses/mit/)


## Authors

- [@arnaud-ritti](https://github.com/arnaud-ritti)

  
