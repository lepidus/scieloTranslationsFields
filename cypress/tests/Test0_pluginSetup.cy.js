describe('Plugin configuration', function () {
	it('Enables plugin', function () {
		cy.login('dbarnes', null, 'publicknowledge');
		cy.contains('a', 'Website').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();

		cy.get('input[id^=select-cell-scielotranslationsfieldsplugin]').check();
		cy.get('input[id^=select-cell-scielotranslationsfieldsplugin]').should('be.checked');
	});
});
