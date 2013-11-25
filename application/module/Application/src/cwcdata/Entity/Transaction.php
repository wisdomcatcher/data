<?php
namespace Application\Entity;

class Transaction extends AbstractEntity {

    public static $fields_names = array(
        'id'      => 'id',
        'sum'     => 'сумма',
        'comment' => 'комментарий',
        'tags'    => 'теги',
        'date'    => 'дата',
        'created' => 'создано',
        'updated' => 'обновлено'
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