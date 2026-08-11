<?php

namespace App\GraphQL\Type;

use DateTimeImmutable;
use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class DateTimeType extends ScalarType implements AliasedInterface
{
    public function serialize($value): string
    {
        return $value->format("Y-m-d H:i:s");
    }

    public function parseValue($value): DateTimeImmutable
    {
        return new DateTimeImmutable($value);
    }

    public function parseLiteral(Node $valueNode, ?array $variables = null): DateTimeImmutable
    {
        if (!property_exists($valueNode, 'value')) {
            throw new Error('no value on ValueNode');
        }

        return new DateTimeImmutable($valueNode->value);
    }

    public static function getAliases(): array
    {
        return ['DateTimeImmutable'];
    }
}