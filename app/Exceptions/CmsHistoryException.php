<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/** Contains only reviewed, user-facing CMS history messages, never raw SDK errors. */
class CmsHistoryException extends HttpException
{
    public function __construct(int $status, string $message, public readonly string $errorCode, ?Throwable $previous = null)
    {
        parent::__construct($status, $message, $previous);
    }

    public function render(Request $request)
    {
        if (! $request->expectsJson()) {
            return null;
        }

        return response()->json([
            'ok' => false,
            'message' => $this->getMessage(),
            'error_code' => $this->errorCode,
        ], $this->getStatusCode());
    }
}
