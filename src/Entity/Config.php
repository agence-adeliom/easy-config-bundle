<?php

namespace Adeliom\EasyConfigBundle\Entity;

use Adeliom\EasyCommonBundle\Traits\EntityIdTrait;
use Adeliom\EasyConfigBundle\Enum\EasyConfigEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity('key')]
#[ORM\HasLifecycleCallbacks]
#[ORM\MappedSuperclass(repositoryClass: \Adeliom\EasyConfigBundle\Repository\ConfigRepository::class)]
class Config
{
    use EntityIdTrait;

    #[ORM\Column(name: 'config', type: \Doctrine\DBAL\Types\Types::STRING, length: 255, unique: true)]
    private ?string $key = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $value = null;

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return Config
     */
    public function setKey(mixed $key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Config
     */
    public function setName(mixed $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param null $description
     *
     * @return Config
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Config
     */
    public function setType(mixed $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param null $value
     *
     * @return Config
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function __isset($name)
    {
        /**
         * If you get the error: 'Can't get a way to read the property "code" in class "App\Entity\EasyConfig\Config".'
         * Then you should add your own code in children Config class that
         * call parent::__isset() and then your logic that returns true
         */
        if (EasyConfigEnum::tryFrom($name)) {
            return true;
        }

        return match ($name) {
            'id' => isset($this->id),
            'key' => isset($this->key),
            'name' => isset($this->name),
            'description' => isset($this->description),
            'type' => isset($this->type),
            'value' => isset($this->value),
            default => false,
        };
    }

    /**
     * @return null
     */
    public function __get($name)
    {
        if ($this->type == $name) {
            return match ($name) {
                EasyConfigEnum::DATE->value => $this->getDate(),
                EasyConfigEnum::TIME->value => $this->getTime(),
                EasyConfigEnum::DATETIME->value => $this->getDatetime(),
                EasyConfigEnum::BOOLEAN->value => $this->getBoolean(),
                default => $this->value,
            };
        }

        return match ($name) {
            'id' => $this->getId(),
            'key' => $this->getKey(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'type' => $this->getType(),
            'value' => $this->getValue(),
            default => null,
        };
    }

    /**
     * @param null $value
     *
     * @return Config
     */
    public function __set($name, $value)
    {
        if ($name == $this->type) {
            $this->value = $value;
        }

        return $this;
    }

    public function getBoolean()
    {
        if (EasyConfigEnum::BOOLEAN->value == $this->type) {
            return (bool) $this->value;
        }

        return null;
    }

    public function setDate(?\DateTime $date)
    {
        if (EasyConfigEnum::DATE->value == $this->type && $date) {
            $this->value = $date->format('Y-m-d');
        }

        return null;
    }

    public function getDate()
    {
        if (EasyConfigEnum::DATE->value == $this->type) {
            try {
                return new \DateTime($this->value);
            } catch (\Exception) {
                return null;
            }
        }

        return null;
    }

    public function setTime(?\DateTime $date)
    {
        if (EasyConfigEnum::TIME->value == $this->type) {
            $this->value = $date->format('H:i:s');
        }

        return null;
    }

    public function getTime()
    {
        if (EasyConfigEnum::TIME->value == $this->type) {
            try {
                return new \DateTime($this->value);
            } catch (\Exception) {
                return null;
            }
        }

        return null;
    }

    public function setDatetime(?\DateTime $date)
    {
        if (EasyConfigEnum::DATETIME->value == $this->type && $date) {
            $this->value = $date->format('Y-m-d H:i:s');
        }

        return null;
    }

    public function getDatetime()
    {
        if (EasyConfigEnum::DATETIME->value == $this->type) {
            try {
                return new \DateTime($this->value);
            } catch (\Exception) {
                return null;
            }
        }

        return null;
    }
}
