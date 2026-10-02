<?php

namespace Tests\Feature\QA;

use App\Models\Service;
use App\Models\ServicePhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Modul D — Form Listing Provider (TC-D01 .. TC-D10).
 */
class ListingFormQaTest extends TestCase
{
    use RefreshDatabase, BuildsQAData;

    public function test_tc_d01_submit_valid_listing_creates_pending_and_marks_provider(): void
    {
        $user = $this->makeUser(['is_provider' => false]);

        $resp = $this->actingAs($user)->post('/services', $this->validServicePayload());

        $resp->assertRedirect(route('dashboard'));
        $svc = Service::first();
        $this->assertNotNull($svc, 'Listing harus tersimpan (TC-D01).');
        $this->assertSame('pending', $svc->status, 'Listing baru harus berstatus pending (UAT#3).');
        $user->refresh();
        $this->assertTrue((bool) $user->is_provider, 'User harus menjadi provider (UAT#3).');
    }

    public function test_tc_d02_required_fields_validated(): void
    {
        $user = $this->makeUser();

        $resp = $this->actingAs($user)->postJson('/services', []);

        $resp->assertUnprocessable()->assertJsonValidationErrors([
            'title', 'category_id', 'description', 'whatsapp',
            'address', 'city', 'latitude', 'longitude',
        ]);
        $this->assertSame(0, Service::count());
    }

    public function test_tc_d03_description_min_20_chars_validated(): void
    {
        $user = $this->makeUser();

        $resp = $this->actingAs($user)->postJson('/services', $this->validServicePayload([
            'description' => 'pendek',
        ]));

        $resp->assertUnprocessable()->assertJsonValidationErrors(['description']);
    }

    public function test_tc_d04_latitude_longitude_range_validated(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->postJson('/services', $this->validServicePayload([
            'latitude' => 999,
        ]))->assertUnprocessable()->assertJsonValidationErrors(['latitude']);

        $this->actingAs($user)->postJson('/services', $this->validServicePayload([
            'longitude' => -200,
        ]))->assertUnprocessable()->assertJsonValidationErrors(['longitude']);
    }

    public function test_tc_d05_photo_over_2mb_rejected(): void
    {
        $user = $this->makeUser();
        $big = UploadedFile::fake()->image('besar.jpg', 100, 100)->size(3000); // 3 MB

        $resp = $this->actingAs($user)->postJson('/services', $this->validServicePayload([
            'photos' => [$big],
        ]));

        $resp->assertUnprocessable()->assertJsonValidationErrors(['photos.0']);
        $this->assertSame(0, Service::count());
    }

    public function test_tc_d06_valid_photo_saved_as_webp_with_cover(): void
    {
        Storage::fake('public');
        $user = $this->makeUser();
        $img = UploadedFile::fake()->image('foto.jpg', 100, 100);

        $this->actingAs($user)->post('/services', $this->validServicePayload([
            'photos' => [$img],
        ]))->assertRedirect(route('dashboard'));

        $svc = Service::first();
        $this->assertNotNull($svc);
        $photo = ServicePhoto::where('service_id', $svc->id)->first();
        $this->assertNotNull($photo, 'Baris service_photos harus tercipta (TC-D06).');
        $this->assertTrue((bool) $photo->is_cover, 'Foto pertama harus jadi cover (TC-D06).');
        $this->assertStringStartsWith('services/', $photo->path);
        $this->assertStringEndsWith('.webp', $photo->path, 'Foto harus disimpan sebagai WebP (TC-D06).');
        Storage::disk('public')->assertExists($photo->path);
        $files = Storage::disk('public')->files('services');
        $this->assertCount(1, $files, 'Foto harus tersimpan di storage/app/public/services (TC-D06).');
    }

    public function test_tc_d07_validation_errors_visible_on_form(): void
    {
        $user = $this->makeUser();
        $payload = $this->validServicePayload(['description' => 'pendek']);

        // Submit gagal lalu ikuti redirect ke halaman form dalam satu rantai request.
        // (Memecah rantai dengan assertSessionHasErrors membuat store session di-start
        //  dua kali dan flash error ter-marshalling ganda hingga hilang — lihat 05-retest.)
        $resp = $this->actingAs($user)
            ->from('/services-new')
            ->followingRedirects()
            ->post('/services', $payload);

        $resp->assertOk();
        $this->assertStringContainsString(
            'Bidang deskripsi harus setidaknya 20 karakter.',
            $resp->getContent(),
            'Pesan error validasi harus tampil di halaman form, bukan halaman kosong (TC-D07).'
        );
    }

    public function test_tc_d08_old_input_preserved_after_invalid_submit(): void
    {
        $user = $this->makeUser();
        $payload = $this->validServicePayload([
            'title' => 'Judul Uji Old Input',
            'description' => 'pendek',
        ]);

        $this->actingAs($user)->from('/services-new')->post('/services', $payload)
            ->assertSessionHasErrors('description');

        $html = $this->actingAs($user)->get('/services-new')->getContent();
        $this->assertStringContainsString(
            'value="Judul Uji Old Input"',
            $html,
            'Input lama harus dipertahankan setelah submit gagal (TC-D08).'
        );
    }

    public function test_tc_d09_whatsapp_number_normalized_to_62(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/services', $this->validServicePayload([
            'whatsapp' => '081234567890',
        ]))->assertRedirect(route('dashboard'));

        $svc = Service::first();
        $this->assertSame(
            '6281234567890',
            $svc->whatsapp,
            'Nomor WA 08xx harus dinormalisasi ke 628xx agar wa.me valid (BR-06, TC-D09).'
        );

        $this->actingAs($user)->post('/services', $this->validServicePayload([
            'whatsapp' => '+62 812-3456-7890',
        ]))->assertRedirect(route('dashboard'));

        $svc2 = Service::latest('id')->first();
        $this->assertSame('6281234567890', $svc2->whatsapp, 'Nomor +62 8xx harus dinormalisasi ke 628xx (BR-06, TC-D09).');
    }

    public function test_tc_d10_listing_form_copy_is_indonesian(): void
    {
        $user = $this->makeUser();

        $resp = $this->actingAs($user)->get('/services-new');

        $resp->assertOk();
        $resp->assertSee('Pasang jasamu');
        $resp->assertSee('Nama Jasa');
        $resp->assertSee('Kategori');
        $resp->assertSee('No. WhatsApp');
        $resp->assertSee('Deskripsi');
        $resp->assertSee('Alamat Lengkap');
        $resp->assertSee('Kota');
        $resp->assertSee('Harga Mulai');
        $resp->assertSee('Titik Lokasi');
        $resp->assertSee('Foto (maks 2MB)');
        $resp->assertSee('Kirim Jasa');
    }
}
