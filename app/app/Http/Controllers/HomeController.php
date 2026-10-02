<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Service;
class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['services' => fn($q) => $q->active()])->get();
        $featured = Service::active()->where('is_verified', true)
            ->with(['category','photos','reviews'])
            ->withAvg('reviews', 'rating')
            ->orderByDesc('views_count')->take(6)->get();
        return view('home.index', compact('categories', 'featured'));
    }
}
