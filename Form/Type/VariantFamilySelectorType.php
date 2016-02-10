<?php

namespace Sidus\EAVVariantBundle\Form\Type;

use Sidus\EAVModelBundle\Configuration\FamilyConfigurationHandler;
use Sidus\EAVModelBundle\Entity\Data;
use Sidus\EAVModelBundle\Exception\MissingFamilyException;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVVariantBundle\Model\VariantAttributeType;
use Sidus\EAVVariantBundle\Model\VariantFamily;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class VariantFamilySelectorType extends AbstractType
{
    /** @var FamilyConfigurationHandler */
    protected $familyConfigurationHandler;

    /**
     * VariantFamilySelectorType constructor.
     * @param FamilyConfigurationHandler $familyConfigurationHandler
     */
    public function __construct(FamilyConfigurationHandler $familyConfigurationHandler)
    {
        $this->familyConfigurationHandler = $familyConfigurationHandler;
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     * @throws UndefinedOptionsException
     * @throws MissingFamilyException
     * @throws \UnexpectedValueException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => true,
            'constraints' => new NotBlank(),
        ]);
        $resolver->setRequired([
            'attribute',
            'parent_data',
        ]);
        $resolver->setNormalizer('attribute', function(Options $options, $value) {
            if (!$value instanceof AttributeInterface) {
                throw new \UnexpectedValueException('"attribute" option must be an AttributeInterface');
            }
            /** @var AttributeInterface $value */
            if (!$value->getType() instanceof VariantAttributeType) {
                throw new \UnexpectedValueException("Attribute's type must be a VariantAttributeType");
            }
            return $value;
        });
        $resolver->setNormalizer('parent_data', function(Options $options, $value) {
            if (!$value instanceof Data) {
                $class = get_class($value);
                throw new \UnexpectedValueException("parent_data option must be an instance of Sidus\\EAVModelBundle\\Entity\\Data, '{$class}' given");
            }
            $attributeCode = $options['attribute']->getCode();
            /** @var Data $value */
            if (!$value->getFamily()->hasAttribute($attributeCode)) {
                throw new \UnexpectedValueException("Attribute '{$attributeCode}' does not belong to data's family");
            }
            return $value;
        });
        $resolver->setNormalizer('choices', function(Options $options, $value) {
            $attribute = $options['attribute'];
            $families = [];
            foreach ($attribute->getOptions()['variant_families'] as $familyCode) {
                $family = $this->familyConfigurationHandler->getFamily($familyCode);
                if (!$family instanceof VariantFamily) {
                    throw new \UnexpectedValueException("Variant families in attribute options must be of type VariantFamily, '{$family->getCode()}' is not a variant");
                }
                $families[ucfirst($family)] = $family;
            }
            return $families;
        });
    }

    public function getParent()
    {
        return 'sidus_family_selector';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sidus_variant_family_selector';
    }
}
