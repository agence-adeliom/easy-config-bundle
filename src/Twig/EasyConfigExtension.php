<?php

namespace Adeliom\EasyConfigBundle\Twig;

use Adeliom\EasyConfigBundle\Repository\ConfigRepository;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class EasyConfigExtension extends AbstractExtension
{
    /** @var ConfigRepository  */
    protected $configRepository;

    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('easy_config', [$this, 'getConfig'], ['is_safe' => ['js', 'html'], 'needs_context' => true, 'needs_environment' => true]),
        ];
    }

    /**
     * @param string $key
     */
    public function getConfig(Environment $env, array $context, string $key, bool $directValue = true)
    {
        if ($config = $this->configRepository->getByKey($key)){
            if($directValue){
                $value = $config->{$config->getType()};

                if(in_array($config->getType(), ["code", "wysiwyg", "textarea", "text"])){
                    return new Markup($value, 'UTF-8');
                }elseif ($config->getType() == "json") {
                    return json_decode($value, true);
                }else{
                    return $value;
                }
            }
            return [
                'type' => $config->getType(),
                'value' => $config->{$config->getType()},
                'raw_value' => $config->getValue()
            ];
        }
        return null;
    }

}
