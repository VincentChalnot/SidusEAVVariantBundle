<?php

namespace Sidus\EAVVariantBundle\Validator\Constraints;

use Sidus\EAVModelBundle\Entity\Data;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Symfony\Component\Validator\Constraint;

class UniqueVariant extends Constraint
{
    /** @var Data */
    public $parentData;

    /** @var AttributeInterface */
    public $attribute;

    /**
     * @inheritdoc
     */
    public function getRequiredOptions()
    {
        return ['parentData', 'attribute'];
    }

    public function validatedBy()
    {
        return 'sidus_unique_variant';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
