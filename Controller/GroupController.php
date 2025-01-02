<?php

namespace BisonLab\UserBundle\Controller;

use BisonLab\UserBundle\Entity\Group;
use BisonLab\UserBundle\Form\GroupType;
use BisonLab\UserBundle\Repository\GroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route(path: '/bisonlab_group')]
class GroupController extends AbstractController
{
    #[Route(path: '/', name: 'bisonlab_group_index', methods: ['GET'])]
    public function index(GroupRepository $groupRepository): Response
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        return $this->render('@BisonLabUser/group/index.html.twig', [
            'groups' => $groupRepository->findAll(),
        ]);
    }

    #[Route(path: '/new', name: 'bisonlab_group_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($group);
            $entityManager->flush();

            return $this->redirectToRoute('bisonlab_group_index');
        }

        return $this->render('@BisonLabUser/group/new.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'bisonlab_group_show', methods: ['GET'])]
    public function show(Group $group): Response
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        return $this->render('@BisonLabUser/group/show.html.twig', [
            'group' => $group,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'bisonlab_group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Group $group, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('bisonlab_group_index');
        }

        return $this->render('@BisonLabUser/group/edit.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'bisonlab_group_delete', methods: ['DELETE'])]
    public function delete(Request $request, Group $group, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        if ($this->isCsrfTokenValid('delete'.$group->getId(), $request->request->get('_token'))) {
            $entityManager->remove($group);
            $entityManager->flush();
        }

        return $this->redirectToRoute('bisonlab_group_index');
    }
}
