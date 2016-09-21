
# Wizard

The wizard module helps to build wizard forms. The following example combines the
news and news category form into a wizard:

```php
<?php

$objWizardManager = new \Kajona\Wizard\System\WizardManager(
    \Kajona\System\System\Database::getInstance(),
    $this->objSession,
    $this->objToolkit
);

$objWizardManager->addPage(
    "news",
    new \Kajona\Wizard\System\WizardPage(
        \Kajona\News\System\NewsNews::class,
        \Kajona\System\Admin\AdminFormgenerator::BIT_BUTTON_CONTINUE,
        "News"
    )
);

$objWizardManager->addPage(
    "category",
    new \Kajona\Wizard\System\WizardPage(
        \Kajona\News\System\NewsCategory::class,
        \Kajona\System\Admin\AdminFormgenerator::BIT_BUTTON_CONTINUE | \Kajona\System\Admin\AdminFormgenerator::BIT_BUTTON_BACK,
        "Category"
    )
);

return $objWizardManager->actionWizard(
    \Kajona\System\System\Link::getLinkAdminHref($this->getArrModule("modul"), "wizard"),
    function($strUrl){
        $this->adminReload($strUrl);
    },
    function(){
        $this->adminReload(\Kajona\System\System\Link::getLinkAdminHref($this->getArrModule("modul"), "list"));
    }
);
```
