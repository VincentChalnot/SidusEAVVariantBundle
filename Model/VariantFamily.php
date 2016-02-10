<?php

namespace Sidus\EAVVariantBundle\Model;

use Sidus\EAVModelBundle\Configuration\AttributeConfigurationHandler;
use Sidus\EAVModelBundle\Configuration\FamilyConfigurationHandler;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\Family;
use UnexpectedValueException;

class VariantFamily extends Family
{
    /** @var array */
    protected $axles;

    /**
     * @inheritdoc
     */
    public function __construct(
        $code,
        AttributeConfigurationHandler $attributeConfigurationHandler,
        FamilyConfigurationHandler $familyConfigurationHandler,
        array $config
    ) {
        foreach ($config['axles'] as $axle) {
            $this->axles[$axle] = $attributeConfigurationHandler->getAttribute($axle);
        }
        unset($config['axles']);
        parent::__construct($code, $attributeConfigurationHandler, $familyConfigurationHandler, $config);
    }

    /**
     * @return AttributeInterface[]
     */
    public function getAxles()
    {
        return $this->axles;
    }

    /**
     * @param $code
     * @return AttributeInterface
     * @throws UnexpectedValueException
     */
    public function getAxle($code)
    {
        if (!$this->hasAxle($code)) {
            throw new UnexpectedValueException("Unknown axle {$code} in family {$this->code}");
        }
        return $this->axles[$code];
    }

    /**
     * @param $code
     * @return bool
     */
    public function hasAxle($code)
    {
        return !empty($this->axles[$code]);
    }

    /**
     * @param array $axles
     */
    public function setAxles(array $axles)
    {
        $this->axles = $axles;
    }

    /**
     * @inheritdoc
     */
    public function getAttribute($code)
    {
        if ($this->hasAxle($code)) {
            return $this->getAxle($code);
        }
        return parent::getAttribute($code);
    }

    /**
     * @inheritdoc
     */
    public function hasAttribute($code)
    {
        if ($this->hasAxle($code)) {
            return true;
        }
        return parent::hasAttribute($code);
    }
}