Viva IT - Authentication Bundle
============

Installation
------------
###Using composer
``` bash
$ composer require viviat/auth-bundle
```

###Enabling bundle
``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Vivait\AuthBundle\VivaitAuthBundle()
    );
}
```

###Add routing rules
Add the routing to your ```app\config\routing.yml``` file:
```yaml
vivait_auth:
    resource: "@VivaitAuthBundle/Resources/config/routing.yml"
    prefix:   /
```

###Add the security rules
Replace the contents of your ```app/config/security.yml``` with the contents of [security.yml](Resources/config/security.yml).

In your new ```security.yml```, change the following config option to be the route of your application homepage:
```yaml
    firewalls:
        secured_area:
            form_login:
                default_target_path: viva_app_homepage
```
