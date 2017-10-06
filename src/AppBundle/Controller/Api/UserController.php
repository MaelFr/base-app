<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\User;
use AppBundle\Form\UpdateUserType;
use AppBundle\Pagination\PaginationFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 */
class UserController extends BaseController
{
//    /**
//     * @Route("/users")
//     * @Method({"POST"})
//     *
//     * @param Request $request
//     *
//     * @return JsonResponse
//     */
//    public function newAction(Request $request)
//    {
//        $user = new User();
//        $form = $this->createForm(UserType::class, $user);
//        $this->processForm($request, $form);
//
//        if (!$form->isValid()) {
//            return $this->throwApiProblemValidationException($form);
//        }
//
//        $user->setPlainPassword();
//
//        $em = $this->getDoctrine()->getManager();
//        $em->persist($user);
//        $em->flush();
//
//        $response = $this->createApiResponse($user, 201);
//        $userUrl = $this->generateUrl('api_users_show', ['username' => $user->getUsername()]);
//        $response->headers->set('Location', $userUrl);
//
//        return $response;
//    }

    /**
     * @Route("/users/{username}", name="api_users_show")
     * @Method({"GET"})
     *
     * @param string $username
     *
     * @return JsonResponse
     */
    public function showAction($username)
    {
        // Todo: ne pas retourner le password
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(['username' => $username]);

        if (!$user) {
            throw $this->createNotFoundException(sprintf(
                'No user found with name "%s"',
                $username
            ));
        }

        $response = $this->createApiResponse($user);

        return $response;
    }

    /**
     * @Route("/users", name="api_users_collection")
     * @Method({"GET"})
     *
     * @param Request           $request
     * @param PaginationFactory $paginationFactory
     *
     * @return JsonResponse
     */
    public function listAction(Request $request, PaginationFactory $paginationFactory)
    {
        $filter = $request->query->get('filter');

        $qb = $this->getDoctrine()->getRepository('AppBundle:User')->findAllQueryBuilder($filter);

        $paginatedCollection = $paginationFactory->createCollection($qb, $request, 'api_users_collection');

        $response = $this->createApiResponse($paginatedCollection);

        return $response;
    }

    /**
     * @Route("/users/{username}")
     * @Method({"PUT", "PATCH"})
     *
     * @param User   $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(User $user, Request $request)
    {
        $form = $this->createForm(UpdateUserType::class, $user);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            return $this->throwApiProblemValidationException($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $response = $this->createApiResponse($user);

        return $response;
    }

    /**
     * @Route("/users/{username}")
     * @Method({"DELETE"})
     *
     * @param string $username
     *
     * @return Response
     */
    public function deleteAction($username)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(['username' => $username]);

        if ($user) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return new Response(null, 204);
    }
}
