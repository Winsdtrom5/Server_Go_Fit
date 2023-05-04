<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CORS implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Add CORS headers to the response
        $response = service('response');
        $response->setHeader('Access-Control-Allow-Origin', '*')
                 ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                 ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                 ->setHeader('Access-Control-Max-Age', 86400)
                 ->setHeader('Access-Control-Allow-Credentials', 'true');

        // Handle preflight requests (OPTIONS)
        if ($request->getMethod(true) === 'OPTIONS') {
            return $response->setStatusCode(200);
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after the request
        return $response;
    }
}
