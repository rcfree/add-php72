<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:16
 */

namespace ClinicLE\Page\PageResponse;

use Symfony\Component\HttpFoundation\Response;
use CLE;

class ViewPageResponse extends Response
{
    private $view;
    private $data=array();
    private $status=200;

    function __construct($view, $data=array(),$status=200) {
        $this->view = $view;
        $this->data = $data;
        $this->status = $status;

        parent::__construct("",$status,array("Content-type"=>"text/html;charset=utf-8"));
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param mixed $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    function send() {
        $webAppPath=CLE::sys()->getEnvironment()->getWebAppPath();
        $data = $this->data ? : array();
        if (CLE::sys()->config('notification')) {
            $data['notify'] = CLE::sys()->config('notification');
        }

        extract($data);
        CLE::info("views:".$this->getView());
        include_once($webAppPath."/views/".$this->getView().".php");
        //CLE::log()->info("content:".$content);
        //$this->setContent($content);
        $this->setStatus($this->status);
        return parent::send();
    }
}