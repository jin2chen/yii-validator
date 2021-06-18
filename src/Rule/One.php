<?php

declare(strict_types=1);

namespace jin2chen\YiiValidator\Rule;

use InvalidArgumentException;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\ValidationContext;

final class One extends NestRule
{
    public static function rule(): One
    {
        return new One();
    }

    protected function validateValue($value, ValidationContext $context = null): Result
    {
        if (!$value instanceof DataSetInterface) {
            throw new InvalidArgumentException();
        }

        if (!$context) {
            throw new InvalidArgumentException();
        }

        $results = $this->getValidator()->validate($value);
        $this->addResultSet($results, $context->getAttribute() ?? '');

        return new Result();
    }
}
