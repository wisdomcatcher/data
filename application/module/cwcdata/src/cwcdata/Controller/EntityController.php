<?php
namespace cwcdata\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class EntityController extends AbstractActionController
{
	const Model = 'cwcdata\Model\Transaction';
    public function indexAction()
    {
        $sm      = $this->getServiceLocator();
        $dbModel = $sm->get(self::Model);
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
            (int)$this->params()->fromQuery('start', 0),
            (int)$this->params()->fromQuery('limit', 25),
            array(
                'filters' => $filters
            )
        );

        $viewModel =  new JsonModel(array(
            'success' => true,
            'items'   => $items['items']->count() ? $items['items']->toArray() : array(),
            'total'   => $items['total']
        ));

        return $viewModel;
    }

    public function getAction()
    {
        $sm      = $this->getServiceLocator();
        $dbModel = $sm->get('cwcdata\Model\Entity');
        $id      = (int)$this->params()->fromPost('id');
        
        $data    = $dbModel->getBy(array('id' => $id));

        $viewModel =  new JsonModel(array(
            'success' => true,
            'data'    => $data
        ));

        return $viewModel;
    }

    public function addAction()
    {
        $sm      = $this->getServiceLocator();
        $dbModel = $sm->get(self::Model);

        $post = $this->params()->fromPost();

        $group_fields = array(
            'transactionData' => array(
                'date'    => 'date', 
                'sum'     => 'float',
                'comment' => 'string',
                'tags'    => 'string'
            )
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

        if(!empty($data['transactionData'])) {
            $dbModel->addTransaction($data['transactionData']);
        }

        $viewModel =  new JsonModel(array(
            'success' => true,
        ));

        return $viewModel;
    }

    public function updateAction()
    {
        $sm      = $this->getServiceLocator();
        $dbModel = $sm->get(self::Model);

        $post = $this->params()->fromPost();

        $group_fields = array(
            'transactionData' => array(
                'date'    => 'date', 
                'sum'     => 'float',
                'comment' => 'string',
                'tags'    => 'string'
            )
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
        if(!empty($data) && $id)
        {
            $dbModel->updateTransaction($id, $data['transactionData']);
        }

        $viewModel =  new JsonModel(array(
            'success' => true,
        ));

        return $viewModel;
    }

    public function deleteAction()
    {
        $sm      = $this->getServiceLocator();
        $dbModel = $sm->get(self::Model);
        $id      = $this->params()->fromPost('id', 0);

        if($id) {
            $dbModel->deleteTransaction($id);
        }
        
        $viewModel =  new JsonModel(array(
            'success' => true,
        ));

        return $viewModel;
    }
}
