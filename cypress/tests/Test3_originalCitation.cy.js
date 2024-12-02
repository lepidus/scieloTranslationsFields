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

describe('SciELO Translations Fields - Original document citation features', function () {
	let submissionData;

	before(function () {
		submissionData = {
			title: "Voodoo Child",
			abstract: 'Great guitar solos',
			keywords: ['guitar'],
            originalDoi: '10.1590/0037-8682-0167-2020',
            originalDoiCitation: 'Croda, J., Oliveira, W. K. de, Frutuoso, R. L., Mandetta, L. H., \
                Baia-da-Silva, D. C., Brito-Sousa, J. D., Monteiro, W. M., & Lacerda, M. V. G. (2020). \
                COVID-19 in Brazil: advantages of a socialized unified health system and preparation to contain cases. \
                Revista Da Sociedade Brasileira de Medicina Tropical, 53. https://doi.org/10.1590/0037-8682-0167-2020',
            contributors: [
                {
                    'given': 'Jimi',
                    'family': 'Hendrix',
                    'email': 'jimi.hendrix@outlook.com',
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

    it('Creates a new submission with a deposited original DOI', function() {
        cy.login('ckwantes', null, 'publicknowledge');
        cy.get('div#myQueue a:contains("New Submission")').click();

        beginSubmission(submissionData);
        cy.get('input[name="originalDocumentHasDoi"][value="1"]').check();
        cy.get('input[name="originalDocumentDoi"]').type(submissionData.originalDoi, {delay: 0});
        detailsStep(submissionData);
        cy.addSubmissionGalleys(submissionData.files);
        cy.contains('button', 'Continue').click();
        contributorsStep(submissionData);
        cy.contains('button', 'Continue').click();
        
        cy.contains('The original document has a DOI');
        cy.contains(submissionData.originalDoi);

        cy.contains('button', 'Submit').click();
        cy.get('.modal__panel:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
    it('Original document citation is shown at workflow', function () {
        cy.login('ckwantes', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.get('#publication-button').click();
        cy.get('#translationData-button').click();

        cy.contains('Original document citation');
        cy.get('input[name="originalDocumentCitation"]').should('have.value', submissionData.originalDoiCitation);
    });
});