<?php

namespace Bookshelf\Controller;

use Bookshelf\Core\Exception\DbException;
use Bookshelf\Model\Book;
use Bookshelf\Model\User;
use Bookshelf\Core\Validation\Constraint\EmailConstraint;
use Bookshelf\Core\Validation\Constraint\AlphabeticalConstraint;
use Bookshelf\Core\Validation\Constraint\NotBlankConstraint;
use Bookshelf\Core\Validation\Constraint\ConfirmConstraint;
use Bookshelf\Core\Validation\Validator;


/**
 * @author Aleksandr Kolobkov
 */
class UserController extends Controller
{
    /**
     * Default method that show user account page
     */
    public function defaultAction()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->redirectTo("/login");
        }
        $this->render('User', 'AccountPage', ['user' => $currentUser]);
    }

    public function deleteBookAction()
    {
        $user = User::findOneBy(['email' => $this->session->get('email')]);
        try {
            $user->deleteBook($this->request->get('book_id'));
            $this->addSuccessMessage('Книга успешно удалена!');
            $this->redirectTo('/user');
        } catch(DbException $e) {
            $this->logAndDisplayError($e, 'Ошибка удаления книги!');
        }
    }

    /**
     * Method that show page for change user data
     */
    public function showAction()
    {
        $user = $this->getCurrentUser();
        $this->render('User', 'ChangeData', ['user' => $user]);
    }

    public function attachBookAction()
    {
        $user = $this->getCurrentUser();
        $book = Book::find($this->request->get('book_id'));
        if ($book) {
            $user->setBooks([$book]);
            $this->redirectTo('/user');
        } else {
            $this->addErrorMessage('Книга не найдена');
            $this->redirectTo('/books');
        }
    }

    /**
     * Method that update user data after changing
     */
    public function changeUserDataAction()
    {
        $user = new User();
        $user->setFirstName($this->request->get('firstname'));
        $user->setEmail($this->request->get('email'));
        $user->setLastName($this->request->get('lastname'));
        $user->setPassword($this->request->get('password'));
        $errorArray = $this->validateUserUpdate($user);

        $currentUser = $this->getCurrentUser();
        $user->setId($currentUser->getId());
        $currentUser->getContacts();
        $user->setPassword($this->changePassword($currentUser));
        $params['user'] = $user;
        if ($errorArray) {
            $params['user'] = $currentUser;
            $params['errors'] = $errorArray;
            $this->render('User', 'ChangeData', $params);
        }
        $this->session->set('email', $this->request->get('email'));
        $this->session->set('firstname', $this->request->get('firstname'));
        try {
            $user->save();
            $this->addSuccessMessage('Данные успешно обновлены!');
            $this->redirectTo("/user");
        } catch(DbException $e) {
            $this->logAndDisplayError($e, 'Произошёл сбой при попытке сменить данные пользователя. Пожалуйста повторите попытку позднее');


            return $this->render('User', 'ChangeData', $params);
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
     * Method that validate user information then user trying update his data
     *
     * @param User $user
     * @return array
     */
    private function validateUserUpdate($user)
    {
        $constraints = [
            'firstname' => new AlphabeticalConstraint($user, 'firstname', "Имя должно состоять только из букв"),
            'lastname' => new AlphabeticalConstraint($user, 'lastname', "Фамилия должна состоять только из букв"),
            'firstnameBlank' => new NotBlankConstraint($user, 'firstname'),
            'lastnameBlank' => new NotBlankConstraint($user, 'lastname'),
            'emailName' => new EmailConstraint($user, 'email'),
            'emailBlank' => new NotBlankConstraint($user, 'email'),
            'passwordConfirm' => new ConfirmConstraint($user, $this->request->get('confirm_password'), 'password')
        ];

        return $this->validate($constraints);
    }

    /**
     * Method that return old password if passwords forms empty,
     * else return new password that was input in forms
     *
     * @param User $userFromDb
     * @return string
     */
    private function changePassword($userFromDb)
    {
        $confirmPassword = $this->request->get('confirm_password');
        $password = $this->request->get('password');
        if (empty($password) && empty($confirmPassword)) {
            return $userFromDb->getPassword();
        } else {
            return $this->request->get('password');
        }
    }
}
