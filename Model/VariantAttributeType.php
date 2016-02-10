<?php

namespace Sidus\EAVVariantBundle\Model;

use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\AttributeType;

class VariantAttributeType extends AttributeType
{
    /**
     * @param AttributeInterface $attribute
     * @throws \Exception
     */
    public function setAttributeDefaults(AttributeInterface $attribute)
    {
        $attribute->setMultiple(true);
        $attribute->setCollection(false);
        $attribute->addFormOption('attribute', $attribute);
        $attribute->addFormOption('inherit_data', true);
    }
}
