<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class BadWordsValidator extends ConstraintValidator
{
    private const BAD_WORDS = ['mauvais', 'nul', 'idiot', 'bête', 'spam', 'arnaque']; 

    public function validate($value, Constraint $constraint)
    {
        /* @var App\Validator\BadWords $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        foreach (self::BAD_WORDS as $badWord) {
            if (stripos($value, $badWord) !== false) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $badWord)
                    ->addViolation();
                break;
            }
        }
    }
}
