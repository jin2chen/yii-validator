# Yii Nest Validator

[![Latest Stable Version](https://poser.pugx.org/jin2chen/yii-validator/v)](https://packagist.org/packages/jin2chen/yii-validator)
[![Total Downloads](https://poser.pugx.org/jin2chen/yii-validator/downloads)](https://packagist.org/packages/jin2chen/yii-validator)
[![License](https://poser.pugx.org/jin2chen/yii-validator/license)](https://packagist.org/packages/jin2chen/yii-validator)
[![Build status](https://github.com/jin2chen/yii-validator/workflows/build/badge.svg)](https://github.com/jin2chen/yii-validator/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jin2chen/yii-validator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jin2chen/yii-validator/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jin2chen/yii-validator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jin2chen/yii-validator/?branch=master)
[![static analysis](https://github.com/jin2chen/yii-validator/workflows/static%20analysis/badge.svg)](https://github.com/jin2chen/yii-validator/actions?query=workflow%3A%22static+analysis%22)

Validator for nest validation.

## Requirements

- PHP 7.4 or higher.

## Installation

The package could be installed with composer:

```shell
composer require jin2chen/yii-validator --prefer-dist
```

## General usage
### 1. Use `DataSetInterface` and `RulesProviderInterface`
```php
use jin2chen\YiiValidator\Rule\Many;
use jin2chen\YiiValidator\Rule\One;
use jin2chen\YiiValidator\Validator;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\Rule;
use Yiisoft\Validator\RulesProviderInterface;

abstract class Model implements DataSetInterface, RulesProviderInterface
{
    public function getAttributeValue(string $attribute)
    {
        return $this->{$attribute};
    }

    public function hasAttribute(string $attribute): bool
    {
        return property_exists($this, $attribute);
    }
}

/**
 * @internal
 */
final class UserForm extends Model
{
    /**
     * @var string
     */
    public $firstname;
    /**
     * @var string
     */
    public $lastname;
    /**
     * @var string
     */
    public $email;
    /**
     * @var Profile
     */
    public $profile;
    /**
     * @var Address[]
     */
    public $addresses;

    public function getRules(): array
    {
        return [
            'firstname' => $this->firstnameRules(),
            'lastname' => $this->lastnameRules(),
            'email' => $this->emailRules(),
            'profile' => $this->profileRules(),
            'addresses' => $this->addressesRules(),
        ];
    }

    private function firstnameRules(): array
    {
        return [
            Rule\Required::rule(),
            Rule\MatchRegularExpression::rule('/[a-z]+\s*[a-z]+/i'),
        ];
    }

    private function lastnameRules(): array
    {
        return [
            Rule\Required::rule(),
            Rule\MatchRegularExpression::rule('/[a-z]+\s*[a-z]+/i'),
        ];
    }

    private function emailRules(): array
    {
        return [
            Rule\Required::rule(),
            Rule\Email::rule(),
        ];
    }

    private function profileRules(): array
    {
        return [
            Rule\Required::rule(),
            One::rule(),
        ];
    }

    private function addressesRules(): array
    {
        return [
            Many::rule()->skipOnEmpty(true),
        ];
    }
}

final class Profile extends Model
{
    /**
     * @var string
     */
    public $website;
    /**
     * @var string
     */
    public $title;

    public function getRules(): array
    {
        return [
            'website' => $this->websiteRules(),
            'title' => $this->titleRules(),
        ];
    }

    private function websiteRules(): array
    {
        return [
            Rule\Url::rule()->skipOnEmpty(true),
        ];
    }

    private function titleRules(): array
    {
        return [
            Rule\InRange::rule(['CEO', 'COO', 'CFO'])->skipOnEmpty(true),
        ];
    }
}

final class Address extends Model
{
    /**
     * @var string
     */
    public $street;
    /**
     * @var string
     */
    public $city;
    /**
     * @var string
     */
    public $state;
    /**
     * @var string
     */
    public $zipcode;

    public function getRules(): array
    {
        return [
            'street' => $this->streetRules(),
            'city' => $this->cityRules(),
            'state' => $this->stateRules(),
            'zipcode' => $this->zipcodeRules(),
        ];
    }

    private function streetRules(): array
    {
        return [
            Rule\Required::rule(),
        ];
    }

    private function cityRules(): array
    {
        return [
            Rule\Required::rule(),
        ];
    }

    private function stateRules(): array
    {
        return [
            Rule\Required::rule(),
            Rule\MatchRegularExpression::rule('/^[A-Z]{2}$/')
        ];
    }

    private function zipcodeRules(): array
    {
        return [
            Rule\MatchRegularExpression::rule('/\d{6}/')->skipOnEmpty(true),
        ];
    }
}

$form = new UserForm();
$validator = new Validator();

$form->profile = new Profile();
$form->profile->website = 'www.jinchen.me';

$address1 = new Address();
$address2 = new Address();
$address2->state = 'ABC';
$addresses = [
    $address1,
    $address2,
];
$form->addresses = $addresses;

$results = $validator->validate($form);
print_r($results->getErrors());

//[
//    'firstname' => ['Value cannot be blank.'],
//    'lastname' => ['Value cannot be blank.'],
//    'email' => ['Value cannot be blank.'],
//    'profile.website' => ['This value is not a valid URL.'],
//    'addresses.0.street' => ['Value cannot be blank.'],
//    'addresses.0.city' => ['Value cannot be blank.'],
//    'addresses.0.state' => ['Value cannot be blank.'],
//    'addresses.1.street' => ['Value cannot be blank.'],
//    'addresses.1.city' => ['Value cannot be blank.'],
//    'addresses.1.state' => ['Value is invalid.'],
//];
```

### 2. Use array data
```php
use jin2chen\YiiValidator\Rule\Many;
use jin2chen\YiiValidator\Rule\One;
use jin2chen\YiiValidator\Validator;
use Yiisoft\Validator\Rule;

$data = [
    'email' => 'abc.com',
    'profile' => [
        'website' => 'www.jinchen.me',
    ],
    'addresses' => [
        [
            'id' => 22,
            'state' => 'AAA',
        ],
        [
            'id' => 32,
            'state' => 'BBB',
        ],
    ],
];

$rules = [
    'email' => [
        Rule\Email::rule(),
    ],
    'profile' => [
        One::rule()->withRules(
            [
                'website' => [
                    Rule\Url::rule(),
                ],
            ]
        ),
    ],
    'addresses' => [
        Many::rule()->withRules(
            [
                'state' => [
                    Rule\MatchRegularExpression::rule('/^[A-Z]{2}$/'),
                ],
            ]
        )->withIndexKey('id'),
    ],
];

$validator = new Validator();
$results = $validator->validate($data, $rules);
print_r($results->getErrors());
//[
//    'email' => ['This value is not a valid email address.'],
//    'profile.website' => ['This value is not a valid URL.'],
//    'addresses.22.state' => ['Value is invalid.'],
//    'addresses.32.state' => ['Value is invalid.'],
//]
```

## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```
