<?php

namespace BisonLab\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

use BisonLab\UserBundle\Form\ResetPasswordRequestFormType;
use BisonLab\UserBundle\Entity\User;
use BisonLab\UserBundle\Form\UserType;
use BisonLab\UserBundle\Form\ChangePasswordType;
use BisonLab\UserBundle\Repository\UserRepository;
use BisonLab\UserBundle\Lib\ExternalEntityConfig;

#[Route(path: '/bisonlab_user')]
class UserController extends AbstractController
{
    #[Route(path: '/', name: 'bisonlab_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        // I'll use the (current)User objects own checks. That makes the
        // application using this bundle able to use whatever role names they
        // want.
        if (!$this->getUser() || !$this->getUser()->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        return $this->render('@BisonLabUser/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route(path: '/new', name: 'bisonlab_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

    #[Route(path: '/profile', name: 'bisonlab_user_profile', methods: ['GET'])]
    public function profile(): Response
    {
        $user = $this->getUser();
        return $this->render('@BisonLabUser/user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Change password on self.
     */
    #[Route(path: '/change_password', name: 'bisonlab_self_change_password', methods: ['GET', 'POST'])]
    public function changeSelfPasswordAction(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('plainPassword')->getData();

            // Encode the plain password, and set it.
            $encodedPassword = $passwordHasher->hashPassword(
                $user, $password
            );

            $user->setPassword($encodedPassword);

            $entityManager->flush();

            return $this->redirectToRoute('bisonlab_user_profile');
        } else {
            return $this->render('@BisonLabUser/user/change_self_password.html.twig',
                array(
                'entity' => $user,
                'form' => $form->createView(),
            ));
        }
    }

    /**
     * Change password on user.
     */
    #[Route(path: '/{id}/change_password', name: 'bisonlab_user_change_password', methods: ['GET', 'POST'])]
    public function changeUserPasswordAction(Request $request, UserPasswordHasherInterface $passwordHasher, User $user, EntityManagerInterface $entityManager)
    {
        if (!$admin_user = $this->getUser())
            throw $this->createAccessDeniedException("No access for you");
        if (!$admin_user->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        $form = $this->createForm(ChangePasswordType::class, $user, ['no_current_check' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('plainPassword')->getData();

            // Encode the plain password, and set it.
            $encodedPassword = $passwordHasher->hashPassword(
                $user, $password
            );

            $user->setPassword($encodedPassword);

            $entityManager->flush();

            return $this->redirectToRoute('bisonlab_user_show', ['id' => $user->getId()]);
        } else {
            return $this->render('@BisonLabUser/user/change_user_password.html.twig',
                array(
                'user' => $user,
                'form' => $form->createView(),
            ));
        }
    }

    #[Route(path: '/search', name: 'bisonlab_user_search', methods: ['GET'])]
    #[Route(path: '/search', name: 'user_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $entityManager)
    {
        $access = $request->query->get("access") ?? "web";
        if (!$term = $request->query->get("term"))
            $term = $request->query->get("username");

        // Gotta be able to handle two-letter usernames.
        if (strlen($term) > 1) {
            $class = new User();
            $repo = $entityManager->getRepository(User::class);
            $result = array();
            $q = $repo->createQueryBuilder('u')
                ->where('lower(u.username) LIKE :term')
                ->orWhere('lower(u.email) LIKE :term')
                ->setParameter('term', strtolower($term) . '%');
            if (property_exists($class, 'full_name')) {
                $q->orWhere('lower(u.full_name) LIKE :full_name')
                ->setParameter('full_name', '%' . strtolower($term) . '%');
            }
            if (property_exists($class, 'mobile_phone_number')) {
                $q->orWhere('lower(u.mobile_phone_number) LIKE :mobile_phone_number')
                ->setParameter('mobile_phone_number', '%' . strtolower($term) . '%');
            }
            if (property_exists($class, 'phone_number')) {
                $q->orWhere('lower(u.phone_number) LIKE :phone_number')
                ->setParameter('phone_number', '%' . strtolower($term) . '%');
            }
            if (property_exists($class, 'state')) {
                if (!$states = $request->query->get("states"))
                    $states = [];
                if ($state = $request->query->get("state"))
                    $states[] = $state;
                if (count($states) > 0)
                    $q->andWhere('u.state) in (:states)')
                        ->setParameter('states', $states);
            }

            if ($users = $q->getQuery()->getResult()) {
                foreach ($users as $user) {
                    // TODO: Add full name.
                    $res = array(
                        'userid'   => $user->getId(),
                        'value'    => $user->getUserName(),
                        'email'    => $user->getEmail(),
                        'label'    => $user->getUserName(),
                        'username' => $user->getUserName(),
                    );
                    // Override if full name exists.
                    if (property_exists($user, 'full_name')
                            && $user->getFullName()) {
                        $res['label'] = $user->getFullName();
                        $res['value'] = $user->getFullName();
                    }
                    if ($request->get("value_with_email")) {
                        $res['value'] = $res['value'] . " - " . $user->getEmail();
                        $res['label'] = $res['label'] . " - " . $user->getEmail();
                    }
                    $result[] = $res;
                }
            }
        } else {
            $result = "Too little information provided for a viable search";
        }

        if ($access != "web") {
            return new JsonResponse($result);
        }

        $params = array(
            'entities'      => $users,
        );
        return $this->render('@BisonLabUser/User/index.html.twig',
            $params);
    }

    #[Route(path: '/{id}', name: 'bisonlab_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        $reset_form = $this->createForm(ResetPasswordRequestFormType::class);
        return $this->render('@BisonLabUser/user/show.html.twig', [
            'user' => $user,
            'reset_form' => $reset_form->createView(),
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'bisonlab_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if (!$admin_user = $this->getUser())
            throw $this->createAccessDeniedException("No access for you");
        if (!$admin_user->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('bisonlab_user_index');
        }

        return $this->render('@BisonLabUser/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'bisonlab_user_delete', methods: ['DELETE'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if (!$admin_user = $this->getUser())
            throw $this->createAccessDeniedException("No access for you");
        if (!$admin_user->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('bisonlab_user_index');
    }
}
