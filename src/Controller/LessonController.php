<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Entity\Certification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LessonController extends AbstractController
{
    /**
     * Affiche les détails d'une leçon.
     *
     * @param Lesson $lesson La leçon à afficher.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response La réponse HTTP avec les détails de la leçon.
     */
    #[Route('/lesson/{id}', name: 'lesson_detail')]
    public function lessonDetail(Lesson $lesson, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voir cette leçon.');
            return $this->redirectToRoute('app_login');
        }

        $hasAccess = $em->getRepository(\App\Entity\Purchase::class)->findOneBy([
            'user' => $user,
            'lesson' => $lesson,
            'status' => 'completed'
        ]);

        if (!$hasAccess) {
            $hasAccess = $em->getRepository(\App\Entity\Purchase::class)->findOneBy([
                'user' => $user,
                'course' => $lesson->getCourse(),
                'status' => 'completed'
            ]);
        }

        if (!$hasAccess) {
            $this->addFlash('error', 'Vous n’avez pas accès à cette leçon.');
            return $this->redirectToRoute('course_detail', ['id' => $lesson->getCourse()->getId()]);
        }

        return $this->render('lesson/detail.html.twig', [
            'lesson' => $lesson
        ]);
    }

    /**
     * Valide une leçon.
     *
     * @param Lesson $lesson La leçon à valider.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response La réponse HTTP de redirection avec l'Id de la leçon.
     */
    #[Route('/lesson/validate/{id}', name: 'validate_lesson', methods: ['POST'])]
    public function validateLesson(Lesson $lesson, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_login');
        }

        $purchase = $em->getRepository(Purchase::class)->findOneBy([
            'user' => $user,
            'lesson' => $lesson,
            'status' => 'completed'
        ]);

        if (!$purchase) {
            $purchase = $em->getRepository(Purchase::class)->findOneBy([
                'user' => $user,
                'course' => $lesson->getCourse(),
                'status' => 'completed'
            ]);
        }

        if (!$purchase) {
            $this->addFlash('error', 'Vous n’avez pas accès à cette leçon.');
            return $this->redirectToRoute('course_detail', ['id' => $lesson->getCourse()->getId()]);
        }
        
        //Add validated lesson to User
        $user->addValidatedLesson($lesson);
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Leçon validée avec succès !');
        return $this->redirectToRoute('lesson_detail', ['id' => $lesson->getId()]);

        // Verify if all lessons are validated
        $theme = $lesson->getCourse()->getTheme();
        $allCourses = $theme->getCourses();
        $allLessons = [];

        foreach ($allCourses as $course) {
            $allLessons = array_merge($allLessons, $course->getLessons()->toArray());
        }

        $validatedLessons = $user->getValidatedLessons()->filter(fn($l) => $l->getCourse()->getTheme() === $theme);

        if (count($allLessons) === count($validatedLessons)) {
            $certification = new Certification();
            $certification->setUser($user);
            $certification->setTheme($theme);
            $certification->setDateObtained(new \DateTime());

            $em->persist($certification);
            $em->flush();

            $this->addFlash('success', 'Félicitations ! Vous avez obtenu la certification Knowledge Learning pour le thème ' . $theme->getName());
        }

        return $this->redirectToRoute('lesson_detail', ['id' => $lesson->getId()]);
    }
}
