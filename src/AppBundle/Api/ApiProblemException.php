<?php

namespace AppBundle\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiProblemException extends HttpException
{
    /** @var ApiProblem */
    private $apiProblem;

    // Todo: Why removing $statusCode from the constructor throws a ServiceCircularReferenceException ?
    public function __construct(
        ApiProblem $apiProblem,
        $statusCode,
        \Exception $previous = null,
        array $headers = [],
        $code = 0
    ) {
        $this->apiProblem = $apiProblem;
        $statusCode = $apiProblem->getStatusCode();
        $message = $apiProblem->getTitle();

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    /**
     * Get apiProblem
     *
     * @return ApiProblem
     */
    public function getApiProblem(): ApiProblem
    {
        return $this->apiProblem;
    }
}