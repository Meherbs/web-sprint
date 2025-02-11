<?php

namespace App\Controller;

use App\Data\SearchData2;
use App\Data\SearchStay;
use App\Service\UploaderService;
use App\Entity\Stay;
use App\Entity\User;
use App\Form\Advancedresearch;
use App\Form\SearchFormType2;
use App\Form\StayType;
use App\Repository\StayRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/stay")
 */
class StayController extends AbstractController
{
    /**
     * @Route("/", name="app_stay_index", methods={"GET"})
     */
    public function index(StayRepository $stayRepository, Request $request): Response
    {

        $data = new SearchData2();
        $form = $this->createForm(SearchFormType2::class, $data);
        $form->handleRequest($request);
        $stays = $stayRepository->findSearch($data);




        return $this->render('stay/index.html.twig', [
            'stays' => $stays,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/new", name="app_stay_new", methods={"GET", "POST"})
     */
    public function new(Request $request, StayRepository $stayRepository): Response
    {
        $stay = new Stay();
        $form = $this->createForm(StayType::class, $stay);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            #test here
            $photo = $form->get('photo')->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            $uploads_directory = $this->getParameter('kernel.project_dir') . '/public/uploads';
            $filename = md5(uniqid()) . '.' . $photo->guessExtension();
            $photo->move(
                $uploads_directory,
                $filename
            );
            $stay->setPhoto($filename);

            $stayRepository->add($stay);
            return $this->redirectToRoute('app_stay_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stay/new.html.twig', [
            'stay' => $stay,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_stay_show", methods={"GET"})
     */
    public function show(Stay $stay): Response
    {
        return $this->render('stay/show.html.twig', [
            'stay' => $stay
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_stay_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Stay $stay, StayRepository $stayRepository): Response
    {
        $form = $this->createForm(StayType::class, $stay);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stayRepository->add($stay);
            return $this->redirectToRoute('app_stay_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stay/edit.html.twig', [
            'stay' => $stay,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_stay_delete", methods={"POST"})
     */
    public function delete(Request $request, Stay $stay, StayRepository $stayRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $stay->getId(), $request->request->get('_token'))) {
            $stayRepository->remove($stay);
        }

        return $this->redirectToRoute('app_stay_index', [], Response::HTTP_SEE_OTHER);
    }
}
