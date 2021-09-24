# Setup database

## Using doctrine migrations

```bash
php bin/console doctrine:migration:diff
php bin/console doctrine:migration:migrate
```

## Without

```bash
php bin/console doctrine:schema:update --force
```

# Manage configs in your Easyadmin dashboard

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
