<?php
namespace App\Listener;

use Tk\Event\Subscriber;
use Tk\Kernel\KernelEvents;

/**
 * This object helps cleanup the structure of the controller code
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class PageTemplateHandler extends \Bs\Listener\PageTemplateHandler
{

    /**
     * @param \Tk\Event\Event $event
     * @throws \Exception
     */
    public function showPage(\Tk\Event\Event $event)
    {
        parent::showPage($event);
        $controller = $event->get('controller');
        if ($controller instanceof \Bs\Controller\Iface) {
            $page = $controller->getPage();
            if (!$page) return;
            $template = $page->getTemplate();
            /** @var \Bs\Db\User $user */
            $user = $controller->getUser();
            //$uri = \Bs\Uri::create();
            //if ($user && $uri->getRoleType(\Tk\ObjectUtil::getClassConstants($this->getConfig()->createRole(), 'TYPE')) != '') {
            if ($user) {
                // About dialog
                $dialog = new \Bs\Ui\AboutDialog();
                $template->appendTemplate($template->getBodyElement(), $dialog->show());

                // Logout dialog
                $dialog = new \Bs\Ui\LogoutDialog();
                $template->appendTemplate($template->getBodyElement(), $dialog->show());

                // Set permission choices
                $perms = $user->getRole()->getPermissions();
                foreach ($perms as $perm) {
                    $template->setChoice($perm);
                    $controller->getTemplate()->setChoice($perm);
                }

            }

            // Add anything to the page template here ...
            $url = \Bs\Uri::create('/html/app/img/unimelb-logo-lge.png');
            $template->appendHtml('nav-footer', sprintf('<a href="https://fvas.unimelb.edu.au/" target="_blank" title="Visit FVAS"><img src="%s" class="img-fluid" alt="Logo" /></a>', $url));

        }
    }


    /**
     * @return \App\Config|\Tk\Config
     */
    public function getConfig()
    {
        return \App\Config::getInstance();
    }

}