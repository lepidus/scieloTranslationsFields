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
    cy.get('input[name="originalDocumentDoi"]').type(submissionData.originalDoi, {delay: 0});
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

describe('SciELO Translations Fields - Contributors verifications', function () {
    let submissionData;

	before(function () {
		submissionData = {
			title: "Alone, together",
			abstract: 'Thoughts about relationship problems',
			keywords: ['guitars'],
            originalDoi: '10.1590/0037-8682-0167-2020',
            contributors: [
                {
                    'given': 'Julian',
                    'family': 'Casablancas',
                    'email': 'julian.casablancas@outlook.com',
                    'country': 'United States',
                    'role': 'Author'
                },
                {
                    'given': 'Albert',
                    'family': 'Hammond',
                    'email': 'albert.hammond@outlook.com',
                    'country': 'United States',
                    'role': 'Translator'
                }
            ],
            files: [
                {
                    'file': 'dummy.pdf',
                    'fileName': 'dummy.pdf',
                    'mimeType': 'application/pdf',
                    'genre': 'Preprint Text',
                }
            ]
		}
	});
    
    it('Creates translator role', function() {
        cy.login('dbarnes', null, 'publicknowledge');

        cy.contains('a', 'Users & Roles').click();
        cy.get('#roles-button').click();

        cy.contains('span', 'Translator').should('not.exist');
        cy.contains('a', 'Create New Role').click();

        cy.get('#userGroupForm').within(() => {
            cy.get('#roleId').select('Author');
            cy.get('input[name="name[en]"]').type('Translator', {delay: 0});
            cy.get('input[name="abbrev[en]"]').type('Trans', {delay: 0});
            cy.get('.submitFormButton').click();
        });
        
        cy.wait(1000);
        cy.contains('span', 'Translator');
    });
    it('Creates a new submission. Asserts there is at least one translator contributor', function() {
        cy.login('ckwantes', null, 'publicknowledge');
        cy.get('div#myQueue a:contains("New Submission")').click();

        beginSubmission(submissionData);
        detailsStep(submissionData);
        cy.addSubmissionGalleys(submissionData.files);
        cy.contains('button', 'Continue').click();
        contributorsStep([submissionData.contributors[0]]);
        cy.contains('button', 'Continue').click();

        cy.contains('There must be at least one contributor with the "Translator" role');

        cy.contains('.pkpSteps__step__label', 'Contributors').click();
        contributorsStep([submissionData.contributors[1]]);
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();

        cy.contains('span', 'Julian Casablancas').parent().within(() => {
            cy.contains('Author');
        });
        cy.contains('span', 'Albert Hammond').parent().within(() => {
            cy.contains('Translator');
        });

        cy.contains('button', 'Submit').click();
        cy.get('.modal__panel:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
});