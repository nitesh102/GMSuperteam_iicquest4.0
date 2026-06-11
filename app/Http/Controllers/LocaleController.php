<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
  protected array $supported = ['en', 'ne'];

  public function update(Request $request): RedirectResponse
  {
    $locale = $request->input('locale', 'en');

    if (! in_array($locale, $this->supported, true)) {
      $locale = 'en';
    }

    $request->session()->put('locale', $locale);

    return back();
  }
}
