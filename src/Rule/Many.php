<?php

declare(strict_types=1);

namespace jin2chen\YiiValidator\Rule;

use InvalidArgumentException;
use jin2chen\YiiValidator\NestRule;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\ValidationContext;

use function is_array;
use function is_scalar;

/**
 * @method Many withRules(iterable $rules)
 */
final class Many extends NestRule
{
    private ?string $indexKey = null;

    public static function rule(): Many
    {
        return new Many();
    }

    public function withIndexKey(string $key): self
    {
        $new = clone $this;
        $new->indexKey = $key;
        return $new;
    }

    /**
     * @param mixed $data
     * @param string $default
     * @return string
     */
    private function getIndexKey($data, string $default): string
    {
        if ($this->indexKey === null) {
            return $default;
        }

        if (is_array($data)) {
            /** @var scalar|object|array $result */
            $result = $data[$this->indexKey] ?? $default;
            return is_scalar($result) ? (string)$result : $default;
        }

        if ($data instanceof DataSetInterface) {
            /** @var scalar|object|array $result */
            $result = $data->getAttributeValue($this->indexKey);
            return is_scalar($result) ? (string)$result : $default;
        }

        return $default;
    }

    protected function validateValue($value, ValidationContext $context = null): Result
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('Value must be an array.');
        }

        if (!$context) {
            throw new InvalidArgumentException('Context must be set.');
        }

        $attribute = $context->getAttribute() ?: '';
        /** @var DataSetInterface|array $item */
        foreach ($value as $index => $item) {
            $results = $this->getValidator()->validate($item, $this->getRules());
            $this->addResultSet($results, $attribute . '.' . $this->getIndexKey($item, (string)$index));
        }

        return new Result();
    }
}
