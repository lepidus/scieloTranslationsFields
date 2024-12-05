<?php

namespace APP\plugins\generic\scieloTranslationsFields\classes\components\forms;

use PKP\components\forms\FormComponent;
use PKP\components\forms\FieldOptions;
use PKP\components\forms\FieldText;
use PKP\components\forms\FieldHTML;

class TranslationDataForm extends FormComponent
{
    public $id = 'translationData';
    public $method = 'PUT';

    public function __construct($action, $submission, $placedOn)
    {
        $publication = $submission->getCurrentPublication();
        $originalDocumentDoi = $publication->getData('originalDocumentDoi');

        $this->action = $action . "&placedOn=$placedOn";
        $this->addField(new FieldText('originalDocumentDoi', [
            'label' => __('plugins.generic.scieloTranslationsFields.originalDocumentDoi'),
            'description' => __('plugins.generic.scieloTranslationsFields.originalDocumentDoi.description'),
            'isMultilingual' => false,
            'isRequired' => true,
            'value' => $originalDocumentDoi,
        ]));

        if (!empty($originalDocumentDoi) && $placedOn == 'workflow') {
            $originalDocumentCitation = $publication->getData('originalDocumentCitation');

            if (empty($originalDocumentCitation)) {
                $originalDocumentCitation = __('plugins.generic.scieloTranslationsFields.originalDocumentCitation.couldntRetrieve');
            }

            $this->addField(new FieldHTML('originalDocumentDoi', [
                'label' => __('plugins.generic.scieloTranslationsFields.originalDocumentCitation'),
                'description' => "<p style=\"text-align: justify\">{$originalDocumentCitation}</p>",
            ]));
        }
    }
}
