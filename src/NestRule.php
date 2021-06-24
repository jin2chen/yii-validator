<?php

declare(strict_types=1);

namespace jin2chen\YiiValidator;

use Yiisoft\Validator\ResultSet;
use Yiisoft\Validator\Rule;
use Yiisoft\Validator\RuleInterface;

/**
 * @psalm-import-type AggregateRule from Validator
 */
abstract class NestRule extends Rule implements NestRuleInterface
{
    private ?ResultSet $resultSet = null;
    private ?Validator $validator = null;
    /**
     * @var RuleInterface[][]
     * @psalm-var AggregateRule
     */
    private iterable $rules = [];

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

    public function setValidator(Validator $validator): NestRuleInterface
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * @return Rule[][]
     * @psalm-return AggregateRule
     */
    public function getRules(): iterable
    {
        return $this->rules;
    }

    /**
     * @param RuleInterface[][] $rules
     * @psalm-param AggregateRule $rules
     * @return NestRuleInterface
     */
    public function withRules(iterable $rules): NestRuleInterface
    {
        $new = clone $this;
        $new->rules = $rules;
        return $new;
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
