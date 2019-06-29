<?php


namespace App\Controller;


use App\Entity\Device;
use App\Repository\DeviceRepository;
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
     * @Route("/devices/new", methods={"GET", "POST"})
     */
    public function new(Request $request)
    {

        $form = $this->_getNewDeviceForm();

        $form->handleRequest($request);
        $this->_handleSubmit($form);

        return $this->render('devices/new.html.twig', ['form' => $form->createView()]);
    }

    private function _handleSubmit($form)
    {
        if ($form->isSubmitted() && $form->isValid()) {

            $device = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($device);
            $entityManager->flush();

            return $this->redirectToRoute('devices');
        }
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
}
