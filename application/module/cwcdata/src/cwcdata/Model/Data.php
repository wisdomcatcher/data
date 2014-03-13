<?php

namespace cwcdata\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;
//use Zend\Db\RowGateway\RowGateway;

class Data extends AbstractTableGateway
{
    public $entity;
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }
    //public function __construct(Adapter $adapter)
    //{
    //    //parent::__construct('cwc_entity', $adapter);//, null, new \Zend\Db\ResultSet\HydratingResultSet(new \Zend\Stdlib\Hydrator\ArraySerializable, new \cwcdata\Entity\Transaction));
    //}

    public function getList(array $params)
    {
        $table     = 'cwc_transaction';
        $table_tag = 'cwc_transaction_tag';
        $tagged    = true;

        $db     = $this->adapter;
        $sql    = new Sql($db);
        $columns = array('id');

        foreach($this->entity->fields as $field) {
            $columns[] = $field['name'];
        }
        
        $select = $sql->select()
            ->from(array('T'=>$table))
            ->columns($columns)
            ->order(array('T.date DESC'));
        if($tagged) {
            $select
                ->join(array('TT' => $table_tag), 'TT.data_id = T.id', array(), 'left')
                ->join(array('TAG' => 'cwc_tag'), 'TAG.id = TT.tag_id', array('tags'=> new Expression('GROUP_CONCAT(DISTINCT TAG.name)')), 'left')
                ->group('T.id');
        }   
        if(!empty($params['offset']))
        {
            $select->offset($params['offset']);
        }
        if(!empty($params['limit']))
        {
            $select->limit($params['limit']);
        }
        if(!empty($params['filters']))
        {
            foreach($params['filters'] as $filter)
            {
                switch($filter['property'])
                {
                    case 'tag_id':
                        $select->where->equalTo('TT.tag_id', $filter['value']);
                        break;
                    case 'date':
                        $select->where->like('T.date', '%' . $filter['value'] . '%');
                        break;
                }
            }
        }
        $selectString = $sql->getSqlStringForSqlObject($select);
        $result       = $db->query($selectString, $db::QUERY_MODE_EXECUTE);
        $count        = $db->query('SELECT FOUND_ROWS() AS count', Adapter::QUERY_MODE_EXECUTE)->current();

        $items = $result->toArray();

        return array( 
            'items' => $items,
            'total' => $count['count']
        );
    }

    public function getBy(array $params)
    {
        $table     = 'cwc_transaction';
        $table_tag = 'cwc_transaction_tag';
        $tagged    = true;

        $db     = $this->adapter;
        $sql    = new Sql($db);
        $select = $sql->select()
            ->from(array('T'=>$table))
            ->columns(array(
                'id',
                'sum',
                'comment',
                'date' => new Expression('DATE_FORMAT(T.date, "%Y-%m-%d")')
            ))
            ->limit(1);
        if($tagged) {
            $select
                ->join(array('TT' => $table_tag), 'TT.data_id = T.id', array(), 'left')
                ->join(array('TG' => 'cwc_tag'), 'TG.id = TT.tag_id', array('tags' => new Expression('GROUP_CONCAT(TG.name)')),'left')
                ->group('T.id');
        } 
        if(!empty($params['id']))
        {
            $select->where(array('T.id' => (int)$params['id']));
        }
        $selectString = $sql->getSqlStringForSqlObject($select);
        $result       = $db->query($selectString, $db::QUERY_MODE_EXECUTE);

        return $result->current();
    }

    public function addData($data)
    {
        $entity    = $this->entity;
        $table     = 'cwc_transaction';
        $table_tag = 'cwc_transaction_tag';
        $db        = $this->adapter;
        /*if(empty($data['sum']) || empty($data['tags']) || empty($data['date']))
        {
            return false;
        }*/
        //var_dump($data);die;

        if(!empty($data['tags']))
        {//tags must be reserved and never been used by user
            $tags = explode(',', trim($data['tags']));
            array_walk($tags, 'trim');
        }

        $this->adapter->getDriver()->getConnection()->beginTransaction();
        //var_dump($tags);
        try
        {
            $types = array(
                '2' => function($val) {
                    return date('Y-m-d H:i:s', strtotime($val));
                }
            );
            $insert_data = array(
                'created' => date('Y-m-d H:i:s')
            );
            foreach($entity['fields'] as $field) {
                if(!empty($data[$field['field_name']])) {
                    $insert_data[$field['field_name']] = !empty($types[$field['field_type']]) ? $types[$field['field_type']]($data[$field['field_name']]) : $data[$field['field_name']];
                }
            }
            //var_dump($insert_data);die;
            //$this->insert($insert_data);
            //$data_id = $this->lastInsertValue;
            $sql         = new Sql($db);
            $query       = $sql->insert($table)->values($insert_data);
            $queryString = $sql->getSqlStringForSqlObject($query);
            $result      = $db->query($queryString, Adapter::QUERY_MODE_EXECUTE);
            $data_id     = $db->getDriver()->getLastGeneratedValue();

            if(!empty($data['tags'])) 
            {
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

                    $tag_data = array(
                        'data_id' => $data_id,
                        'tag_id'  => $tag_id
                    );

                    $sql         = new Sql($db);
                    $insert      = $sql->insert($table_tag)->values($tag_data);
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

    public function updateData($id, $data)
    {
        $entity    = $this->entity;
        $table     = 'cwc_transaction';
        $table_tag = 'cwc_transaction_tag';
        $db        = $this->adapter;

        /*if(empty($data['sum']) || empty($data['tags']) || empty($data['date']))
        {
            return false;
        }*/
        $cur_data = $this->getBy(array('id' => $id));
        if(empty($cur_data['id']))
        {
            return false;
        }

        if($entity['tagged']) 
        {
            $sql          = new Sql($db);
            $select       = $sql->select()->from(array('TT'=>$table_tag))->columns(array('tag_id'))->where(array('data_id' => $id));
            $selectString = $sql->getSqlStringForSqlObject($select);
            $result       = $db->query($selectString, Adapter::QUERY_MODE_EXECUTE);
            $cur_tags     = $result->toArray();
            $cur_tags_ids = array();
            foreach($cur_tags as $tag)
            {
                $cur_tags_ids[] = $tag['tag_id'];
            }
            if(!empty($data['tags']))
            {
                $new_tags = explode(',', trim($data['tags']));
                array_walk($new_tags, 'trim');
            }
        }

        $this->adapter->getDriver()->getConnection()->beginTransaction();
        //var_dump($tags);
        try
        {
            $types = array(
                '2' => function($val) {
                    return date('Y-m-d H:i:s', strtotime($val));
                }
            );
            $update_data = array();
            foreach($entity['fields'] as $field) {
                if(!empty($data[$field['field_name']])) {
                    $update_data[$field['field_name']] = !empty($types[$field['field_type']]) ? $types[$field['field_type']]($data[$field['field_name']]) : $data[$field['field_name']];
                }
            }
            //var_dump($update_data, $data);die;
            //$this->update($update_data, array('id' => $id));
            $sql         = new Sql($db);
            $query       = $sql->update($table)->set($update_data)->where(array('id' => $id));
            $queryString = $sql->getSqlStringForSqlObject($query);
            $result      = $db->query($queryString, Adapter::QUERY_MODE_EXECUTE);

            if(!empty($data['tags']) && $entity['tagged']) 
            {
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
                    $delete      = $sql->delete($table_tag)->where(array('data_id' => $id, 'tag_id' => $delete_ids));
                    $queryString = $sql->getSqlStringForSqlObject($delete);
                    $result      = $db->query($queryString, $db::QUERY_MODE_EXECUTE);
                }
                if (count($new_ids)) 
                {
                    foreach ($new_ids as $new_id) 
                    {
                        $tag_data = array(
                            'data_id' => $id,
                            'tag_id'         => $new_id
                        );
                        $sql         = new Sql($db);
                        $insert      = $sql->insert($table_tag)->values($tag_data);
                        $queryString = $sql->getSqlStringForSqlObject($insert);
                        $result      = $db->query($queryString, Adapter::QUERY_MODE_EXECUTE);
                    }
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

    public function deleteData($id)
    {
        $entity    = $this->entity;
        $table     = 'cwc_transaction';
        $table_tag = 'cwc_transaction_tag';
        $db        = $this->adapter;

        $this->adapter->getDriver()->getConnection()->beginTransaction();

        try
        {
            $sql         = new Sql($db);
            $delete      = $sql->delete($table_tag)->where(array('data_id' => $id));
            $queryString = $sql->getSqlStringForSqlObject($delete);
            $result      = $db->query($queryString, Adapter::QUERY_MODE_EXECUTE);

            $sql         = new Sql($db);
            $delete      = $sql->delete($table)->where(array('id' => $id));
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