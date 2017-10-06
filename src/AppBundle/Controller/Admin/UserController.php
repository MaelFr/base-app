<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\User;
use AppBundle\Form\UpdateUserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/users")
 */
class UserController extends Controller
{
    /**
     * Affiche la liste des utilisateurs
     *
     * @Route("/", name="admin_users_index")
     * @Route("/{page}", name="admin_users_index_page", requirements={"page": "\d+"})
     * @Method({"GET"})
     *
     * @param int $page
     *
     * @return Response
     */
    public function indexAction($page = 1)
    {
        if ($page == 0) {
            return $this->redirectToRoute('admin_users_index');
        }

        $repo = $this->getDoctrine()->getRepository('AppBundle:User');
        $users = $repo->findAllForPage($page);
        $nbUsers = $repo->count();
        $nbPages = ceil($nbUsers / 10);

        if ($page > $nbPages) {
            return $this->redirectToRoute('admin_users_index_page', ['page' => $nbPages]);
        }

        return $this->render(':admin/user:list.html.twig', [
            'users' => $users,
            'nbUsers' => $nbUsers,
            'nbPages' => $nbPages,
        ]);
    }

    /**
     * Affiche le détail d'un utilisateur
     *
     * @Route("/show/{id}", name="admin_users_show")
     * @Method({"GET"})
     *
     * @param User $user
     *
     * @return Response
     */
    public function showUserAction(User $user)
    {
        return $this->render(':admin/user:show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="admin_users_edit")
     *
     * @Security("is_granted('CAN_EDIT_USER', user)")
     *
     * @param User    $user
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(User $user, Request $request)
    {
        $form = $this->createForm(UpdateUserType::class, $user);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('admin_users_show', ['id' => $user->getId()]);
        }

        return $this->render(':admin/user:edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
