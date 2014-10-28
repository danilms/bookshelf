<?php

namespace Bookshelf\Controller;

use Bookshelf\Core\Logger\Logger;
use Bookshelf\Core\Session;
use Bookshelf\Core\Templater;
use Bookshelf\Core\TemplaterException;
use Exception;
/**
 * @author Aleksandr Kolobkov
 */
class MainController
{
    /**
     * @var string default name for controller
     */
    private $controllName = 'Main';

    /**
     * @var var for templater instance
     */
    private $templater;

    /**
     * @var var for Logger class instance
     */
    private $logger;

    /**
     * @var var for Session class instance
     */
    private $session;

    /**
     * Function that create templater and session instance
     */
    public function __construct()
    {
        $this->logger= new Logger('./logs/');
        $this->session = new Session();
        try {
            $this->templater = new Templater();
        } catch (TemplaterException $e) {
            $this->logger->error("Can't create templater in MainController. Reason: $e");
            throw new Exception ('Controller error');
        }
    }

    /**
     * Return default action for $this controller
     */
    public function defaultAction()
    {
        $this->indexAction();
    }

    /**
     * When execute will show Main/IndexView.html
     */
    public function indexAction()
    {
        if ($this->session->get('logInStatus', 0) === 1) {
            $this->templater->show($this->controllName, 'AccountPage', ['name' => $this->session->get('email')]);
        } else {
            $login = new LoginController();
            $actionName = 'index';
            $param = array(
                "title" => 'Test',
                "text" => 'This is test so relax and be happy',
                "menu" => $login->getLoginForm()
            );
            $this->templater->show($this->controllName, $actionName, $param);
        }
    }
}
