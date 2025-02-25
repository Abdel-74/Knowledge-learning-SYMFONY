<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Certification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class CertificationController extends AbstractController
{
    /**
     * Affiche la liste des certifications de l'utilisateur.
     *
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP avec les certifications.
     */
    #[Route('/certifications', name: 'user_certifications')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $certifications = $entityManager->getRepository(Certification::class)->findBy(['user' => $user]);

        return $this->render('certification/index.html.twig', [
            'certifications' => $certifications
        ]);
    }
}
