<?php
/**
 * This code belongs to of Opensoft company
 */

namespace Bookshelf\Model;


class Rating extends ActiveRecord
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $rating;

    /**'
     * @var integer
     */
    private $bookId;

    /**'
     * @var integer
     */
    private $userId;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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
     * @return Rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return integer
     */
    public function getBookId()
    {
        return $this->bookId;
    }

    /**
     * @param integer $bookId
     * @return Rating
     */
    public function setBookId($bookId)
    {
        $this->bookId = $bookId;

        return $this;
    }

    /**
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param integer $userId
     * @return Rating
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'ratings';
    }

    /**
     * @return array
     */
    protected function toArray()
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'book_id' => $this->bookId,
            'user_id' => $this->userId
        ];
    }

    /**
     * Method that set value in property for class instance
     *
     * @param $array
     */
    protected function initStateFromArray($array)
    {
        $this->rating = $array['rating'];
        $this->id = $array['id'];
        $this->bookId = $array['book_id'];
        $this->userId = $array['user_id'];
    }
}
