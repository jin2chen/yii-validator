<?php

declare(strict_types=1);

namespace jin2chen\YiiValidator\Rule;

use InvalidArgumentException;
use jin2chen\YiiValidator\NestRule;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\ValidationContext;

/**
 * @method One withRules(iterable $rules)
 */
final class One extends NestRule
{
    public static function rule(): One
    {
        return new One();
    }

    protected function validateValue($value, ValidationContext $context = null): Result
    {
        if (!$context) {
            throw new InvalidArgumentException('Context must be set.');
        }

        $results = $this->getValidator()->validate($value, $this->getRules());
        $this->addResultSet($results, $context->getAttribute() ?? '');

        return new Result();
    }
}
