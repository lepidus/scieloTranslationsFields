<?php

/**
 * @file plugins/generic/scieloTranslationsFields/ScieloTranslationsFieldsPlugin.php
 *
 * Copyright (c) 2024 Lepidus Tecnologia
 * Copyright (c) 2024 SciELO
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt.
 *
 * @class ScieloTranslationsFieldsPlugin
 * @ingroup plugins_generic_scieloTranslationsFields
 *
 */

namespace APP\plugins\generic\scieloTranslationsFields;

use PKP\plugins\Hook;
use PKP\plugins\GenericPlugin;
use APP\core\Application;
use APP\pages\submission\SubmissionHandler;
use APP\plugins\generic\scieloTranslationsFields\classes\components\forms\TranslationDataForm;

class ScieloTranslationsFieldsPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        if (Application::isUnderMaintenance()) {
            return true;
        }

        if ($success && $this->getEnabled($mainContextId)) {
            Hook::add('TemplateManager::display', [$this, 'modifySubmissionWizardSteps']);
            Hook::add('Template::SubmissionWizard::Section::Review', [$this, 'addFieldsToReviewStep']);
            Hook::add('Submission::validateSubmit', [$this, 'validateSubmissionFields']);
            Hook::add('Template::Workflow', [$this, 'removeRelationsFromWorkflow']);
            Hook::add('TemplateManager::display', [$this, 'addResourcesToWorkflow']);
            Hook::add('Template::Workflow::Publication', [$this, 'addTranslationDataTabToWorkflow']);
            Hook::add('Schema::get::publication', [$this, 'addOurFieldsToPublicationSchema']);
        }

        return $success;
    }

    public function getDisplayName()
    {
        return __('plugins.generic.scieloTranslationsFields.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.scieloTranslationsFields.description');
    }

    public function addOurFieldsToPublicationSchema($hookName, $params)
    {
        $schema = &$params[0];

        $schema->properties->{'originalDocumentHasDoi'} = (object) [
            'type' => 'integer',
            'apiSummary' => true,
            'validation' => ['nullable'],
        ];
        $schema->properties->{'originalDocumentDoi'} = (object) [
            'type' => 'string',
            'apiSummary' => true,
            'validation' => ['nullable'],
        ];

        return Hook::CONTINUE;
    }

    public function modifySubmissionWizardSteps($hookName, $params)
    {
        $request = Application::get()->getRequest();
        $templateMgr = $params[0];

        if ($request->getRequestedPage() !== 'submission' || $request->getRequestedOp() === 'saved') {
            return Hook::CONTINUE;
        }

        $submission = $request
            ->getRouter()
            ->getHandler()
            ->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);

        if (!$submission || !$submission->getData('submissionProgress')) {
            return Hook::CONTINUE;
        }

        $steps = $templateMgr->getState('steps');
        $editedSteps = [];

        foreach ($steps as $step) {
            if ($step['id'] === 'editors') {
                $step['sections'] = $this->removeRelationsSection($step['sections']);
            }

            if ($step['id'] === 'details') {
                $step['sections'] = $this->addTranslationDataSection($step['sections'], $submission, $request);
            }

            $editedSteps[] = $step;
        }

        $templateMgr->setState(['steps' => $editedSteps]);
        $templateMgr->registerFilter("output", [$this, 'removeRelationsFromReviewStepFilter']);

        return Hook::CONTINUE;
    }

    private function removeRelationsSection($stepSections)
    {
        $editedSections = [];
        foreach ($stepSections as $section) {
            if ($section['id'] != 'relation') {
                $editedSections[] = $section;
            }
        }
        return $editedSections;
    }

    private function getTranslationDataForm($submission, $request)
    {
        $context = $request->getContext();
        $publication = $submission->getLatestPublication();
        $publicationEndpoint = 'submissions/' . $submission->getId() . '/publications/' . $publication->getId();
        $saveFormUrl = $request->getDispatcher()->url($request, Application::ROUTE_API, $context->getPath(), $publicationEndpoint);

        return new TranslationDataForm($saveFormUrl, $submission);
    }

    private function addTranslationDataSection($stepSections, $submission, $request)
    {
        $translationDataForm = $this->getTranslationDataForm($submission, $request);

        $stepSections[] = [
            'id' => 'translationData',
            'name' => __('plugins.generic.scieloTranslationsFields.translationData.title'),
            'description' => __('plugins.generic.scieloTranslationsFields.translationData.description'),
            'type' => SubmissionHandler::SECTION_TYPE_FORM,
            'form' => $translationDataForm->getConfig(),
        ];

        return $stepSections;
    }

    public function removeRelationsFromReviewStepFilter($output, $templateMgr)
    {
        if (str_contains($output, '<h3 id="review-relation">')) {
            $output = '';
            $templateMgr->unregisterFilter("output", [$this, 'removeRelationsFromReviewStepFilter']);
        }

        return $output;
    }

    public function addFieldsToReviewStep($hookName, $params)
    {
        $step = $params[0]['step'];
        $templateMgr = $params[1];
        $output = &$params[2];

        if ($step === 'details') {
            $output .= $templateMgr->fetch($this->getTemplateResource('review/translationDataFields.tpl'));
        }

        return Hook::CONTINUE;
    }

    public function validateSubmissionFields($hookName, $params)
    {
        $errors = &$params[0];
        $submission = $params[1];
        $publication = $submission->getCurrentPublication();

        if (is_null($publication->getData('originalDocumentHasDoi'))) {
            $errors['originalDocumentHasDoi'] = [__('plugins.generic.scieloTranslationsFields.error.originalDocumentHasDoi.required')];
        }

        return Hook::CONTINUE;
    }

    public function removeRelationsFromWorkflow($hookName, $params)
    {
        $templateMgr = &$params[1];
        $templateMgr->registerFilter("output", [$this, 'removeRelationsButtonWorkflowFilter']);

        return Hook::CONTINUE;
    }

    public function removeRelationsButtonWorkflowFilter($output, $templateMgr)
    {
        if (
            preg_match('/class="pkpWorkflow__header"/', $output)
            && preg_match('/<span[^>]+class="pkpPublication__relation"/', $output, $matches, PREG_OFFSET_CAPTURE)
        ) {
            $blockStartPosition = $matches[0][1];

            preg_match('/<\/span>/', $output, $matches, PREG_OFFSET_CAPTURE, $blockStartPosition);
            $blockEndPosition = $matches[0][1] + strlen('</span>');

            $output = substr_replace($output, '', $blockStartPosition, $blockEndPosition - $blockStartPosition);
            $templateMgr->unregisterFilter('output', array($this, 'removeRelationsButtonWorkflowFilter'));
        }

        return $output;
    }

    public function addResourcesToWorkflow($hookName, $params)
    {
        $templateMgr = $params[0];
        $template = $params[1];
        $request = Application::get()->getRequest();
        $submission = $templateMgr->getTemplateVars('submission');

        if ($template != 'workflow/workflow.tpl' && $template != 'authorDashboard/authorDashboard.tpl') {
            return Hook::CONTINUE;
        }

        $translationDataForm = $this->getTranslationDataForm($submission, $request);
        $components = $templateMgr->getState('components');
        $components[$translationDataForm->id] = $translationDataForm->getConfig();

        $templateMgr->setState(['components' => $components]);

        return Hook::CONTINUE;
    }

    public function addTranslationDataTabToWorkflow($hookName, $params)
    {
        $templateMgr = &$params[1];
        $output = &$params[2];

        $output .= $templateMgr->fetch($this->getTemplateResource('translationDataTab.tpl'));

        return Hook::CONTINUE;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\scieloTranslationsFields\ScieloTranslationsFieldsPlugin', '\ScieloTranslationsFieldsPlugin');
}