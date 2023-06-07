<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Publication;
use App\Entity\PublicationAccess;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PublicationVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if ($attribute !== 'VIEW') {
            return false;
        }

        // only vote on `Publication` objects
        if (!$subject instanceof Publication) {
            return false;
        }

        return true;
    }


    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        /** @var Publication $publication */
        $publication = $subject;

        // if the publication access is false, allow all users (including anonymous) to view
        if (!$publication->isAccess()) {
            return true;
        }

        // if the user is anonymous, do not grant access to restricted publications
        if (!$user instanceof UserInterface) {
            return false;
        }

        // if the user is admin, always allow access
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        switch ($attribute) {
            case 'VIEW':
                return $this->canView($publication, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }



    private function canView(Publication $publication, User $user)
    {
        // if the user is the author, allow them to view
        if ($publication->getUser() === $user) {
            return true;
        }

        // if the publication access is true, only allow the author and the users in PublicationAccess to view
        if ($publication->isAccess()) {
            $publicationAccesses = $publication->getPublicationAccesses();
            foreach ($publicationAccesses as $publicationAccess) {
                if ($publicationAccess->getUser() === $user) {
                    return true;
                }
            }
        }

        return false;
    }
}
