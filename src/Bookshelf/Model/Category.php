<?php
/**
 * This code belongs to of Opensoft company
 */

namespace Bookshelf\Model;

use Bookshelf\Core\Db;
use ReflectionObject;

/**
 * @author Danil Vasiliev <danil.vasiliev@opensoftdev.ru>
 */
class Category extends ActiveRecord
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
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'categories';
    }

    /**
     * Return all property for user
     *
     * @return array
     */
    protected function getState()
    {
        return ['id' => $this->id, 'name' => $this->name];
    }

    /**
     * Set value in user instance class from array
     * @param $array
     * @return mixed
     */

    protected function setState($array)
    {
        $this->name = $array['name'];
        $this->id = $array['id'];
    }

}
