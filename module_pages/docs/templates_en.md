#Templates

When building a website with Kajona, it's almost everything about templates. Templates control the appearance of your site and will make the page unique within the crowd.

In general, templates are used to separate the content from the layout. This means, you don't need to know anything about php, databases or other technical stuff, it's just all up to html, css and maybe some js magic. Kajona required no special template language, syntax or some else, strange things.

Kajona v1 to v4 had their own way of handling templates, but v5 makes this all new. Since Kajona v5, it's all about blocks, block and placeholder elements.

##A page template

So, let's have a look at a simple, but comprehensive page-template (see the full version at [GitHub](https://github.com/kajona/kajonacms/blob/master/module_pages/templates/default/tpl/module_pages/standard.tpl)):

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="description" content="%%description%%"/>
    <meta name="keywords" content="%%keywords%%"/>
    <meta name="robots" content="index, follow"/>
    <title>%%additionalTitle%%%%title%% | Kajona</title>
    <link rel="canonical" href="%%canonicalUrl%%"/>
    <link rel="stylesheet" href="_webpath_/templates/default/css/bootstrap.min.css?_system_browser_cachebuster_" type="text/css"/>
    <!-- IMPORTANT! Include the kajona_head!! This injects jQuery, too-->
    %%kajona_head%%
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-3">%%mastermainnavi_navigation%%</div>
        <div class="col-sm-9">

            <kajona-blocks kajona-name="Headline">
            
                <kajona-block kajona-name="Headline">
                    <h1>%%headline_plaintext%%</h1>
                </kajona-block>

            </kajona-blocks>

            <kajona-blocks kajona-name="Page Intro">

                <kajona-block kajona-name="Header and Text">
                    <h3>%%headline_plaintext%%</h3>
                    <p>%%content_richtext%%</p>
                </kajona-block>

                <kajona-block kajona-name="Two Columns Header and Text">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3>%%headlineleft_plaintext%%</h3>
                            <p>%%contentleft_richtext%%</p>
                        </div>
                        <div class="col-sm-6">
                            <h3>%%headlineright_plaintext%%</h3>
                            <p>%%contentright_richtext%%</p>
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Text Only">
                    <p>%%content_richtext%%</p>
                </kajona-block>

            </kajona-blocks>
		</div>
	</div>
</div>
</body>
</html>
```

As you hopefully noticed, the templates is plain html in most cases. Enriched with some special Kajona-flavour: placeholders, constants, blocks and block. Oh, and a special case of a placeholder, the master-page-placeholder (we'll get to this later on).

##blocks and block
In the templates' body section, all relevant parts are wrapped in blocks-elements. A blocks element is used as a brace around a list of possible block elements. Blocks are fixed within their order and are tight to their position within the page-template. 

Each blocks element should contain at least a single block element. In most cases, a list of possible block elements will be placed inside the blocks element. See a single block as an option the page-admin may fill with content. To have a common wording, we'll now say the page-admin will create an instance of the block as soon as the block is filled with content using Kajona.
A block may be instantiated repeatably, to more then once within the same blocks. In addition, different block elements may be shifted within the blocks elements.

Let's strip this down to an example. In our page-template we have the following blocks and block elements:

| blocks | block | 
|--------|-------|---|
| Headline | Headline |
| Page Intro | Header and Text, Two Columns Header and Text, Text Only |

Formally, a blocks-element is created using a kajona-blocks tag:

	<kajona-blocks kajona-name="blocks name"> [kajona-block] </kajona-blocks>
	
Containing a kajona-block:	

	<kajona-block kajona-name="block name"> [markup, placeholder] </kajona-blocks>

So, if the template is used for a new page, the page-admin has to following options: 
* To create a headline block within the headline block
* To create on or more of the block elements within the page intro block

Possible usages of the structure could be:

	Headline
		Headline
	Page Intro
		Header and Text
		Text Only
		Two Columns Header and Text
		Text Only
		
or

	PageIntro
		Header and Text
		
or
	
	Headline
		Headline
	Page Intro
		Text Only
		Text Only
		Header and Text
		Text Only
		Two Columns Header and Text
		Two Columns Header and Text
		Text Only
		
Got it? Great! Now, when looking into the details of a block, you may have noticed the weird looking placeholders, e.g. ```%%headline_plaintext%%``` or ```%%content_richtext%%```.

> Heads up! The name-attribute of a blocks or block element may only be made out of character from a-z (upper and lower case allowed), numbers and the dash (-) and space ( ) characters. All other characters will lead to errors.

##Placeholders	

A placeholder consists of three/four fragments: the leading and opening and closing chars, a name and an element-reference: ```%%[name]_[element]%%```
The name may be chosen freely in order to identify the placeholder in the backend. The element must be the (technical) name of an element currently available in your Kajona installation. A list of installed elements is available in the backend, module pages action page-elements. Therefor our ```Text only```block from the example above provides the possibility / obligation to fill a ```%%content_richtext%%``` placeholder.

##Element and module templates

As we've seen above, a placeholder is some kind of link to contents created using the backend. In most cases this is a simple element such as plaintext or formatted rich text, but it could also be a complex element such as a image gallery or a contact form.

Looking at a simple element such as a plaintext element, formatting is an easy one. Just wrap the placeholder ```%%name_plaintext%%``` with some tags and classes and style the layout directly in the page-template, e.g.

	<div class="pageintro">%%name_plaintext%%</div>
	
However, when looking at more complex element such as a gallery, the styling goes beyond adding a wrapper-tag around the placeholder. For complex elements, Kajona makes use of "element templates" or "module templates". This means, when editing a page-element, you are able to chose the layout of the element from a list of available templates. Let's look at an example, the faqs template (full version is available on [Github](https://github.com/kajona/kajonacms/blob/master/module_faqs/templates/default/tpl/module_faqs/demo.tpl)): 

	<faqs_list>
	    <div class="faqsList">
	        %%faq_categories%%
	    </div>
	</faqs_list>
	
	<faq_category>
	    <div class="faqCategory">
	        <div class="faqCategoryTitle"><h3 data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</h3></div>
	        <div>
	            %%faq_faqs%%
	        </div>
	    </div>
	</faq_category>
	
	<faq_faq>
	    <div class="panel panel-default">
	        <div class="panel-heading">
	            <span class="pull-right">%%faq_rating%%</span>
	            <h4 class="panel-title">
	                <span data-kajona-editable="%%strSystemid%%#strQuestion#plain">%%strQuestion%%</span>
	            </h4>
	        </div>
	        <div id="collapse%%strSystemid%%" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading%%strSystemid%%">
	            <span data-kajona-editable="%%strSystemid%%#strAnswer">%%strAnswer%%</span>
	        </div>
	    </div>
	</faq_faq>	
	
As you may have noticed, the template is made out of three sections

* ```faqs_list``` A wrapper around the whole list of faqs to be rendered. The placeholder ```faq_categories``` is replaced with the categories and their faqs afterwards.
* ```faqs_category``` A wrapper for the current category of faqs. Useful to render the name of the category or some markup to make a categories' entries visible or hidden. This section is called for each category to be shown in the portal. The placeholder```faq_faqs```is replaced by the list of assigned faqs afterwards.
* ```faqs_faq``` Finally, the section for a single faq entry. Renders the question and the answer and whatever markup you may need.

> Heads up! Since Kajona V5 ships modules as phar-archives, you may need to add the faqs-template to your templatepack in order to start modifying it. Copy the template from Github to ```/templates/your_template_name/tpl/module_faqs/demo.tpl``` or add a new template located at ```/templates/your_template_name/tpl/module_faqs/newfaqlayout.tpl```.


##Master template and placeholder
So just when you thought you're set up to go writing your own template, we have another thing for you: a mystical master-page with special placeholders. 

Let's face the following scenario: You designed a modern template and start to fill your pages. Naturally, each page needs some common elements such as a navigation or maybe a search-box. A possible solution could be to add the matching placeholder to your page-template: ```%%navi_navigation%%``` and ```%%quicksearch_search%%```. Works, but is a real pain: The placeholders need to be filled on each page and dare to forget it once. In order to avoid those redundant elements and placeholders, Kajona comes with a feature called master-page.

The master page is called "master" - what a surprise - and makes use of a template called ```master.tpl```.

The default template contains something like this (see the [Github](https://github.com/kajona/kajonacms/blob/master/module_pages/templates/default/tpl/module_pages/master.tpl) version):

	%%mastermainnavi_navigation%%
	%%masterportalnavi_navigation%%
	%%masterpathnavi_navigation%%
	%%masterlanguageswitch_languageswitch%%
	%%mastertopnews_news%%
	%%mastersearch_search%%
	
So, the template is made of placeholders only - no markup, no blocks. The only difference is, that each placeholder is prefixed with ```master```. The placeholders are filled using the backend but are not visible by default, the master-page is not viewable directly in the portal.

To make use of those special elements, the name of the placeholder needs to be added to the "real" page-template using exactly the same name as on the ```master.tpl``` template. Looking back at our introducing template, the part 

	 <div class="col-sm-3">%%mastermainnavi_navigation%%</div>
	 
does exactly the same thing. The placeholder is prefixed with ```master``` and uses the same name as the placeholder on the master-template. Kajona is now able to match the placeholder from the master page with the placeholder from the concrete page and merges them. The prefix ```master``` is therefore reserved for this special purpose, they are removed from the list of possible placeholders when editing a concrete page using the backend.