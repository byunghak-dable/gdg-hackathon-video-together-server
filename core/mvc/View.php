<?php

namespace core\mvc;

class View
{

    protected $view;
    protected $data;

    public function __construct($view, $data)
    {
        $this->view = $view;
        $this->data = $data;
    }

    public function loadView()
    {
        extract($this->data);
        return require "app/views/view.{$this->view}.php";
    }
}
