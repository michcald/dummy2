<?php

namespace Michcald\Dummy\Form\Entity;

class ListForm extends \Michcald\Form
{
    private $repository;

    public function __construct()
    {
        $reqVal = new \Michcald\Validator\NotEmpty();

        $val1 = new \Michcald\Validator\Number\Int();
        $val1->setMin(0);

        $page = new \Michcald\Form\Element\Number();
        $page->setName('page')
            ->addValidator($reqVal)
            ->addValidator($val1);
        $this->addElement($page);

        $limit = new \Michcald\Form\Element\Number();
        $limit->setName('limit')
            ->addValidator($reqVal)
            ->addValidator($val1);
        $this->addElement($limit);

        $query = new \Michcald\Form\Element\Text();
        $query->setName('query');
        $this->addElement($query);

        $filters = new \Michcald\Form\Element\Text();
        $filters->setName('filters');
        $this->addElement($filters);

        $orders = new \Michcald\Form\Element\Text();
        $orders->setName('orders');
        $this->addElement($orders);
    }

    public function setRepository(\Michcald\Dummy\Model\Repository $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    public function isValid()
    {
        $values = $this->getValues();

        if (array_key_exists('filters', $values)) {

            if (!is_array($values)) {
                return false;
            }

            foreach ($values['filters'] as $filter) {
                if (!array_key_exists('field', $filter) ||
                    !array_key_exists('value', $filter)) {
                    return false;
                }

                if (!$this->repository->hasField($filter['field'])) {
                    return false;
                }
            }
        }

        if (array_key_exists('orders', $values)) {

            if (!is_array($values)) {
                return false;
            }

            foreach ($values['orders'] as $filter) {
                if (!array_key_exists('field', $filter) ||
                    !array_key_exists('direction', $filter)) {
                    return false;
                }

                if (!$this->repository->hasField($filter['field'])) {
                    return false;
                }

                if (!in_array(strtolower($filter['direction']), array('asc', 'desc'))) {
                    return false;
                }
            }
        }

        return parent::isValid();
    }
}