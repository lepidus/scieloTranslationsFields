<?php

namespace APP\plugins\generic\scieloTranslationsFields\classes\components\forms;

use PKP\components\forms\FormComponent;
use PKP\components\forms\FieldText;

class TranslationDataForm extends FormComponent
{
    public $id = 'translationData';
    public $method = 'PUT';

    public function __construct($action, $submission)
    {
        $this->action = $action;
        $this->addField(new FieldText('originalDocumentDoi', [
            'label' => __('plugins.generic.scieloTranslationsFields.originalDocumentDoi'),
            'description' => __('plugins.generic.scieloTranslationsFields.originalDocumentDoi.description'),
            'isMultilingual' => false,
            'value' => $submission->getData('isTranslationOfDoi'),
        ]));
    }
}
