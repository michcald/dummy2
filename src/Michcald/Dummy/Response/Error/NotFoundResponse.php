<?php

namespace Michcald\Dummy\Response\Error;

class NotFoundResponse extends AbstractResponse
{
    public function __construct($message = null)
    {
        parent::__construct();
        
        $this->setStatusCode(404);
        
        $this->setMessage('Not found');
    }
}