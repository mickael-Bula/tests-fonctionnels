<?php

namespace App\Controller;

use App\Entity\FoodRecord;
use App\Form\FoodType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route ("/diary")
 */
class DiaryController extends AbstractController
{
    /**
     * @Route ("/homepage", name="homepage")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('diary/index.html.twig');
    }

    /**
     * @Route("/list", name="diary")
     */
    public function listAction()
    {
        //dump($this->getUser()); die;

        $repository = $this->getDoctrine()->getRepository('AppBundle:FoodRecord');

        return $this->render(
            'diary/list.html.twig',
            [
                'records' => $repository->findBy(
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
     */
    public function addRecordAction(Request $request)
    {
        $foodRecord = new FoodRecord($this->getUser());
        $form = $this->createForm(FoodType::class, $foodRecord);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($foodRecord);
            $em->flush();

            $this->addFlash('success', 'Une nouvelle entrée dans votre journal a bien été ajoutée.');

            return $this->redirectToRoute('add-new-record');
        }

        return $this->render('diary/addRecord.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/record", name="delete-record")
     */
    public function deleteRecordAction(Request $request)
    {
        if (!$record = $this->getDoctrine()->getRepository('AppBundle:FoodRecord')->findOneById($request->request->get('record_id'))) {
            $this->addFlash('danger', "L'entrée du journal n'existe pas.");

            return $this->redirectToRoute('diary');
        }

        $csrf_token = new CsrfToken('delete_record', $request->request->get('_csrf_token'));

        if ($this->get('security.csrf.token_manager')->isTokenValid($csrf_token)) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($record);
            $em->flush();

            $this->addFlash('success', "L'entrée a bien été supprimée du journal.");
        } else {
            $this->addFlash('error', 'An error occurred.');
        }

        return $this->redirectToRoute('diary');
    }

    public function caloriesStatusAction()
    {
        return $this->render(
            'diary/caloriesStatus.html.twig',
            ['remainingCalories' => $this->get('daily_calories')->getDailyRemainingCalories($this->getUser(), new \Datetime())]
        );
    }
}
