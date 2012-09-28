<?php

namespace FSC\RestBundle\REST\Description;

use Symfony\Component\Form\FormInterface;

class FormDescription
{
    private $rel;
    private $method;
    private $actionUrl;
    private $form;
    private $data;
    private $selfUrl;

    public function __construct($rel, $method, $actionUrl, FormInterface $form, $data, $selfUrl)
    {
        $this->rel = $rel;
        $this->method = $method;
        $this->actionUrl = $actionUrl;
        $this->form = $form;
        $this->data = $data;
        $this->selfUrl = $selfUrl;
    }

    public function getRel()
    {
        return $this->rel;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getActionUrl()
    {
        return $this->actionUrl;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getSelfUrl()
    {
        return $this->selfUrl;
    }
}
