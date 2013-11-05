<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class TransactionController extends AbstractActionController
{
	const Model = 'app\Model\Transaction';
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

    /*public function addAction()
    {
    	$form = new \Application\Form\TransactionForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) 
        {
        	$form->setData($request->getPost());
        	$sm = $this->getServiceLocator();
            $transactionTable = $sm->get('app\Model\Transaction');
            $data = (array)$request->getPost();
            //$transaction = new \Application\Entity\Transaction();
            var_dump($data);
            //$transaction->exchangeArray((array)$request->getPost());
            $transactionTable->saveTransaction($data);
            return $this->redirect()->toUrl('/application/transaction');
        }

        return array('form' => $form);
    }*/

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
            foreach($fields as $field => $type)
            {
                if(isset($post[$field])) 
                {
                    $data[$group][$field] = $post[$field];
                    if($type=='int')
                    {
                        $data[$group][$field] = (int)$data[$group][$field];
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
