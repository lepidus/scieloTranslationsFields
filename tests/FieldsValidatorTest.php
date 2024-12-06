<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\scieloTranslationsFields\classes\FieldsValidator;

class FieldsValidatorTest extends TestCase
{
    public function testValidateValidDois()
    {
        $fieldsValidator = new FieldsValidator();

        $this->assertTrue($fieldsValidator->validateDoi('10.1000/xyz123'));
        $this->assertTrue($fieldsValidator->validateDoi('10.1038/nphys1170'));
        $this->assertTrue($fieldsValidator->validateDoi('10.1109/5.771073'));
    }

    public function testValidateInvalidDois()
    {
        $fieldsValidator = new FieldsValidator();

        $this->assertFalse($fieldsValidator->validateDoi('10.1234'));
        $this->assertFalse($fieldsValidator->validateDoi('10.1038/abc!123'));
        $this->assertFalse($fieldsValidator->validateDoi('10./1000/abcd'));
    }
}
