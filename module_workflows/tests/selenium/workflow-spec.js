
describe('module_workflows', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test list', function() {
        browser.get('http://127.0.0.1:8080/index.php?admin=1&module=workflows&action=list');

        var workflows = element.all(by.css('.title'));

        expect(workflows.count()).toEqual(3);
        expect(workflows.get(0).getText()).toEqual('Zusammenfassung neuer Nachrichten');
        expect(workflows.get(1).getText()).toEqual('Berechnung Enddatum Objekte');
        expect(workflows.get(2).getText()).toEqual('Maßnahme');
    });

});
