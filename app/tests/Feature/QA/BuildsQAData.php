<?php

namespace Tests\Feature\QA;

use App\Models\Category;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServicePhoto;
use App\Models\ServiceView;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Pembantu pembuatan data uji untuk test QA.
 */
trait BuildsQAData
{
    protected function makeUser(array $attrs = []): User
    {
        return User::factory()->create($attrs);
    }

    protected function makeCategory(array $attrs = []): Category
    {
        return Category::create(array_merge([
            'name' => 'Servis AC '.Str::upper(Str::random(4)),
            'slug' => 'servis-ac-'.Str::lower(Str::random(6)),
        ], $attrs));
    }

    protected function makeService(User $user, Category $cat, array $attrs = []): Service
    {
        return Service::create(array_merge([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'title' => 'Servis AC Profesional '.Str::upper(Str::random(4)),
            'slug' => 'servis-ac-'.Str::lower(Str::random(10)),
            'description' => 'Cuci AC, isi freon, dan perbaikan bocor. Teknisi bersertifikat bergaransi 3 bulan.',
            'whatsapp' => '6281234567890',
            'phone' => '0211234567',
            'address' => 'Jl. Contoh No. 1',
            'city' => 'Jakarta Selatan',
            'latitude' => -6.260,
            'longitude' => 106.810,
            'price_from' => 150000,
            'status' => 'active',
            'is_verified' => true,
            'views_count' => 0,
        ], $attrs));
    }

    protected function makeReview(Service $service, int $rating = 5, string $comment = 'Sangat memuaskan'): Review
    {
        return Review::create([
            'service_id' => $service->id,
            'user_id' => null,
            'rating' => $rating,
            'comment' => $comment,
            'is_verified' => true,
        ]);
    }

    protected function makeView(Service $service, ?string $date = null): ServiceView
    {
        return ServiceView::create([
            'service_id' => $service->id,
            'viewed_at' => $date ?? now()->toDateString(),
            'ip_hash' => Str::random(64),
        ]);
    }

    protected function makePhoto(Service $service, string $path = 'services/qa-cover.webp', bool $cover = true): ServicePhoto
    {
        return ServicePhoto::create([
            'service_id' => $service->id,
            'path' => $path,
            'is_cover' => $cover,
        ]);
    }

    /**
     * Ambil nilai angka pada kartu metrik dashboard, mis. "Kontak Baru" => 2.
     * Struktur kartu: <p ...>Label</p><p class="text-3xl font-extrabold">N</p>
     */
    protected function metricFrom(string $html, string $label): ?int
    {
        $pattern = '/<p class="text-sm text-slate-500">'.preg_quote($label, '/').'<\/p><p class="text-3xl font-extrabold">(\d+)<\/p>/';

        return preg_match($pattern, $html, $m) === 1 ? (int) $m[1] : null;
    }

    /**
     * Payload POST /services yang valid (sesuai validasi ServiceController@store).
     */
    protected function validServicePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Tukang Listrik Panggilan 24 Jam',
            'category_id' => $this->makeCategory(['name' => 'Listrik '.Str::upper(Str::random(4))])->id,
            'description' => 'Instalasi dan perbaikan listrik rumah, tambah daya, dan konslet. Bergaransi dan SLO-ready.',
            'whatsapp' => '6281122334455',
            'phone' => '0215551234',
            'address' => 'Jl. Merdeka No. 10',
            'city' => 'Jakarta Barat',
            'latitude' => -6.170,
            'longitude' => 106.760,
            'price_from' => 200000,
        ], $overrides);
    }
}
