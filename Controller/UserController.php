<?php

namespace BisonLab\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use BisonLab\UserBundle\Entity\User;
use BisonLab\UserBundle\Form\UserType;
use BisonLab\UserBundle\Repository\UserRepository;
use BisonLab\UserBundle\Lib\ExternalEntityConfig;

/**
 * @Route("/bisonlab_user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="bisonlab_user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        // I'll use the (current)User objects own checks. That makes the
        // application using this bundle able to use whatever role names they
        // want.
        if (!$admin_user = $this->getUser())
            throw $this->createAccessDeniedException("No access for you");
        if (!$admin_user->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        return $this->render('@BisonLabUser/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="bisonlab_user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$admin_user = $this->getUser())
            throw $this->createAccessDeniedException("No access for you");
        if (!$admin_user->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            // Gotta reset password, but something must be in.
            $user->setPassword(uniqid());
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('bisonlab_user_index');
        }

        return $this->render('@BisonLabUser/user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="bisonlab_user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        if (!$admin_user = $this->getUser())
            throw $this->createAccessDeniedException("No access for you");
        if (!$admin_user->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        return $this->render('@BisonLabUser/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="bisonlab_user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        if (!$admin_user = $this->getUser())
            throw $this->createAccessDeniedException("No access for you");
        if (!$admin_user->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('bisonlab_user_index');
        }

        return $this->render('@BisonLabUser/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="bisonlab_user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if (!$admin_user = $this->getUser())
            throw $this->createAccessDeniedException("No access for you");
        if (!$admin_user->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('bisonlab_user_index');
    }
}
