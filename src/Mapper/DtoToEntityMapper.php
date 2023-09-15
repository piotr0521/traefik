<?php

declare(strict_types=1);

namespace Groshy\Mapper;

use AutoMapperPlus\AutoMapperInterface;
use AutoMapperPlus\CustomMapper\CustomMapper;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Money;
use Money\Parser\DecimalMoneyParser;
use stdClass;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\Type;

class DtoToEntityMapper extends CustomMapper
{
    private ReflectionExtractor $extractor;

    public function __construct(
        private readonly AutoMapperInterface $mapper,
    ) {
        $this->extractor = new ReflectionExtractor();
    }

    public function mapToObject($source, $destination)
    {
        $tempData = $this->mapper->map($source, stdClass::class);
        if (method_exists($destination, 'getData')) {
            $tempData = $this->replaceTypes($tempData, $source, $destination->getData());
            $this->mapper->mapToObject($tempData, $destination->getData());
            $destination->setData(clone $destination->getData());
        }
        $tempData = $this->replaceTypes($tempData, $source, $destination);
        $this->mapper->mapToObject($tempData, $destination);

        return $destination;
    }

    // Hack to find and replace scalar values by enums or money objects if destination requires them
    private function replaceTypes($tempData, $source, $destination): mixed
    {
        $destinationClass = get_class($destination);
        $fields = array_intersect(array_keys(get_object_vars($tempData)), $this->extractor->getProperties($destinationClass));
        foreach ($fields as $field) {
            $enum = $this->getEnum($destinationClass, $field);
            if (!is_null($enum) && is_scalar($tempData->{$field})) {
                $tempData->{$field} = call_user_func([$enum, 'from'], $tempData->{$field});
                continue;
            }
            if ($this->isMoney($destinationClass, $field) && is_scalar($tempData->{$field})) {
                $tempData->{$field} = (new DecimalMoneyParser(new ISOCurrencies()))->parse(strval($tempData->{$field}), new Currency('USD'));
                continue;
            }
            $destType = $this->extractor->getTypes($destinationClass, $field)[0];
            if ($this->needRecursiveMapping(get_class($source), $field, $destinationClass, $field)) {
                $tempData->{$field} = $this->map($tempData->{$field}, $destType->getClassName());
            }
        }

        return $tempData;
    }

    private function getEnum(string $class, string $property): ?string
    {
        foreach ($this->extractor->getTypes($class, $property) as $type) {
            if (Type::BUILTIN_TYPE_OBJECT != $type->getBuiltinType()) {
                continue;
            }
            if (enum_exists($type->getClassName())) {
                return $type->getClassName();
            }
        }

        return null;
    }

    private function isMoney(string $class, string $property): bool
    {
        foreach ($this->extractor->getTypes($class, $property) as $type) {
            if (Money::class == $type->getClassName()) {
                return true;
            }
        }

        return false;
    }

    private function needRecursiveMapping(string $sourceClass, string $sourceField, string $destClass, string $destField): bool
    {
        $destTypes = $this->extractor->getTypes($destClass, $destField);
        foreach ($destTypes as $destType) {
            if ('object' != $destType->getBuiltinType()) {
                return false;
            }
        }
        $sourceTypes = $this->extractor->getTypes($sourceClass, $sourceField);
        foreach ($sourceTypes as $sourceType) {
            if ('object' != $sourceType->getBuiltinType()) {
                return false;
            }
        }
        if (!strpos(strtolower($sourceField), 'dto') || !strpos(strtolower($destField), 'dto')) {
            return false;
        }

        $destTypes = array_map(fn (Type $el) => $el->getClassName(), $destTypes);
        $sourceTypes = array_map(fn (Type $el) => $el->getClassName(), $sourceTypes);

        return 0 == count(array_filter(array_intersect($destTypes, $sourceTypes), fn ($el) => !is_null($el)));
    }
}
