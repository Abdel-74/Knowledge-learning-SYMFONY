<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);

        // Create a test user if it doesn't already exist
        $user = $this->userRepository->findOneByEmail('test@example.com');

        if (!$user) {
            $user = new User();
            $user->setEmail('test@example.com');
            $user->setName('Test User');
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(true);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    /**
     * Test user login with valid credentials.
     */
    public function testUserLogin(): void
    {
        $crawler = $this->client->request('GET', '/login');

        // Verify that the login page is accessible
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Please sign in');

        // Retrieve the form and fill it with test data
        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Submit the form
        $this->client->submit($form);

        // Verify that the response is a redirect to the home page
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        // Verify that the user is logged in
        $this->assertSelectorTextContains('h1', 'Bienvenue sur Knowledge Learning');
    }

    /**
     * Test user login with invalid credentials.
     */
    public function testUserLoginWithInvalidCredentials(): void
    {
        $crawler = $this->client->request('GET', '/login');

        // Verify that the login page is accessible
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Please sign in');

        // Retrieve the form and fill it with invalid data
        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Submit the form
        $this->client->submit($form);

        // Verify that the response is a redirect to the login page
        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();

        // Verify that the error message is displayed
        $this->assertSelectorTextContains('.alert-danger', 'Invalid credentials.');
    }

    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up the database after the tests
        $user = $this->userRepository->findOneByEmail('test@example.com');
        if ($user) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        // Close the EntityManager to avoid memory leaks
        $this->entityManager->close();
    }
}