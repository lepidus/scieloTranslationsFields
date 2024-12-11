<?php

use PKP\tests\DatabaseTestCase;
use PKP\userGroup\UserGroup;
use APP\submission\Submission;
use APP\publication\Publication;
use PKP\user\User;
use APP\author\Author;
use PKP\security\Role;
use APP\facades\Repo;
use APP\plugins\generic\scieloTranslationsFields\classes\FieldsValidator;

class FieldsValidatorTest extends DatabaseTestCase
{
    private $fieldsValidator;
    private $translatorsUserGroup;
    private $contextId = 1;
    private $locale = 'en';

    public function setUp(): void
    {
        $this->fieldsValidator = new FieldsValidator();
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
            'email' => 'julian.casablancas@outlook.com'
        ]);

        $author2 = new Author();
        $author2->setAllData([
            'userGroupId' => $this->translatorsUserGroup->getId(),
            'email' => 'albert.hammond@outlook.com'
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
        $this->assertTrue($this->fieldsValidator->validateDoi('10.1000/xyz123'));
        $this->assertTrue($this->fieldsValidator->validateDoi('10.1038/nphys1170'));
        $this->assertTrue($this->fieldsValidator->validateDoi('10.1109/5.771073'));
    }

    public function testValidateInvalidDois()
    {
        $this->assertFalse($this->fieldsValidator->validateDoi('10.1234'));
        $this->assertFalse($this->fieldsValidator->validateDoi('10.1038/abc!123'));
        $this->assertFalse($this->fieldsValidator->validateDoi('10./1000/abcd'));
    }

    public function testGetTranslatorsUserGroup()
    {
        $retrievedUserGroup = $this->fieldsValidator->getTranslatorsUserGroup($this->contextId);

        $this->assertEquals($this->translatorsUserGroup->getId(), $retrievedUserGroup->getId());
    }

    public function testValidateSubmissionHasTranslator()
    {
        $authors = $this->createTestAuthors();
        $submission = $this->createTestSubmission([$authors[0]]);

        $translatorsUserGroupId = $this->translatorsUserGroup->getId();
        $submissionHasTranslator = $this->fieldsValidator->submissionHasTranslator($submission, $translatorsUserGroupId);
        $this->assertFalse($submissionHasTranslator);

        $publication = $submission->getCurrentPublication();
        $publication->setData('authors', $authors);
        $submissionHasTranslator = $this->fieldsValidator->submissionHasTranslator($submission, $translatorsUserGroupId);
        $this->assertTrue($submissionHasTranslator);
    }

    public function testValidateTranslatorsHaveOrcid()
    {
        $authors = $this->createTestAuthors();
        $submission = $this->createTestSubmission($authors);

        $translatorsUserGroupId = $this->translatorsUserGroup->getId();
        $translatorsHaveOrcid = $this->fieldsValidator->translatorsHaveOrcid($submission, $translatorsUserGroupId);
        $this->assertFalse($translatorsHaveOrcid);

        $authors[1]->setData('orcid', 'https://orcid.org/0000-0002-1825-0097');
        $publication = $submission->getCurrentPublication();
        $publication->setData('authors', $authors);
        $translatorsHaveOrcid = $this->fieldsValidator->translatorsHaveOrcid($submission, $translatorsUserGroupId);
        $this->assertTrue($translatorsHaveOrcid);
    }

    public function testGetContributorForUser()
    {
        $authors = $this->createTestAuthors();
        $submission = $this->createTestSubmission($authors);

        $user = new User();
        $user->setData('email', 'julian.casablancas@outlook.com');

        $expectedAuthor = $authors[0];
        $this->assertEquals($expectedAuthor, $this->fieldsValidator->getContributorForUser($submission, $user));

        $user->setData('email', 'another.person@outlook.com');
        $this->assertNull($this->fieldsValidator->getContributorForUser($submission, $user));
    }
}
