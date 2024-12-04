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
        cy.contains('legend', 'Original document DOI');
        cy.contains('Does the original document this submission is translating have a DOI?');

        detailsStep(submissionData);
        cy.addSubmissionGalleys(submissionData.files);
        cy.contains('button', 'Continue').click();
        contributorsStep(submissionData);
        cy.contains('button', 'Continue').click();

        cy.wait(1000);
        cy.contains('h3', 'Translation data');
        cy.contains('h4', 'Original document DOI');
        cy.get('h4').contains(/^DOI$/).should('not.exist');
        cy.contains('You must inform if the original document has a DOI');
        cy.contains('button', 'Submit').should('be.disabled');

        cy.contains('.pkpSteps__step__label', 'Details').click();
        cy.get('input[name="originalDocumentHasDoi"][value="1"]').check();
        cy.contains('label', 'DOI');
        cy.contains('Please insert the DOI of the original document this submission is translating');
        cy.get('input[name="originalDocumentDoi"]').type('Invalid DOI', {delay: 0});
        Cypress._.times(4, () => {
            cy.contains('button', 'Continue').click();
        });
        cy.contains('The DOI entered is invalid. Please include only the identifier (e.g. "10.1234/ExampleDOI")');

        cy.contains('.pkpSteps__step__label', 'Details').click();
        cy.get('input[name="originalDocumentDoi"]').clear().type(submissionData.originalDoi, {delay: 0});
        Cypress._.times(4, () => {
            cy.contains('button', 'Continue').click();
        });

        cy.contains('The original document has a DOI');
        cy.contains('h4', 'DOI');
        cy.contains(submissionData.originalDoi);

        cy.contains('button', 'Submit').click();
        cy.get('.modal__panel:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
    it('Original DOI field is displayed at Workflow page', function () {
        cy.login('ckwantes', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.get('#publication-button').click();
        cy.get('#translationData-button').click();

        cy.contains('Original document DOI');
        cy.get('input[name="originalDocumentHasDoi"][value="1"]').should('be.checked');
        cy.contains('DOI')
        cy.get('input[name="originalDocumentDoi"]').should('have.value', submissionData.originalDoi);

        cy.get('input[name="originalDocumentDoi"]').clear().type('Invalid DOI', {delay: 0});
        cy.get('#translationData').within(() => {
            cy.contains('button', 'Save').click();
        });
        cy.contains('The DOI entered is invalid. Please include only the identifier (e.g. "10.1234/ExampleDOI")');
    });
});