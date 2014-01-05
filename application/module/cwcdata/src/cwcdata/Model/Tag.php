<?php

namespace cwcdata\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;
//use Zend\Db\RowGateway\RowGateway;

class Tag extends TableGateway
{
    public function __construct(Adapter $adapter)
    {
        parent::__construct('cwc_tag', $adapter, null, new \Zend\Db\ResultSet\HydratingResultSet(new \Zend\Stdlib\Hydrator\ArraySerializable, new \cwcdata\Entity\Tag));
    }

    public function getList($offset = 0, $limit = 100, $params = array())
    {
        $db     = $this->adapter;
        $sql    = new Sql($db);
        $select = $sql->select()
            ->from(array('T'=>$this->table))
            ->columns(array(
                new Expression('SQL_CALC_FOUND_ROWS T.id AS id'), 
                'name'
            ))
            ->group('T.id')
            ->order(array('T.name'));
        if($limit) {
            $select->limit($limit)->offset($offset);
        }
        
        $selectString = $sql->getSqlStringForSqlObject($select);
        $result       = $db->query($selectString)->execute();
        $count        = $db->query('SELECT FOUND_ROWS() AS count', Adapter::QUERY_MODE_EXECUTE)->current();

        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($result);

        return array( 
            'items' => $resultSet,
            'total' => $count['count']
        );
    }

    public function getBy(array $params)
    {
        $db     = $this->adapter;
        $sql    = new Sql($db);
        $select = $sql->select()
            ->from(array('T'=>$this->table))
            ->columns(array(
                'id',
                'name',
            ))
            ->group('T.id')
            ->limit(1);
        if(!empty($params['id']))
        {
            $select->where(array('T.id' => (int)$params['id']));
        }
        $selectString = $sql->getSqlStringForSqlObject($select);
        $result       = $db->query($selectString, $db::QUERY_MODE_EXECUTE);

        return $result->current();
    }
}