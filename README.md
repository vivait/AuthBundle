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
