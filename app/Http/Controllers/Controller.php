<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    /**
     * Fournit la methode $this->authorize(...) utilisee par les
     * controleurs pour verifier les Policies (ex: FilePolicy au Sprint 2).
     */
    use AuthorizesRequests;
}
