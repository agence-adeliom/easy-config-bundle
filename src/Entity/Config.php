<?php

namespace Adeliom\EasyConfigBundle\Entity;

use Adeliom\EasyCommonBundle\Traits\EntityIdTrait;
use Adeliom\EasyConfigBundle\Enum\EasyConfigType;
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

    /**
     * @return null
     */
    public function __get($name)
    {
        if ($this->type == $name) {
            switch ($name) {
                case EasyConfigType::DATE:
                    return $this->getDate();
                case EasyConfigType::TIME:
                    return $this->getTime();
                case EasyConfigType::DATETIME:
                    return $this->getDatetime();
                case EasyConfigType::BOOLEAN:
                    return $this->getBoolean();
                default:
                    return $this->value;
            }
        }

        return null;
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
        if (EasyConfigType::BOOLEAN == $this->type) {
            return (bool) $this->value;
        }

        return null;
    }

    public function setDate(?\DateTime $date)
    {
        if (EasyConfigType::DATE == $this->type && $date) {
            $this->value = $date->format('Y-m-d');
        }

        return null;
    }

    public function getDate()
    {
        if (EasyConfigType::DATE == $this->type) {
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
        if (EasyConfigType::TIME == $this->type) {
            $this->value = $date->format('H:i:s');
        }

        return null;
    }

    public function getTime()
    {
        if (EasyConfigType::TIME == $this->type) {
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
        if (EasyConfigType::DATETIME == $this->type && $date) {
            $this->value = $date->format('Y-m-d H:i:s');
        }

        return null;
    }

    public function getDatetime()
    {
        if (EasyConfigType::DATETIME == $this->type) {
            try {
                return new \DateTime($this->value);
            } catch (\Exception) {
                return null;
            }
        }

        return null;
    }
}
