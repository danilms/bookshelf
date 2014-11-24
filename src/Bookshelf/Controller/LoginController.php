<?php

namespace Bookshelf\Controller;

use Bookshelf\Core\Validation\Constraint\EmailConstraint;
use Bookshelf\Core\Validation\Constraint\AlphabeticalConstraint;
use Bookshelf\Core\Validation\Constraint\NotBlankConstraint;
use Bookshelf\Core\Validation\Constraint\ConfirmConstraint;
use Bookshelf\Core\Validation\Constraint\UniqueConstraint;
use Bookshelf\Core\Validation\Validator;
use Bookshelf\Model\User;
use Bookshelf\Model\ActiveRecord;

/**
 * @author Aleksandr Kolobkov
 */
class LoginController extends Controller
{
    /**
     * @var string default name for controller
     */
    private $controllerName = 'Login';

    /**
     * Default action for $this class
     */
    public function defaultAction()
    {
        if ($this->getCurrentUser()) {
            $this->redirectTo('/books');
        }

        return $this->showLoginForm();
    }

    /**
     * Search user in Db and set email and firstname in session
     */
    public function loginAction()
    {
        $user = User::findOneBy([
            'email' => $this->request->get('email'),
            'password' => $this->request->get('password')
        ]);
        if ($user) {
            $this->session->set('currentUser', $user);
            $this->redirectTo("/books");
        }

        $params = [
            'email' => $this->request->get('email'),
            'errors' => ['email' => ['Пользователя нет']]
        ];

        return $this->showLoginForm($params);
    }

    /**
     * Method that create login form on page
     */
    public function showLoginForm($params = [])
    {
        $this->render($this->controllerName, 'Form', $params);
    }

    /**
     * Delete email from session
     */
    public function logoutAction()
    {
        $this->session->delete('currentUser');
        $this->redirectTo("/");
    }

    /**
     * Method that create register form
     */
    public function registerFormAction()
    {
        $user = new User();
        return $this->showRegisterForm(['user' => $user]);
    }

    /**
     * Create register page
     * If found error then recreate register and fill username line with value from last try
     */
    public function registerAction()
    {
        $user = new User();
        $user->setFirstName($this->request->get('firstname'));
        $user->setEmail($this->request->get('email'));
        $user->setLastName($this->request->get('lastname'));
        $user->setPassword($this->request->get('password'));
        $params['user'] = $user;

        $errorArray = $this->registrationValidate($user);
        if ($errorArray) {
            $params['errors'] = $errorArray;
            return $this->showRegisterForm($params);
        }
        try {
            $user->save();
            $this->session->set('currentUser', $user);
            $this->addSuccessMessage('Вы успешно зарегистрированы!');
            $this->redirectTo("/");
        } catch(DbException $e) {
            $this->logAndDisplayError($e,'На данный момент регистрация невозможна, пожалуйста повторите попытку позднее');

            return $this->showRegisterForm($params);
        }

    }

    /**
     * Method that used constraints from array for data validate
     *
     * @param $constraints array
     * @return array
     */
    private function validate($constraints)
    {
        $validator = new Validator();
        foreach ($constraints as $constraint) {
            $validator->addConstraint($constraint);
        }

        return $validator->validate();
    }

    /**
     * @param User $user
     * @return array
     */
    private function registrationValidate(ActiveRecord $user)
    {
        $constraintList = [
            'firstnameBlank' => new NotBlankConstraint($user, 'firstname'),
            'lastnameBlank' => new NotBlankConstraint($user, 'lastname'),
            'emailBlank' => new NotBlankConstraint($user, 'email'),
            'passwordBlank' => new NotBlankConstraint($user, 'password'),
            'firstname' => new AlphabeticalConstraint($user, 'firstname', "Имя должно состоять только из букв"),
            'lastname' => new AlphabeticalConstraint($user, 'lastname', "Фамилия должна состоять только из букв"),
            'emailUnique' => new UniqueConstraint($user, 'email'),
            'emailName' => new EmailConstraint($user, 'email'),
            'passwordConfirm' => new ConfirmConstraint($user, $this->request->get('confirm_password'), 'password')
        ];

        return $this->validate($constraintList);
    }

    /**
     * Method that show register page
     *
     * @param null|array $params
     */
    private function showRegisterForm($params = null)
    {
        $this->render($this->controllerName, 'RegisterForm', $params);
    }
}
