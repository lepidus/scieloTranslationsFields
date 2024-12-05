import '../support/commands.js';

describe('SciELO Translations Fields - Contributors verifications', function () {
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
});