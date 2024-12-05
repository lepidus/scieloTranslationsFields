<?php

namespace APP\plugins\generic\scieloTranslationsFields\api\v1\translationsFields;

use PKP\handler\APIHandler;
use PKP\security\Role;
use PKP\security\authorization\PolicySet;
use PKP\security\authorization\RoleBasedHandlerOperationPolicy;
use PKP\db\DAORegistry;
use APP\facades\Repo;
use APP\plugins\generic\scieloTranslationsFields\classes\clients\DoiClient;
use APP\plugins\generic\scieloTranslationsFields\classes\DoiValidator;

class TranslationsFieldsHandler extends APIHandler
{
    public function __construct()
    {
        $this->_handlerPath = 'translationsFields';
        $roles = [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_AUTHOR];
        $this->_endpoints = [
            'PUT' => [
                [
                    'pattern' => $this->getEndpointPattern() . '/saveTranslationFields',
                    'handler' => [$this, 'saveTranslationFields'],
                    'roles' => $roles
                ],
            ],
        ];
        parent::__construct();
    }

    public function authorize($request, &$args, $roleAssignments)
    {
        $rolePolicy = new PolicySet(PolicySet::COMBINING_PERMIT_OVERRIDES);

        foreach ($roleAssignments as $role => $operations) {
            $rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
        }
        $this->addPolicy($rolePolicy);

        return parent::authorize($request, $args, $roleAssignments);
    }

    private function getSubmission($slimRequest)
    {
        $queryParams = $slimRequest->getQueryParams();
        $submissionId = (int) $queryParams['submissionId'];

        return Repo::submission()->get($submissionId);
    }

    private function validateOriginalDoi($originalDocumentDoi)
    {
        $doiValidator = new DoiValidator();
        return $doiValidator->validate($originalDocumentDoi);
    }

    public function saveTranslationFields($slimRequest, $response, $args)
    {
        $requestParams = $slimRequest->getParsedBody();
        $placedOn = $slimRequest->getQueryParams()['placedOn'];
        $submission = $this->getSubmission($slimRequest);
        $publication = $submission->getCurrentPublication();

        $originalDocumentDoi = $requestParams['originalDocumentDoi'];
        $originalDocumentCitation = null;

        if (!empty($originalDocumentDoi)) {
            if ($this->validateOriginalDoi($originalDocumentDoi)) {
                $doiClient = new DoiClient();
                $originalDocumentCitation = $doiClient->getApaCitation($originalDocumentDoi);
            } elseif ($placedOn == 'workflow') {
                return $response->withStatus(400)->withJson([
                    'originalDocumentDoi' => [__('plugins.generic.scieloTranslationsFields.error.originalDocumentDoi.invalidDoi')]
                ]);
            }
        }

        Repo::publication()->edit($publication, [
            'originalDocumentDoi' => $originalDocumentDoi,
            'originalDocumentCitation' => $originalDocumentCitation
        ]);

        $submission = Repo::submission()->get($submission->getId());
        $publication = $submission->getCurrentPublication();

        $contextId = $submission->getData('contextId');
        $userGroups = Repo::userGroup()->getCollector()
            ->filterByContextIds([$contextId])
            ->getMany();

        $genreDao = DAORegistry::getDAO('GenreDAO');
        $genres = $genreDao->getByContextId($contextId)->toArray();

        return $response->withJson(
            Repo::publication()->getSchemaMap($submission, $userGroups, $genres)->map($publication),
            200
        );
    }
}
