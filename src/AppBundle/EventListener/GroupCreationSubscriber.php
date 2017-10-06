<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Group;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GroupCreationSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::GROUP_CREATE_SUCCESS => 'onGroupCreateSuccess',
        ];
    }

    public function onGroupCreateSuccess(FormEvent $event)
    {
        /** @var Group $group */
        $group = $event->getForm()->getData();

        $name = $this->textToUnderscore($group->getName());
        $role = 'ROLE_'.strtoupper($this->wd_remove_accents($name));

        $group->setRoles([$role]);
    }

    private function wd_remove_accents($str, $charset='utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
    }

    private function textToUnderscore($string, $us = "_") {
        // If spaced name
        if (strpos($string, ' ')) {
            return preg_replace('/\s+/', $us, $string);
        }
        // If camelCased name
        return preg_replace(
            '/(?<=\d)(?=[A-Za-z])|(?<=[A-Za-z])(?=\d)|(?<=[a-z])(?=[A-Z])/', $us, $string);
    }
}