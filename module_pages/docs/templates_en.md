#Templates

When building a website with Kajona, it's almost everything about templates. Templates control the appearance of your site and will make the page unique within the crowd.

In general, templates are used to separate the content from the layout. This means, you don't need to know anything about php, databases or other technical stuff, it's just all up to html, css and maybe some js magic. Kajona required no special template language, syntax or some else, strange things.

Kajona v1 to v4 had their own way of handling templates, but v5 makes this all new. Since Kajona v5, it's all about blocks, block and placeholder elements.

#A page template

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

#blocks and block
In the templates' body section, all relevant parts are wrapped in blocks-elements. A blocks element is used as a brace around a list of possible block elements. Blocks are fixed within their order and are tight to their position within the page-template. 

Each blocks element should contain at least a single block element. In most cases, a list of possible block elements will be placed inside the blocks element. See a single block as an option the page-admin may fill with content. To have a common wording, we'll now say the page-admin will create an instance of the block as soon as the block is filled with content using Kajona.
A block may be instantiated repeatably, to more then once within the same blocks. In addition, different block elements may be shifted within the blocks elements.

Let's strip this down to an example. In our page-template we have the following blocks amd block elements:

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

#Placeholders	

A placeholder consists of three/four fragments: the leading and opening and closing chars, a name and an element-reference: ```%%[name]_[element]%%```
The name may be chosen freely in order to identify the placeholder in the backend. The element must be the (technical) name of an element currently available in your Kajona installation. A list of installed elements is available in the backend, module pages action page-elements. Therefor our ```Text only```block from the example above provides the possibility / obligation to fill a ```%%content_richtext%%``` placeholder.

#Element templates

