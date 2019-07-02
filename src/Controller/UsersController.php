<?php

namespace App\Controller;

use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Route("/", name="hompage", methods={"GET"})
     */
    public function homepage()
    {
        return $this->redirectToRoute('users');
    }

    /**
     * @Route("/users", name="users", methods={"GET"})
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
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/users/new", name="new_user", methods={"GET", "POST"})
     */
    public function new(Request $request)
    {
        $form = $this->_getNewUserForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->_handleSubmit($form);

            return $this->redirectToRoute('users');
        }

        return $this->render('users/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route( "users/{user}/update", name="update_user", methods={"GET", "PUT"} )
     */
    public function update(User $user, Request $request)
    {

        $form = $this->_getEditUserForm($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->_handleSubmit($form);

            return $this->redirectToRoute('users');
        }

        return $this->render('users/update.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/users/{user}/delete", name="delete_user", methods={"GET", "DELETE"})
     */
    public function delete(User $user, Request $request)
    {
        $form = $this->_getDeleterForm($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            /**
             * @todo find a better way of doing this
             *
             * Remove all assigned devices
             */
            foreach ($user->getDevice()->getValues() as $device) {
                $device->setOwner(null);

                $entityManager->persist($device);
                $entityManager->flush();
            }

            $entityManager->remove($user);
            $entityManager->flush();

            return $this->redirectToRoute('users');
        }

        return $this->render('users/delete.html.twig', [
            'users' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param $form
     */
    private function _handleSubmit($form)
    {
        $user = $form->getData();
        $password = $this->encoder->encodePassword($user, $user->getPassword());
        $entityManager = $this->getDoctrine()->getManager();

        $user->setPassword($password);
        $entityManager->persist($user);
        $entityManager->flush();
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
                'firstname',
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
     * Get edit user form
     *
     * @param User $user
     * @return \Symfony\Component\Form\FormInterface
     */
    private function _getEditUserForm(User $user)
    {

        $form = $this->createFormBuilder($user)
            ->setMethod('PUT')
            ->add(
                'email',
                EmailType::class,
                ['attr' => ['class' => 'form-control'],]
            )
            ->add(
                'username',
                TextType::class,
                ['attr' => ['class' => 'form-control']]
            )
            ->add(
                'firstname',
                TextType::class,
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
     * Get delete user form
     *
     * @param User $user
     * @return \Symfony\Component\Form\FormInterface
     */
    private function _getDeleterForm(User $user)
    {
        $form = $this->createFormBuilder($user)
            ->setMethod('DELETE')
            ->add(
                'save',
                SubmitType::class,
                ['label' => 'Delete user'])
            ->getForm();

        return $form;
    }
}
