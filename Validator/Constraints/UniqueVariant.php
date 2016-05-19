<?php

namespace Sidus\EAVVariantBundle\Validator\Constraints;

use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Symfony\Component\Validator\Constraint;

class UniqueVariant extends Constraint
{
    /** @var DataInterface */
    public $parentData;

    /** @var AttributeInterface */
    public $attribute;

    /**
     * @return array
     */
    public function getRequiredOptions()
    {
        return ['parentData', 'attribute'];
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'sidus_unique_variant';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
