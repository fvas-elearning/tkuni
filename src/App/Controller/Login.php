<?php
namespace App\Controller;

use Tk\Request;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Auth;
use Tk\Auth\AuthEvents;
use Tk\Event\AuthEvent;
use Uni\Controller\Iface;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Login extends Iface
{

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var \App\Db\Institution
     */
    protected $institution = null;

    /**
     * Login constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Login');
    }

    /**
     * @throws Form\Exception
     */
    private function init()
    {
        /** @var Auth $auth */
        if ($this->getUser()) {
            \Tk\Uri::create($this->getUser()->getHomeUrl())->redirect();
        }
        if (!$this->form)
            $this->form = new Form('loginForm');
        $this->form->addField(new Field\Input('username'));
        $this->form->addField(new Field\Password('password'));
        $this->form->addField(new Event\Submit('login', array($this, 'doLogin')));
    }

    /**
     * @param Request $request
     * @throws Form\Exception
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->institution = \App\Db\InstitutionMap::create()->findByDomain($request->getUri()->getHost());
        if ($this->institution) {
            $this->doInsLogin($request, $this->institution->getHash());
        }
        $this->init();
        $this->form->addField(new Event\Link('forgotPassword', \Tk\Uri::create('/recover.html')));
        $this->form->execute();
    }

    /**
     * @param Request $request
     * @param $instHash
     * @throws Form\Exception
     * @throws \Exception
     */
    public function doInsLogin(Request $request, $instHash)
    {
        if (!$this->institution)
            $this->institution = \App\Db\InstitutionMap::create()->findByHash($instHash);
        if (!$this->institution || !$this->institution->active ) {
            \Tk\Alert::addWarning('Invalid or inactive Institution.');
            \App\Uri::create('/index.html');
        }
        $this->init();
        $this->form->addField(new Field\Hidden('instHash', $instHash));
        $this->form->addField(new Event\Link('forgotPassword', \Tk\Uri::create('/recover.html')));
        $this->form->execute();
    }

    /**
     * doLogin()
     *
     * @param \Tk\Form $form
     * @throws \Tk\Exception
     */
    public function doLogin($form)
    {
        if (!$form->getFieldValue('username')) {
            $form->addFieldError('username', 'Please enter a valid username');
        }
        if (!$form->getFieldValue('password')) {
            $form->addFieldError('password', 'Please enter a valid password');
        }
        if ($form->hasErrors()) {
            return;
        }

        try {
            // Fire the login event to allow developing of misc auth plugins
            $event = new AuthEvent();
            $event->replace($form->getValues());
            $this->getConfig()->getEventDispatcher()->dispatch(AuthEvents::LOGIN, $event);

            $result = $event->getResult();
            if (!$result) {
                $form->addError('Invalid username or password');
                return;
            }
            if (!$result->isValid()) {
                $form->addError( implode("<br/>\n", $result->getMessages()) );
                return;
            }

            // Copy the event to avoid propagation issues
            $sEvent = new AuthEvent($event->getAdapter());
            $sEvent->replace($event->all());
            $sEvent->setResult($event->getResult());
            $sEvent->setRedirect($event->getRedirect());
            $this->getConfig()->getEventDispatcher()->dispatch(AuthEvents::LOGIN_SUCCESS, $sEvent);
            if ($sEvent->getRedirect())
                $sEvent->getRedirect()->redirect();

        } catch (\Exception $e) {
            \Tk\Log::error($e->__toString());
            $form->addError('Login Error: ' . $e->getMessage());
        }
    }

    /**
     * show()
     *
     * @return \Dom\Template
     * @throws \Dom\Exception
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $fren = new \Tk\Form\Renderer\Dom($this->form);
        $template->insertTemplate($this->form->getId(), $fren->show());

        if ($this->institution) {
            if ($this->institution->getLogoUrl()) {
                $template->setChoice('instLogo');
                $template->setAttr('instLogo', 'src', $this->institution->getLogoUrl()->toString());
            }
            $template->insertText('instName', $this->institution->name);
            $template->setChoice('inst');
        } else {
            $template->setChoice('noinst');
            $template->setChoice('recover');
        }
        if ($this->getConfig()->get('site.client.registration') && !$this->institution) {
            $template->setChoice('register');
        }

        return $template;
    }
    
}