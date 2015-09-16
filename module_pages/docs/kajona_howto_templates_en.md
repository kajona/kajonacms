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
    |              |- <various. element_xxxx folders>
    |              |- <various. module_xxxx folders>
    |    |- <lateron your template folder will be created right here>

```

###/templates
This is the place were the actual templates are saved. A default pack (folder) is shipped with kajona, your own layouts are stores in additional folders.
Each module got his own folder to have a strict separation between the modules. In addition, some page elements are using templates – they are stored in the subfolders starting with element_.

###/templates/default/css
All your CSS files go here. It's recommended to use the sample file styles.css as a base to get started because it includes some default style definitions. Nevertheless, please don't change the file directly. When updating your Kajona installation, the modifications may be lost.
Adoptions to the file are made in a separate copy.

###/templates/default/pics
This is folder containing all public images relevant for the layout.


##Create a new template set
Login at the backend, chose the aspect “management” from the dropdown at the upper right corner. Select the link “installed templates” at the section “packagemanagement”. The system will show you a list of template-packs currently available and installed.

By using the buttons at the end of list you may add new template-packs. Either by uploading them to your Kajona installation, or by creating them from scratch.
Select the button “create a new template-pack” and a list of all modules and elements available at your system is shown. All those elements are available in the default-template pack. Start by giving your template a title, e.g. “mynewtemplate” and activate all modules and elements your want to redesign. If you skip a module or element, the specific template won't be overwritten and will be loaded from the default-pack as a kind of fallback.
Select a least “module_pages”. This is the main element in order to layout your webseite. The module should be selected by default.

As soon as you click “save”, a new folder “mynewtemplate” or named according to the title you've provided will appear in the filesystem:

```
|- templates
    |    |- default
    |    |- mynewtemplate
    |         |- css
    |         |- js
    |         |- tpl
    |              |- element_image
    |              |- element_paragraph
    |              |- element_row
    |              |- module_pages
    
```

Let's begin with the major template: module_pages
Residing at module_pages, you'll find two files: master.tpl and standard.tpl. The standard.tpl file is the one to modify. Or, even better: copy the file and customize it for special pages such as the welcome-page.
Examples:

* home.tpl		the welcome page
* standard.tpl		regular content pages
* guestbook.tpl		a special, funky guestbook.
* ...

Open the template `/templates/mynewtemplate/tpl/modul_pages/standard.tpl` in your favorite text- or html-editor. Remember to open it using the UTF-8 encoding, otherwise you may get some strange characters in your webbrowser later on.
The original template should look like this one:

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_02.jpg&maxWidth=600)

As you see it's just plain HTML with some Kajona placeholders (%%placeholder_name%%).

It includes the base HTML structure with all head-definitions like the page title, loading of CSS files and meta tags.



One thing you should know about placeholders is that they exist in three different kinds:

* Regular placeholders
e.g. %%title%%
They contain for example the page title or meta description.
Page element placeholders

* %%\<placeholdername\>\_\<elementname\>%% e.g. %%text\_headline%%
The placeholder name is arbitrary, followed by an underscore and the name of the page element (which need to match an existing page element).
Use a pipe to allow different page elements at one placeholder,
e.g. %%text\_paragraph|image%%.

* Masterpage element placeholders
%%master\<placeholdername\>\_\<elementname\>%%
e.g. %%mastermainnavi\_navigation%%
These placeholders behave the same as usual page element placeholders, but they have to be set on the master page. This is useful for page elements you want to show on every page, e.g. the navigations. Make sure you also define the placeholders in the master-template (master.tpl).

In addition there is the placeholder `%%kajona_head%%` which contains some JavaScript code and the constant _ webpath _ which contains the URL path of the current system. Also the constant _ system_browser_cachebuster _ should be added to all references to JavaScript and CSS files, so the system can force the browser to reload all files from the server instead of loading them out of the browsers cache. 
Have a look into the file /project/portal/global_includes.php to add new or edit existing static placeholders. If the file doesn't exist, copy it from  

`/core/module_pages/portal/global_includes.php` to 

`/project/portal/global_includes.php`.

Please have a look into the manual of module pages for a list of available regular placeholders and to learn more about the templates including the way the masterpage works in detail.

##Modify the template to your needs
So as you now know the basic about placeholders in templates, just edit the demo template like you want and save it.
Besides simple changes to the html-structure you want to change the css-styles in nearly every case. We recommened to use the default styles.css file in order to get started and modify the file to your need. Therefore, you have to copy the default file to your template, so from /templates/default/css. In this folder you'll find two files, styles.css and styles-full.css. The contents are the same, but the styles.css was minified in order to boost the loading of the website. Use the styles-full.css as a template:

1. Modify the default styles
Copy the file /templates/default/css/styles.css to your template pack: /templates/mynewtemplate/css/styles.css. Open the file and change it according to your needs. To make use of the file, you have to update the link to the css file in your templates' header section:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_03.jpg&maxWidth=600)


2. Create a new file (recommend)
Create a new css-file at /templates/mynewtemplate/css , e.g. MyStyles.css.
Add the file to the list of style sheets in your template. Add it as the last sheet in order to overwrite the default styles without touching them.
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_04.jpg&maxWidth=600)

3. Use only your own style sheets
Create a new css file and add it to your template, replacing all other linked css-files. This is the most flexible way but the most expensive one, too.



Now go into the administration and activate your new template. Chose the list of “installed template” from the “packagemanagement” and set your template active.




##Customize the navigation
Copy the  file `/core/module_navigation/templates/default/tpl/module_navigation/mainnavi.tpl` to `/templates/mynewtemplate/module_navigation/mainnavi.tpl`.
Open the file `/templates/mynewtemplate/module_navigation/mainnavi.tpl:

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_05.jpg&maxWidth=600)

Here you see another cool feature of Kajonas' template engine: template sections.

Some modules like the navigation or news provide different template sections which will be used in different cases – for example the section \<level\_1_\active\> is used when displaying an active navigation point, \<level\_1\_inactive\> is used for showing an inactive navigation point.

At the moment, level 2 is added right into the LI-tag of level 1. But we want to show level 2 in a separate box. For this we need a second navigation template. Just create a new file mainnavi2.tpl and edit both files:


/templates/mynewtemplate/module_navigation/mainnavi.tpl:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_06.jpg&maxWidth=600)

/templates/mynewtemplate/module_navigation/mainnavi2.tpl:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_07.jpg&maxWidth=600)

Just remove the sections and placeholders you don't need, like the %%level2%% in \<level\_1\_active\> in our template for level 1.
In the second template we have some nearly blank sections because we only want to display the entries of level 2 here.


##Introducing the master page
You already learned that you can use masterpage element placeholders in page templates (e.g. %%mastermainnavi\_navigation%%). Let's see how the masterpage works.
Open `/templates/mynewtemplate/module_pages/master.tpl`:

It's pretty small and only made up of masterpage element placeholders which must match the name you used in other page templates.
Have a look into the page „master“ in the folder „_system“ in the pages administration to see which elements are applied.
So because we want to display level 2 of the main navigation in a separate box and with a own template, we add a second masterpage element placeholder called %%mastermainnavi2_navigation%%:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_09.jpg)

Because we already added the placeholder in our page template...
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_10.jpg&maxWidth=450)

...you now just need to add another navigation element on the master page for level 2. Go into the administration, open the master page and add a new navigation element for the placeholder mastermainnavi2\_navigation. Choose the navigation „mainnavigation“ and select the template mainnavi2.tpl.

Now your portal should look like this:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_11.jpg&maxWidth=600)


##And what about the news?
You may also want to present your news in a nice individual layout. Nothing easier than this, since you already know what you have to do: edit templates and CSS styles.


Copy the template demo.tpl from `/core/module_news/templates/default/tpl/module_news/demo.tpl` to `/templates/mynewtemplate/module_news/demo.tpl`.
Have a look into `/templates/mynewtemplate/module_news/demo.tpl` and edit it to your needs, e.g.:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_13.jpg&maxWidth=600)

As you see, the structure is similar to the templates of module navigation. And in addition, you can use the dynamic [lang,key,module] placeholder-schema to insert language dependent texts out of the language files located in the folder /lang.
For example the placeholder [lang,news\_mehr,news] will insert the text „[read more]“ which is saved in `/core/module_news/lang/module_news/lang_news_en.php` if the users browser is using am english language setting. Otherwise another language file will be loaded.


##Last words
All templates, css files and graphics used in this tutorial are available under 
http://www.kajonabase.net/Templates/templates.simpleday.fileDetails.52e720850b762e0c6b21.html

The sample layout is based on the layout „Simpleday“ by Igor Jovic (http://www.spinz.se/csstemplates.htm).

Have fun implementing your own individual layouts ;-)