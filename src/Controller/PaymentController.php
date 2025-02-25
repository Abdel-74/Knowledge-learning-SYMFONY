<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class PaymentController extends AbstractController
{
    /**
     * Ajoute un cursus au panier pour achat.
     *
     * @param Course $course Le cursus à acheter.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response La réponse HTTP de redirection.
     */
    #[Route('/purchase/course/{id}', name: 'purchase_course')]
    #[IsGranted('ROLE_USER')]
    public function purchaseCourse(Course $course, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setCourse($course);
        $purchase->setStatus('pending');

        $em->persist($purchase);
        $em->flush();

        return $this->redirectToRoute('cart_show');
    }

    /**
     * Ajoute une leçon au panier pour achat.
     *
     * @param Lesson $lesson La leçon à acheter.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response La réponse HTTP de redirection.
     */
    #[Route('/purchase/lesson/{id}', name: 'purchase_lesson')]
    #[IsGranted('ROLE_USER')]
    public function purchaseLesson(Lesson $lesson, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setLesson($lesson);
        $purchase->setStatus('pending');

        $em->persist($purchase);
        $em->flush();

        return $this->redirectToRoute('cart_show');
    }

    /**
     * Initialise le processus de paiement en créant une session de paiement Stripe.
     *
     * Cette méthode récupère les articles du panier de l'utilisateur connecté qui n'ont pas encore été payés.
     *
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @param UrlGeneratorInterface $urlGenerator Générateur d'URL.
     * @return Response La réponse HTTP redirigeant vers l'interface de paiement Stripe.
     */
    #[Route('/checkout', name: 'checkout')]
    #[IsGranted('ROLE_USER')]
    public function checkout(EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): Response
    {
        $user = $this->getUser();
        $cartItems = $em->getRepository(Purchase::class)->findBy([
            'user' => $user,
            'isPaid' => false,
        ]);

        if (empty($cartItems)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_show');
        }

        Stripe::setApiKey($this->getParameter('stripe.secret_key'));

        $lineItems = [];
        foreach ($cartItems as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item->getCourse() ? $item->getCourse()->getName() : $item->getLesson()->getName(),
                    ],
                    'unit_amount' => ($item->getCourse() ? $item->getCourse()->getPrice() : $item->getLesson()->getPrice()) * 100,
                ],
                'quantity' => 1,
            ];
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $urlGenerator->generate('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $urlGenerator->generate('cart_show', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($session->url, 303);
    }

    /**
     * Gère le retour après un paiement réussi.
     *
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response La réponse HTTP.
     */
    #[Route('/payment/success', name: 'payment_success')]
    #[IsGranted('ROLE_USER')]
    public function paymentSuccess(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $cartItems = $em->getRepository(Purchase::class)->findBy([
            'user' => $user,
            'isPaid' => false,
        ]);

        foreach ($cartItems as $purchase) {
            $purchase->setIsPaid(true);
            $purchase->setStatus('completed');
        }

        $em->flush();

        $this->addFlash('success', 'Paiement réussi ! Vous avez accès aux cours et leçons.');
        return $this->render('payment/success.html.twig');
    }
}