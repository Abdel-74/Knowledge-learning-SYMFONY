<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ThemeController extends AbstractController
{
    /**
     * Affiche les détails d'un thème et les cursus associés.
     *
     * @param Theme $theme Le thème à afficher.
     * @param CourseRepository $courseRepository Le repository des cursus.
     * @return Response La réponse HTTP avec le thème et les cursus associés.
     */
    #[Route('/theme/{id}', name: 'theme_detail')]
    public function detail(Theme $theme, CourseRepository $courseRepository): Response
    {
        $courses = $courseRepository->findBy(['theme' => $theme]);

        return $this->render('theme/detail.html.twig', [
            'theme' => $theme,
            'courses' => $courses,
        ]);
    }
}