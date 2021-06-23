<?php

declare(strict_types=1);

namespace jin2chen\YiiValidator;

use Yiisoft\Validator\ResultSet;
use Yiisoft\Validator\RuleInterface;

interface NestRuleInterface extends RuleInterface
{
    public function getResultSet(): ResultSet;
    public function getValidator(): Validator;
    public function setValidator(Validator $validator): NestRuleInterface;
}
