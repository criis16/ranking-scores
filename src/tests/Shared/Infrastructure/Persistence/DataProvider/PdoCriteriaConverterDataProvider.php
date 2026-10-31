<?php

namespace Tests\Shared\Infrastructure\Persistence\DataProvider;

final class PdoCriteriaConverterDataProvider
{
    /**
     * @return iterable<string, array{string, string}>
     * [operador del filtro, condición SQL esperada sobre la columna score]
     */
    public static function operators(): iterable
    {
        yield 'equal' => ['=', 'score = :p0'];
        yield 'not equal' => ['!=', 'score != :p0'];
        yield 'greater than' => ['>', 'score > :p0'];
        yield 'less than' => ['<', 'score < :p0'];
        yield 'contains' => ['CONTAINS', "score LIKE '%' || :p0 || '%'"];
        yield 'not contains' => ['NOT_CONTAINS', "NOT (score LIKE '%' || :p0 || '%')"];
    }
}
