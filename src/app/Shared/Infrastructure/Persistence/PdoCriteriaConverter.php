<?php

namespace App\Shared\Infrastructure\Persistence;

use App\Shared\Domain\Criteria\Criteria;
use App\Shared\Domain\Criteria\Filter;
use App\Shared\Domain\Criteria\FilterOperator;

final class PdoCriteriaConverter
{
    /**
     * @param array<string, string> $fieldMap Campo del criteria => columna real.
     *                                        Campo fuera del mapa → excepción.
     * @return array{where: string, order: string, limit: string, params: array<string, string>}
     */
    public static function convert(Criteria $criteria, array $fieldMap): array
    {
        [$where, $params] = self::buildWhere($criteria, $fieldMap);

        return [
            'where' => $where,
            'order' => self::buildOrder($criteria, $fieldMap),
            'limit' => self::buildLimit($criteria),
            'params' => $params,
        ];
    }

    private static function buildWhere(Criteria $criteria, array $fieldMap): array
    {
        if (!$criteria->hasFilters()) {
            return ['', []];
        }

        $conditions = [];
        $params = [];

        foreach ($criteria->plainFilters() as $index => $filter) {
            $column = self::resolveColumn($filter, $fieldMap);
            $param = ':p' . $index;
            $params[$param] = $filter->value()->value();

            $conditions[] = match ($filter->operator()) {
                FilterOperator::EQUAL => sprintf('%s = %s', $column, $param),
                FilterOperator::NOT_EQUAL => sprintf('%s != %s', $column, $param),
                FilterOperator::GT => sprintf('%s > %s', $column, $param),
                FilterOperator::LT => sprintf('%s < %s', $column, $param),
                FilterOperator::CONTAINS => sprintf("%s LIKE '%%' || %s || '%%'", $column, $param),
                FilterOperator::NOT_CONTAINS => sprintf("NOT (%s LIKE '%%' || %s || '%%')", $column, $param),
            };
        }

        // Todos los filtros combinados con AND (igual que el CompositeExpression TYPE_AND de la referencia)
        return [sprintf('WHERE %s', implode(' AND ', $conditions)), $params];
    }

    private static function buildOrder(Criteria $criteria, array $fieldMap): string
    {
        if (!$criteria->hasOrder()) {
            return '';
        }

        $orderBy = $criteria->order();

        return sprintf(
            'ORDER BY %s %s',
            self::resolveColumnByField($orderBy->orderBy()->value(), $fieldMap),
            strtoupper($orderBy->orderType()->value)   // asc|desc (NONE nunca llega aquí)
        );
    }

    private static function buildLimit(Criteria $criteria): string
    {
        $parts = [];

        // LIMIT siempre que haya limit u offset (LIMIT -1 no vale en Postgres,
        // OFFSET sin LIMIT no vale en SQLite → usamos un entero grande portable)
        if ($criteria->limit() !== null || $criteria->offset() !== null) {
            $parts[] = 'LIMIT ' . (int)($criteria->limit() ?? PHP_INT_MAX);
        }
        if ($criteria->offset() !== null) {
            $parts[] = 'OFFSET ' . (int)$criteria->offset();
        }

        return implode(' ', $parts);
    }

    private static function resolveColumn(Filter $filter, array $fieldMap): string
    {
        return self::resolveColumnByField($filter->field()->value(), $fieldMap);
    }

    private static function resolveColumnByField(string $field, array $fieldMap): string
    {
        if (!array_key_exists($field, $fieldMap)) {
            throw new \InvalidArgumentException(sprintf('Field <%s> is not searchable.', $field));
        }

        return $fieldMap[$field];
    }
}
