<?php
namespace Application\Form;
use Zend\Form\Form;

class TransactionForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('transaction');

        $this->add(array(
            'name' => 'date',
            'type' => 'Text',
            'options' => array(
                'label' => 'Date',
            ),
        ));

        $this->add(array(
            'name' => 'sum',
            'type' => 'Text',
            'options' => array(
                'label' => 'Sum',
            ),
        ));

        $this->add(array(
            'name' => 'tags',
            'type' => 'Text',
            'options' => array(
                'label' => 'Tags',
            ),
        ));

        $this->add(array(
            'name' => 'comment',
            'type' => 'Text',
            'options' => array(
                'label' => 'Comment',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Go',
                'id' => 'submitbutton',
            ),
        ));
    }
}