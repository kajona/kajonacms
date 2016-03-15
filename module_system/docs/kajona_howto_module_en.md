
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

	/**
	 * @author demo@kajona.de
	 * @targetTable userdirectory_person.person_id
	 *
	 * @module userdirectory
	 * @moduleId _userdirectory_module_id_
	 */
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
	     
Right now, the module is fully configured and ready to be installed. In order to do so, we want to trigger the installation of the module. Therefore the packagemanagement of your Kajona installation backend should be opened (management-aspect):
	
	http://your_kajona_root/index.php?admin=1&module=packagemanager&action=list
	
If everything went well, the module 'userdirectory' is listed and ready to install.

> Heads up! Kajona caches internal information regarding the modules available. If the userdirectory is missing from the list, try to delete the folder /project/temp/cache and reload the list afterwards.

Go ahead and install the module by clicking the install/update icon. The installation should succeed with a log like following:

	Installing tables...
	Registering userdirectory-element...
	Element registered...
	Setting aspect assignments...
	Updating default template pack...	

Afterwards the new module resides within the main-navigation, the entry "Userdirectory" is append to the end of the module.

> Heads up! You may need to switch to the "Contents" aspect before.		   
  
Continue by creating a new record, e.g. "Joe Doe, joe@example.com". After submitting the form, the new entry is shown in the list.

As you may have noticed, the form-fields are labeled "name" and "email". Hows about changing "email" to "e-mail"? Easy one! Open the file ```/core/module_userdirectory/lang/module_userdirectory/lang_userdirectory_en.php``` and change the entry ```form_userdirectory_email```accordingly:

	$lang["form_userdirectory_email"] = "e-mail";
	
Create another user-entry 	with the credentials "Luky Luke", "luky@example.com". The list now contains two entries, order by the date of creation. This makes sense for news or other records, but since we have a list if users, sorting them by name would be way better. And, why not rendering the users' mail address directly in the list? Therefore we need to change the model-definition of the person-object (```/core/module_userdirectory/system/UserdirectoryPerson.php```). 
At first we'll change the sorting by adding the ```@listOrder```annotation to the  name-property:

	/**
     * @var string
     * @tableColumn userdirectory_person.person_name
     * @tableColumnDatatype char254
     * @fieldType text
     * @fieldMandatory
     * @addSearchIndex
     * @templateExport
     * 
     * @listOrder ASC
     */
     private $strName = "";
     
     
While we're already in the file, change ```getStrAdditionalInfo``` to return the assigned e-mail address:
 
	public function getStrAdditionalInfo()
	{	
	   return $this->strEmail;
	}
	
Reload the list in the backend and enjoy your work.	
> Heads up! Kajona caches internal information regarding annotations. If the list seems to ignore your changes, try to delete the folder /project/temp/cache and reload the list afterwards.    
    
Since the backend views are customized, let's have a look at the frontend representation. Out of the box, the module is able to render the list of users created by the backend. Therefore, we need to add a new placeholder for our userdirectory element to the current page-element (right now we're assuming you know how to edit a page-template).

> Heads up! Since Kajona V5 is shipped with phar-based modules only, you may want to create a new template-pack before. Therefore you may use the backend, module Packagemanagement, Action Template-Manager, Create a new template-pack. Set the new pack active, afterwards!

Open your current page-template in a text-editor and add the following snippet:

	<kajona-blocks kajona-name="Userlist Blocks">
        <kajona-block kajona-name="Userlist">
            %%list_userdirectory%%
        </kajona-block>
    </kajona-blocks>
    
Go on and create a new page using the backend using this template. Kajona should offer you to create a new userdirectory-element instantly. Viewing the page just created in the portal should give you a (ugly) list of the records created in the backend.

Since we're a fan of readable, content, let's change the way how the records are rendered. Therefore we'll open the template ```/core/module_userdirectory/templates/default/tpl/module_userdirectory/default.tpl``` and change it to our needs. How's about a nice bootstrap table:

	<userdirectory_list>
	    <table class="table table-striped">
	        %%userdirectory_records%%
	    </table>
	</userdirectory_list>
	
	<userdirectory_record>
	    <tr>
	        <td><span data-kajona-editable="%%strSystemid%%#strName#plain">%%strName%%</span></td>
	        <td><span data-kajona-editable="%%strSystemid%%#strEmail#plain">%%strEmail%%</span></td>
	    </tr>
	</userdirectory_record>    
	
Nice and way better!
You may even have noticed, that the Kajonas' portal-editor is integrated automatically (yeah, those are the data-kajona-editable attributes). So it's even possible to change the records and their properties directly and inline in the page. Wohoo!
	
What now? Let's summarize: We created a module using the ModuleGenerator, changed parts of the model and the templates as well as some properties. The module should be published, shouldn't it? As we've seen earlier, all other modules are available as phar-modules, so how do we generate a new phar-archive out of our module-directory? Head back to the backend and the packagemanagement:

	http://your_kajona_root/index.php?admin=1&module=packagemanager&action=list	
You may have noticed the "generate and download phar-package out of the directory" button present at our modules list entry. Go ahead and click the icon in order to generate a phar our of the module-directory. The phar will be sent to you immediately. 

> Heads up! Your php configuration must have set "phar.readonly" to 0!

The generated filename contains some cryptical suffix - just remove it and name it ```module_userdirectory.phar```. The last step is to publish the package using www.kajona.de in order to make it available to other users, too!