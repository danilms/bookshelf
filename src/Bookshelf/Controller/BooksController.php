<?php
/**
 * This code belongs to of Opensoft company
 */

namespace Bookshelf\Controller;

use Bookshelf\Core\Templater;
use Bookshelf\Model\Book;

/**
 * @author Danil Vasiliev <daniil.vasilev@opensoftdev.ru>
 */
class BooksController
{

    /**
     * @var string default name for controller
     */
    private $controllerName = 'Books';

    /**
     * @var var for Templater class instance
     */
    private $templater;

    /**
     * Magic function that create templater class instance
     */
    public function __construct()
    {
        $this->templater = new Templater();
    }

    public function defaultAction()
    {

        $orderBy = [
            'category_id' => 'ASC',
            'author' => 'ASC',
            'name' => 'ASC'
        ];

        if (!$_POST['search']) {
            $books = Book::search($orderBy);
        } else {
            $searchParameters = [
                'b.name' => $_POST['search'],
                'author' => $_POST['search'],
                'c.name' => $_POST['search']
            ];
            $books = Book::search($orderBy, $searchParameters);
        }

        $result = [];
        foreach ($books as $book) {
            $categoryName = $book->getCategory()->getName();
            if (!array_key_exists($categoryName, $result)) {
                $result[$categoryName] = array();
            }
            $result[$categoryName][] = $book;
        }

        return $this->templater->show($this->controllerName, 'Default', $result);
    }

}
