<?php

namespace cwcdata\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;
//use Zend\Db\RowGateway\RowGateway;

class Entity extends TableGateway
{
    public function __construct(Adapter $adapter)
    {
        parent::__construct('cwc_entity', $adapter);//, null, new \Zend\Db\ResultSet\HydratingResultSet(new \Zend\Stdlib\Hydrator\ArraySerializable, new \cwcdata\Entity\Transaction));
    }

    public function loadFields($items)
    {
        $ids = array();
        foreach($items as $i => $item) {
            $ids[] = $item['id'];
        }
        if(!empty($ids)) {
            //$persons = $this->personModel->select(array('id' => $person_ids));
            $db     = $this->adapter;
            $sql    = new Sql($db);
            $select = $sql->select()
                ->from(array('EF'=>'cwc_entity_field'))
                ->columns(array(
                    'id',
                    'entity_id',
                    'name',
                    'field_name',
                    'field_type'
                ))
                ->join(array('E' => 'cwc_entity'), 'E.id = EF.entity_id', array())
                ->where(array('E.id' => $ids));
            $selectString = $sql->getSqlStringForSqlObject($select);
            $result       = $db->query($selectString, $db::QUERY_MODE_EXECUTE);
            $fields       = $result->toArray();
            $fields_by_ids= array();
            foreach($fields as $field) {
                $fields_by_ids[$field['entity_id']][] = $field;
            }
            foreach($items as $i => $item) {
                if(isset($fields_by_ids[$item['id']])) {
                    $items[$i]['fields'] = $fields_by_ids[$item['id']];
                }
            }
        }
    }

    public function getBy(array $params)
    {
        $db     = $this->adapter;
        $sql    = new Sql($db);
        $select = $sql->select()
            ->from(array('E'=>$this->table))
            ->columns(array(
                'id',
                'name',
                'table_name',
                'tagged'
            ))
            ->join(array('EF' => 'cwc_entity_field'), 'EF.entity_id = E.id', array(), 'left')
            ->group('E.id')
            ->limit(1);
        if(!empty($params['id']))
        {
            $select->where(array('E.id' => (int)$params['id']));
        }
        $selectString = $sql->getSqlStringForSqlObject($select);
        $result       = $db->query($selectString, $db::QUERY_MODE_EXECUTE);

        $result = $result->current();

        if(!empty($result)) {
            $this->loadFields(array($result));
        }

        return $result;
    }
}