<?php

namespace cwcdata\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;
//use Zend\Db\RowGateway\RowGateway;

class Transaction extends TableGateway
{
    public function __construct(Adapter $adapter)
    {
        parent::__construct('cwc_transaction', $adapter, null, new \Zend\Db\ResultSet\HydratingResultSet(new \Zend\Stdlib\Hydrator\ArraySerializable, new \cwcdata\Entity\Transaction));
    }

    public function getList($offset = 0, $limit = 100, $params = array())
    {
        $db     = $this->adapter;
        $sql    = new Sql($db);
        $select = $sql->select()
            ->from(array('T'=>$this->table))
            ->columns(array(
                new Expression('SQL_CALC_FOUND_ROWS T.id AS id'), 
                'sum',
                'comment',
                'date',
                'created'
            ))
            ->join(array('TT' => 'cwc_transaction_tag'), 'TT.transaction_id = T.id', array(), 'left')
            ->join(array('TAG' => 'cwc_tag'), 'TAG.id = TT.tag_id', array('tags'=> new Expression('GROUP_CONCAT(DISTINCT TAG.name)')), 'left')
            ->group('T.id')
            ->order(array('T.date DESC', 'T.id DESC'))
            ->limit($limit)
            ->offset($offset);

        if(!empty($params['filters']))
        {
            foreach($params['filters'] as $filter)
            {
                switch($filter['property'])
                {
                    case 'tag_id':
                        $select->where->equalTo('TAG.id', $filter['value']);
                        break;
                }
            }
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
                'sum',
                'comment',
                'date' => new Expression('DATE_FORMAT(T.date, "%Y-%m-%d")')
            ))
            ->join(array('TT' => 'cwc_transaction_tag'), 'TT.transaction_id = T.id', array(), 'left')
            ->join(array('TG' => 'cwc_tag'), 'TG.id = TT.tag_id', array('tags' => new Expression('GROUP_CONCAT(TG.name)')),'left')
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

    public function addTransaction($data)
    {
        $db = $this->adapter;

        if(empty($data['sum']) || empty($data['tags']) || empty($data['date']))
        {
            return false;
        }

        $tags = explode(',', trim($data['tags']));
        array_walk($tags, 'trim');

        $this->adapter->getDriver()->getConnection()->beginTransaction();
        //var_dump($tags);
        try
        {
            $transaction_data = array(
                'sum'     => $data['sum'],
                'date'    => date('Y-m-d H:i:s', strtotime($data['date'])),
                'comment' => $data['comment'],
                'created' => date('Y-m-d H:i:s')
            );
            //var_dump($transaction_data);
            $this->insert($transaction_data);
            $transaction_id = $this->lastInsertValue;

            foreach($tags as $tag)
            {
                $sql          = new Sql($db);
                $select       = $sql->select()->from(array('T'=>'cwc_tag'))->columns(array('id'))->where(array('name' => $tag))->limit(1);
                $selectString = $sql->getSqlStringForSqlObject($select);
                $result       = $db->query($selectString, Adapter::QUERY_MODE_EXECUTE);
                $check        = $result->current();

                if(empty($check['id']))
                {
                    $tag_data = array(
                        'name'    => $tag,
                        'created' => date('Y-m-d H:i:s')
                    );
                    //var_dump($tag_data);
                    $sql         = new Sql($db);
                    $insert      = $sql->insert('cwc_tag')->values($tag_data);
                    $queryString = $sql->getSqlStringForSqlObject($insert);
                    $result      = $db->query($queryString, Adapter::QUERY_MODE_EXECUTE);
                    $tag_id      = $db->getDriver()->getLastGeneratedValue();
                }
                else
                {
                    $tag_id = $check['id'];
                }

                $transaction_tag_data = array(
                    'transaction_id' => $transaction_id,
                    'tag_id'         => $tag_id
                );

                $sql         = new Sql($db);
                $insert      = $sql->insert('cwc_transaction_tag')->values($transaction_tag_data);
                $queryString = $sql->getSqlStringForSqlObject($insert);
                $result      = $db->query($queryString, Adapter::QUERY_MODE_EXECUTE);
            }

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $this->adapter->getDriver()->getConnection()->rollback();
            throw $e;
        }
    }

    public function updateTransaction($id, $data)
    {
        $db = $this->adapter;

        if(empty($data['sum']) || empty($data['tags']) || empty($data['date']))
        {
            return false;
        }
        $transaction = $this->getBy(array('id' => $id));
        if(empty($transaction['id']))
        {
            return false;
        }

        $sql          = new Sql($db);
        $select       = $sql->select()->from(array('TT'=>'cwc_transaction_tag'))->columns(array('tag_id'))->where(array('transaction_id' => $id));
        $selectString = $sql->getSqlStringForSqlObject($select);
        $result       = $db->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        $cur_tags     = $result->toArray();
        $cur_tags_ids = array();
        foreach($cur_tags as $tag)
        {
            $cur_tags_ids[] = $tag['tag_id'];
        }

        $new_tags = explode(',', trim($data['tags']));
        array_walk($new_tags, 'trim');

        $this->adapter->getDriver()->getConnection()->beginTransaction();
        //var_dump($tags);
        try
        {
            $transaction_data = array(
                'sum'     => $data['sum'],
                'date'    => date('Y-m-d H:i:s', strtotime($data['date'])),
                'comment' => $data['comment']
            );
            //var_dump($transaction_data);
            $this->update($transaction_data, array('id' => $id));

            $new_tags_ids = array();
            foreach($new_tags as $tag)
            {
                $sql          = new Sql($db);
                $select       = $sql->select()->from(array('T'=>'cwc_tag'))->columns(array('id'))->where(array('name' => $tag))->limit(1);
                $selectString = $sql->getSqlStringForSqlObject($select);
                $result       = $db->query($selectString, Adapter::QUERY_MODE_EXECUTE);
                $check        = $result->current();

                if(empty($check['id']))
                {
                    $tag_data = array(
                        'name'    => $tag,
                        'created' => date('Y-m-d H:i:s')
                    );
                    //var_dump($tag_data);
                    $sql         = new Sql($db);
                    $insert      = $sql->insert('cwc_tag')->values($tag_data);
                    $queryString = $sql->getSqlStringForSqlObject($insert);
                    $result      = $db->query($queryString, Adapter::QUERY_MODE_EXECUTE);
                    $tag_id      = $db->getDriver()->getLastGeneratedValue();
                }
                else
                {
                    $tag_id = $check['id'];
                }
                $new_tags_ids[] = $tag_id;
            }

            $delete_ids = array_diff($cur_tags_ids, $new_tags_ids);
            $new_ids    = array_diff($new_tags_ids, $cur_tags_ids);

            if (count($delete_ids)) 
            {
                $sql         = new Sql($db);
                $delete      = $sql->delete('cwc_transaction_tag')->where(array('transaction_id' => $id, 'tag_id' => $delete_ids));
                $queryString = $sql->getSqlStringForSqlObject($delete);
                $result      = $db->query($queryString, $db::QUERY_MODE_EXECUTE);
            }
            if (count($new_ids)) 
            {
                foreach ($new_ids as $new_id) 
                {
                    $transaction_tag_data = array(
                        'transaction_id' => $id,
                        'tag_id'         => $new_id
                    );
                    $sql         = new Sql($db);
                    $insert      = $sql->insert('cwc_transaction_tag')->values($transaction_tag_data);
                    $queryString = $sql->getSqlStringForSqlObject($insert);
                    $result      = $db->query($queryString, Adapter::QUERY_MODE_EXECUTE);
                }
            }

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $this->adapter->getDriver()->getConnection()->rollback();
            throw $e;
        }
    }

    public function deleteTransaction($id)
    {
        $db = $this->adapter;

        $this->adapter->getDriver()->getConnection()->beginTransaction();

        try
        {
            $sql         = new Sql($db);
            $delete      = $sql->delete('cwc_transaction_tag')->where(array('transaction_id' => $id));
            $queryString = $sql->getSqlStringForSqlObject($delete);
            $result      = $db->query($queryString, Adapter::QUERY_MODE_EXECUTE);

            $sql         = new Sql($db);
            $delete      = $sql->delete('cwc_transaction')->where(array('id' => $id));
            $queryString = $sql->getSqlStringForSqlObject($delete);
            $result      = $db->query($queryString, Adapter::QUERY_MODE_EXECUTE);
            
            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $this->adapter->getDriver()->getConnection()->rollback();
            throw $e;
        }
    }
}