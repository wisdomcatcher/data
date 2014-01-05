<?php
namespace cwcdata\Entity;

class Tag extends AbstractEntity {

    public static $fields_names = array(
        'id'   => 'id',
        'name' => 'название',
    );

    public function exchangeArray(array $data)
    {
        foreach(self::$fields_names as $field => $name)
        {
            $this->$field = (isset($data[$field])) ? $data[$field] : null;
        }
    }

    public function getArrayCopy()
    {
        $result = array();
        foreach(self::$fields_names as $field => $name)
        {
            $result[$field] = $this->$field;
        }
        return $result;
    }
}