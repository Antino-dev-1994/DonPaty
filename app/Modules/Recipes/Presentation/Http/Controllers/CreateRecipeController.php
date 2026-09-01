<?php

namespace App\Modules\Recipes\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Recipes\Presentation\Support\RecipeFormOptions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateRecipeController extends Controller
{
    public function __invoke(Request $request, RecipeFormOptions $options): Response
    {
        abort_unless($request->user()->hasPermission('recipes.manage'), 403);
        return Inertia::render('recipes/Create', $options->get());
    }
}
