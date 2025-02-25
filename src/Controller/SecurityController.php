<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * Gère la connexion de l'utilisateur.
     *
     * @param AuthenticationUtils $authenticationUtils Le service d'authentification.
     * @return Response La réponse HTTP avec le formulaire de connexion.
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * Gère la déconnexion de l'utilisateur.
     *
     * @throws \Exception Cette méthode ne doit jamais être atteinte.
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {
        throw new \Exception('This should never be reached!');
    }

    /**
     * Active le compte de l'utilisateur via un token.
     *
     * @param string $token Le token d'activation.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités.
     * @return Response La réponse HTTP de redirection.
     */
    #[Route('/activate/{token}', name: 'app_activate')]
    public function activate($token, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['activationToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('Ce token est invalide.');
        }

        $user->setIsVerified(true);
        $user->setActivationToken(null);
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte a été activé avec succès.');

        return $this->redirectToRoute('app_login');
    }
}
