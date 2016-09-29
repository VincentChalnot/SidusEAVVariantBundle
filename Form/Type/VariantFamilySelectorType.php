<?php

namespace Sidus\EAVVariantBundle\Form\Type;

use Sidus\EAVModelBundle\Configuration\FamilyConfigurationHandler;
use Sidus\EAVModelBundle\Exception\MissingFamilyException;
use Sidus\EAVModelBundle\Form\Type\FamilySelectorType;
use Sidus\EAVVariantBundle\Model\VariantFamily;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class VariantFamilySelectorType extends AbstractType
{
    /** @var FamilyConfigurationHandler */
    protected $familyConfigurationHandler;

    /**
     * VariantFamilySelectorType constructor.
     *
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
     * @throws ConstraintDefinitionException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
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
        $resolver->setNormalizer('attribute', function (Options $options, $value) {
            return VariantType::normalizeVariantAttribute($value);
        });
        $resolver->setNormalizer('parent_data', function (Options $options, $value) {
            return VariantType::normalizeParentData($options, $value);
        });
        $resolver->setNormalizer('choices', function (Options $options, $value) {
            $attribute = $options['attribute'];
            $families = [];
            /** @var array $variantFamilies */
            $variantFamilies = $attribute->getOptions()['variant_families'];
            foreach ($variantFamilies as $familyCode) {
                $family = $this->familyConfigurationHandler->getFamily($familyCode);
                if (!$family instanceof VariantFamily) {
                    throw new \UnexpectedValueException("Variant families in attribute options must be of type VariantFamily, '{$family->getCode()}' is not a variant");
                }
                $families[ucfirst($family)] = $family;
            }

            return $families;
        });
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return FamilySelectorType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sidus_variant_family_selector';
    }
}
