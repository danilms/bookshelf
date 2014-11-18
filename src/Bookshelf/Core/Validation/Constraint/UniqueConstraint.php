<?php
/**
 * This code belongs to of Opensoft company
 */

namespace Bookshelf\Core\Validation\Constraint;

use Bookshelf\Model\ActiveRecord;

class UniqueConstraint implements ConstraintInterface
{
    /**
     * @var ActiveRecord
     */
    private $model;

    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var string
     */
    private $message = 'Это название занято';

    /**
     * @param ActiveRecord $model
     * @param string $propertyName
     * @param string $message
     */
    public function __construct(ActiveRecord $model, $propertyName, $message = null)
    {
        $this->model = $model;
        $this->propertyName = $propertyName;
        if ($message) {
            $this->message = $message;
        }
    }

    /**
     * @param array $errors
     */
    public function validate(array &$errors)
    {
        $getter = 'get' . ucfirst($this->propertyName);
        $value = $this->model->$getter();
        $resultModel = $this->model->findOneBy([$this->propertyName => $value]);

        if ($resultModel) {
            if (!$this->model->getId() || ($this->model->getId() && $this->model->getId() != $resultModel->getId())) {
                $errors[$this->propertyName][] = $this->message;
                $errors['unique'] = $resultModel;
            }

        }
    }
}
