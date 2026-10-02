<?php
namespace App\Http\Controllers;
use App\Models\Lead;
use App\Models\ServiceView;
use Illuminate\Http\Request;
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $serviceIds = $user->services()->pluck('id');
        $totalServices = $serviceIds->count();
        $viewsToday = ServiceView::whereIn('service_id', $serviceIds)->where('viewed_at', now()->toDateString())->count();
        $newContacts = Lead::whereIn('service_id', $serviceIds)->whereDate('created_at', now()->toDateString())->count();
        $services = $user->services()->with('category')->withCount('leads')->orderByDesc('views_count')->paginate(10);
        return view('dashboard.index', compact('totalServices','viewsToday','newContacts','services'));
    }
}
