<?php

namespace Vivait\AuthBundle\EventListener;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Vivait\AuthBundle\Entity\BaseUser;

/**
 * Adapted from https://github.com/KnpLabs/KnpRadBundle/
 *
 * Class PasswordHashListener
 * @package Vivait\AuthBundle\EventListener
 */
class PasswordHashListener implements EventSubscriber
{
    private $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof BaseUser) {
            return;
        }

        $this->updatePasswordHash($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof BaseUser) {
            return;
        }

        $this->updatePasswordHash($entity);

        $args->setNewValue('password', $entity->getPassword());
    }

    private function updatePasswordHash(BaseUser $entity)
    {
        $entity->hashPassword($this->encoderFactory);
        $entity->eraseCredentials();
    }
}