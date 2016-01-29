# DI Container

In the 5.0 release Kajona introduced a DI container. That means we have a standard way for a module to provide services
which are globally available for the system. In the following chapter we will explain more detailed what a service is
and how you can use it inside Kajona. Note the following description of a service and DI container was copied from the 
Symfony documentation (http://symfony.com/doc/current/book/service_container.html).

## What is a Service?

Put simply, a Service is any PHP object that performs some sort of "global" task. It's a purposefully-generic name used 
in computer science to describe an object that's created for a specific purpose (e.g. delivering emails). Each service 
is used throughout your application whenever you need the specific functionality it provides. You don't have to do 
anything special to make a service: simply write a PHP class with some code that accomplishes a specific task. 
Congratulations, you've just created a service!

## What is a Service Container?

A Service Container (or dependency injection container) is simply a PHP object that manages the instantiation of 
services (i.e. objects). For example, suppose you have a simple PHP class that delivers email messages. Without a 
service container, you must manually create the object whenever you need it:

    use AppBundle\Mailer;
    
    $mailer = new Mailer('sendmail');
    $mailer->send('ryan@example.com', ...);

This is easy enough. The imaginary Mailer class allows you to configure the method used to deliver the email messages 
(e.g. sendmail, smtp, etc). But what if you wanted to use the mailer service somewhere else? You certainly don't want to 
repeat the mailer configuration every time you need to use the Mailer object. What if you needed to change the transport 
from sendmail to smtp everywhere in the application? You'd need to hunt down every place you create a Mailer service and 
change it.

## Provide a service

In order to provide a service in your module you have to define a class called `ServiceProvider` in the system folder.
This class implements the `Pimple\ServiceProviderInterface` interface. On startup the system calls the `register` method
where you can add any service to the container. I.e. if our module wants to provide a logger service we could use the 
following `ServiceProvider` class:

    namespace Kajona\Acme\System;
    
    use Pimple\Container;
    use Pimple\ServiceProviderInterface;
    
    /**
     * @package Kajona\Acme\System
     */
    class ServiceProvider implements ServiceProviderInterface
    {
        public function register(Container $objContainer)
        {
            $objContainer['logger'] = function($c){
                return new Logger();
            };
        }
    }

## Using services inside a controller or workflow

Inside a controller we can use the `@Inject` annotation to retrieve the logger instance.

    class PagesAdminController extends class_admin_simple implements interface_admin
    {
        /**
         * @Inject logger
         */
        protected $logger;
        
        protected function actionTest()
        {
            $this->logger->info("Something just happened!");
        }
    }
