<?php

namespace Michcald\Dummy\App\Dao;

class App extends \Michcald\Dummy\Dao
{
    public function create(array $row = null)
    {
        $app = new \Michcald\Dummy\App\Model\App();

        if ($row) {
            $app->setName($row['name'])
                ->setDescription($row['description'])
                ->setPassword($row['password']);

            if (isset($row['id'])) {
                $app->setId($row['id']);
            }
        }

        return $app;
    }

    public function getTable()
    {
        return 'meta_app';
    }

}