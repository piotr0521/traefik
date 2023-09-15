<?php

declare(strict_types=1);

namespace Groshy\Application\Manager;

use Groshy\Entity\PositionValue;
use Groshy\Model\PositionDate;
use Talav\Component\Resource\Manager\ResourceManager;
use Talav\Component\Resource\Model\ResourceInterface;

class PositionValueManager extends ResourceManager
{
    public function updatePositionValue(PositionDate $positionDate): void
    {
        /** @var PositionValue $positionValue */
        $positionValue = $this->getRepository()->findOneBy(['valueDate' => $positionDate->date, 'position' => $positionDate->position]);
        // no transactions but existing position value means that transaction date has been updated and
        // // value is not correct anymore, need to remove it
        if (!$positionDate->hasData() && !is_null($positionValue)) {
            $this->remove($positionValue);

            return;
        }
        // we have some transactions so can calculate position value
        if (is_null($positionValue)) {
            $positionValue = $this->create();
            $positionValue->setValueDate($positionDate->date);
            $positionValue->setPosition($positionDate->position);
            $this->add($positionValue);
        }
        // update balance transaction provides the most accurate information
        if ($positionDate->hasValue()) {
            $positionValue->setValue($positionDate->getValue()->amount);
            $positionValue->setQuantity($positionDate->getValue()->quantity);

            return;
        }
        // non balance change transactions only provide the difference between previous value and new value
        // try to get previous value
        /** @var PositionValue $previous */
        $previous = $this->getRepository()->getLastBeforeDateForPosition($positionDate->date, $positionDate->position);
        if (!is_null($previous)) {
            $positionValue->setValue($previous->getValue()->add($positionDate->getAmountChange()));
            if (!is_null($previous->getQuantity()) && !is_null($positionDate->getQuantityChange())) {
                $positionValue->setQuantity($previous->getQuantity() + $positionDate->getQuantityChange());
            }
        } else {
            $positionValue->setValue($positionDate->getAmountChange());
            $positionValue->setQuantity($positionDate->getQuantityChange() > 0.0 ? $positionDate->getQuantityChange() : null);
        }
    }

    /**
     * @param PositionValue $resource
     */
    public function remove(ResourceInterface $resource): void
    {
        if ($resource->isLastPositionValue()) {
            $resource->getPosition()->removeLastValue();
        }
        $resource->getPositionEvent()->removeValue();
        parent::remove($resource);
    }
}
