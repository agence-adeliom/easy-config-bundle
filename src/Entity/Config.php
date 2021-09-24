<?php

namespace Adeliom\EasyConfigBundle\Entity;

use Adeliom\EasyCommonBundle\Traits\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity("key")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\MappedSuperclass(repositoryClass="Adeliom\EasyConfigBundle\Repository\ConfigRepository")
 */
class Config
{
    use EntityIdTrait;

    /**
     * @ORM\Column(name="config", type="string", length=255, unique=true)
     */
    private $key;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description = null;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $value = null;

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     * @return Config
     */
    public function setKey($key)
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
     * @param mixed $name
     * @return Config
     */
    public function setName($name)
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
     * @param mixed $type
     * @return Config
     */
    public function setType($type)
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
        if($this->type == $name){
            switch ($name){
                case 'date':
                    return $this->getDate();
                case 'time':
                    return $this->getTime();
                case 'datetime':
                    return $this->getDatetime();
                case 'boolean':
                    return $this->getBoolean();
                default:
                    return $this->value;
            }
        }
        return null;
    }

    /**
     * @param null $value
     * @return Config
     */
    public function __set($name, $value)
    {
        if($name == $this->type){
            $this->value = $value;
        }
        return $this;
    }

    public function getBoolean()
    {
        if($this->type == 'boolean'){
            return (bool) $this->value;
        }
        return null;
    }

    public function setDate(?\DateTime $date)
    {
        if($this->type == 'date' && $date){
            $this->value = $date->format("Y-m-d");
        }
        return null;
    }

    public function getDate()
    {
        if($this->type == 'date'){
            try {
                return new \DateTime($this->value);
            }catch (\Exception $e){
                return null;
            }
        }
        return null;
    }

    public function setTime(?\DateTime $date)
    {
        if($this->type == 'time'){
            $this->value = $date->format("H:i:s");
        }
        return null;
    }

    public function getTime()
    {
        if($this->type == 'time'){
            try {
                return new \DateTime($this->value);
            }catch (\Exception $e){
                return null;
            }
        }
        return null;
    }

    public function setDatetime(?\DateTime $date)
    {
        if($this->type == 'datetime' && $date){
            $this->value = $date->format("Y-m-d H:i:s");
        }
        return null;
    }

    public function getDatetime()
    {
        if($this->type == 'datetime'){
            try {
                return new \DateTime($this->value);
            }catch (\Exception $e){
                return null;
            }
        }
        return null;
    }

}
