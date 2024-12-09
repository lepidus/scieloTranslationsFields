<?php

use PKP\tests\DatabaseTestCase;
use PKP\userGroup\UserGroup;
use APP\submission\Submission;
use APP\publication\Publication;
use APP\author\Author;
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

    private function createTestAuthors()
    {
        $author1 = new Author();
        $author1->setAllData([
            'userGroupId' => 4,
        ]);

        $author2 = new Author();
        $author2->setAllData([
            'userGroupId' => $this->translatorsUserGroup->getId(),
        ]);

        return [$author1, $author2];
    }

    private function createTestSubmission($authors)
    {
        $publication = new Publication();
        $publication->setAllData([
            'id' => 789,
            'authors' => $authors
        ]);

        $submission = new Submission();
        $submission->setAllData([
            'id' => 788,
            'publications' => [$publication],
            'currentPublicationId' => $publication->getId()
        ]);

        return $submission;
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

    public function testValidateSubmissionHasTranslator()
    {
        $fieldsValidator = new FieldsValidator();
        $authors = $this->createTestAuthors();
        $submission = $this->createTestSubmission([$authors[0]]);

        $translatorsUserGroupId = $this->translatorsUserGroup->getId();
        $submissionHasTranslator = $fieldsValidator->validateSubmissionHasTranslator($submission, $translatorsUserGroupId);
        $this->assertFalse($submissionHasTranslator);

        $publication = $submission->getCurrentPublication();
        $publication->setData('authors', $authors);
        $submissionHasTranslator = $fieldsValidator->validateSubmissionHasTranslator($submission, $translatorsUserGroupId);
        $this->assertTrue($submissionHasTranslator);
    }

    public function testValidateTranslatorsHaveOrcid()
    {
        $fieldsValidator = new FieldsValidator();
        $authors = $this->createTestAuthors();
        $submission = $this->createTestSubmission($authors);

        $translatorsUserGroupId = $this->translatorsUserGroup->getId();
        $translatorsHaveOrcid = $fieldsValidator->translatorsHaveOrcid($submission, $translatorsUserGroupId);
        $this->assertFalse($translatorsHaveOrcid);

        $authors[1]->setData('orcid', 'https://orcid.org/0000-0002-1825-0097');
        $publication = $submission->getCurrentPublication();
        $publication->setData('authors', $authors);
        $translatorsHaveOrcid = $fieldsValidator->translatorsHaveOrcid($submission, $translatorsUserGroupId);
        $this->assertTrue($translatorsHaveOrcid);
    }
}
