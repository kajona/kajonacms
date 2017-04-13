#Working with templates

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_01.jpg&maxWidth=600)


Within this tutorial you will learn how to work with templates in Kajona and how you can customize your Kajona-powered website just by editing a few HTML and CSS files. So you actually don't need any knowledge of PHP programming in order to enhance the portal output (this is what your website visitors will see) with your fancy layouts and styles.

Before we're getting started, it's important you have a running Kajona installation on your local or remote webserver. If you haven't, please have a look at the Kajona Quick Install Guide available at our website.

##The file structure
At first it's useful to understand the file and folder structure of your Kajona installation. For this tutorial the following folders and files are relevant:

```
<kajona_root>
    |- admin
    |- core
    |- files
    |- project
    |- templates
    |    |- default
    |         |- css
    |              - styles.css
    |              - <additional css files>
    |         |- js
    |         |- pics
    |         |- tpl
    |              |- <various. module_xxxx folders>
    |    |- <lateron your template folder will be created right here>

```

###/templates
This is the place were the actual templates are saved. A default pack (folder) is shipped with kajona, your own layouts are stores in additional folders.
Each module got his own folder to have a strict separation between the modules. In addition, some page elements and modules are using templates – they are stored in the subfolders starting with module_.

###/templates/default/css
All your CSS files go here. 

###/templates/default/pics
This is folder containing all public images relevant for the layout.


##Create a new template set
Login at the backend, chose the aspect “Adminstration” from the dropdown at the upper right corner. At the section “Packagemanagement” select the link “Template manager” (you will see the list of installed templates).

By using the buttons at the end of the list you may add new template-packs. Either by uploading them to your Kajona installation, or by creating them from scratch.
Select the button “create a new template-pack” and a list of all modules and elements available at your system is shown. All those elements are available in the default-template pack. Start by giving your template a title, e.g. “mynewtemplate” and activate all modules and elements you want to redesign. If you skip a module or element, the specific template won't be overwritten and will be loaded from the default-pack as a kind of fallback.
Select at least “module_pages”. This is the main element in order to layout your website. The module should be selected by default.

As soon as you click “save”, a new folder “mynewtemplate” or named according to the title you've provided will appear in the filesystem:

```
|- templates
    |    |- default
    |    |- mynewtemplate
    |         |- css
    |         |- js
    |         |- tpl
    |              |- element_date
    |              |- element_image
    |              |- element_paragraph
    |              |- element_plaintext
    |              |- element_row
    |              |- module_pages
    
```

##Let's begin with the major template: module_pages
Residing at module_pages, you'll find three files: home.tpl, master.tpl and standard.tpl. The standard.tpl and the home.tpl files are the ones to modify.

Open the template `/templates/mynewtemplate/tpl/modul_pages/standard.tpl` in your favorite text- or html-editor. Remember to open it using the UTF-8 encoding, otherwise you may get some strange characters in your webbrowser later on.

As you see it's just plain HTML with some Kajona placeholders (%%placeholder_name%%) and kajona blocks.

It includes the base HTML structure with all head-definitions like the page title, loading of CSS files and meta tags.


One thing you should know about placeholders is that they exist in different kinds:

* Regular placeholders
e.g. `%%title%%`
They contain for example the page title or meta description.

* Blocks-element
A blocks-element is used as a container for various block elements. Those will be created later on by the user.
`<kajona-blocks kajona-name="Headline">`

* Block-element
A block element is created within a blocks element and contains the real placeholders. Within a blocks element there may be a unlimited amount of block elements, those elements may be sorted using the backend.
```
	<kajona-blocks kajona-name="Headline">
        <kajona-block kajona-name="Headline">
            <div class="page-header">
                <h1>%%headline_plaintext%%</h1>
            </div>
        </kajona-block>
	</kajona-blocks>
 ```



* Page element placeholders in block-elements 
`%%<placeholdername>_<elementname>%%` e.g. `%%text_headline%%`
The placeholder name is arbitrary, followed by an underscore and the name of the page element (which need to match an existing page element).

* Masterpage element placeholders
`%%master<placeholdername>_<elementname>%%` 
e.g. `%%mastermainnavi_navigation%%`
These placeholders behave the same as usual page element placeholders, but they have to be set on the master page. This is useful for page elements you want to show on every page, e.g. the navigations. Make sure you define the placeholders in the master-template (master.tpl) as well.


> Heads up! The name-attribute of a blocks or block element may only be made out of character from a-z (upper and lower case allowed), numbers and the dash (-) and space ( ) characters. All other characters will lead to errors.


In addition there is the placeholder `%%kajona_head%%` which contains some JavaScript code and the constant `_ webpath _` which contains the URL path of the current system. Also the constant `_ system_browser_cachebuster _` should be added to all references to JavaScript and CSS files, so the system can force the browser to reload all files from the server instead of loading them out of the browsers cache. 



##Modify the template to your needs
So as you now know the basic about placeholders in templates, just edit the demo template (standard.tpl) like you want and save it.
Besides simple changes to the html-structure you want to change the css-styles in nearly every case. We recommend to use the default bootstrap.css file in order to get started and modify the file to your need. Therefore, you have to copy the default file to your template, so from /templates/default/css. In this folder you'll find two files, bootstrap.css and bootstrap.min.css. The contents are the same, but the bootstrap.min.css was minified in order to boost the loading of the website. Use the bootstrap.css as a template:

1. Modify the default styles
Copy the file `/templates/default/css/bootstrap.css` to your template pack: `/templates/mynewtemplate/css/bootstrap.css`. Open the file and change it according to your needs. To make use of the file, you have to update the link to the css file in your templates' header section:
```
	<!-- Template specific stylesheets: CSS and fonts -->
	<link rel="stylesheet" href="_webpath_/templates/mytemplatename/css/bootstrap.css?_system_browser_cachebuster_" type="text/css"/>
``` 


2. Create a new file (recommend)
Create a new css-file at `/templates/mynewtemplate/css` , e.g. `mystyles.css`.
Add the file to the list of style sheets in your template. Add it as the last sheet in order to overwrite the default styles without touching them.
```
	<!-- Template specific stylesheets: CSS and fonts -->
	<link rel="stylesheet" href="_webpath_/templates/default/css/bootstrap.css?_system_browser_cachebuster_" type="text/css"/>
	<link rel="stylesheet" href="_webpath_/templates/mynewtemplate/css/mystyles.css?_system_browser_cachebuster_" type="text/css"/>
```

3. Use only your own style sheets
Create a new css file and add it to your template, replacing all other linked css-files. This is the most flexible way but the most expensive one, too.


Now go into the administration and activate your new template. From the “Packagemanagement” chose the “Template manager” (you will see the list of installed templates) and set your template active.



##Customize the navigation
Copy the  file `https://raw.githubusercontent.com/kajona/kajonacms/master/module_navigation/templates/default/tpl/module_navigation/mainnavi.tpl` to `/templates/mynewtemplate/module_navigation/mainnavi.tpl`.
Open the file `/templates/mynewtemplate/module_navigation/mainnavi.tpl`:


Here you see another cool feature of Kajonas' template engine: template sections.

Some modules like the navigation or news provide different template sections which will be used in different cases – for example the section `<level_1_active>` is used when displaying an active navigation point, `<level_1_inactive>` is used for showing an inactive navigation point.

At the moment, level 2 is added right into the LI-tag of level 1. But we want to show level 2 in a separate box. For this we need a second navigation template. Just create a new file mainnavi2.tpl and edit both files:


`/templates/mynewtemplate/module_navigation/mainnavi.tpl`:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_06.jpg&maxWidth=600)

`/templates/mynewtemplate/module_navigation/mainnavi2.tpl`:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_07.jpg&maxWidth=600)

Just remove the sections and placeholders you don't need, like the `%%level2%%` in `<level_1_active>` in our template for level 1.
In the second template we have some nearly blank sections because we only want to display the entries of level 2 here.


##Introducing the master page
You already learned that you can use masterpage element placeholders in page templates (e.g. `%%mastermainnavi_navigation%%`). Let's see how the masterpage works.
Open `/templates/mynewtemplate/module_pages/master.tpl`:

It's pretty small and only made up of masterpage element placeholders which must match the name you used in other page templates.
Have a look at the page „master“ in the folder „_system“ in the pages administration to see which elements are applied.
So because we want to display level 2 of the main navigation in a separate box and with an own template, we add a second masterpage element placeholder called `%%mastermainnavi2_navigation%%`:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_09.jpg)

Because we already added the placeholder in our page template...
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_10.jpg&maxWidth=450)

...you now just need to add another navigation element on the master page for level 2. Go into the administration, open the master page and add a new navigation element for the placeholder `mastermainnavi2_navigation`. Choose the navigation „mainnavigation“ and select the template "mainnavi2.tpl".

Now your portal should look like this:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_11.jpg&maxWidth=600)


##And what about the news?
You may also want to present your news in a nice individual layout. Nothing easier than this, since you already know what you have to do: edit templates and CSS styles.


Copy the template demo.tpl from `https://github.com/kajona/kajonacms/blob/master/module_news/templates/default/tpl/module_news/demo.tpl` to `/templates/mynewtemplate/module_news/demo.tpl`.

Have a look into `/templates/mynewtemplate/module_news/demo.tpl` and edit it to your needs, e.g.:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_13.jpg&maxWidth=600)

As you see, the structure is similar to the templates of module navigation. And in addition, you can use the dynamic `[lang,key,module]` placeholder-schema to insert language dependent texts out of the language files located in the folder /lang.
For example the placeholder `[lang,news_mehr,news]` will insert the text „[read more]“ which is saved in `/core/module_news/lang/module_news/lang_news_en.php` if the users browser is using an english language setting. Otherwise another language file will be loaded.


##Last words
All templates, css files and graphics used in this tutorial are available under 
http://www.kajonabase.net/Templates/templates.simpleday.fileDetails.52e720850b762e0c6b21.html

The sample layout is based on the layout „Simpleday“ by Igor Jovic (http://www.spinz.se/csstemplates.htm).

Have fun implementing your own individual layouts ;-)