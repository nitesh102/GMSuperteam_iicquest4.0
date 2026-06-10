<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
  protected array $supported = ['en', 'ne'];

  public function handle(Request $request, Closure $next): Response
  {
    $locale = $request->session()->get('locale', config('app.locale', 'en'));

    if (! in_array($locale, $this->supported, true)) {
      $locale = 'en';
    }

    app()->setLocale($locale);

    return $next($request);
  }
}
