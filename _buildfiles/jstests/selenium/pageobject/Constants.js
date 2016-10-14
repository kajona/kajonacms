"use strict";

/**
 * Class holding constants for the page objects
 *
 */
class Constants {

    //LoginPage
    static get LOGINPAGE_XPATH_CONTAINER() { return "//*[@id='loginContainer_content']";}
    static get LOGINPAGE_XPATH_INPUT_USERNAME() { return Constants.LOGINPAGE_XPATH_CONTAINER + "//*[@id='name']";}
    static get LOGINPAGE_XPATH_INPUT_PASSWORD() { return Constants.LOGINPAGE_XPATH_CONTAINER + "//*[@id='passwort']";}
    static get LOGINPAGE_XPATH_LOGINBUTTON() { return Constants.LOGINPAGE_XPATH_CONTAINER + "/form[1]/div[last()]/div/button";}
    static get LOGINPAGE_XPATH_ERROR_BOX() { return Constants.LOGINPAGE_XPATH_CONTAINER + "/div[@id='loginError']";}


    //MainContent
    static get MAINCONTENT_CSS_MAINCONTENT() { return "div#content";  }


    //LeftNaviation
    static get LEFTNAVIGATION_XPATH_NAVIGATION() { return ".//*[@id='moduleNavigation']";}
    static get LEFTNAVIGATION_XPATH_NAVIGATION_HAMBURGER() { return ".//*[@data-toggle='offcanvas']";}//visible when page width < 932px


    //TopMenu
    static get TOPMENU_XPATH_SEARCHBOX_INPUT() { return "//*[@id='globalSearchInput']";}
    static get TOPMENU_XPATH_SEARCHBOX_LNK_SEARCHRESULTS() { return "//*[@class='detailedResults ui-menu-item']/a";}
    
    static get TOPMENU_XPATH_USERMENU() { return "//*[@class='dropdown userNotificationsDropdown']";}
    static get TOPMENU_XPATH_USERMENU_MESSAGES() { return Constants.TOPMENU_XPATH_USERMENU + "/ul/li[1]/a";}
    static get TOPMENU_XPATH_USERMENU_TAGS() { return Constants.TOPMENU_XPATH_USERMENU + "/ul/li[2]/a";}
    static get TOPMENU_XPATH_USERMENU_HELP() { return Constants.TOPMENU_XPATH_USERMENU + "/ul/li[3]/a";}
    static get TOPMENU_XPATH_USERMENU_MESSAGES_SUBMENU() { return Constants.TOPMENU_XPATH_USERMENU + "//*[@id='messagingShortlist']";}
    static get TOPMENU_XPATH_USERMENU_MESSAGES_LNK_SHOWALLMESAGES() { return Constants.TOPMENU_XPATH_USERMENU_MESSAGES_SUBMENU + "/li[last()]/a";}
    static get TOPMENU_XPATH_USERMENU_LOGOUT_LNK() { return Constants.TOPMENU_XPATH_USERMENU + "/ul/li[last()]/a";}
    
    static get TOPMENU_XPATH_ASPECT_SELECTBOX() { return "//*[@class='navbar navbar-fixed-top']/div[1]/div/div/div[2]/select";}


    //ContentTopBar
    static get CONTENTTOPBAR_CSS_CONTENTTOPBAR() { return "div.contentTopbar";}
    static get CONTENTTOPBAR_ID_TITLE() { return "moduleTitle";}


    //PathNavi
    static get PATHNAVI_CSS_PATHCONTAINER() { return "div.pathNaviContainer";}
    static get PATHNAVI_CSS_BREADCRUMP() { return "ul.breadcrumb";}


    //List
    static get LIST_CSS_ROOT() { return ".table.admintable";}
    static get LIST_CSS_ROWS() { return "tbody > tr:not([data-systemid='batchActionSwitch'])";}
    static get LIST_CSS_BATCHACTIONROW() { return "tbody > tr[data-systemid='batchActionSwitch']";}


    //ListPagination
    static get LISTPAGINATION_CSS_PAGINATION() { return ".pager";}
    static get LISTPAGINATION_CSS_PAGELINKS() { return "li[data-kajona-pagenum]"};
    static get LISTPAGINATION_CSS_TOTALCOUNT() { return "li:last-child"};
    static get LISTPAGINATION_XPATH_FIRSTPAGINATIONELEMENT() { return "following-sibling::div[@class='pager'][1]";}
    //static get _CSS_ADMIN_TABLE_PAGER_PAGE_NEXT() { return "li:nth-last-of-type(2)"};//TODO to be defined


    //Form
    static get FORM_CSS_ROOT() { return "form.form-horizontal";}
    static get FORM_CSS_SAVEBUTTON() { return "button[name=submitbtn]";}
}

module.exports = Constants;