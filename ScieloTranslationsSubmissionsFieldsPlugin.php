<?php

/**
 * @file plugins/generic/scieloTranslationsSubmissionsFields/ScieloTranslationsSubmissionsFieldsPlugin.php
 *
 * Copyright (c) 2024 Lepidus Tecnologia
 * Copyright (c) 2024 SciELO
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt.
 *
 * @class ScieloTranslationsSubmissionsFieldsPlugin
 * @ingroup plugins_generic_scieloTranslationsSubmissionsFields
 *
 */

namespace APP\plugins\generic\scieloTranslationsSubmissionsFields;

use PKP\plugins\Hook;
use PKP\plugins\GenericPlugin;
use APP\core\Application;

class ScieloTranslationsSubmissionsFieldsPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        if (Application::isUnderMaintenance()) {
            return true;
        }

        // if ($success && $this->getEnabled($mainContextId)) {
        // }

        return $success;
    }

    public function getDisplayName()
    {
        return __('plugins.generic.scieloTranslationsSubmissionsFields.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.scieloTranslationsSubmissionsFields.description');
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\scieloTranslationsSubmissionsFields\ScieloTranslationsSubmissionsFields', '\ScieloTranslationsSubmissionsFields');
}
