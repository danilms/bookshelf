<?php

namespace Bookshelf\Controller;

use Bookshelf\Core\Exception\DbException;
use Bookshelf\Model\Contact;
use Bookshelf\Core\Validation\Constraint\EmailConstraint;
use Bookshelf\Core\Validation\Constraint\NotBlankConstraint;
use Bookshelf\Core\Validation\Constraint\PhoneConstraint;
use Bookshelf\Core\Validation\Validator;
use InvalidArgumentException;

/**
 * @author Aleksandr Kolobkov
 */
class ContactsController extends Controller
{

    /**
     * Method that adding contact in base for user
     */
    public function  addContactAction()
    {
        $errors = [];
        $user = $this->getCurrentUser();
        if ($user) {
            if ($this->request->get('contact_type')) {
                try {
                    $contact = $user->createContact($this->request->get('contact_type'), $this->request->get('value'));
                    if (!$errors) {
                        try {
                            $contact->save();
                            $this->addSuccessMessage('Контакт успешно добавлен!');
                            $this->redirectTo("/user/show/?id=" . $contact->getUser()->getId());
                        } catch (DbException $e) {
                            $this->logAndDisplayError($e, 'Ошибка добавления контакта!');
                        }
                    }
                    $errors = $this->checkDataByContactType($contact);
                } catch (InvalidArgumentException $e) {
                    $errors['type'] = ['Данный тип контакта не поддерживается'];
                }
            } else {
                $errors['type'] = ['Это поле не может быть пустым'];
            }
        } else {
            $errors['contact'] = 'Пользователь не найден';
        }

        return $this->render('User', 'ChangeData', ['user' => $user, 'errors' => $errors]);
    }

    /**
     * Method that show page for change user contact data
     */
    public function showContactAction()
    {
        $user = $this->getCurrentUser();
        $id = $this->request->get('contact_id');
        $contacts = $user->getContacts();
        foreach ($contacts as $contact) {
            if ($id == $contact->getId()) {
                return $this->render('Contact', 'ChangeContactsData', ['contact' => $contact]);
            }
        }
        $errors['contact'] = 'Контакт не найден';

        return $this->render('User', 'ChangeData', ['user' => $user, 'errors' => $errors]);
    }

    /**
     * Method that update data for user contact then he want change it
     */
    public function changeDataAction()
    {
        $user = $this->getCurrentUser();
        $id = $this->request->get('contact_id');
        $contacts = $user->getContacts();
        foreach ($contacts as $contact) {
            if ($id == $contact->getId()) {

                $contact->setType($this->request->get('contact_type'));
                $contact->setValue($this->request->get('value'));

                $errors = $this->checkDataByContactType($contact);
                if (!$errors) {
                    try {
                        $contact->save();
                        $this->addSuccessMessage('Информация успешно обновлена!');
                        $this->redirectTo("/user/show/?id=" . $contact->getUser()->getId());
                    } catch (DbException $e) {
                        $this->logAndDisplayError($e, 'Ошибка изменения контакта!');
                    }
                }
            }
        }
        $errors['contact'] = 'Контакт не найден';

        return $this->render('Contact', 'ChangeContactsData', ['contact' => $contacts[$id], 'errors' => $errors]);
    }

    /**
     * Method that delete contact from user
     */
    public function deleteContactsDataAction()
    {
        $currentUser = $this->getCurrentUser();
        $id = $this->request->get('contact_id');
        $contacts = $currentUser->getContacts();
        foreach ($contacts as $contact) {
            if ($id == $contact->getId()) {
                try {
                    $contact->delete();
                    $this->addSuccessMessage('Контакт успешно удалён!');
                    $this->redirectTo("/user/show/?id=" . $contact->getUser()->getId());
                } catch (DbException $e) {
                    $this->logAndDisplayError($e, 'Ошибка удаления контакта!');

                }
            }
        }
        $errors['contact'] = 'Немогу удалить несуществующий контакт';

        return $this->render('User', 'ChangeData', ['user' => $currentUser, 'errors' => $errors]);
    }

    /**
     * Method that check that type of contact was send by user
     *
     * @param Contact $contact
     * @return array
     */
    private function checkDataByContactType(Contact $contact)
    {
        $validator = new Validator();
        $notBlankValue = new NotBlankConstraint($contact, 'value');
        $validator->addConstraint($notBlankValue);
        if ($contact->getType() === Contact::EMAIL_TYPE) {
            $email = new EmailConstraint($contact, 'value');
            $validator->addConstraint($email);
        } elseif ($contact->getType() === Contact::PHONE_TYPE) {
            $phone = new PhoneConstraint($contact, 'value');
            $validator->addConstraint($phone);
        }

        return $validator->validate();
    }
}
