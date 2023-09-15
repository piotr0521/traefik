<?php

declare(strict_types=1);

namespace Groshy\Domain\Enum;

enum PropertyType: string
{
    case SINGLE_FAMILY = 'Single Family';
    case MULTI_FAMILY = 'Multifamily';
    case OFFICE = 'Office';
    case INDUSTRIAL = 'Industrial';
    case RETAIL = 'Retail';
    case HOSPITALITY = 'Hospitality';
    case LAND = 'Land';
    case STORAGE = 'Storage';
    case MEDICAL = 'Medical';
    case CAR_WASH = 'Car Wash';
    case ATM = 'ATM';
    case MOBILE_HOME_PAK = 'Mobile Home Park';
    case MIXED_USE = 'Mixed Use';
    case SENIOR_HOUSING = 'Senior Housing';
    case STUDENT_HOUSING = 'Student Housing';
    case DATA_CENTER = 'Data Center';
    case PARKING = 'Parking';
    case OTHER = 'Other';

    public static function choices(): array
    {
        return array_map(static fn (PropertyType $privacy): string => $privacy->value, PropertyType::cases());
    }
}
