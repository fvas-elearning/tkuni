<?php
namespace App\Page;

use Tk\Request;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StaffPage extends Iface
{
    
    /**
     * AdminPage constructor.
     *
     * @param \App\Controller\Iface $controller
     */
    public function __construct(\App\Controller\Iface $controller)
    {
        if (!$controller->getUser()) {
            \Tk\Uri::create('/login.html')->redirect();
            //throw new UnauthorizedHttpException('You do not have permission to access this resource.');
        }
        parent::__construct($controller);
    }


    public function show()
    {
        $this->initPage();
        /** @var \Dom\Template $template */
        $template = $this->getTemplate();


        return $template;
    }


    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $tplFile =  $this->getTemplatePath().'/main.xtpl';
        return \Dom\Loader::loadFile($tplFile);
    }

}