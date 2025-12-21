<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use App\Models\Faq;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    public function getPage($slug)
    {
        $page = StaticPage::where('slug', $slug)->first();
        
        if (!$page) {
            return response()->json([
                'error' => 'Page not found',
                'message' => "The page with slug '{$slug}' does not exist."
            ], 404);
        }
        
        return response()->json(['data' => $page]);
    }

    public function getFaqs()
    {
        $faqs = Faq::all();
        return response()->json(['data' => $faqs]);
    }
}
