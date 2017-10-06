<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DetailUserVoter extends Voter
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
        if ('CAN_SEE_USER_DETAIL' !== $attribute) {
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
        if (!$this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return false;
        }

        return true;
    }
}