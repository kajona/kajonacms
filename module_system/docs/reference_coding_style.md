# Coding style

All classes should follow the [PSR-2] coding style.

## Naming conventions

Interfaces must have the suffix `Interface` and traits the suffix `Trait`. Normal and abstract classes have no special 
marker.

| Type      | Example       |
|-----------|---------------|
| Class     | News          |
| Abstract  | BasicNews     |
| Interface | NewsInterface |
| Trait     | NewsTrait     |

In general classes should have a descriptive name so that users can easily find the class and understand the function of 
it. Here some example of good class names:

- NewsAdminController
- NewsRecordDeletedListener
- NewsEventProvider
- NewsFeed
- News
- LockManager
- Mail
- FileSystem

The class `NewsAdminController` has the `News` prefix since `AdminController` is difficult to find because almost every 
module has an `AdminController`. The class `LockManager` has no prefix because it is very unlikely that we have many 
lock managers.

### Methods

Like defined in PSR-2 method names must be in CamelCase with the first character as lowercase. If the method returns a
boolean value it is recommended to use the is/has prefix to indicate the response type.

## Namespaces

The namespace must start with a vendor name which is in our case `Kajona`. The next part is the module name which is the
CamelCase name of the module (without the module_ prefix). The rest of the path represents the folder structure inside 
the module. In the following as example some class to file mappings:

| Class     | File          |
|-----------|---------------|
| Kajona\News\System\News   | core\module_news\system\News.php |
| Kajona\News\System\Validators\EmailValidator | core\module_news\system\Validators\EmailValidator.php |

## Folder names

It is possible to create arbitrary folder hierarchies inside a module. Folders inside the `system/`
folder should have plural names.

## Comments

Writing comments help other developers to understand a class or method. The following table
shows for which components you should/must write detailed php comments. With comments we mean
a text describing the behaviour, comments which describe the parameters and types must be
always added.

| Priority | Description   | Required          |
|----------|---------------|--------------------
| 1 | interfaces and abstract methods | yes |
| 2 | classes and public methods | yes |
| 3 | protected methods | yes |
| 4 | private methods | no |

## Legacy

If you want convert an existing module into namespaced classes you can move all legacy classes into the the /legacy 
folder of the module. The old class can then extend the new namespaced class. Through this the old class still exists in 
case other modules needs the class. In example if you want to refactor the class News of the News module you would 
create a new class:

> module_news/system/News.php

    <?php
    
    namespace Kajona\News\System;
    
    class News
    {
        // ...
    }

The News class contains then all logic from the old class.

> module_news/legacy/class_module_news_news.php
    
    <?php
    
    use Kajona\News\System\News;
    
    /**
     * @deprecated 
     */
    class class_module_news_news extends News
    {
    }


[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md

