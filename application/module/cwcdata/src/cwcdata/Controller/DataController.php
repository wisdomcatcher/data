<?php
namespace cwcdata\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class DataController extends AbstractActionController
{
	const Model = 'cwcdata\Model\Data';
    public function indexAction()
    {
        $sm          = $this->getServiceLocator();
        $entity_id   = (int)$this->params()->fromQuery('entity_id', 0);
        $entityModel = $sm->get('cwcdata\Model\Entity');
        $dbModel     = $sm->get(self::Model);
        $entity      = $entityModel->getBy(array('id' => $entity_id));
        $dbModel->entity = $entity;
        $filters = $this->params()->fromQuery('filter', '');
        if(!empty($filters))
        {
            $filters = json_decode($filters);
            foreach($filters as $i=>$filter)
            {
                if(is_object($filter))
                {
                    $filters[$i] = (array)$filter;
                }
            }
        }
        $items   = $dbModel->getList(
            array(
                'offset' => (int)$this->params()->fromQuery('start', 0),
                'limit' => (int)$this->params()->fromQuery('limit', 25),
                'filters' => $filters
            )
        );

        $viewModel =  new JsonModel(array(
            'success' => true,
            'items'   => $items['items'],
            'total'   => $items['total']
        ));

        return $viewModel;
    }

    public function getAction()
    {
        $sm              = $this->getServiceLocator();
        $entity_id       = (int)$this->params()->fromQuery('entity_id', 0);
        $entityModel     = $sm->get('cwcdata\Model\Entity');
        $dbModel         = $sm->get(self::Model);
        $entity          = $entityModel->getBy(array('id' => $entity_id));
        $dbModel->entity = $entity;
        $id              = (int)$this->params()->fromPost('id');
        $data            = $dbModel->getBy(array('id' => $id));

        $viewModel =  new JsonModel(array(
            'success' => true,
            'data'    => $data
        ));

        return $viewModel;
    }

    public function addAction()
    {
        $sm              = $this->getServiceLocator();
        $entity_id       = (int)$this->params()->fromPost('entity_id', 0);
        $entityModel     = $sm->get('cwcdata\Model\Entity');
        $entity          = $entityModel->getBy(array('id' => $entity_id));
        $dbModel         = $sm->get(self::Model);
        $dbModel->entity = $entity;

        $post = $this->params()->fromPost();

        $types = array(
            '1' => 'string',
            '2' => 'date',
            '3' => 'float'
        );

        $fields = array();
        foreach($entity['fields'] as $field) {
            $fields[$field['field_name']] = $types[$field['field_type']];
        }
        if(!empty($entity['tagged'])) {
            $fields['tags'] = 'string';  
        }

        $group_fields = array(
            'data' => $fields
        );

        $data = array();

        foreach($group_fields as $group => $fields)
        {
            foreach($fields as $post_field => $type)
            {
                if(isset($post[$post_field])) 
                {
                    $data_field = $post_field;
                    if(is_array($type))
                    {
                        $data_field = $type[0];
                        $type       = $type[1];
                    }
                    $data[$group][$data_field] = $post[$post_field];
                    if($type=='int')
                    {
                        $data[$group][$data_field] = (int)$data[$group][$data_field];
                    }
                }
            }
        }

        if(!empty($data['data'])) {
            $dbModel->addData($data['data']);
        }

        $viewModel =  new JsonModel(array(
            'success' => true,
        ));

        return $viewModel;
    }


    public function updateAction()
    {
        $sm              = $this->getServiceLocator();
        $entity_id       = (int)$this->params()->fromPost('entity_id', 0);
        $entityModel     = $sm->get('cwcdata\Model\Entity');
        $entity          = $entityModel->getBy(array('id' => $entity_id));
        $dbModel         = $sm->get(self::Model);
        $dbModel->entity = $entity;

        $post = $this->params()->fromPost();

        $types = array(
            '1' => 'string',
            '2' => 'date',
            '3' => 'float'
        );

        $fields = array();
        foreach($entity['fields'] as $field) {
            $fields[$field['field_name']] = $types[$field['field_type']];
        }
        if(!empty($entity['tagged'])) {
            $fields['tags'] = 'string';  
        }

        $group_fields = array(
            'data' => $fields
        );

        $data = array();

        foreach($group_fields as $group => $fields)
        {
            foreach($fields as $post_field => $type)
            {
                if(isset($post[$post_field])) 
                {
                    $data_field = $post_field;
                    if(is_array($type))
                    {
                        $data_field = $type[0];
                        $type       = $type[1];
                    }
                    $data[$group][$data_field] = $post[$post_field];
                    if($type=='int')
                    {
                        $data[$group][$data_field] = (int)$data[$group][$data_field];
                    }
                }
            }
        }

        $id = (int)$this->params()->fromPost('id');
        if(!empty($data['data']) && $id)
        {
            $dbModel->updateData($id, $data['data']);
        }

        $viewModel =  new JsonModel(array(
            'success' => true,
        ));

        return $viewModel;
    }

    public function deleteAction()
    {
        $sm              = $this->getServiceLocator();
        $entity_id       = (int)$this->params()->fromPost('entity_id', 0);
        $entityModel     = $sm->get('cwcdata\Model\Entity');
        $entity          = $entityModel->getBy(array('id' => $entity_id));
        $dbModel         = $sm->get(self::Model);
        $dbModel->entity = $entity;
        $id              = $this->params()->fromPost('id', 0);

        if($id) {
            $dbModel->deleteData($id);
        }
        
        $viewModel =  new JsonModel(array(
            'success' => true,
        ));

        return $viewModel;
    }
}
