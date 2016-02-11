<?php

namespace Sidus\EAVVariantBundle\Form\Type;

use Sidus\EAVModelBundle\Configuration\FamilyConfigurationHandler;
use Sidus\EAVModelBundle\Entity\Data;
use Sidus\EAVModelBundle\Exception\MissingFamilyException;
use Sidus\EAVModelBundle\Form\Type\DataType;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVVariantBundle\Model\VariantAttributeType;
use Sidus\EAVVariantBundle\Model\VariantFamily;
use Sidus\EAVVariantBundle\Validator\Constraints\UniqueVariant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class VariantType extends DataType
{
    /**
     * @param FormInterface $form
     * @param array $options
     * @throws \Exception
     */
    public function buildCreateForm(FormInterface $form, array $options)
    {
        throw new \LogicException('Variant cannot be created without a family');
    }

    /**
     * @param FormInterface $form
     * @param FamilyInterface $family
     * @param Data $data
     * @param array $options
     * @throws \Exception
     */
    public function buildValuesForm(FormInterface $form, FamilyInterface $family, Data $data = null, array $options = [])
    {
        $form->add('axles', 'sidus_axles', [
            'disabled' => $data->getId() ? true : false,
        ]);
        /** @var VariantFamily $family */
        foreach ($family->getAxles() as $attribute) {
            $this->addAttribute($form->get('axles'), $attribute, $family, $data, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
        }
        parent::buildValuesForm($form, $family, $data, $options);
    }

    /**
     * @param OptionsResolver $resolver
     * @throws \Exception
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
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
        $resolver->setNormalizer('constraints', function(Options $options, $constraints) {
            $constraints[] = new UniqueVariant([
                'attribute' => $options['attribute'],
                'parentData' => $options['parent_data'],
            ]);
            return $constraints;
        });
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sidus_variant';
    }
}