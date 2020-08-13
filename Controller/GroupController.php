<?php

namespace BisonLab\UserBundle\Controller;

use BisonLab\UserBundle\Entity\Group;
use BisonLab\UserBundle\Form\GroupType;
use BisonLab\UserBundle\Repository\GroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/bisonlab_group")
 */
class GroupController extends AbstractController
{
    /**
     * @Route("/", name="bisonlab_group_index", methods={"GET"})
     */
    public function index(GroupRepository $groupRepository): Response
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        return $this->render('@BisonLabUser/group/index.html.twig', [
            'groups' => $groupRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="bisonlab_group_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($group);
            $entityManager->flush();

            return $this->redirectToRoute('bisonlab_group_index');
        }

        return $this->render('@BisonLabUser/group/new.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="bisonlab_group_show", methods={"GET"})
     */
    public function show(Group $group): Response
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        return $this->render('@BisonLabUser/group/show.html.twig', [
            'group' => $group,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="bisonlab_group_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Group $group): Response
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('bisonlab_group_index');
        }

        return $this->render('@BisonLabUser/group/edit.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="bisonlab_group_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Group $group): Response
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin())
            throw $this->createAccessDeniedException("No access for you");
        if ($this->isCsrfTokenValid('delete'.$group->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($group);
            $entityManager->flush();
        }

        return $this->redirectToRoute('bisonlab_group_index');
    }
}
