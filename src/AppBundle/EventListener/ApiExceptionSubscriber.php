<?php

namespace AppBundle\EventListener;

use AppBundle\Api\ApiProblem;
use AppBundle\Api\ApiProblemException;
use AppBundle\Api\ResponseFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    /** @var LoggerInterface */
    private $logger;
    /** @var boolean */
    private $debug;
    /** @var ResponseFactory */
    private $responseFactory;

    /**
     * ApiExceptionSubscriber constructor.
     *
     * @param LoggerInterface $logger
     * @param boolean         $debug
     * @param ResponseFactory $responseFactory
     */
    public function __construct(LoggerInterface $logger, $debug, ResponseFactory $responseFactory)
    {
        $this->debug = $debug;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // Todo: use the Logger
        // only reply to /api URLs
        if (strpos($event->getRequest()->getPathInfo(), '/api') !== 0) {
            return;
        }
        $e = $event->getException();
        $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;


        if ($statusCode == 500 && $this->debug) {
            return;
        }

        if ($e instanceof ApiProblemException) {
            $apiProblem = $e->getApiProblem();
        } else {
            $apiProblem = new ApiProblem(
                $statusCode
            );
            // Sets detail for 4XX errors
            if ($e instanceof HttpExceptionInterface) {
                if (403 === $e->getStatusCode()) {
                    $apiProblem->set('detail', 'You don\'t have permission to access this resource.');
                } else {
                    $apiProblem->set('detail', $e->getMessage());
                }
            }
        }

        $response = $this->responseFactory->createResponse($apiProblem);

        $event->setResponse($response);
    }
}