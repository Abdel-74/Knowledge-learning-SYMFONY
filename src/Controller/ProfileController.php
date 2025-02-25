<?php

namespace App\Controller;

use App\Repository\PurchaseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    /**
     * Affiche les leçons achetées par l'utilisateur.
     *
     * @param PurchaseRepository $purchaseRepository Le repository des achats.
     * @return Response La réponse HTTP avec la liste des leçons achetées.
     */
    #[Route('/mes-lecons', name: 'user_lessons')]
    public function userLessons(PurchaseRepository $purchaseRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // Récupérer les leçons achetées
        $purchases = $purchaseRepository->findBy(['user' => $user, 'status' => 'completed']);
        $lessons = [];

        foreach ($purchases as $purchase) {
            if ($purchase->getLesson()) {
                $lessons[] = $purchase->getLesson();
            } elseif ($purchase->getCourse()) {
                foreach ($purchase->getCourse()->getLessons() as $lesson) {
                    $lessons[] = $lesson;
                }
            }
        }

        return $this->render('profile/lessons.html.twig', [
            'lessons' => $lessons
        ]);
    }
}
