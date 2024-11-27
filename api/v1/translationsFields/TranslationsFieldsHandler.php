<?php

namespace APP\plugins\generic\scieloTranslationsFields\api\v1\translationsFields;

use PKP\handler\APIHandler;
use PKP\security\Role;
use PKP\security\authorization\PolicySet;
use PKP\security\authorization\RoleBasedHandlerOperationPolicy;
use APP\facades\Repo;

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

    public function saveTranslationFields($slimRequest, $response, $args)
    {
        $requestParams = $slimRequest->getParsedBody();
        $submission = $this->getSubmission($slimRequest);

        $originalDocumentDoi = $requestParams['originalDocumentDoi'];
        Repo::submission()->edit($submission, ['isTranslationOfDoi' => $originalDocumentDoi]);

        return $response->withStatus(200);
    }
}
