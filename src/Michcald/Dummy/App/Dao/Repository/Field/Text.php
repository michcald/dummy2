<?php

namespace Michcald\Dummy\App\Dao\Repository\Field;

class Text extends \Michcald\Dummy\App\Dao\Repository\Field
{
    public function create(array $row = null)
    {
        $field = new \Michcald\Dummy\App\Model\Repository\Field\Text();

        if ($row) {
            parent::create($field, $row);
        }

        return $field;
    }

}