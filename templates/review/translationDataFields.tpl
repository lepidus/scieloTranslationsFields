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
        <div  class="submissionWizard__reviewPanel__item">
            <h4 class="submissionWizard__reviewPanel__item__header">
                {translate key="plugins.generic.scieloTranslationsFields.originalDocumentDoi"}
            </h4>
            <div class="submissionWizard__reviewPanel__item__value">
                <notification v-if="errors.originalDocumentDoi" type="warning">
                    <icon icon="exclamation-triangle" :inline="true"></icon>
                    {{ errors.originalDocumentDoi[0] }}
                </notification>
                <template v-else>
                    {{ publication.originalDocumentDoi }}
                </template>
            </div>
        </div>
        <div 
            v-if="!errors.originalDocumentDoi"
            class="submissionWizard__reviewPanel__item"
        >
            <h4 class="submissionWizard__reviewPanel__item__header">
                {translate key="plugins.generic.scieloTranslationsFields.originalDocumentCitation"}
            </h4>
            <div class="submissionWizard__reviewPanel__item__value" style="text-align: justify;">
                <template v-if="publication.originalDocumentCitation">
                    {{ publication.originalDocumentCitation }}
                </template>
                <template>
                    {translate key="plugins.generic.scieloTranslationsFields.originalDocumentCitation.couldntRetrieve"}
                </template>
            </div>
        </div>
    </div>
</div>