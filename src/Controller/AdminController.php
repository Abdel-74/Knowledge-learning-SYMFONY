<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Theme;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Form\UserType;
use App\Form\ThemeType;
use App\Form\CourseType;
use App\Form\LessonType;
use App\Form\PurchaseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'app_admin_')]
class AdminController extends AbstractController
{
    /**
     * Affiche le tableau de bord de l'administrateur.
     *
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP avec les données du tableau de bord.
     */
    #[Route('/', name: 'dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        $themes = $entityManager->getRepository(Theme::class)->findAll();
        $courses = $entityManager->getRepository(Course::class)->findAll();
        $lessons = $entityManager->getRepository(Lesson::class)->findAll();
        $purchases = $entityManager->getRepository(Purchase::class)->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
            'themes' => $themes,
            'courses' => $courses,
            'lessons' => $lessons,
            'purchases' => $purchases,
        ]);
    }

    /**
     * Affiche la liste des utilisateurs.
     *
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP avec la liste des utilisateurs.
     */
    #[Route('/users', name: 'users')]
    public function manageUsers(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        return $this->render('admin/users.html.twig', ['users' => $users]);
    }

    /**
     * Modifie un utilisateur.
     *
     * @param User $user L'utilisateur à modifier.
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP avec le formulaire de modification.
     */
    #[Route('/user/edit/{id}', name: 'user_edit')]
    public function editUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/edit_user.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Supprime un utilisateur.
     *
     * @param User $user L'utilisateur à supprimer.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP de redirection.
     */
    #[Route('/user/delete/{id}', name: 'user_delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        $purchases = $entityManager->getRepository(Purchase::class)->findBy(['user' => $user]);

        foreach ($purchases as $purchase) {
            $entityManager->remove($purchase);
        }
    
        $entityManager->remove($user);
        $entityManager->flush();
        return $this->redirectToRoute('app_admin_users');
    }

    /**
     * Affiche la liste des thèmes.
     *
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP avec la liste des thèmes.
     */
    #[Route('/themes', name: 'themes')]
    public function manageThemes(EntityManagerInterface $entityManager): Response
    {
        $themes = $entityManager->getRepository(Theme::class)->findAll();
        return $this->render('admin/themes.html.twig', ['themes' => $themes]);
    }

    /**
     * Modifie un thème.
     *
     * @param Theme $theme le thème à modifier.
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP avec le formulaire de modification.
     */
    #[Route('/theme/edit/{id}', name: 'theme_edit')]
    public function editTheme(Theme $theme, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_admin_themes');
        }

        return $this->render('admin/edit_theme.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Supprime un thème.
     *
     * @param Theme $theme le thème à supprimer.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP de redirection.
     */
    #[Route('/theme/delete/{id}', name: 'theme_delete', methods: ['POST'])]
    public function deleteTheme(Theme $theme, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($theme);
        $entityManager->flush();
        return $this->redirectToRoute('app_admin_themes');
    }

    /**
     * Affiche la liste des cursus.
     *
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP avec la liste des cursus.
     */
    #[Route('/courses', name: 'courses')]
    public function manageCourses(EntityManagerInterface $entityManager): Response
    {
        $courses = $entityManager->getRepository(Course::class)->findAll();
        return $this->render('admin/courses.html.twig', ['courses' => $courses]);
    }

    /**
     * Modifie un cursus.
     *
     * @param Cursus $cursus le cursus à modifier.
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP avec le formulaire de modification.
     */
    #[Route('/course/edit/{id}', name: 'course_edit')]
    public function editCourse(Course $course, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_admin_courses');
        }

        return $this->render('admin/edit_course.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Supprime un cursus.
     *
     * @param Course $course le cursus à supprimer.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP de redirection.
     */
    #[Route('/course/delete/{id}', name: 'course_delete', methods: ['POST'])]
    public function deleteCourse(Course $course, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($course);
        $entityManager->flush();
        return $this->redirectToRoute('app_admin_courses');
    }

    /**
     * Affiche la liste des leçons.
     *
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP avec la liste des leçons.
     */
    #[Route('/lessons', name: 'lessons')]
    public function manageLessons(EntityManagerInterface $entityManager): Response
    {
        $lessons = $entityManager->getRepository(Lesson::class)->findAll();
        return $this->render('admin/lessons.html.twig', ['lessons' => $lessons]);
    }

    /**
     * Modifie un utilisateur.
     *
     * @param Lesson $lesson la leçon à modifier.
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP avec le formulaire de modification.
     */
    #[Route('/lesson/edit/{id}', name: 'lesson_edit')]
    public function editLesson(Lesson $lesson, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_admin_lessons');
        }

        return $this->render('admin/edit_lesson.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Supprime une leçon.
     *
     * @param Lesson $lesson la leçon à supprimer.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP de redirection.
     */
    #[Route('/lesson/delete/{id}', name: 'lesson_delete', methods: ['POST'])]
    public function deleteLesson(Lesson $lesson, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($lesson);
        $entityManager->flush();
        return $this->redirectToRoute('app_admin_lessons');
    }

    /**
     * Affiche la liste des achats.
     *
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP avec la liste des achats.
     */
    #[Route('/purchases', name: 'purchases')]
    public function managePurchase(EntityManagerInterface $entityManager): Response
    {
        $purchases = $entityManager->getRepository(Purchase::class)->findAll();
        return $this->render('admin/purchases.html.twig', ['purchases' => $purchases]);
    }

    /**
     * Modifie un achat.
     *
     * @param Purchase $purchase l'achat à modifier.
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP avec le formulaire de modification.
     */
    #[Route('/purchase/edit/{id}', name: 'purchase_edit')]
    public function editPurchase(Purchase $purchase, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PurchaseType::class, $purchase);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_admin_purchases');
        }

        return $this->render('admin/edit_purchase.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Supprime un achat.
     *
     * @param Pourchase $pourchase l'achat à supprimer.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités.
     * @return Response La réponse HTTP de redirection.
     */
    #[Route('/purchase/delete/{id}', name: 'purchase_delete', methods: ['POST'])]
    public function deletePurchase(Purchase $purchase, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($purchase);
        $entityManager->flush();
        return $this->redirectToRoute('app_admin_purchases');
    }
}
