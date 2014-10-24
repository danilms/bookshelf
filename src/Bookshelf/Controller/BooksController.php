<?php
/**
 * This code belongs to of Opensoft company
 */

namespace Bookshelf\Controller;

use Bookshelf\Core\Db;
use Bookshelf\Core\Request;
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
     * @var array Request
     */
    private $request;

    /**
     * Magic function that create templater class instance
     */
    public function __construct()
    {
        $this->templater = new Templater();
        $this->request = new Request($_GET, $_POST);
    }

    public function defaultAction()
    {
        $orderBy = [
            'category_id' => 'ASC',
            'author' => 'ASC',
            'name' => 'ASC'
        ];

        $search = null;
        if ($this->request->isPost()) {
            $search = $this->request->get('search');
        }
        $searchParameters = [
            'b.name' => $search,
            'author' => $search,
            'c.name' => $search
            ];
        $books = Book::search($orderBy, $searchParameters);

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
