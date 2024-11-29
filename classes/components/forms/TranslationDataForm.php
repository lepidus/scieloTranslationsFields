<?php

namespace APP\plugins\generic\scieloTranslationsFields\classes\components\forms;

use PKP\components\forms\FormComponent;
use PKP\components\forms\FieldOptions;
use PKP\components\forms\FieldText;

class TranslationDataForm extends FormComponent
{
    public $id = 'translationData';
    public $method = 'PUT';

    public function __construct($action, $submission)
    {
        $publication = $submission->getCurrentPublication();

        $this->action = $action;
        $this->addField(new FieldOptions('originalDocumentHasDoi', [
            'label' => __('plugins.generic.scieloTranslationsFields.originalDocumentHasDoi'),
            'description' => __('plugins.generic.scieloTranslationsFields.originalDocumentHasDoi.description'),
            'type' => 'radio',
            'isRequired' => true,
            'value' => $publication->getData('originalDocumentHasDoi'),
            'options' => [
                [
                    'value' => 1,
                    'label' => __('plugins.generic.scieloTranslationsFields.originalDocumentHasDoi.yes')
                ],
                [
                    'value' => 0,
                    'label' => __('plugins.generic.scieloTranslationsFields.originalDocumentHasDoi.no')
                ]
            ],
        ]))
        ->addField(new FieldText('originalDocumentDoi', [
            'label' => __('plugins.generic.scieloTranslationsFields.originalDocumentDoi'),
            'description' => __('plugins.generic.scieloTranslationsFields.originalDocumentDoi.description'),
            'isMultilingual' => false,
            'value' => $publication->getData('originalDocumentDoi'),
            'showWhen' => ['originalDocumentHasDoi', 1]
        ]));
    }
}
