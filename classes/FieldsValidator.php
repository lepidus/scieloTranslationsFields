<?php

namespace APP\plugins\generic\scieloTranslationsFields\classes;

use PKP\userGroup\UserGroup;
use APP\facades\Repo;

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
}
