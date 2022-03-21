<?php

namespace App\Controller;

use App\Entity\FoodRecord;
use App\Form\FoodType;
use App\Services\Diary;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

/**
 * @Route ("/diary")
 */
class DiaryController extends AbstractController
{
    /**
     * @Route ("/homepage", name="homepage")
     * @return Response
     */
    public function indexAction(): Response
    {
        return $this->render('diary/index.html.twig');
    }

    /**
     * @Route("/list", name="diary")
     */
    public function listAction(FoodRecordRepository $foodRecordRepository): Response
    {
        return $this->render(
            'diary/list.html.twig',
            [
                'records' => $foodRecordRepository->findBy(
                    [
                        'userId' => $this->getUser()->getId(),
                        'recordedAt' => new \Datetime()
                    ]
                )
            ]
        );
    }

    /**
     * @Route("/add-new-record", name="add-new-record")
     * @throws ORMException
     */
    public function addRecordAction(Request $request, EntityManager $entityManager): RedirectResponse|Response
    {
        $foodRecord = new FoodRecord($this->getUser());
        $form = $this->createForm(FoodType::class, $foodRecord);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $entityManager->persist($foodRecord);
            $entityManager->flush();

            $this->addFlash('success', 'Une nouvelle entrée dans votre journal a bien été ajoutée.');

            return $this->redirectToRoute('add-new-record');
        }

        return $this->render('diary/addRecord.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/record", name="delete-record")
     */
    public function deleteRecordAction(Request $request, FoodRecordRepository $foodRecordRepository, CsrfTokenManager $tokenManager, EntityManager $entityManager): RedirectResponse
    {
        if (!$record = $foodRecordRepository->findOneById($request->request->get('record_id'))) {
            $this->addFlash('danger', "L'entrée du journal n'existe pas.");

            return $this->redirectToRoute('diary');
        }

        $csrf_token = new CsrfToken('delete_record', $request->request->get('_csrf_token'));

        if ($tokenManager->isTokenValid($csrf_token)) {
            $entityManager->remove($record);
            $entityManager->flush();

            $this->addFlash('success', "L'entrée a bien été supprimée du journal.");
        } else {
            $this->addFlash('error', 'An error occurred.');
        }

        return $this->redirectToRoute('diary');
    }

    public function caloriesStatusAction(Diary $diary): Response
    {
        return $this->render(
            'diary/caloriesStatus.html.twig',
            ['remainingCalories' => $diary->getDailyRemainingCalories($this->getUser(), new \Datetime())]
        );
    }
}
