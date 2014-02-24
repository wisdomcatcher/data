<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace cwcdata\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class TagController extends AbstractActionController
{
	const Model = 'cwcdata\Model\Tag';
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
            0,//(int)$this->params()->fromQuery('start', 0),
            0,//(int)$this->params()->fromQuery('limit', 25),
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
        $dbModel = $sm->get(self::Model);
        $id      = (int)$this->params()->fromPost('id');
        
        $data    = $dbModel->getBy(array('id' => $id));

        $viewModel =  new JsonModel(array(
            'success' => true,
            'data'    => $data
        ));

        return $viewModel;
    }
}
