<?php
/**
 * This code belongs to of Opensoft company
 */

namespace Bookshelf\Model;

use Bookshelf\Core\Session;
use PDO;
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
     * @var float
     */
    private $rating;

    /**
     * @var integer
     */
    private $newRating;

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
     * @var array
     */
    public $ratingValues = [1, 2, 3, 4, 5];

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
        $this->category = Category::find($category);

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
     * @return float
     */
    public function getRating()
    {
        return number_format($this->rating, 2);
    }

    /**
     * @param float $rating
     * @return Book
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return integer
     */
    public function getNewRating()
    {
        return $this->newRating;
    }

    /**
     * @param integer $newRating
     * @return Book
     */
    public function setNewRating($newRating)
    {
        $this->newRating = $newRating;

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
     * Method that insert data in database if $id empty, if $id not empty will update data
     *
     * @param Rating $ratingObject
     * @return bool
     */
    public function save($ratingObject = null)
    {
        try {
            $instanceState = $this->toArray();
            if (empty($instanceState['id'])) {
                unset($instanceState['id']);
                Db::getInstance()->insert($this->getTableName(), $instanceState);
            } else {
                $this->updateBook($this->getTableName(), $instanceState, ['id' => $instanceState['id']], $ratingObject);
            }

            return true;
        } catch (DbException $e) {
            return false;
        }
    }

    /**
     * @param string $tableName
     * @param array $newValues
     * @param array $conditions
     * @param Rating $ratingObject
     */
    public function updateBook($tableName, array $newValues, array $conditions = [], $ratingObject = null)
    {
        if ($ratingObject->getRating()) {
            $ratingObject->setBookId($newValues['id']);
            $session = new Session();
            $ratingObject->setUserId(User::findOneBy(['email'=>$session->get('email')])->getId());

            $ratingObject->save();
        } else {
            Db::getInstance()->update($tableName, $newValues, $conditions);
        }
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
        $category = new Category();
        $tableCategories = $category->getTableName();

        list($searchCondition, $searchValues) = $this->combineSearchCondition($searchParameters);
        $orderCondition = $this->combineOrderByCondition($orderBy);

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
            $book = new Book();
            $book->initStateFromArray($result);
            $book->category = Category::find($result['category_id']);
            $books[] = $book;
        }

        return $books;
    }



    /**
     * @param array $searchParameters
     * @return array
     */
    private function combineSearchCondition($searchParameters)
    {
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

        return array($searchCondition, $searchValues);
    }

    /**
     * @param array $orderBy
     * @return string
     */
    private function combineOrderByCondition($orderBy)
    {
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

        return $orderCondition;
    }

    /**
     * Function that return array with all property value for contact with $id
     *
     * @return array
     */
    protected function toArray()
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category->getId(),
            'name' => $this->name,
            'description' => $this->description,
            'link' => $this->link,
            'author' => $this->author
        ];
    }

    /**
     * Method that set value in property for class instance
     *
     * @param $array
     * @return mixed|void
     */
    protected function initStateFromArray($array)
    {
        $this->name = $array['name'];
        $this->description = $array['description'];
        $this->rating = $array['rating'];
        $this->link = $array['link'];
        $this->author = $array['author'];
        $this->id = $array['id'];
        $this->category = Category::find($array['category_id']);
    }
}
