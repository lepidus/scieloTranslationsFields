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

function addContributor(contributorData) {
    cy.contains('button', 'Add Contributor').click();
    cy.get('input[name="givenName-en"]').type(contributorData.given, {delay: 0});
    cy.get('input[name="familyName-en"]').type(contributorData.family, {delay: 0});
    cy.get('input[name="email"]').type(contributorData.email, {delay: 0});
    cy.get('select[name="country"]').select(contributorData.country);
    
    cy.contains('.pkpFormField--options__optionLabel', contributorData.role).parent().within(() => {
        cy.get('input[type="radio"]').click();
    })

    cy.get('.modal__panel:contains("Add Contributor")').find('button').contains('Save').click();
    cy.waitJQuery();
}

describe('SciELO Translations Fields - Contributors verifications', function () {
    let submissionData;
    let testOrcid;

	before(function () {
		testOrcid = 'https://orcid.org/0000-0002-1825-0097';
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
                    'role': 'Translator',
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
            cy.contains('Role Name').click();
            cy.get('input[name="abbrev[en]"]').type('TR', {delay: 0});
            cy.get('.submitFormButton').click();
        });
        
        cy.wait(1000);
        cy.contains('span', 'Translator');
    });
    it('Asserts there is at least one translator contributor', function () {
        cy.login('ckwantes', null, 'publicknowledge');
        cy.get('div#myQueue a:contains("New Submission")').click();

        beginSubmission(submissionData);
        detailsStep(submissionData);
        cy.addSubmissionGalleys(submissionData.files);
        cy.contains('button', 'Continue').click();
        addContributor(submissionData.contributors[0]);
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();

        cy.contains('There must be at least one contributor with the "Translator" role');

        cy.contains('.pkpSteps__step__label', 'Contributors').click();
        addContributor(submissionData.contributors[1]);
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();

        cy.contains('span', 'Julian Casablancas').parent().within(() => {
            cy.contains('Author');
        });
        cy.contains('span', 'Albert Hammond').parent().within(() => {
            cy.contains('Translator');
        });
        cy.contains('There must be at least one contributor with the "Translator" role')
            .should('not.exist');
    });
    it('Translator contributor should have the ORCID filled', function () {
        cy.login('ckwantes', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        Cypress._.times(4, () => {
            cy.contains('button', 'Continue').click();
        });
        cy.contains('The contributors with the "Translator" role must have their ORCID authenticated');
        
        cy.contains('.pkpSteps__step__label', 'Contributors').click();
        cy.get('.listPanel__itemTitle:visible:contains("Albert Hammond")')
            .parent().parent().within(() => {
                cy.contains('button', 'Edit').click();
            });
        cy.get('input[name="orcid"]').type(testOrcid, {delay: 0});
        cy.get('.modal__panel:contains("Edit")').find('button').contains('Save').click();
        cy.waitJQuery();

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();

        cy.wait(2000);
        cy.contains('The contributors with the "Translator" role must have their ORCID authenticated')
            .should('not.exist');
    });
    it('Submitter contributor must have ORCID filled', function () {
        cy.login('ckwantes', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.get('.listPanel__itemTitle:visible:contains("Catherine Kwantes")')
            .parent().parent().within(() => {
                cy.contains('button', 'Edit').click();
            });
        cy.get('input[name="orcid"]').clear();
        cy.get('.modal__panel:contains("Edit")').find('button').contains('Save').click();
        cy.waitJQuery();

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('The submitter contributor must have their ORCID authenticated');
        
        cy.contains('.pkpSteps__step__label', 'Contributors').click();
        cy.get('.listPanel__itemTitle:visible:contains("Catherine Kwantes")')
            .parent().parent().within(() => {
                cy.contains('button', 'Edit').click();
            });
        cy.get('input[name="orcid"]').type(testOrcid, {delay: 0});
        cy.get('.modal__panel:contains("Edit")').find('button').contains('Save').click();
        cy.waitJQuery();

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();

        cy.wait(2000);
        cy.contains('The submitter contributor must have their ORCID authenticated')
            .should('not.exist');
    });
    it('Clear ORCID of submitter user profile', function () {
        cy.login('ckwantes', null, 'publicknowledge');
		cy.get('.app__headerActions button').eq(1).click();
        cy.contains('a', 'Edit Profile').click();

		cy.get('a[name="publicProfile"]').click();
		cy.get('input[name="orcid"]').clear();
		cy.get('.submitFormButton:visible').click();
    });
    it('If submitter is not a contributor, their profile must have ORCID filled', function () {
        cy.login('ckwantes', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.get('.listPanel__itemTitle:visible:contains("Catherine Kwantes")')
            .parent().parent().within(() => {
                cy.contains('button', 'Delete').click();
            });
        cy.contains('button', 'Delete Contributor').click();
        cy.waitJQuery();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('The user making this submission must have their ORCID authenticated in their profile');

		cy.get('.app__headerActions button').eq(1).click();
        cy.contains('a', 'Edit Profile').click();
		cy.get('a[name="publicProfile"]').click();
		cy.get('input[name="orcid"]').clear().type(testOrcid, {delay: 0});
		cy.get('.submitFormButton:visible').click();

        cy.contains('a', 'Back to Submissions').click();
        cy.findSubmission('myQueue', submissionData.title);
        Cypress._.times(4, () => {
            cy.contains('button', 'Continue').click();
        });
        cy.contains('The user making this submission must have their ORCID authenticated in their profile')
            .should('not.exist');
    });
});