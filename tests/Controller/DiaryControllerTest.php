<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DiaryControllerTest extends WebTestCase
{
    // Le test fonctionnel consiste à lancer une requête pour s'assurer que le controller répond correctement.
    // Pour cela, il faut commencer par créer un client en charge de gérer la requête http.
    private KernelBrowser|null $client = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }
    public function testHomepageIsUp()
    {
        // récupération du Router, de l'EntityManager et de l'utilisateur enregistré en BDD
        $router = $this->client
            ->getContainer()
            ->get('router.default');

        $user = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneByEmail('bula.mickael@gmail.com');

        // Connexion de l'utilisateur à l'API Github
        $this->client->loginUser($user);
        $this->client->request(Request::METHOD_GET, $router->generate('homepage'));

        // Vérification que la réponse renvoie bien un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}