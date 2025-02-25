<?php

namespace App\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Purchase;
use App\Entity\User;
use App\Entity\Theme;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;

class PurchaseRepositoryTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        // Create a test user if it doesn't already exist
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneByEmail('testuser@example.com');

        if (!$user) {
            $user = new User();
            $user->setEmail('testuser@example.com');
            $user->setPassword('password123');
            $user->setName('Test User');
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(true);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    /**
     * Test finding purchases by user.
     */
    public function testFindPurchasesByUser(): void
    {
        // Retrieve the test user
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneByEmail('testuser@example.com');

        // Create a test theme
        $theme = new Theme();
        $theme->setName('Test Theme');
        $this->entityManager->persist($theme);
        $this->entityManager->flush();

        // Create a test course
        $course = new Course();
        $course->setName('Test Course');
        $course->setTheme($theme);
        $course->setPrice(100);

        // Create a test purchase
        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setCourse($course);
        $purchase->setLesson(null);
        $purchase->setStatus('pending');
        $purchase->setIsPaid(false);

        // Persist the entities in the database
        $this->entityManager->persist($course);
        $this->entityManager->persist($purchase);
        $this->entityManager->flush();

        // Retrieve the purchases for the user via the repository
        $purchaseRepository = $this->entityManager->getRepository(Purchase::class);
        $purchases = $purchaseRepository->findBy(['user' => $user]);

        // Verify that the purchase was found and the relationships are correct
        $this->assertEquals('Test Course', $purchases[0]->getCourse()->getName());
        $this->assertEquals('testuser@example.com', $purchases[0]->getUser()->getEmail());
    }

    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}