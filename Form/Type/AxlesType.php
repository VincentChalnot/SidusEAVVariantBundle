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

class AxlesType extends AbstractType
{
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
            'label' => false,
            'inherit_data' => true,
            'read_only' => null,
        ]);
        $resolver->setNormalizer('read_only', function(Options $options, $value) {
            if (null === $value) {
                return $options['disabled'];
            }
            return $value;
        });
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sidus_axles';
    }
}
