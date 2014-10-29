<?php

namespace Bookshelf\Model;

/**
 * @author Aleksandr Kolobkov
 */
class User extends ActiveRecord
{
    /**
     * Data about user contacts will be placed here
     *
     * @var array
     */
    private $contactData;

    /**
     * Data about user books will be placed here
     *
     * @var
     */
    private $booksData;


    /**
     * Property for user firstname
     *
     * @var string
     */
    private $firstName;

    /**
     * Property for user lastname
     *
     * @var string
     */
    private $lastName;

    /**
     * Property for user email
     *
     * @var string
     */
    private $email;

    /**
     * Property for user password
     *
     * @var string
     */
    private $password;

    /**
     * Property for user id
     *
     * @var int
     */
    private $id;

    /**
     * Method that will return data about user books()
     *
     * @return array
     */
    public function getBooksData()
    {
        return $this->booksData;
    }

    /**
     * Method that will fill data about user books from outside
     *
     * @param array $booksData
     */
    public function setBooksData($booksData)
    {
        $this->booksData = $booksData;
    }

    /**
     * Method that return data about user contacts
     *
     * @return array
     */
    public function getContactData()
    {
        if (empty($this->contactData)) {
            $this->takeContactsData();
        }

        return $this->contactData;
    }

    /**
     * Method that will data about user contacts from outside
     *
     * @param mixed $contactData
     */
    public function setContactData($array)
    {
        switch (count($array)) {
            case 0:
                break;
            case 1:
                $this->contactData = $array;
                break;
            default:
                foreach (array_keys($array) as $value) {
                    $this->contactData[$value] = $array[$value];
                }
                break;
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $email string
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Return all property for user
     *
     * @return array
     */
    protected function getState()
    {
        return [
            'firstname' => $this->firstName,
            'lastname' => $this->lastName,
            'email' => $this->email,
            'password' => $this->password,
            'id' => $this->id
        ];
    }

    /**
     * @return string
     */
    protected function getTableName()
    {
        return 'users';
    }

    /**
     * Set value in user instance class from array
     *
     * @param $array
     */
    protected function setState($array)
    {
        $this->firstName = $array['firstname'];
        $this->lastName = $array['lastname'];
        $this->email = $array['email'];
        $this->password = $array['password'];
        $this->id = $array['id'];
    }

    /**
     * Method that will take data from books table and fill property for class instance
     */
    private function takeBooksData()
    {

    }

    /**
     * Method that will take data from contacts table and fill property for class instance
     */
    private function takeContactsData()
    {
        $contactData = [];
        $contact = Contact::findBy('user_id', $this->getId());
        $data = $contact->getContactDataByUser($this->getId());
        foreach (array_keys($data) as $value) {
            $contactData[$value] = [
                'contact_type' => $data[$value]->getContactName(),
                'value' => $data[$value]->getValue(),
                'id' => $data[$value]->getId()
            ];
        }
        $this->contactData = $contactData;
    }
}
