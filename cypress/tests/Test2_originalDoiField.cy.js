import '../support/commands.js';

function beginSubmission(submissionData) {
    cy.get('input[name="locale"][value="en"]').click();
    cy.setTinyMceContent('startSubmission-title-control', submissionData.title);
    
    cy.get('input[name="submissionRequirements"]').check();
    cy.get('input[name="privacyConsent"]').check();
    cy.contains('button', 'Begin Submission').click();
}

function detailsStep(submissionData) {
    cy.setTinyMceContent('titleAbstract-abstract-control-en', submissionData.abstract);
    submissionData.keywords.forEach(keyword => {
        cy.get('#titleAbstract-keywords-control-en').type(keyword, {delay: 0});
        cy.wait(500);
        cy.get('#titleAbstract-keywords-control-en').type('{enter}', {delay: 0});
    });
    cy.contains('button', 'Continue').click();
}

function contributorsStep(submissionData) {
    submissionData.contributors.forEach(authorData => {
        cy.contains('button', 'Add Contributor').click();
        cy.get('input[name="givenName-en"]').type(authorData.given, {delay: 0});
        cy.get('input[name="familyName-en"]').type(authorData.family, {delay: 0});
        cy.get('input[name="email"]').type(authorData.email, {delay: 0});
        cy.get('select[name="country"]').select(authorData.country);
        
        cy.get('.modal__panel:contains("Add Contributor")').find('button').contains('Save').click();
        cy.waitJQuery();
    });

    cy.contains('button', 'Continue').click();
}

describe('SciELO Translations Fields - Original DOI features', function () {
	let submissionData;

	before(function () {
		submissionData = {
			title: "For whom the bell tolls",
			abstract: 'A poem, which describes the idea that all person are connected and part of a whole',
			keywords: ['poem'],
            originalDoi: '10.4567/OriginalDoiTranslated',
            contributors: [
                {
                    'given': 'James',
                    'family': 'Hammett',
                    'email': 'james.hammett@outlook.com',
                    'country': 'United States'
                }
            ],
            files: [
                {
                    'file': 'dummy.pdf',
                    'fileName': 'dummy.pdf',
                    'mimeType': 'application/pdf',
                    'genre': 'Preprint Text'
                }
            ]
		}
	});

    it('Original DOI field is displayed at submission wizard', function() {
        cy.login('ckwantes', null, 'publicknowledge');
        cy.get('div#myQueue a:contains("New Submission")').click();

        beginSubmission(submissionData);

        cy.contains('h2', 'Translation data');
        cy.contains('Please provide the following data regarding the translation you are submitting.')
        cy.contains('label', 'DOI of the original document');
        cy.contains('The DOI of the original document this submission is translating');
        cy.get('input[name="originalDocumentDoi"]').type(submissionData.originalDoi, {delay: 0});
        
        detailsStep(submissionData);
        cy.addSubmissionGalleys(submissionData.files);
        cy.contains('button', 'Continue').click();
        contributorsStep(submissionData);
        cy.contains('button', 'Continue').click();

        cy.wait(1000);
        cy.contains('button', 'Submit').click();
        cy.get('.modal__panel:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
});