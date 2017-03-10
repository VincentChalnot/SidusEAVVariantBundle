<?php

namespace Sidus\EAVVariantBundle\Form\Type;

use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Form\Type\DataType;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVVariantBundle\Model\VariantAttributeType;
use Sidus\EAVVariantBundle\Model\VariantFamily;
use Sidus\EAVVariantBundle\Validator\Constraints\UniqueVariant;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use UnexpectedValueException;

/**
 * Adding axis selection on default DataType
 */
class VariantType extends DataType
{
    /**
     * @param AttributeInterface $value
     *
     * @return AttributeInterface
     * @throws UnexpectedValueException
     */
    public static function normalizeVariantAttribute($value)
    {
        if (!$value instanceof AttributeInterface) {
            throw new UnexpectedValueException('"attribute" option must be an AttributeInterface');
        }
        /** @var AttributeInterface $value */
        if (!$value->getType() instanceof VariantAttributeType) {
            throw new UnexpectedValueException("Attribute's type must be a VariantAttributeType");
        }

        return $value;
    }

    /**
     * @param Options       $options
     * @param DataInterface $value
     *
     * @return DataInterface
     * @throws UnexpectedValueException
     */
    public static function normalizeParentData(Options $options, $value)
    {
        if (!$value instanceof DataInterface) {
            $class = get_class($value);
            throw new UnexpectedValueException(
                "parent_data option must be an instance of Sidus\\EAVModelBundle\\Entity\\Data, '{$class}' given"
            );
        }
        $attributeCode = $options['attribute']->getCode();
        /** @var DataInterface $value */
        if (!$value->getFamily()->hasAttribute($attributeCode)) {
            throw new UnexpectedValueException("Attribute '{$attributeCode}' does not belong to data's family");
        }

        return $value;
    }

    /**
     * @param FormInterface $form
     * @param array         $options
     *
     * @throws \Exception
     */
    public function buildCreateForm(FormInterface $form, array $options)
    {
        throw new \LogicException('Variant cannot be created without a family');
    }

    /**
     * @param FormInterface   $form
     * @param FamilyInterface $family
     * @param DataInterface   $data
     * @param array           $options
     *
     * @throws \Exception
     */
    public function buildValuesForm(
        FormInterface $form,
        FamilyInterface $family,
        DataInterface $data = null,
        array $options = []
    ) {
        if ($family instanceof VariantFamily) {
            $form->add(
                'axles',
                AxlesType::class,
                [
                    'disabled' => $data->getId() ? true : false,
                ]
            );
            $axles = $form->get('axles');
            foreach ($family->getAxles() as $attribute) {
                $this->addAttribute(
                    $axles,
                    $attribute,
                    $data,
                    [
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]
                );
            }
        }
        parent::buildValuesForm($form, $family, $data, $options);
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Exception
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(
            [
                'attribute',
                'parent_data',
            ]
        );
        $resolver->setNormalizer(
            'attribute',
            function (Options $options, $value) {
                return self::normalizeVariantAttribute($value);
            }
        );
        $resolver->setNormalizer(
            'parent_data',
            function (Options $options, $value) {
                return self::normalizeParentData($options, $value);
            }
        );
        $resolver->setNormalizer(
            'constraints',
            function (Options $options, $constraints) {
                $constraints[] = new UniqueVariant(
                    [
                        'attribute' => $options['attribute'],
                        'parentData' => $options['parent_data'],
                    ]
                );

                return $constraints;
            }
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sidus_variant';
    }
}
