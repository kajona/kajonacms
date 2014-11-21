==== Fig/ Docker for Kajona ====

    - the default settings only work on linux!

************************************************************************************
*** Setup your local environment, get all the software
************************************************************************************

    - install docker.com (build ship and run every app everywhere)
    - install fig.sh (fast isolated development environments using docker)


************************************************************************************
*** Configuration
************************************************************************************

    - edit Dockerfiles if you need a proxy for your network environment

    - call fig and bring the containers up
    # fig -p kajona up -d

    - the webserver of the container is available on the host on
      http://localhost  

    - the debugger is available on localhost:9000

    - database is available on localhost:3306

    - use following parameters for installation or in your config.php
    $config['dbhost']               = 'db';                   //Server name 
    $config['dbusername']           = 'kajona';               //Username 
    $config['dbpassword']           = 'kajona';               //Password 
    $config['dbname']               = 'kajona';               //Database name 
    $config['dbdriver']             = 'mysqli';               //DB-Driver 
    $config['dbprefix']             = 'kajona_';              //Table-prefix 
    $config['dbport']               = '';                     //Database port 

    - call fig to stop your servers
    # fig -p kajona stop
