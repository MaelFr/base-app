<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EditUserVoter extends Voter
{
    /** @var AccessDecisionManagerInterface */
    private $decisionManager;

    /**
     * EditUserVoter constructor.
     *
     * @param AccessDecisionManagerInterface $decisionManager
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    /**
     * @inheritdoc
     */
    protected function supports($attribute, $subject)
    {
        if ('CAN_EDIT_USER' !== $attribute) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // ROLE_SUPER_ADMIN can do anything
        if ($this->decisionManager->decide($token, ['ROLE_SUPER_ADMIN'])) {
            return true;
        }

        // Checks if user is logged in AND has ROLE_ADMIN
        if (!$user instanceof User
            || !$this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return false;
        }

        /** @var User $editedUser */
        $editedUser = $subject;
        // Checks if edited user is SuperAdmin
        if ($editedUser->isSuperAdmin()) {
            return false;
        }

        return true;
    }
}