// Selenium user-extension for Kajona testing
// add this file to your Selenium IDE (Firefox extension)
// Options -> "Selenium Core extensions (user-extensions.js)"
// without this extension some of the test will NOT work!!
// you can add more than one extension file by adding a comma separated list of values
// 
// (C) 2012 by mr.bashshell for Kajona



Selenium.prototype.doStbTestFkt = function(input) {
    var content = " Testmeldung";
    LOG.debug("Debug:" + content);
    LOG.info("Info:" + content);

    LOG.debug("Debug: Input war: " + input);
    LOG.info("Info: Input war:" + input);
   	
   	var allText = this.page().bodyText();
   	LOG.info("Text der Seite: " +allText); 
};



Selenium.prototype.doErrorCheckOnPage = function() {
	var allText = this.page().bodyText();
	LOG.info("Check PHP Error/Warning/Notice...");	
	Assert.notMatches("STB: Check for PHP Error Messages", "regexpi:warning:|notice:|parse error|fatal" , allText);
	LOG.info("Check for missing lang entry...");	
	Assert.notMatches("STB: Check for missing lang entry", "regexp:![a-zA-Z0-9_-]+!" , allText);
};




// taken from  http://wiki.openqa.org/display/SEL/assertTextPresetCount
// extended by mr.bashshell (output matches)
Selenium.prototype.assertTextPresentCount = function(expectedText, count) {

    var allText = this.page().bodyText();
    var pattern = new RegExp(expectedText,"g");

    LOG.info("expect text =" + expectedText);
    LOG.info("expect count=" + count);
    LOG.info("matchingText=" + allText.match(pattern));

    if(allText == "") {
        Assert.fail("Page text not found");

    } else if(allText.indexOf(expectedText) == -1) {
        if (count != 0) {
        Assert.fail("'" + expectedText + "' count doesn't match. Found " + allText.match(pattern).length + " matches" );
        }
    } else if(allText.match(pattern).length != count) {
        Assert.fail("'" + expectedText + "' count doesn't match. Found " + allText.match(pattern).length + " matches");
    }
};

