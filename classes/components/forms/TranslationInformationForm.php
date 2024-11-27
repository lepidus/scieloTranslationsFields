<?php

namespace APP\plugins\generic\scieloTranslationsFields\classes\components\forms;

use PKP\components\forms\FormComponent;
use PKP\components\forms\FieldText;

class TranslationInformationForm extends FormComponent
{
    public $id = 'translationInformation';
    public $method = 'PUT';

    public function __construct($action, $submission)
    {
        $this->action = $action;
        $this->addField(new FieldText('originalDoi', [
            'label' => __('plugins.generic.scieloTranslationsFields.originalDoi'),
            'description' => __('plugins.generic.scieloTranslationsFields.originalDoi.description'),
            'isMultilingual' => false,
            'value' => $submission->getData('isTranslationOfDoi'),
        ]));
    }
}
