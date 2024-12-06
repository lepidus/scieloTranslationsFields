<?php

use PKP\tests\DatabaseTestCase;
use PKP\userGroup\UserGroup;
use PKP\security\Role;
use APP\facades\Repo;
use APP\plugins\generic\scieloTranslationsFields\classes\FieldsValidator;

class FieldsValidatorTest extends DatabaseTestCase
{
    private $translatorsUserGroup;
    private $contextId = 1;
    private $locale = 'en';

    public function setUp(): void
    {
        $this->translatorsUserGroup = $this->createTranslatorsUserGroup();
    }

    protected function tearDown(): void
    {
        Repo::userGroup()->delete($this->translatorsUserGroup);
        parent::tearDown();
    }

    private function createTranslatorsUserGroup()
    {
        $translatorsUserGroup = new UserGroup();
        $translatorsUserGroup->setAllData([
            'contextId' => $this->contextId,
            'roleId' => Role::ROLE_ID_AUTHOR,
            'isDefault' => true,
            'showTitle' => false,
            'permitSelfRegistration' => false,
            'permitMetadataEdit' => true,
            'abbrev' => [
                $this->locale => 'TR'
            ]
        ]);

        $translatorsUserGroupId = Repo::userGroup()->add($translatorsUserGroup);
        $translatorsUserGroup->setId($translatorsUserGroupId);

        return $translatorsUserGroup;
    }

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

    public function testGetTranslatorsUserGroup()
    {
        $fieldsValidator = new FieldsValidator();
        $retrievedUserGroup = $fieldsValidator->getTranslatorsUserGroup($this->contextId);

        $this->assertEquals($this->translatorsUserGroup->getId(), $retrievedUserGroup->getId());
    }
}
