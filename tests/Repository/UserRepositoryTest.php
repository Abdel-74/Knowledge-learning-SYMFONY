<?php

namespace App\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\Purchase;
use Doctrine\ORM\EntityManagerInterface;

class UserRepositoryTest extends WebTestCase
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
     * Test finding a user by email.
     */
    public function testFindByEmail(): void
    {
        // Retrieve the user via the repository
        $userRepository = $this->entityManager->getRepository(User::class);
        $foundUser = $userRepository->findOneByEmail('testuser@example.com');

        // Verify that the user was found and the properties match
        $this->assertNotNull($foundUser);
        $this->assertEquals('testuser@example.com', $foundUser->getEmail());
        $this->assertEquals('Test User', $foundUser->getName());
        $this->assertEquals(['ROLE_USER'], $foundUser->getRoles());
        $this->assertTrue($foundUser->isVerified());
    }

    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}