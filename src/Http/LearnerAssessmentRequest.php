<?php

namespace Seiger\sLMS\Http;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Authenticate learner requests with the Evo web session and protect writes.
 *
 * @since 0.1.0
 */
class LearnerAssessmentRequest
{
    /**
     * Attach the Evo web user ID and validate the form token on POST.
     *
     * @param Closure(Request): Response $next
     * @since 0.1.0
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = (int) evo()->getLoginUserID('web');
        if (empty($_SESSION['webValidated']) || $userId < 1) {
            throw new HttpException(401);
        }

        if ($request->isMethod('POST')) {
            $expected = $_SESSION['_token'] ?? null;
            $submitted = $request->post('_token');
            if (!is_string($expected) || $expected === '' || !is_string($submitted) || !hash_equals($expected, $submitted)) {
                throw new HttpException(419);
            }
        }

        $request->attributes->set('slmsLearnerId', $userId);
        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
