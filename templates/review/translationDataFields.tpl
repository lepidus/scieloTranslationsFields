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
                {translate key="plugins.generic.scieloTranslationsFields.originalDocumentDoi"}
            </h4>
            <div class="submissionWizard__reviewPanel__item__value">
                <template>
                    {{ submission.isTranslationOfDoi }}
                </template>
            </div>
        </div>
    </div>
</div>