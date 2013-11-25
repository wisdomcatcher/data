<?php
namespace cwcdata\Entity;

abstract class AbstractEntity implements \Zend\Stdlib\ArraySerializableInterface 
{
    public function toArray()
    {
        return $this->getArrayCopy();
    }
}