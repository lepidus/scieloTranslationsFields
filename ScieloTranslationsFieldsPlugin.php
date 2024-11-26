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

class ScieloTranslationsFieldsPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        if (Application::isUnderMaintenance()) {
            return true;
        }

        if ($success && $this->getEnabled($mainContextId)) {
            Hook::add('TemplateManager::display', [$this, 'removeRelationsFromEditorsStep']);
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

    public function removeRelationsFromEditorsStep($hookName, $params)
    {
        $request = Application::get()->getRequest();
        $templateMgr = $params[0];

        if ($request->getRequestedPage() !== 'submission' || $request->getRequestedOp() === 'saved') {
            return Hook::CONTINUE;
        }

        $steps = $templateMgr->getState('steps');
        $editedSteps = [];

        foreach ($steps as $step) {
            if ($step['id'] === 'editors') {
                $editedSections = [];
                foreach ($step['sections'] as $section) {
                    if ($section['id'] != 'relation') {
                        $editedSections[] = $section;
                    }
                }
                $step['sections'] = $editedSections;
            }
            $editedSteps[] = $step;
        }

        $templateMgr->setState(['steps' => $editedSteps]);
        $templateMgr->registerFilter("output", [$this, 'removeRelationsFromReviewStep']);

        return Hook::CONTINUE;
    }

    public function removeRelationsFromReviewStep($output, $templateMgr)
    {
        if (str_contains($output, '<h3 id="review-relation">')) {
            $output = '';
            $templateMgr->unregisterFilter("output", [$this, 'removeRelationsFromReviewStep']);
        }

        return $output;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\scieloTranslationsFields\ScieloTranslationsFieldsPlugin', '\ScieloTranslationsFieldsPlugin');
}
