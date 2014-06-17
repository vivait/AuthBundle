Viva IT - Settings Bundle
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

###Add the config rules
Add the following to your config.yml to enable Mopa Bootstrap integration:
```yaml
mopa_bootstrap:
    form:
        show_legend: false
    menu:
        enabled: true
```

Add the following to your config.yml to enable Viva Bootstrap integration:
```yaml
twig:
    form:
        resources:
            - 'VivaitBootstrapBundle:Form:fields.html.twig'
```

You may already have a twig configuration in your config,yml, if this is the case then you should combine the two, e.g.

```yaml
twig:
    debug:            %kernel.debug%
    strict_variables: %kernel.debug%
    form:
        resources:
            - 'VivaitBootstrapBundle:Form:fields.html.twig'
```

