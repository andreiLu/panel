<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\Program;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProgramsController extends AbstractController
{
    /**
     * @Route("/programs", name="programs")
     */
    public function index()
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        return $this->render('programs/index.html.twig', [
            'programs' => $programs,
        ]);
    }


    /**
     * @Route("/programs/new", name="new_program", methods={"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function new(Request $request)
    {

        $form = $this->_getNewProgramForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->_handleSubmit($form);

            return $this->redirectToRoute('programs');
        }

        return $this->render('programs/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/programs/{program}/assign", name="assign_program", methods={"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function assign(Program $program, Request $request)
    {
        $form = $this->_getAssignForm($program);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->_handleSubmit($form);

            return $this->redirectToRoute('programs');
        }

        return $this->render('programs/assign.html.twig', [
            'form' => $form->createView(),
            'program' => $program
        ]);
    }

    /**
     * @Route("/programs/{program}/remove", name="remove_program", methods={"GET", "PUT"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function remove(Program $program, Request $request)
    {

        $form = $this->_getRemoveForm($program);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $program->setDevice(null);

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($program);
            $entityManager->flush();

            return $this->redirectToRoute('programs');
        }

        return $this->render('programs/remove.html.twig', [
            'program' => $program,
            'form' => $form->createView()
        ]);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function _getNewProgramForm()
    {
        $program = new Program();
        $form = $this->createFormBuilder($program)
            ->add(
                'name',
                TextType::class,
                ['attr' => ['class' => 'form-control']]
            )
            ->add(
                'description',
                TextType::class,
                ['attr' => ['class' => 'form-control']]
            )
            ->add(
                'save',
                SubmitType::class,
                ['label' => 'Add Program'])
            ->getForm();

        return $form;
    }

    /**
     * Handle form submit
     *
     * @param $form
     */
    private function _handleSubmit($form)
    {
        $program = $form->getData();
        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($program);
        $entityManager->flush();

    }

    private function _getAssignForm(Program $program)
    {

        $form = $this->createFormBuilder($program)
            ->add('device', EntityType::class, [
                'class' => Device::class
            ])
            ->add(
                'save',
                SubmitType::class,
                ['label' => 'Assign Program'])
            ->getForm();

        return $form;
    }

    /**
     * Get remove device form
     *
     * @param Program $program
     * @return \Symfony\Component\Form\FormInterface
     */
    private function _getRemoveForm(Program $program)
    {
        $form = $this->createFormBuilder($program)
            ->setMethod('PUT')
            ->add(
                'save',
                SubmitType::class,
                ['label' => 'Remove program'])
            ->getForm();

        return $form;
    }
}
