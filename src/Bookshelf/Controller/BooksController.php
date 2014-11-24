<?php
/**
 * This code belongs to of Opensoft company
 */

namespace Bookshelf\Controller;

use Bookshelf\Core\Exception\DbException;
use Bookshelf\Core\Validation\Constraint\EntityExistsConstraint;
use Bookshelf\Core\Validation\Constraint\LinkConstraint;
use Bookshelf\Core\Validation\Constraint\NotBlankConstraint;
use Bookshelf\Core\Validation\Constraint\ChoiceConstraint;
use Bookshelf\Core\Validation\Constraint\UniqueConstraint;
use Bookshelf\Core\Validation\Validator;
use Bookshelf\Model\Book;
use Bookshelf\Model\Category;
use Bookshelf\Model\Rating;

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
                try {
                    $book->save();
                    $this->addSuccessMessage('Книга успешно добавлена!');
                } catch (DbException $e) {
                    $this->logAndDisplayError($e, 'Ошибка добавления книги!');
                }
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
        $book = Book::find($this->request->get('book_id'));
        if (!$book) {
            $this->addErrorMessage('Редактируемая книга не найдена!');
            $this->redirectTo('/books');
        }
            $errors = [];
            if ($this->request->isPost()) {
                $errors = $this->fillAndValidate($book);

                if (!$errors) {
                    try {
                        $book->save();
                        $this->addSuccessMessage('Книга успешно отредактирована!');
                    } catch (DbException $e) {
                        $this->logAndDisplayError($e, 'Ошибка редактирования книги!');
                    }
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
            try {
                $book->delete();
                $this->addSuccessMessage('Книга успешно удалена!');
            } catch (DbException $e) {
                $this->logAndDisplayError($e, 'Ошибка удаления книги!');
            }
        }

        $this->redirectTo('/books');
    }

    public function showAction()
    {
        if (!$this->getCurrentUser()) {
            $this->redirectTo('/login');
        } else {
            $book = Book::find($this->request->get('id'));
            if (!$book) {
                $this->addErrorMessage('Книга не найдена!');
                $this->redirectTo('/books');
            }

            $bookId = $book->getId();
            $isRated = false;
            if (Rating::findBy(['book_id' => $bookId, 'user_id' => $this->getCurrentUser()->getId()])) {
                $isRated = true;
            }

            return $this->templater->show($this->controllerName, 'show',
                [
                    'book' => $book,
                    'errors' => [],
                    'is_rated' => $isRated,
                    'currentUser' => $this->getCurrentUser()
                ]);
        }
    }

    /**
     * @return array
     */
    public function addRatingAction()
    {
        $book = Book::find($this->request->get('id'));

        $rating = new Rating();
        $rating->setRating($this->request->get('rating'));
        $rating->setBookId($book->getId());
        $rating->setUserId($this->getCurrentUser()->getId());

        $ratingCorrect = new ChoiceConstraint($rating, 'rating', $book->ratingValues);
        $validator = new Validator();
        $validator->addConstraint($ratingCorrect);
        $errors = $validator->validate();

        if (!$errors) {
            try {
                $rating->save();
                $this->redirectTo('/books/show?id=' . $book->getId());
            } catch (DbException $e) {
                $this->logAndDisplayError($e, 'Ошибка добавления рейтинга!');
            }
        }

        return $this->templater->show($this->controllerName, 'show', ['book' => $book, 'errors' => $errors]);
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
