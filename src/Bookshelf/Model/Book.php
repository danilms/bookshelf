<?php
/**
 * This code belongs to of Opensoft company
 */

namespace Bookshelf\Model;

use PDO;
use ReflectionObject;
use Bookshelf\Core\Db;

class Book extends ActiveRecord
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $rating;

    /**
     * @var string
     */
    private $link;

    /**
     * @var string
     */
    private $author;

    /**
     * @var string
     */
    private $category;

    /**
     * @var array
     */
    private $users = array();

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return Book
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param array $users
     * @return Book
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     * @return Book
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Book
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Book
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return Book
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Book
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return integer
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param integer $rating
     * @return Book
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'books';
    }

    /**
     * @param array $orderBy
     * @param array $searchParameters
     * @return Book[]
     */
    public function search(array $orderBy = [], array $searchParameters = [])
    {
        $db = Db::getInstance();
        $tableBooks = $this->getTableName();
        $tableCategories = Category::getTableName();

        //parse search
        $searchValues = [];
        $searchCondition = '';

        $searchConditions = [];
        foreach ($searchParameters as $key => $value) {
            if ($value === null) {
                continue;
            }
            $searchValues[] = "%$value%";
            $searchConditions[] = "$key LIKE ? ";
        }
        if ($searchConditions) {
            $searchCondition = ' WHERE ' . implode(' OR ', $searchConditions);
        }

        //parse order by
        $orderCondition = '';
        $optionKeys = array_keys($orderBy);
        $orderConditions = [];
        foreach ($optionKeys as $key) {
            $sortOrder = strtoupper($orderBy[$key]);
            if (!in_array($sortOrder, ['ASC', 'DESC'])) {
                $sortOrder = 'ASC';
            }
            $orderConditions[] = sprintf('%s %s', $key, $sortOrder);
        }
        if ($orderConditions) {
            $orderCondition = ' ORDER BY ' . implode(', ', $orderConditions);
        }

        $sql = "SELECT
                    b.id, b.category_id, b.name, b.description, b.rating, b.link,
                    b.owner_id, b.author, c.name as category_name
                FROM $tableBooks as b
                    JOIN $tableCategories as c ON (c.id = b.category_id)
                    $searchCondition
                    $orderCondition";

        $db->execute($sql, $searchValues);
        $resultArray = $db->getStatement()->fetchAll(PDO::FETCH_ASSOC);

        $books = array();
        foreach ($resultArray as $result) {
            $book = New Book();
            $book->setState($result);
            $books[] = $book;
        }

        return $books;
    }

    /**
     * Return all property for user
     *
     * @return array
     */
    protected function getState()
    {
        return ['id' => $this->id, 'name' => $this->name, 'category_id' => $this->category, 'description' => $this->description,
            'rating' => $this->rating, 'link' => $this->link, 'author' => $this->author];
    }

    /**
     * Set value in user instance class from array
     * @param $array
     * @return mixed
     */

    protected function setState($array)
    {
        $this->name = $array['name'];
        $this->description = $array['description'];
        $this->rating = $array['rating'];
        $this->link = $array['link'];
        $this->author = $array['author'];
        $this->id = $array['id'];

        $category = New Category();
        $this->category = $category->find($array['category_id']);
    }

}

