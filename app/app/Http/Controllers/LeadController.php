<?php
namespace App\Http\Controllers;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class LeadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            // Hanya listing aktif yang boleh menerima lead (integritas data, TC-F06).
            'service_id' => ['required', Rule::exists('services', 'id')->where('status', 'active')],
            'channel' => 'required|in:whatsapp,phone,quote',
            'message' => 'nullable|string|max:1000',
        ]);
        $data['user_id'] = auth()->id();
        Lead::create($data);
        return response()->json(['ok' => true]);
    }
}
