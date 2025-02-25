<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = $this->entityManager->getRepository(User::class);
    }

    /**
     * Test user registration.
     */
    public function testUserRegistration(): void
    {
        $crawler = $this->client->request('GET', '/register');

        // Verify that the registration page is accessible
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Register');

        // Retrieve the form and fill it with test data
        $form = $crawler->selectButton('Register')->form([
            'registration_form[name]' => 'Test User',
            'registration_form[email]' => 'test@example.com',
            'registration_form[plainPassword]' => 'password123',
            'registration_form[agreeTerms]' => true,
        ]);

        // Submit the form
        $this->client->submit($form);

        // Verify that the user has been created in the database
        $user = $this->userRepository->findOneByEmail('test@example.com');

        $this->assertNotNull($user);
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertFalse($user->isVerified()); // The user is not yet verified
    }

    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up the database after the test
        $user = $this->userRepository->findOneByEmail('test@example.com');
        if ($user) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        // Close the EntityManager to avoid memory leaks
        $this->entityManager->close();
    }
}