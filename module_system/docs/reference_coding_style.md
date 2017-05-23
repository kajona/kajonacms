# Coding style

# PHP

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

Every class must have also a php block containing at least the `@package` and `@author` annotation. The doc block must 
have also a general description of the class. In the following an example class doc block:

```
/**
 * My class description
 *
 * @package module_foo
 * @author foo.bar@kajona.de
 */
```

### Methods

Like defined in PSR-2 method names must be in CamelCase with the first character as lowercase. If the method returns a
boolean value it is recommended to use the is/has prefix to indicate the response type.

In case a class implements an interface it is possible to use the `@inheritdoc` annotation to indicate that the 
description of the interface is also valid for this method. If the method has a special behaviour you should avoid the 
`@inheritdoc` annotation and describe this behaviour in a separate php doc block.

## Namespaces

The namespace must start with a vendor name which is in our case `Kajona`. The next part is the module name which is the
CamelCase name of the module (without the module_ prefix). The rest of the path represents the folder structure inside 
the module. In the following as example some class to file mappings:

| Class     | File          |
|-----------|---------------|
| Kajona\News\System\News   | core\module_news\system\News.php |
| Kajona\News\System\Validators\EmailValidator | core\module_news\system\validators\EmailValidator.php |

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
| 4 | private methods | only required if the method is complex |

# Javascript

We use an AMD loader to split up our javascript code base into multiple modules. Each module must be documented using
the [JSDoc] syntax to document the defined functions and objects. For more information take a look at: 
http://usejsdoc.org/howto-amd-modules.html

In the following some examples how to document a module. In case you return a variable you have to use the `@exports`
annotation:

    /**
     * @module mymodule
     */
    define('mymodule', ['dep1', 'dep2'], function (foo, bar) {
    
        /** @exports mymodule */
        var obj = {};
    
        return obj;
    
    });

In case you return directly an object you have to use the `@alias` annotation:

    /**
     * @module mymodule
     */
    define('mymodule', ['dep1', 'dep2'], function (foo, bar) {
    
        return /** @alias module:mymodule */ {
            foo: function(){
                return "bar";
            }
        };
    
    });


[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[JSDoc]: http://usejsdoc.org/

