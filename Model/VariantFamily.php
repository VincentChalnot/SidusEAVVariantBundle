<?php

namespace Sidus\EAVVariantBundle\Model;

use Sidus\EAVModelBundle\Registry\AttributeRegistry;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Sidus\EAVModelBundle\Context\ContextManager;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\Family;
use UnexpectedValueException;

class VariantFamily extends Family
{
    /** @var array */
    protected $axles;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        $code,
        AttributeRegistry $attributeRegistry,
        FamilyRegistry $familyRegistry,
        ContextManager $contextManager,
        array $config
    ) {
        foreach ($config['axles'] as $axle) {
            $this->axles[$axle] = $attributeRegistry->getAttribute($axle);
        }
        unset($config['axles']);
        parent::__construct(
            $code,
            $attributeRegistry,
            $familyRegistry,
            $contextManager,
            $config
        );
    }

    /**
     * @return AttributeInterface[]
     */
    public function getAxles()
    {
        return $this->axles;
    }

    /**
     * @param string $code
     *
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
     * @param string $code
     *
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
     * {@inheritdoc}
     */
    public function getAttribute($code)
    {
        if ($this->hasAxle($code)) {
            return $this->getAxle($code);
        }

        return parent::getAttribute($code);
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute($code)
    {
        if ($this->hasAxle($code)) {
            return true;
        }

        return parent::hasAttribute($code);
    }
}
