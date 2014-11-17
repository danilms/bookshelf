<?php
/**
 * This code belongs to of Opensoft company
 */

namespace Bookshelf\Controller;

use Bookshelf\Core\Validation\Constraint\EntityExistsConstraint;
use Bookshelf\Core\Validation\Constraint\LinkConstraint;
use Bookshelf\Core\Validation\Constraint\NotBlankConstraint;
use Bookshelf\Core\Validation\Constraint\ChoiceConstraint;
use Bookshelf\Core\Validation\Constraint\UniqueConstraint;
use Bookshelf\Core\Validation\Validator;
use Bookshelf\Model\Book;
use Bookshelf\Model\Category;
use Bookshelf\Model\Rating;
use Bookshelf\Model\User;

/**
 * @author Danil Vasiliev <daniil.vasilev@opensoftdev.ru>
 */
class BooksController extends Controller
{
    /**
     * @var string default name for controller
     */
    private $controllerName = 'Books';

    public function defaultAction()
    {
        $orderBy = [
            'category_id' => 'ASC',
            'author' => 'ASC',
            'name' => 'ASC'
        ];

        $searchParameters = [];
        if ($this->request->isPost()) {
            $search = $this->request->get('search');
            $searchParameters = [
                'b.name' => $search,
                'author' => $search,
                'c.name' => $search
            ];
        }
        $bookObject = new Book();
        $books = $bookObject->search($orderBy, $searchParameters);

        $result = [];
        foreach ($books as $book) {
            $categoryName = $book->getCategory()->getName();
            if (!array_key_exists($categoryName, $result)) {
                $result[$categoryName] = array();
            }
            $result[$categoryName][] = $book;
        }

        return $this->render($this->controllerName, 'Default', ['books' => $result]);
    }

    public function addAction()
    {
        $errors = [];
        $book = new Book();

        if ($this->request->isPost()) {
            $errors = $this->fillAndValidate($book);
            if (!$errors) {
                $book->save();
                $this->addSuccessMessage('Книга успешно добавлена!');

                $this->redirectTo('/books');
            }
        }

        return $this->render($this->controllerName, 'Add', [
            'errors' => $errors,
            'categories' =>  Category::findAll(),
            'book' => $book,
            'availableAuthors' => $this->getAvailableAuthors()
        ]);
    }

    public function updateAction()
    {
        $book = Book::find($this->request->get('id'));

        if (!$book) {
            $this->addErrorMessage('Редактируемая книга не найдена!');
            $this->redirectTo('/books');
        }
            $errors = [];
            if ($this->request->isPost()) {
                $errors = $this->fillAndValidate($book);

                if (!$errors) {
                    $book->save();
                    $this->addSuccessMessage('Книга успешно отредактирована!');

                    $this->redirectTo('/books');
                }
            }

            return $this->render($this->controllerName, 'Update', [
                'errors' => $errors,
                'categories' => Category::findAll(),
                'book' => $book,
                'availableAuthors' => $this->getAvailableAuthors()
            ]);
    }

    public function deleteAction()
    {
        $book = Book::find($this->request->get('id'));

        if (!$book) {
            $this->addErrorMessage('Удаляемая книга не найдена!');
        } else {
            $book->delete();
            $this->addSuccessMessage('Книга успешно удалена!');
        }

        $this->redirectTo('/books');
    }

    public function InfoAction()
    {
        $book = Book::find($this->request->get('id'));
        $rating = new Rating();

        if (!$book) {
            $this->addErrorMessage('Книга не найдена!');
            $this->redirectTo('/books');
        }

        $bookId = $book->getId();
        $userId = User::findOneBy(['email'=>$this->session->get('email')])->getId();
        $isRated = false;
        if (Rating::findBy(['book_id' => $bookId, 'user_id' => $userId])) {
            $isRated = true;
        }

        $errors = [];
        if ($this->request->isPost()) {

            $rating->setRating($this->request->get('rating'));
            $ratingCorrect = new ChoiceConstraint($rating, 'rating', $book->ratingValues);
            $validator = new Validator();
            $validator->addConstraint($ratingCorrect);
            $errors = $validator->validate();

            if (!$errors) {
                $book->save($rating);
                $this->redirectTo('/books/info?id=' . $book->getId());
            }
        }

        return $this->templater->show($this->controllerName, 'Info', ['book' => $book, 'errors' => $errors, 'is_rated' =>$isRated]);
    }

    /**
     * @param Book $book
     * @return array
     */
    private function fillAndValidate(Book $book)
    {
        $book->setName($this->request->get('name'));
        $book->setAuthor($this->request->get('author'));
        $book->setCategory($this->request->get('category_id'));
        $book->setDescription($this->request->get('description'));
        $book->setLink($this->request->get('link'));

        return $this->validate($book);
    }

    /**
     * @param Book $book
     * @return array
     */
    private function validate($book)
    {
        $nameNotBlank = new NotBlankConstraint($book, 'name');
        $nameUnique = new UniqueConstraint($book, 'name');
        $authorNotBlank = new NotBlankConstraint($book, 'author');
        $linkCorrect = new LinkConstraint($book, 'link');
        $categoryIsset = new EntityExistsConstraint($book->getCategory(), 'id', 'category');

        $validator = new Validator();
        $validator->addConstraint($nameNotBlank);
        $validator->addConstraint($nameUnique);
        $validator->addConstraint($authorNotBlank);
        $validator->addConstraint($linkCorrect);
        $validator->addConstraint($categoryIsset);

        $errors = $validator->validate();

        return $errors;
    }

    /**
     * @return array
     */
    public static function getAvailableAuthors()
    {
        $books = Book::findAll();
        $availableTags = [];
        foreach ($books as $book) {
            $availableTags[] = $book->getAuthor();
        }

        return $availableTags;
    }
}
