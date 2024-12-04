<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\scieloTranslationsFields\classes\DoiValidator;

class DoiValidatorTest extends TestCase
{
    public function testValidateValidDoi()
    {
        $doiValidator = new DoiValidator();

        $this->assertTrue($doiValidator->validate('10.1000/xyz123'));
        $this->assertTrue($doiValidator->validate('10.1038/nphys1170'));
        $this->assertTrue($doiValidator->validate('10.1109/5.771073'));
    }

    public function testValidateInvalidDoi()
    {
        $doiValidator = new DoiValidator();

        $this->assertFalse($doiValidator->validate('10.1234'));
        $this->assertFalse($doiValidator->validate('10.1038/abc!123'));
        $this->assertFalse($doiValidator->validate('10./1000/abcd'));
    }
}
