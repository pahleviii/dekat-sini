<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceView;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();
        $q = Service::active()->with(['category','photos'])->withAvg('reviews','rating');

        if ($s = $request->get('search')) {
            $q->where(function($w) use ($s) {
                $w->where('title','like',"%{$s}%")->orWhere('description','like',"%{$s}%");
            });
        }
        if ($cat = $request->get('category')) {
            $q->whereHas('category', fn($w) => $w->where('slug', $cat));
        }
        if ($min = (float) $request->get('min_rating', 0)) {
            $q->having('reviews_avg_rating','>=',$min);
        }
        if ($request->filled(['lat','lng'])) {
            $lat = (float) $request->get('lat'); $lng = (float) $request->get('lng');
            $radius = (float) $request->get('radius', 20);
            // Catatan: jangan tambahkan "services.*, " di sini — withAvg() sudah menambahkan
            // select services.*, sehingga duplikasi membuat query COUNT paginate gagal (BUG-001).
            $q->selectRaw("(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat,$lng,$lat])
              ->having('distance','<=',$radius)->orderBy('distance');
        }
        match ($request->get('sort', 'recommended')) {
            'rating' => $q->orderByDesc('reviews_avg_rating'),
            'nearest' => $request->filled(['lat','lng']) ? null : $q->orderByDesc('views_count'),
            'price_asc' => $q->orderBy('price_from'),
            default => $q->orderByDesc('is_verified')->orderByDesc('views_count'),
        };

        $services = $q->paginate(12)->withQueryString();
        return view('services.index', compact('services','categories'));
    }

    public function show(Service $service)
    {
        abort_unless($service->status === 'active', 404);
        $service->load(['category','photos','reviews','user']);
        $service->loadAvg('reviews','rating');
        $ipHash = sha1(request()->ip().'|'.$service->id.'|'.now()->format('Y-m-d-H'));
        $cacheKey = "view:{$service->id}:{$ipHash}";
        if (!Cache::has($cacheKey)) {
            ServiceView::create(['service_id'=>$service->id,'viewed_at'=>now()->toDateString(),'ip_hash'=>$ipHash]);
            $service->increment('views_count');
            Cache::put($cacheKey, true, 3600);
        }
        $related = Service::active()->where('category_id',$service->category_id)
            ->where('id','!=',$service->id)->with('photos')->withAvg('reviews','rating')->take(3)->get();
        $waText = urlencode("Halo, saya lihat layanan {$service->title} di DekatSini. Apakah tersedia?");
        $waLink = "https://wa.me/{$service->whatsapp}?text={$waText}";
        return view('services.show', compact('service','related','waLink'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('services.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|min:20',
            'whatsapp' => 'required|string|max:20',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'price_from' => 'nullable|integer|min:0',
            'photos.*' => 'nullable|image|max:2048',
        ]);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = Str::slug($data['title']).'-'.Str::random(6);
        $data['whatsapp'] = $this->normalizeWhatsapp($data['whatsapp']);
        $data['status'] = 'pending';
        $service = Service::create($data);
        $request->user()->update(['is_provider' => true]);
        if ($request->hasFile('photos')) {
            $manager = new ImageManager(new Driver());
            foreach ($request->file('photos') as $i => $file) {
                try {
                    $img = $manager->decode($file->getPathname())->scaleDown(1200);
                    $name = 'services/'.Str::uuid().'.webp';
                    Storage::disk('public')->put($name, (string) $img->encode(new WebpEncoder(80)));
                    $path = $name;
                } catch (\Throwable $e) {
                    $path = $file->store('services', 'public');
                }
                $service->photos()->create(['path' => $path, 'is_cover' => $i === 0]);
            }
        }
        return redirect()->route('dashboard')->with('success', 'Jasa berhasil dikirim, menunggu verifikasi admin.');
    }

    public function edit(Service $service)
    {
        abort_unless($service->user_id === auth()->id(), 403);
        $categories = Category::all();
        return view('services.edit', compact('service','categories'));
    }

    public function update(Request $request, Service $service)
    {
        abort_unless($service->user_id === auth()->id(), 403);
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|min:20',
            'whatsapp' => 'required|string|max:20',
            'price_from' => 'nullable|integer|min:0',
            'status' => 'in:active,paused',
        ]);
        // Listing pending tidak boleh diaktifkan sendiri oleh provider (BR-01 / UAT#3).
        if ($service->status === 'pending' && ($data['status'] ?? null) === 'active') {
            throw ValidationException::withMessages([
                'status' => 'Listing masih menunggu verifikasi admin dan belum bisa diaktifkan sendiri.',
            ]);
        }
        $data['whatsapp'] = $this->normalizeWhatsapp($data['whatsapp']);
        $service->update($data);
        return redirect()->route('dashboard')->with('success', 'Jasa diperbarui.');
    }

    public function destroy(Service $service)
    {
        abort_unless($service->user_id === auth()->id(), 403);
        $service->delete();
        return redirect()->route('dashboard')->with('success', 'Jasa dihapus.');
    }

    /**
     * Normalisasi nomor WhatsApp ke format internasional 628xx (BR-06).
     * "0812..." / "+62 812..." -> "62812..." agar link wa.me valid.
     */
    private function normalizeWhatsapp(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number);

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }
}
