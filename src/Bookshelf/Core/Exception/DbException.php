<?php
/**
 * This code belongs to of Opensoft company
 */

namespace Bookshelf\Core\Exception;

use Bookshelf\Core\Logger\Logger;
use Exception;
use RuntimeException;

class DbException extends RuntimeException
{
    public static function executionFailed()
    {
        return new self('Something goes wrong');
    }

    public static function insertFailed($tableName, $keys, $values, Exception $previous)
    {
        return new self(
            sprintf('Failed to insert new row to %s. Field names are %s, values - %s', $tableName, $keys, $values),
            null,
            $previous
        );
    }

    public static function deleteFailed($tableName, $values, Exception $previous)
    {
        return new self(
            sprintf('Failed to delete row to %s. Values - %s', $tableName, $values),
            null,
            $previous
        );
    }

    public static function updateFailed($tableName, $values, Exception $previous)
    {
        return new self(
            sprintf('Failed to update row to %s. Values - %s', $tableName, $values),
            null,
            $previous
        );
    }
}
