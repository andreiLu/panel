<?php

namespace App\Controller;

use App\Entity\User;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersController extends AbstractController
{
    protected $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @Route("/users", methods={"GET"})
     */
    public function index()
    {

        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        return $this->render('users/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/users/new", methods={"GET", "POST"})
     */
    public function new(Request $request)
    {
        $form = $this->_getNewUserForm();

        $form->handleRequest($request);
        $this->_handleSubmit($form);

        return $this->render('users/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @param $form
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    private function _handleSubmit($form)
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $password = $this->encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($password);
            $user->setCreatedAt(new DateTime('now'));
            $user->setUpdatedAt(new DateTime('now'));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('users');
        }
    }

    /**
     * Create the html for new user form object
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function _getNewUserForm()
    {
        $user = new User();
        $form = $this->createFormBuilder($user)
            ->add(
                'email',
                EmailType::class,
                ['attr' => ['class' => 'form-control']]
            )
            ->add(
                'username',
                TextType::class,
                ['attr' => ['class' => 'form-control']]
            )
            ->add(
                'first_name',
                TextType::class,
                ['attr' => ['class' => 'form-control']]
            )
            ->add(
                'last_name',
                TextType::class,
                ['attr' => ['class' => 'form-control']]
            )
            ->add(
                'password',
                PasswordType::class,
                ['attr' => ['class' => 'form-control']]
            )
            ->add(
                'save',
                SubmitType::class,
                ['label' => 'Add User'])
            ->getForm();

        return $form;
    }

    /**
     * @param Request $request
     * @Route("/users/new", methods={"POST"})
     */
//    public function create(Request $request)
//    {
//
//        $entityManager = $this->getDoctrine()->getManager();
//        $user = new User();
//        $user->setEmail($request->request->get('email'));
//        $user->setUsername($request->request->get('username'));
//        $user->setPassword($request->request->get('password'));
//        $user->setFirstName($request->request->get('first_name'));
//        $user->setLastName($request->request->get('last_Name'));
//        $user->setPassword(1999);
//
//        $password = $this->encoder->encodePassword($user, $request->request->get('password'));
//        $user->setPassword($password);
//
//        dd($user);
//
//        $entityManager->persist($user);
//
//
//    }

    /**
     * @Route("/users/{$user}/edit", methods={"PUT"})
     */
    public function update($user)
    {

    }

    /**
     * @Route("/users/{$user}/delete", methods={"PUT"})
     */
    public function delete($user)
    {

    }

}
