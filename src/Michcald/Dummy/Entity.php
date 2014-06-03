<?php

namespace Michcald\Dummy;

class Entity
{
    private $repository;

    private $vars = array();

    public function __construct(Repository $repo)
    {
        $this->repository = $repo;
    }

    public function __set($key, $value)
    {
        $this->vars[$key] = $value;
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->vars)) {
            return $this->vars[$key];
        }

        return null;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function toArray($includeId = true)
    {
        $array = array();

        foreach ($this->repository->getFields() as $field) {

            $fieldName = $field->getName();

            if ($fieldName == 'id' && !$includeId) {
                continue;
            }

            if (array_key_exists($fieldName, $this->vars)) {
                $array[$fieldName] = $this->vars[$fieldName];
            } else {
                $array[$fieldName] = null;
            }
        }

        return $array;
    }

    public function toExposeArray()
    {
        $array = array();

        foreach ($this->repository->getFields() as $field) {

            $fieldName = $field->getName();

            if ($field->isExposable()) {
                if (array_key_exists($fieldName, $this->vars)) {
                    
                    if ($field instanceof Entity\Field\File) {
                        
                        $url = 'uploads/' . 
                                $this->getRepository()->getName() . '/' .
                                $this->id . '/' . $this->vars[$fieldName];
                        
                        $array[$fieldName] = array(
                            'url' => $url,
                            'size' => filesize($url)
                        );
                        
                        // if img write the width and the height
                    } else {
                        $array[$fieldName] = $this->vars[$fieldName];
                    }
                    
                } else {
                    $array[$fieldName] = null;
                }
            }
        }

        return $array;
    }
}