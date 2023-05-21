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
        // Définition du Client, duRouter et de l'utilisateur enregistré en BDD
        $this->client = static::createClient();

        // Fait en sorte que le Crawler suive toutes les redirections, ce qui évite d'avoir à le faire pour chaque requête
        $this->client->followRedirects();

        $this->router = $this->client
            ->getContainer()
            ->get('router.default');

        $this->user = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneByEmail('bula.mickael@gmail.com');

        // Connexion de l'utilisateur à l'API Github
        $this->client->loginUser($this->user);
    }
    public function testHomepageIsUp()
    {
        $this->client->request(Request::METHOD_GET, $this->router->generate('homepage'));

        // Vérification que la réponse renvoie bien un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testHomePageWithCrawler()
    {
        // récupération du Crawler retourné par la réponse du client
        $crawler = $this->client->request(Request::METHOD_GET, $this->router->generate('homepage'));

        // Vérifie qu'il n'y a qu'un seul titre de niveau H1
        $countH1 = $crawler
            ->filter('h1')
            ->count();

        $this->assertSame(1, $countH1);

        // Vérification que la réponse contient un titre de niveau H1 avec le contenu 'bienvenue sur FoodDiary !'
        $title = $crawler
            ->filter('h1')
            ->text();

        $this->assertSame('Bienvenue sur FoodDiary !', $title);
    }

    /**
     * Ceci est le test original du tutoriel
     *
     * @return void
     */
    public function testAddRecord(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, $this->router->generate('add-new-record'));
        $form = $crawler->selectButton('Enregistrer')->form();
        $form['food[entitled]'] = 'Plat de pâtes';
        $form['food[calories]'] = 600;
        $this->client->submit($form);

        $this->assertSelectorTextContains('div.alert.alert-success','Une nouvelle entrée dans votre journal a bien été ajoutée');
    }

    /**
     * Ceci est la reprise du test précédent, pour lequel j'ai modifié
     * la manière de récupérer le formulaire (par son nom plutôt que par le bouton
     *
     * @return void
     */
    public function testFormSubmission(): void
    {
        // requête sur la page du formulaire à partir de son nom déclaré dans DiaryController
        $crawler = $this->client->request(Request::METHOD_POST, $this->router->generate('add-new-record'));

        // Vérification que la réponse renvoie bien un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // récupération du formulaire
        $form = $crawler->filter('form[name="food"]');

        // vérifie que le formulaire se nomme bien 'food'
        $formName = $form->attr('name');
        $this->assertSame('food', $formName);

        // création d'un formulaire avec la méthode Crawler::form() en vue de soumettre des données
        $testForm = $form->form();

        // hydratation du formulaire
        $testForm['food[entitled]'] = 'Plat de nouilles';
        $testForm['food[calories]'] = 300;

        // soumission du formulaire
        $this->client->submit($testForm);
        // $this->client->followRedirect(); // Permet de suivre une redirection

        $this->assertSelectorTextContains('div.alert.alert-success','Une nouvelle entrée dans votre journal a bien été ajoutée');
    }
}