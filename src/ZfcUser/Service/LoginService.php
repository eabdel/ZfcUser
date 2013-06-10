<?php

namespace ZfcUser\Service;

use ZfcUser\Authentication\Adapter\AdapterChain;
use ZfcUser\Options\ModuleOptions;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\Form\FormInterface;

class LoginService
{
    /**
     * @var AdapterChain
     */
    protected $adapterChain;

    /**
     * @var AuthenticationService
     */
    protected $authenticationService;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Zend\Form\FormInterface
     */
    protected $form;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @param FormInterface $form
     * @param \ZfcUser\Options\ModuleOptions $options
     */
    public function __construct(
        FormInterface $form,
        ModuleOptions $options
    ) {
        $this->form    = $form;
        $this->options = $options;
    }

    /**
     * Logs a user in with the given identity and credential. Takes an array of parameters
     * which gets passed directly to the pre and post login events. It's up to each adapter
     * to ignore the auth attempt if the parameters they are expecting aren't available.
     *
     * @param array $data
     * @triggers pre.login
     * @triggers post.login
     * @return \Zend\Authentication\Result
     */
    public function login(array $data)
    {
        $this->getEventManager()->trigger('pre.login', $this, $data);

        $authService = $this->getAuthenticationService();
        $adapter     = $this->getAdapterChain();
        $adapter->setEventParams($data);

        $result = $authService->authenticate($adapter);

        $this->getEventManager()->trigger('post.login', $this, array('result' => $result));

        return $result;
    }

    /**
     * Clear authenticated identity.
     *
     * @triggers pre.logout
     * @triggers post.logout
     */
    public function logout()
    {
        $this->getEventManager()->trigger('pre.logout', $this);

        $this->getAuthenticationService()->clearIdentity();

        $this->getEventManager()->trigger('post.logout', $this);
    }

    /**
     * {@inheritDoc}
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers(array(__CLASS__, get_class($this)));
        $this->eventManager = $eventManager;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventManager()
    {
        if (!$this->eventManager instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }
        return $this->eventManager;
    }

    /**
     * @param \ZfcUser\Authentication\Adapter\AdapterChain $adapterChain
     * @return LoginService
     */
    public function setAdapterChain(AdapterChain $adapterChain)
    {
        $this->adapterChain = $adapterChain;
        return $this;
    }

    /**
     * @return \ZfcUser\Authentication\Adapter\AdapterChain
     */
    public function getAdapterChain()
    {
        if (!$this->adapterChain instanceof AdapterChain) {
            $this->adapterChain = new AdapterChain();
        }
        return $this->adapterChain;
    }

    /**
     * @param \Zend\Authentication\AuthenticationService $authenticationService
     * @return LoginService
     */
    public function setAuthenticationService(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
        return $this;
    }

    /**
     * @return \Zend\Authentication\AuthenticationService
     */
    public function getAuthenticationService()
    {
        if (!$this->authenticationService instanceof AuthenticationService) {
            $this->authenticationService = new AuthenticationService();
        }
        return $this->authenticationService;
    }

    /**
     * @return \Zend\Form\FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}