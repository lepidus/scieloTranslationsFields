<?php

namespace APP\plugins\generic\scieloTranslationsFields\classes;

use PKP\userGroup\UserGroup;
use APP\submission\Submission;
use APP\author\Author;
use PKP\user\User;
use APP\facades\Repo;
use APP\plugins\generic\scieloTranslationsFields\classes\TranslationsFieldsDAO;

class FieldsValidator
{
    public function validateDoi(string $doi): bool
    {
        return (preg_match('/^10\.\d{4,9}\/[-._;()\/:A-Z0-9]+$/i', $doi) === 1);
    }

    public function getTranslatorsUserGroup(int $contextId): ?UserGroup
    {
        $contextUserGroups = Repo::userGroup()->getCollector()
            ->filterByContextIds([$contextId])
            ->getMany();

        foreach ($contextUserGroups as $userGroup) {
            $userGroupAbbrev = strtolower($userGroup->getData('abbrev', 'en'));

            if ($userGroupAbbrev === 'tr') {
                return $userGroup;
            }
        }

        return null;
    }

    public function submissionHasTranslator(Submission $submission, int $translatorsUserGroupId): bool
    {
        $publication = $submission->getCurrentPublication();
        $authors = $publication->getData('authors');

        foreach ($authors as $author) {
            $authorUserGroupId = $author->getData('userGroupId');

            if ($authorUserGroupId == $translatorsUserGroupId) {
                return true;
            }
        }

        return false;
    }

    public function translatorsHaveOrcid(Submission $submission, int $translatorsUserGroupId): bool
    {
        $publication = $submission->getCurrentPublication();
        $authors = $publication->getData('authors');

        foreach ($authors as $author) {
            $authorUserGroupId = $author->getData('userGroupId');

            if ($authorUserGroupId == $translatorsUserGroupId) {
                $authorOrcid = $author->getData('orcid');

                if (empty($authorOrcid)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getSubmitterUser(int $submissionId): ?User
    {
        $translationsFieldsDao = new TranslationsFieldsDAO();
        $submitterId = $translationsFieldsDao->getSubmitterId($submissionId);

        if (is_null($submitterId)) {
            return null;
        }

        return Repo::user()->get($submitterId);
    }

    public function getContributorForUser(Submission $submission, User $user): ?Author
    {
        $publication = $submission->getCurrentPublication();
        $userEmail = $user->getData('email');

        foreach ($publication->getData('authors') as $author) {
            if ($author->getData('email') == $userEmail) {
                return $author;
            }
        }

        return null;
    }
}
