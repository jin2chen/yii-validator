<?php

declare(strict_types=1);

namespace jin2chen\YiiValidator;

use Closure;
use Yiisoft\Validator\DataSet\ArrayDataSet;
use Yiisoft\Validator\DataSet\ScalarDataSet;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\FormatterInterface;
use Yiisoft\Validator\PostValidationHookInterface;
use Yiisoft\Validator\ResultSet;
use Yiisoft\Validator\RuleInterface;
use Yiisoft\Validator\Rules;
use Yiisoft\Validator\RulesProviderInterface;
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Validator\ValidatorInterface;

use function is_array;
use function is_object;

/**
 * Validator validates {@link DataSetInterface} against rules set for data set attributes.
 *
 * @psalm-type AggregateRule = iterable<string, iterable<array-key, RuleInterface|callable>>
 */
final class Validator implements ValidatorInterface
{
    private bool $isSubValidator = false;
    private ?FormatterInterface $formatter;

    public function __construct(?FormatterInterface $formatter = null)
    {
        $this->formatter = $formatter;
    }

    /**
     * @inheritdoc
     */
    public function validate($data, $rules = []): ResultSet
    {
        $data = $this->normalizeDataSet($data);

        if ($data instanceof RulesProviderInterface) {
            $rules = $data->getRules();
        }

        $context = new ValidationContext($data);
        $results = new ResultSet();

        foreach ($rules as $attribute => $attributeRules) {
            $ruleSet = new Rules($this->addValidatorForNestRule($attributeRules));
            if ($this->formatter !== null) {
                $ruleSet = $ruleSet->withFormatter($this->formatter);
            }
            $results->addResult(
                $attribute,
                $ruleSet->validate(
                    $data->getAttributeValue($attribute),
                    $context->withAttribute($attribute)
                )
            );

            $this->addNestRuleErrors($ruleSet, $results);
        }

        if (!$this->isSubValidator && $data instanceof PostValidationHookInterface) {
            $data->processValidationResult($results);
        }

        return $results;
    }

    /**
     * @param iterable<array-key, RuleInterface|callable> $rules
     * @return list<RuleInterface|callable>
     */
    private function addValidatorForNestRule(iterable $rules): array
    {
        $results = [];
        foreach ($rules as $rule) {
            if ($rule instanceof NestRuleInterface) {
                $results[] = $rule->setValidator($this->withSubValidator());
            } else {
                $results[] = $rule;
            }
        }

        return $results;
    }

    /**
     * @psalm-suppress InaccessibleProperty
     * @param Rules $ruleSet
     * @param ResultSet $resultSet
     */
    private function addNestRuleErrors(Rules $ruleSet, ResultSet $resultSet): void
    {
        /** @psalm-var callable(): RuleInterface[] $getRules */
        $getRules = Closure::bind(function () {
            /** @var Rules $this */
            return $this->rules;
        }, $ruleSet, Rules::class);
        $rules = $getRules();

        foreach ($rules as $rule) {
            if (!$rule instanceof NestRuleInterface) {
                continue;
            }

            foreach ($rule->getResultSet() as $attribute => $result) {
                $resultSet->addResult($attribute, $result);
            }
        }
    }

    public function withFormatter(?FormatterInterface $formatter): self
    {
        $new = clone $this;
        $new->formatter = $formatter;
        return $new;
    }

    private function withSubValidator(): self
    {
        $new = clone $this;
        $new->isSubValidator = true;
        return $new;
    }

    /**
     * @param mixed $data
     * @return DataSetInterface
     */
    private function normalizeDataSet($data): DataSetInterface
    {
        if ($data instanceof DataSetInterface) {
            return $data;
        }

        if (is_object($data) || is_array($data)) {
            return new ArrayDataSet((array)$data);
        }

        return new ScalarDataSet($data);
    }
}
