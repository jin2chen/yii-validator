<?php

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace jin2chen\YiiValidator\Tests;

use jin2chen\YiiValidator\Rule\Many;
use jin2chen\YiiValidator\Rule\One;
use jin2chen\YiiValidator\Validator;
use PHPUnit\Framework\TestCase;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\Rule;
use Yiisoft\Validator\RulesProviderInterface;

class ValidatorTest extends TestCase
{
    public function testValidate()
    {
        $form = new UserForm();
        $validator = new Validator();

        $form->profile = new Profile();
        $form->profile->website = '//www.jinchen.me';

        $address1 = new Address();
        $address2 = new Address();
        $address2->state = 'ABC';
        $addresses = [
            $address1,
            $address2,
        ];
        $form->addresses = $addresses;

        $results = $validator->validate($form);
        $this->assertFalse($results->isValid());
    }
}

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
            Rule\MatchRegularExpression::rule('/^[A-Z]{2}$/'),
        ];
    }

    private function zipcodeRules(): array
    {
        return [
            Rule\MatchRegularExpression::rule('/\d{6}/')->skipOnEmpty(true),
        ];
    }
}
