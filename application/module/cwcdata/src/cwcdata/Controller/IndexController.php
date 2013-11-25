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

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
    	$sm  = $this->getServiceLocator();
    	$cfg = $sm->get('config');
    	$allowed = array(
            $cfg['admin']['username'] => $cfg['admin']['password']
        );

        $allow = 0;
        foreach($allowed as $user => $password)
        {
            if (
                $this->getRequest()->getServer('PHP_AUTH_USER') == $user &&
                $this->getRequest()->getServer('PHP_AUTH_PW') == $password
            ) 
            {
                $allow = 1;
            }
        }
        if (!$allow) 
        {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'wrong auth';
            exit;
        } 
        else 
        {
	    	$this->layout('layout/ext-layout');
	    	$viewModel = new ViewModel();
	    	$viewModel->setTemplate('application/index/ext-index.phtml');
	        return $viewModel;
	    }
    }
}
