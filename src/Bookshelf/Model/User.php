<?php

namespace Bookshelf\Model;

use Bookshelf\Core\Db;
use InvalidArgumentException;
use PDO;

/**
 * @author Aleksandr Kolobkov
 */
class User extends ActiveRecord
{
    /**
     * Data about user contacts will be placed here
     *
     * @var Contact[]
     */
    private $contacts;

    /**
     * Data about user books will be placed here
     *
     * @var Book[]
     */
    private $books;


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

    public function __sleep()
    {
        $this->books = null;
        $this->contacts = null;

        return array_keys(get_object_vars($this));
    }
    /**
     * Method that will return array of book instances
     *
     * @return array
     */
    public function getBooks()
    {
        if (empty($this->books)) {
            $this->fetchBooks();
        }

        return $this->books;
    }

    /**
     * Method that will fill data about user books from outside
     *
     * @param array $books
     */
    public function setBooks($books)
    {
       $this->books = $books;
    }

    /**
     * Method that return data about user contacts
     *
     * @return array
     */
    public function getContacts()
    {
        if (empty($this->contacts)) {
            $this->fetchContacts();
        }

        return $this->contacts;
    }

    /**
     * @param string $type
     * @param string $value
     * @return Contact
     */
    public function createContact($type, $value)
    {
        if (in_array($type, Contact::$allowableTypes)) {
            $contact = new Contact($this);
            $contact->setType($type);
            $contact->setValue($value);

            return $contact;
        } else {
            throw new InvalidArgumentException('Improper contact type');
        }
    }

    public function deleteBook($bookId)
    {
        $this->delete('users_to_books', ['user_id' => $this->getId(), 'book_id' => $bookId]);
        Book::deleteIfOrphane($bookId);
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
     * @param Book $book
     */
    public function attachBook($book)
    {
        $this->bindBookToUser($book);
        $this->books[] = $book;
    }

    /**
     * Return all property for user
     *
     * @return array
     */
    protected function toArray()
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
    protected function initStateFromArray($array)
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
    private function fetchBooks()
    {
        $sql = "SELECT books.* FROM users_to_books INNER JOIN books ON users_to_books.book_id = books.id WHERE users_to_books.user_id = $this->id";
        Db::getInstance()->execute($sql);
        $booksData = Db::getInstance()->getStatement()->fetchAll(PDO::FETCH_ASSOC);
        foreach ($booksData as $bookData) {
            $book = new Book;
            $book->initStateFromArray($bookData);
            $this->books[] = $book;
        }
    }

    /**
     * Method that will take data from contacts table and fill property for class instance
     */
    private function fetchContacts()
    {
        // TODO Find way for sending contacts table name in user model
        $fetchResult = Db::getInstance()->fetchBy('contacts', ['user_id' => $this->getId()]);
        foreach ($fetchResult as $contactData) {
            $contact = $this->createContact($contactData['name'], $contactData['value']);
            $contact->setId($contactData['id']);
            $this->contacts[] = $contact;
        }
    }

    /**
     * @param Book $book
     */
    private function bindBookToUser($book)
    {
        Db::getInstance()->insert('users_to_books', ['user_id' => $this->getId(), 'book_id' => $book->getId()]);
    }
}
