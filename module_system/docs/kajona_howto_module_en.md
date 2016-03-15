
#HowTo: Your own module

This tutorial should describe the way to create a new module for Kajona.

Since Kajona is built around modules, it's easy to extend the framework with your own logic and contents.

This tutorial expects that you have installed Kajona successfully and the installation is up and running.

Basic knowledge of git is helpful, bot not necessary.

As an example, we want to create a module containing a database of person. The module should provide a backend-view to create, update and delete persons and a frontend-view in order to render a list of persons on the website generated with Kajona.

Since creating a module for Kajona is a common task, there's a nice little script, aiming to generate some skeleton files and a raw-version of the module.

In order to start the development of a new module, we need the [KajonaModuleGenerator](https://github.com/sidler/KajonaModuleGenerator), a simple php-script generating you all the basic files.
You can [download the latest version either directly 
from Github](https://github.com/sidler/KajonaModuleGenerator/archive/master.zip)
or you clone the repository, e.g. in a folder within your webservers document-root:

	mkdir kajonamodulegenerator
	git clone https://github.com/sidler/KajonaModuleGenerator.git
	
Either way you should now have the KajonaModuleGenerator, containing tow directories (skeleton, src) and a few php-files. Make sure to have the whole directory writeable by the webserver:

	chmod 0777 KajonaModuleGenerator

Fire up your browser and open the file

	KajonaModuleGenerator/index.php	

You should now see a plain text-page with a few form-elements. In order to generate our module we should provide the following information:

- Module Name: userdirectory
- Module Author: Thats you! Provide your e-mail adress
- Module Nr: Just stick to the generated value
- Record Name: That's the name of the modules' data-object. In our case we want to name it "person"
- Leave the checkbox 'Generate portal code' enabled

In the next part, add at least two record properties by pressing "+ Add property":

1.  Name property:
    *  Property Name: name
    *  Indexable: yes. We want the search-engine to index this field.
    *  Mandatory: yes. A name is required when creating a new person.
    *  Export to template: yes. Otherwise the list within the portal gets useless.
    *  Datatype: char254. We think that 254 chars are enough, but feel free to change the value.
    *  Fieldtype: text. A regular text-input-field should be rendered when editing the record.

2.  E-Mail property:
    *  Property Name: email
    *  Indexable: yes    
    *  Mandatory: yes.    
    *  Export to template: yes. 
    *  Datatype: char254. 
    *  Fieldtype: text. 

That's it. Proceed by clicking "Generate Module" and have a look at the funky output.

The output should end with a message similar to

	CCleaning module...
	Generating ZIP-Archive...
	adding ...
	created zip-file at .../module_userdirectory.zip
	direct download: module_userdirectory.zip

Download the generated zip file and extract it to your Kajonas /core folder, so that the content of the zip-file is located at:

	/kajona-folder/core/module_userdirectory/
	
Don't worry if there are mostly phar-files under /core, the framework is able to handle both: packaged modules as phar-files and development-version of modules as extracted directories.

Inside the ```module_userdirectory``` you should find the following contents:

	admin 			-> the backend code of your module
	installer		-> the installer of your module
	lang			-> properties and localization of your module
	portal			-> the frontend code of your module
	system			-> the business-object of your module
	templates		-> the templates used to render your module in the frontend
	metadata.xml	-> package-metadata, e.g. dependencies
	
Have a look at all the files the ModuleGenerator created for you, especially at the file ```/system/UserdirectoryPerson```. It holds the two properties created before and lists the relevant annotations to map an object from and to the database:

	class UserdirectoryPerson extends Model implements ModelInterface, AdminListableInterface
	{
	    /**
	     * @var string
	     * @tableColumn userdirectory_person.person_name
	     * @tableColumnDatatype char254
	     * @fieldType text
	     * @fieldMandatory
	     * @addSearchIndex
	     * @templateExport
	     */
	     private $strName = "";
		
	    /**
	     * @var string
	     * @tableColumn userdirectory_person.person_email
	     * @tableColumnDatatype char254
	     * @fieldType text
	     * @fieldMandatory
	     * @addSearchIndex
	     * @templateExport
	     */
	     private $strEmail = "";
	     
Right now, the module is fully configured and ready to be installed. In order to do so, we want to trigger the installation of the module. Therefore the packagemanagement of your Kajona installation should be opend.	     