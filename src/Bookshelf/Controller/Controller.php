<?php
/**
 * This code belongs to of Opensoft company
 */

namespace Bookshelf\Controller;

use Bookshelf\Core\Exception\DbException;
use Bookshelf\Core\Logger\Logger;
use Bookshelf\Core\Request;
use Bookshelf\Core\Session;
use Bookshelf\Core\Templater;

abstract class Controller {
    /**
     * @var var for Logger class instance
     */
    protected $logger;

    /**
     * @var var for Templater class instance
     */
    protected  $templater;

    /**
     * @var array Request
     */
    protected  $request;

    /**
     * @var Session
     */
    protected  $session;

    public function __construct()
    {
        $this->logger= new Logger('../logs/');
        $this->templater = new Templater();
        $this->request = new Request($_GET, $_POST);
        $this->session = new Session();
    }

    /**
     * @param string $controllerName
     * @param string $actionName
     * @param array $params
     */
    protected function render($controllerName, $actionName, $params = [])
    {
        $params['flashMessages'] = $this->session->pullFlashMessages();
        $params['currentUser'] = $this->getCurrentUser();
        $this->templater->show($controllerName, $actionName, $params);
    }

    protected  function redirectTo($path)
    {
        header('Location: ' . $path);
        exit;
    }

    /**
     * @param string $message
     */
    protected function addErrorMessage($message)
    {
        $this->session->addFlashMessage('danger', $message);
    }

    /**
     * @param string $message
     */
    protected function addSuccessMessage($message)
    {
        $this->session->addFlashMessage('success', $message);
    }

    /**
     * @return User
     */
    protected function getCurrentUser()
    {
        return $this->session->get('currentUser');
    }

    /**
     * @param DbException $e
     * @param string $message
     */
    protected function logAndDisplayError(DbException $e, $message)
    {
        $this->addErrorMessage($message);
        $this->logger->error($e->getMessage());
        $this->logger->error($e->getTraceAsString());
    }
}
