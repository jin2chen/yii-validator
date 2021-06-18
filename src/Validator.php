<?php

declare(strict_types=1);

namespace jin2chen\YiiValidator;

use InvalidArgumentException;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\FormatterInterface;
use Yiisoft\Validator\PostValidationHookInterface;
use Yiisoft\Validator\ResultSet;
use Yiisoft\Validator\Rule;
use Yiisoft\Validator\Rules;
use Yiisoft\Validator\RulesProviderInterface;
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Validator\ValidatorInterface;

final class Validator implements ValidatorInterface
{
    private bool $isSubValidator = false;
    private ?FormatterInterface $formatter;

    public function __construct(?FormatterInterface $formatter = null)
    {
        $this->formatter = $formatter;
    }

    /**
     * @param mixed $data
     * @param iterable<string, Rule[]> $rules
     *
     * @return ResultSet
     */
    public function validate($data, $rules = []): ResultSet
    {
        if (!$data instanceof DataSetInterface) {
            throw new InvalidArgumentException();
        }

        if ($data instanceof RulesProviderInterface) {
            $rules = $data->getRules();
        }

        $context = new ValidationContext($data);
        $results = new ResultSet();

        foreach ($rules as $attribute => $attributeRules) {
            $nestRule = $this->extractNestRule($attributeRules);
            if ($nestRule) {
                $nestRule->setValidator($this->withSubValidator());
            }

            $aggregateRule = new Rules($attributeRules);
            if ($this->formatter !== null) {
                $aggregateRule = $aggregateRule->withFormatter($this->formatter);
            }
            $results->addResult(
                $attribute,
                $aggregateRule->validate($data->getAttributeValue($attribute), $context->withAttribute($attribute))
            );

            if ($nestRule) {
                $this->addNestRuleErrors($nestRule, $results);
            }
        }
        if (!$this->isSubValidator && $data instanceof PostValidationHookInterface) {
            $data->processValidationResult($results);
        }
        return $results;
    }

    /**
     * @param Rule[] $rules
     * @return NestRuleInterface|null
     */
    private function extractNestRule(iterable $rules): ?NestRuleInterface
    {
        foreach ($rules as $rule) {
            if ($rule instanceof NestRuleInterface) {
                return $rule;
            }
        }

        return null;
    }

    private function addNestRuleErrors(NestRuleInterface $rule, ResultSet $resultSet): void
    {
        foreach ($rule->getResultSet() as $attribute => $result) {
            $resultSet->addResult($attribute, $result);
        }
    }

    public function withFormatter(?FormatterInterface $formatter): self
    {
        $new = clone $this;
        $new->formatter = $formatter;
        return $new;
    }

    public function withSubValidator(): self
    {
        $new = clone $this;
        $new->isSubValidator = true;
        return $new;
    }
}
