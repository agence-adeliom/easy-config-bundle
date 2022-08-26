<?php

namespace Adeliom\EasyConfigBundle\Twig;

use Adeliom\EasyConfigBundle\Repository\ConfigRepository;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class EasyConfigExtension extends AbstractExtension
{
    public function __construct(protected ConfigRepository $configRepository)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('easy_config', \Closure::fromCallable(fn (\Twig\Environment $env, array $context, string $key, bool $directValue = true) => $this->getConfig($env, $context, $key, $directValue)), ['is_safe' => ['js', 'html'], 'needs_context' => true, 'needs_environment' => true]),
        ];
    }

    public function getConfig(Environment $env, array $context, string $key, bool $directValue = true)
    {
        if ($config = $this->configRepository->getByKey($key)) {
            if ($directValue) {
                $value = $config->{$config->getType()};

                if (in_array($config->getType(), ['code', 'wysiwyg', 'textarea', 'text'])) {
                    return new Markup($value, 'UTF-8');
                } elseif ('json' == $config->getType()) {
                    return json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR);
                } else {
                    return $value;
                }
            }

            return [
                'type' => $config->getType(),
                'value' => $config->{$config->getType()},
                'raw_value' => $config->getValue(),
            ];
        }

        return null;
    }
}
