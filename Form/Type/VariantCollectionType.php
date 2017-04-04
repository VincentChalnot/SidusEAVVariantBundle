<?php

namespace Sidus\EAVVariantBundle\Form\Type;

use Sidus\EAVModelBundle\Registry\AttributeRegistry;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Exception\MissingFamilyException;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVVariantBundle\Model\VariantAttributeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VariantCollectionType extends AbstractType
{
    /** @var FamilyRegistry */
    protected $familyRegistry;

    /** @var AttributeRegistry */
    protected $attributeRegistry;

    /** @var string */
    protected $routes;

    /**
     * @param FamilyRegistry    $familyRegistry
     * @param AttributeRegistry $attributeRegistry
     * @param array                         $routes
     */
    public function __construct(
        FamilyRegistry $familyRegistry,
        AttributeRegistry $attributeRegistry,
        array $routes
    ) {
        $this->familyRegistry = $familyRegistry;
        $this->attributeRegistry = $attributeRegistry;
        $this->routes = $routes;
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     *
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!$form->getData() instanceof DataInterface) {
            throw new \UnexpectedValueException('Form data must be an EAV Data');
        }
        /** @var AttributeInterface $attribute */
        $attribute = $options['attribute'];
        /** @var DataInterface $data */
        $data = $form->getData();
        $view->vars['data'] = $data;

        $variantFamiliesOption = $attribute->getOption('variant_families');
        if (empty($variantFamiliesOption)) {
            throw new \LogicException(
                'Variant attribute must have at least one variant family in the variant_families option'
            );
        }
        $variantFamilies = [];
        foreach ($variantFamiliesOption as $code) {
            $variantFamilies[] = $this->familyRegistry->getFamily($code);
        }
        $view->vars['variant_families'] = $variantFamilies;
        $view->vars['routes'] = $this->routes;
        $view->vars['variants'] = $data->getValuesData($attribute);
        $view->vars['base_route_parameters'] = [
            'attribute' => $attribute->getCode(),
            'parentId' => $data->getId(),
        ];
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws AccessException
     * @throws UndefinedOptionsException
     * @throws MissingFamilyException
     * @throws \UnexpectedValueException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'required' => false,
                'routes' => $this->routes,
            ]
        );
        $resolver->setRequired(
            [
                'attribute',
            ]
        );
        $resolver->setNormalizer(
            'attribute',
            function (Options $options, $value) {
                if ($value instanceof AttributeInterface) {
                    return $value;
                }
                $attribute = $this->attributeRegistry->getAttribute($value);
                if (!$attribute->getType() instanceof VariantAttributeType) {
                    throw new \UnexpectedValueException('Attribute option must be of type VariantAttributeType');
                }

                return $attribute;
            }
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sidus_variant_collection';
    }
}
