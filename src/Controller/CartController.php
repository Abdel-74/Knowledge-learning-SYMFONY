<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CartController extends AbstractController
{   
    /**
     * Affiche le panier de l'utilisateur.
     *
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response La réponse HTTP avec les éléments du panier.
     */
    #[Route('/cart', name: 'cart_show')]
    #[IsGranted('ROLE_USER')]
    public function show(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $cartItems = $em->getRepository(Purchase::class)->findBy([
            'user' => $user,
            'isPaid' => false,
        ]);

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
        ]);
    }

    /**
     * Ajoute un cursus au panier.
     *
     * @param Course $course Le cursus à ajouter.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response La réponse HTTP de redirection.
     */
    #[Route('/cart/add/course/{id}', name: 'cart_add_course')]
    #[IsGranted('ROLE_USER')]
    public function addCourse(Course $course, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setCourse($course);
        $purchase->setStatus('pending');

        $em->persist($purchase);
        $em->flush();

        $this->addFlash('success', 'Cursus ajouté au panier.');
        return $this->redirectToRoute('cart_show');
    }

    /**
     * Ajoute une leçon au panier.
     *
     * @param Lesson $lesson La leçon à ajouter.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response La réponse HTTP de redirection.
     */
    #[Route('/cart/add/lesson/{id}', name: 'cart_add_lesson')]
    #[IsGranted('ROLE_USER')]
    public function addLesson(Lesson $lesson, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setLesson($lesson);
        $purchase->setStatus('pending');

        $em->persist($purchase);
        $em->flush();

        $this->addFlash('success', 'Leçon ajoutée au panier.');
        return $this->redirectToRoute('cart_show');
    }

    /**
     * Supprime un article du panier.
     *
     * @param Pourchase $pourchase L'article à supprimer.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response La réponse HTTP de redirection.
     */
    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    #[IsGranted('ROLE_USER')]
    public function remove(Purchase $purchase, EntityManagerInterface $em): Response
    {
        $em->remove($purchase);
        $em->flush();

        $this->addFlash('success', 'Article retiré du panier.');
        return $this->redirectToRoute('cart_show');
    }
}
