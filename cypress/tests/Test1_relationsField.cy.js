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

describe('SciELO Translations Fields - Relations field features', function () {
	let submissionData;

	before(function () {
		submissionData = {
			title: "Living a boy's adventure tale",
			abstract: 'Describes an adventure tale created and narrated by a boy',
			keywords: ['adventure tale', 'boyhood'],
            contributors: [
                {
                    'given': 'Morten',
                    'family': 'Furuholmen',
                    'email': 'morten.furuholmen@outlook.com',
                    'country': 'Norway'
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

    it('Relations fields are not shown at submission wizard', function() {
        cy.login('ckwantes', null, 'publicknowledge');

        cy.findSubmission('myQueue', submissionData.title);
        cy.contains('button', 'Continue').click();

        cy.get('div#myQueue a:contains("New Submission")').click();
        beginSubmission(submissionData);
        detailsStep(submissionData);
        cy.addSubmissionGalleys(submissionData.files);
        cy.contains('button', 'Continue').click();
        contributorsStep(submissionData);
        cy.contains('button', 'Continue').click();

        cy.contains('h2', 'Relation status').should('not.exist');
        cy.contains('legend', 'Relation status').should('not.exist');
        cy.contains('button', 'Continue').click();

        cy.wait(1000);
        cy.contains('h3', 'Relation status').should('not.exist');
        cy.contains('button', 'Submit').click();
        cy.get('.modal__panel:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
});