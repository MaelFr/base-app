<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Member;
use AppBundle\Form\MemberType;
use AppBundle\Form\UpdateMemberType;
use AppBundle\Pagination\PaginationFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('ROLE_USER')")
 */
class MemberController extends BaseController
{
    /**
     * @Route("/members")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function newAction(Request $request)
    {
        $member = new Member();
        $form = $this->createForm(MemberType::class, $member);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            return $this->throwApiProblemValidationException($form);
        }

        $member->setCreatedBy($this->getUser());

        $em = $this->getDoctrine()->getManager();
        $em->persist($member);
        $em->flush();

        $response = $this->createApiResponse($member, 201);
        $memberUrl = $this->generateUrl('api_members_show', ['name' => $member->getName()]);
        $response->headers->set('Location', $memberUrl);

        return $response;
    }

    /**
     * @Route("/members/{name}", name="api_members_show")
     * @Method({"GET"})
     *
     * @param string $name
     *
     * @return JsonResponse
     */
    public function showAction($name)
    {
        $member = $this->getDoctrine()->getRepository('AppBundle:Member')->findOneBy(['name' => $name]);

        if (!$member) {
            throw $this->createNotFoundException(sprintf(
                'No member found with name "%s"',
                $name
            ));
        }

        $response = $this->createApiResponse($member);

        return $response;
    }

    /**
     * @Route("/members", name="api_members_collection")
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

        $qb = $this->getDoctrine()->getRepository('AppBundle:Member')->findAllQueryBuilder($filter);

        $paginatedCollection = $paginationFactory->createCollection($qb, $request, 'api_members_collection');

        $response = $this->createApiResponse($paginatedCollection);

        return $response;
    }

    /**
     * @Route("/members/{name}")
     * @Method({"PUT", "PATCH"})
     *
     * @param Member   $member
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Member $member, Request $request)
    {
        $form = $this->createForm(UpdateMemberType::class, $member);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            return $this->throwApiProblemValidationException($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($member);
        $em->flush();

        $response = $this->createApiResponse($member);

        return $response;
    }

    /**
     * @Route("/members/{name}")
     * @Method({"DELETE"})
     *
     * @param string $name
     *
     * @return Response
     */
    public function deleteAction($name)
    {
        $member = $this->getDoctrine()->getRepository('AppBundle:Member')->findOneBy(['name' => $name]);

        if ($member) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($member);
            $em->flush();
        }

        return new Response(null, 204);
    }
}
