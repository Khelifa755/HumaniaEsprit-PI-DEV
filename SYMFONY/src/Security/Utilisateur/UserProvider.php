<?php

namespace App\Security\Utilisateur;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Loads Utilisateur from DB by email (primary) or username (fallback).
 * Used by Symfony Security for session refresh and the login form.
 */
class UserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly UtilisateurRepository $repository,
    ) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Try email first, then username
        $user = $this->repository->findByEmail($identifier)
             ?? $this->repository->findOneBy(['username' => $identifier]);

        if (!$user) {
            $e = new UserNotFoundException();
            $e->setUserIdentifier($identifier);
            throw $e;
        }
        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(
                sprintf('Invalid user class "%s".', get_class($user))
            );
        }
        $refreshed = $this->repository->find($user->getId());
        if (!$refreshed) {
            $e = new UserNotFoundException();
            $e->setUserIdentifier($user->getUserIdentifier());
            throw $e;
        }
        return $refreshed;
    }

    public function supportsClass(string $class): bool
    {
        return $class === Utilisateur::class || is_subclass_of($class, Utilisateur::class);
    }
}