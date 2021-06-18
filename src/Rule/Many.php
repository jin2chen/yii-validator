<?php

declare(strict_types=1);

namespace jin2chen\YiiValidator\Rule;

use InvalidArgumentException;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\ValidationContext;

final class Many extends NestRule
{
    public static function rule(): Many
    {
        return new Many();
    }

    protected function validateValue($value, ValidationContext $context = null): Result
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException();
        }

        if (!$context) {
            throw new InvalidArgumentException();
        }

        $attribute = $context->getAttribute() ?: '';

        /**
         * @var mixed $index
         * @var DataSetInterface $item
         */
        foreach ($value as $index => $item) {
            $results = $this->getValidator()->validate($item);
            $this->addResultSet($results, $attribute . '.' . $index);
        }

        return new Result();
    }
}
