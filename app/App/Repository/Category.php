<?php

namespace App\Repository;

class Category extends \Michcald\Dummy\Repository
{
    public function getDb()
    {
        $config = \Michcald\Dummy\Config::getInstance();
        return new \Michcald\Db\Adapter(
            $config->database['adapter'], 
            $config->database['host'],
            $config->database['user'],
            $config->database['password'],
            $config->database['dbname']
        );
    }
    
    public function __construct()
    {
        parent::__construct();

        $strlen = new \Michcald\Validator\String();
        $strlen->setMax(255);
        
        $name = new \Michcald\Dummy\Entity\Field\String('name');
        $name->setLabel('Category Name')
                ->setDescription('Category name')
                ->setExpose(true)
                ->setSearchable(true)
                ->setRequired(true)
                ->addValidator($strlen);
        $this->addField($name);

        $description = new \Michcald\Dummy\Entity\Field\Text('description');
        $description
                ->setLabel('Category Description')
                ->setDescription('Insert the descr')
                ->setExpose(true)
                ->setSearchable(true);
        $this->addField($description);
        
        $img = new \Michcald\Dummy\Entity\Field\File('img');
        $img
                ->setLabel('Img')
                ->setDescription('')
                ->setExpose(true)
                ->setSearchable(false);
        $this->addField($img);
    }

    public function getName()
    {
        return 'post_category';
    }

    public function getLabel($plural = false)
    {
        return $plural ? 'Post Categories' : 'Post Category';
    }

    public function getParentEntities()
    {
        return array();
    }

    public function getMaxRecords()
    {
        return 100000000;
    }
}
