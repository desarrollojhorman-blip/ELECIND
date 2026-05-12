<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class LoginViewResponse implements Responsable
{
    public function toResponse($request): Response
    {
        return response()->view('auth.login');
    }
}
