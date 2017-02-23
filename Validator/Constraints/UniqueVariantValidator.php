<?php

namespace Sidus\EAVVariantBundle\Validator\Constraints;

use Exception;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVVariantBundle\Model\VariantFamily;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @property ExecutionContextInterface $context
 */
class UniqueVariantValidator extends ConstraintValidator
{
    /** @var string */
    protected $dataClass;

    /**
     * UniqueVariantValidator constructor.
     *
     * @param string $dataClass
     */
    public function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param DataInterface $data The value that should be validated
     * @param Constraint    $constraint The constraint for the validation
     *
     * @return ConstraintViolationListInterface
     * @throws Exception
     */
    public function validate($data, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueVariant) {
            throw new \UnexpectedValueException(
                "Can't validate this type of constraint, please provide a UniqueVariant constraint"
            );
        }
        if (!$data instanceof $this->dataClass) {
            $class = get_class($data);
            throw new \UnexpectedValueException("Can't validate data of class {$class}");
        }
        $family = $data->getFamily();
        if (!$family instanceof VariantFamily) {
            return;
        }
        $currentCombination = [];
        foreach ($family->getAxles() as $attribute) {
            if ($attribute->isMultiple()) {
                throw new \LogicException(
                    "Family axle '{$attribute->getCode()}' is multiple, multiple axles support is not implemented yet"
                );
            }
            $currentCombination[$attribute->getCode()] = $data->getValueData($attribute);
        }

        /** @var DataInterface $variantData */
        foreach ($constraint->parentData->getValuesData($constraint->attribute) as $variantData) {
            if ($variantData->getFamilyCode() !== $family->getCode()) {
                continue;
            }
            if ($variantData->getId() === $data->getId()) {
                continue;
            }
            $variantDataCombination = [];
            foreach ($family->getAxles() as $attribute) {
                $variantDataCombination[$attribute->getCode()] = $variantData->getValueData($attribute);
            }
            if ($this->compareCombinations($currentCombination, $variantDataCombination)) {
                $this->context->buildViolation('sidus_eav_variant.errors.invalid_axle_combination')
                    ->addViolation();
            }
        }
    }

    /**
     * @param array $currentCombination
     * @param array $variantDataCombination
     *
     * @return bool
     */
    protected function compareCombinations(array $currentCombination, array $variantDataCombination)
    {
        foreach ($currentCombination as $code => $value) {
            // If at least one value is different, we're good
            if ($variantDataCombination[$code] !== $value) {
                return false;
            }
        }

        return true;
    }
}
