<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CourseController extends AbstractController
{
    /**
     * Affiche les détails d'un cursus.
     *
     * @param Course $course Le cursus à afficher.
     * @param PurchaseRepository $purchaseRepository Répository des achats.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response La réponse HTTP avec les détails du cursus.
     */
    #[Route('/course/{id}', name: 'course_detail')]
    public function detail(Course $course, PurchaseRepository $purchaseRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Récupérer les leçons associées à ce cursus
        $lessons = $course->getLessons();

        // Vérifier si l'utilisateur a acheté chaque leçon
        $userPurchases = [];
        if ($user) {
            $purchases = $em->getRepository(Purchase::class)->findBy([
                'user' => $this->getUser(),
                'status' => 'completed' 
            ]);
        
            foreach ($purchases as $purchase) {
                if ($purchase->getLesson()) {
                    $userPurchases[$purchase->getLesson()->getId()] = true;
                }
                if ($purchase->getCourse()) {
                    $userPurchases[$purchase->getCourse()->getId()] = true;
        
                    foreach ($purchase->getCourse()->getLessons() as $lesson) {
                        $userPurchases[$lesson->getId()] = true;
                    }
                }
            }
        }

        return $this->render('course/detail.html.twig', [
            'course' => $course,
            'lessons' => $lessons,
            'userPurchases' => $userPurchases,
        ]);
    }
}