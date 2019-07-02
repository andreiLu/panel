<?php


namespace App\Controller;


use App\Entity\Device;
use App\Entity\User;
use App\Repository\DeviceRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DevicesController extends AbstractController
{

    /**
     * @Route("/devices", name="devices", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function index()
    {
        $devices = $this->getDoctrine()
            ->getRepository(Device::class)
            ->findAll();

        return $this->render('devices/index.html.twig', [
            'devices' => $devices,
        ]);
    }

    /**
     * @Route("/devices/new", name="new_device", methods={"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function new(Request $request)
    {

        $form = $this->_getNewDeviceForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->_handleSubmit($form);

            return $this->redirectToRoute('devices');
        }


        return $this->render('devices/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/devices/{device}/assign", name="assign_device", methods={"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function assign(Device $device, Request $request)
    {

        $form = $this->_getAssignForm($device);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->_handleSubmit($form);

            return $this->redirectToRoute('devices');
        }

        return $this->render('devices/assign.html.twig', [
            'form' => $form->createView(),
            'device' => $device
        ]);
    }

    /**
     * @Route("/devices/{device}/remove", name="remove_device", methods={"GET", "PUT"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function remove(Device $device, Request $request)
    {

        $form = $this->_getRemoveForm($device);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $device->setOwner(null);

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($device);
            $entityManager->flush();

            return $this->redirectToRoute('devices');
        }

        return $this->render('devices/remove.html.twig', [
            'device' => $device,
            'form' => $form->createView()
        ]);
    }

    /**
     * Handle form submit
     *
     * @param $form
     */
    private function _handleSubmit($form)
    {
        $device = $form->getData();
        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($device);
        $entityManager->flush();
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function _getNewDeviceForm()
    {
        $names = DeviceRepository::getAvailableDeviceNames();
        $device = new Device();
        $form = $this->createFormBuilder($device)
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
                'type',
                ChoiceType::class,
                [
                    'choices' => DeviceRepository::getAvailableDeviceTypes(),
                    'choice_label' => function ($option, $key, $value) use ($names) {
                        return array_key_exists($value, $names) ? $names[$value] : strtoupper($value);
                    },
                    'attr' => ['class' => 'form-control']
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                ['label' => 'Add Device'])
            ->getForm();

        return $form;
    }

    /**
     * Get assign device form
     *
     * @param Device $device
     * @return \Symfony\Component\Form\FormInterface
     */
    private function _getAssignForm(Device $device)
    {

        $form = $this->createFormBuilder($device)
            ->add('owner', EntityType::class, [
                'class' => User::class
            ])
            ->add(
                'save',
                SubmitType::class,
                ['label' => 'Assign Device'])
            ->getForm();

        return $form;
    }

    /**
     * Get remove device form
     *
     * @param Device $device
     * @return \Symfony\Component\Form\FormInterface
     */
    private function _getRemoveForm(Device $device)
    {
        $form = $this->createFormBuilder($device)
            ->setMethod('PUT')
            ->add(
                'save',
                SubmitType::class,
                ['label' => 'Remove Device'])
            ->getForm();

        return $form;
    }
}
