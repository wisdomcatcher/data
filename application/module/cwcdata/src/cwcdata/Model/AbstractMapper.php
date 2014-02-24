<?php

namespace cwcdata\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;
//use Zend\Db\RowGateway\RowGateway;

class AbstractMapper extends TableGateway
{
    public function __construct(Adapter $adapter)
    {
        //parent::__construct('cwc_transaction', $adapter, null, new \Zend\Db\ResultSet\HydratingResultSet(new \Zend\Stdlib\Hydrator\ArraySerializable, new \cwcdata\Entity\Transaction));
    }
}