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

        // if ($success && $this->getEnabled($mainContextId)) {
        // }

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
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\scieloTranslationsFields\ScieloTranslationsFields', '\ScieloTranslationsFields');
}
