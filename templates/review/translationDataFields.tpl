<div class="submissionWizard__reviewPanel">
    <div class="submissionWizard__reviewPanel__header">
        <h3 id="review-plugin-scieloTranslation-translation-data">
            {translate key="plugins.generic.scieloTranslationsFields.translationData.title"}
        </h3>
        <pkp-button
            aria-describedby="review-plugin-scieloTranslation-translation-data"
            class="submissionWizard__reviewPanel__edit"
            @click="openStep('{$step.id}')"
        >
            {translate key="common.edit"}
        </pkp-button>
    </div>
    <div class="submissionWizard__reviewPanel__body">
        <div class="submissionWizard__reviewPanel__item">
            <h4 class="submissionWizard__reviewPanel__item__header">
                {translate key="plugins.generic.scieloTranslationsFields.originalDocumentHasDoi"}
            </h4>
            <div class="submissionWizard__reviewPanel__item__value">
                <notification v-if="errors.originalDocumentHasDoi" type="warning">
                    <icon icon="exclamation-triangle" :inline="true"></icon>
                    {translate key="plugins.generic.scieloTranslationsFields.error.originalDocumentHasDoi.required"}
                </notification>
                <template v-else>
                    {{ publication.originalDocumentHasDoi ? __('common.yes') : __('common.no')}}
                </template>
            </div>
        </div>
        <div 
            v-if="publication.originalDocumentHasDoi"
            class="submissionWizard__reviewPanel__item"
        >
            <h4 class="submissionWizard__reviewPanel__item__header">
                {translate key="plugins.generic.scieloTranslationsFields.originalDocumentDoi"}
            </h4>
            <div class="submissionWizard__reviewPanel__item__value">
                <template>
                    {{ publication.originalDocumentDoi }}
                </template>
            </div>
        </div>
    </div>
</div>