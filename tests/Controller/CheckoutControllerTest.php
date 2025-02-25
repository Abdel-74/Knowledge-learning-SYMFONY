<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\Theme;
use App\Entity\Purchase;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CheckoutControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);

        // Create a test user
        $user = new User();
        $user->setName('Test User');
        $user->setEmail('buyer@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'securepassword'));
        $user->setRoles(['ROLE_USER']);
        $user->setIsVerified(true);

        $this->entityManager->persist($user);

        // Create a test theme
        $theme = new Theme();
        $theme->setName('Test Theme');
        $this->entityManager->persist($theme);

        // Create a test course
        $course = new Course();
        $course->setName('Test Course');
        $course->setPrice(100);
        $course->setTheme($theme);
        $this->entityManager->persist($course);

        $this->entityManager->flush();
    }

    /**
     * Test the checkout process.
     */
    public function testCheckoutProcess(): void
    {
        // Retrieve the test user
        $user = $this->entityManager->getRepository(User::class)->findOneByEmail('buyer@example.com');
        $course = $this->entityManager->getRepository(Course::class)->findOneByName('Test Course');

        // Log in as the test user
        $this->client->loginUser($user);

        // Add item to cart
        $crawler = $this->client->request('GET', '/cart/add/course/' . $course->getId());
        $this->assertResponseRedirects('/cart');
        $this->client->followRedirect();

        // Proceed to checkout
        $crawler = $this->client->request('GET', '/checkout');
        $this->assertResponseRedirects();
        $this->client->followRedirect();

        // Simulate a successful payment response
        $crawler = $this->client->request('GET', '/payment/success');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Merci pour votre achat !');

        // Verify that the purchase is marked as paid
        $purchase = $this->entityManager->getRepository(Purchase::class)->findOneBy(['user' => $user]);
        $this->assertEquals('completed', $purchase->getStatus());
    }

    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Retrieve the test course
        $course = $this->entityManager->getRepository(Course::class)->findOneByName('Test Course');
        if ($course) {
            // Retrieve purchases associated with this course
            $purchases = $this->entityManager->getRepository(Purchase::class)->findBy(['course' => $course]);
            // Remove the purchases
            foreach ($purchases as $purchase) {
                $this->entityManager->remove($purchase);
            }
            // Remove the course
            $this->entityManager->remove($course);
        }

        // Retrieve the test theme
        $theme = $this->entityManager->getRepository(Theme::class)->findOneByName('Test Theme');
        if ($theme) {
            $this->entityManager->remove($theme);
        }

        // Retrieve the test user
        $user = $this->entityManager->getRepository(User::class)->findOneByEmail('buyer@example.com');
        if ($user) {
            // Retrieve purchases associated with this user
            $purchases = $this->entityManager->getRepository(Purchase::class)->findBy(['user' => $user]);
            // Remove the purchases
            foreach ($purchases as $purchase) {
                $this->entityManager->remove($purchase);
            }
            // Remove the user
            $this->entityManager->remove($user);
        }

        // Apply the deletions
        $this->entityManager->flush();

        // Close the EntityManager to avoid memory leaks
        $this->entityManager->close();
    }
}