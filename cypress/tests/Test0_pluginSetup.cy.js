describe('Plugin configuration', function () {
	it('Enables plugin', function () {
		cy.login('dbarnes', null, 'publicknowledge');
		cy.contains('a', 'Website').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();

		cy.get('input[id^=select-cell-scielotranslationsfieldsplugin]').check();
		cy.get('input[id^=select-cell-scielotranslationsfieldsplugin]').should('be.checked');
	});
	it('Adds ORCID to user profile', function () {
		cy.login('ckwantes', null, 'publicknowledge');
		cy.get('.app__headerActions button').eq(1).click();
        cy.contains('a', 'Edit Profile').click();

		cy.get('a[name="publicProfile"]').click();
		cy.get('input[name="orcid"]').type('https://orcid.org/0000-0002-1825-0097', {delay: 0});
		cy.get('.submitFormButton:visible').click();
	});
});
