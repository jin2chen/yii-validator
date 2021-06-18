<?php

declare(strict_types=1);

namespace jin2chen\YiiValidator\Rule;

use jin2chen\YiiValidator\NestRuleInterface;
use jin2chen\YiiValidator\Validator;
use Yiisoft\Validator\ResultSet;
use Yiisoft\Validator\Rule;

abstract class NestRule extends Rule implements NestRuleInterface
{
    private ?ResultSet $resultSet = null;
    private ?Validator $validator = null;

    public function getResultSet(): ResultSet
    {
        if (null === $this->resultSet) {
            $this->resultSet = new ResultSet();
        }

        return $this->resultSet;
    }

    public function getValidator(): Validator
    {
        if (null === $this->validator) {
            $this->validator = new Validator();
        }

        return $this->validator;
    }

    public function setValidator(Validator $validator): void
    {
        $this->validator = $validator;
    }

    protected function addResultSet(ResultSet $resultSet, string $prefix): void
    {
        $comResultSet = $this->getResultSet();
        foreach ($resultSet as $attribute => $result) {
            $key = $prefix . '.' . $attribute;
            $comResultSet->addResult($key, $result);
        }
    }
}
