<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $cats = [
            ['name' => 'Listrik', 'slug' => 'listrik', 'icon' => 'bolt'],
            ['name' => 'Servis AC', 'slug' => 'servis-ac', 'icon' => 'ac_unit'],
            ['name' => 'Laundry', 'slug' => 'laundry', 'icon' => 'local_laundry_service'],
            ['name' => 'Kebersihan', 'slug' => 'kebersihan', 'icon' => 'cleaning_services'],
            ['name' => 'Plumbing', 'slug' => 'plumbing', 'icon' => 'plumbing'],
            ['name' => 'Auto Care', 'slug' => 'auto-care', 'icon' => 'directions_car'],
            ['name' => 'Pet Care', 'slug' => 'pet-care', 'icon' => 'pets'],
            ['name' => 'Wellness', 'slug' => 'wellness', 'icon' => 'spa'],
        ];
        foreach ($cats as $c) Category::firstOrCreate(['slug' => $c['slug']], $c);

        $provider = User::firstOrCreate(['email' => 'provider@dekatsini.id'], [
            'name' => 'Pak Hendra', 'password' => bcrypt('password'), 'is_provider' => true, 'phone' => '081234567890',
        ]);
        $provider2 = User::firstOrCreate(['email' => 'budi@dekatsini.id'], [
            'name' => 'Budi Santoso', 'password' => bcrypt('password'), 'is_provider' => true, 'phone' => '081298765432',
        ]);

        $samples = [
            ['title' => 'Servis AC Cepat Jaksel', 'cat' => 'servis-ac', 'city' => 'Jakarta Selatan', 'lat' => -6.260, 'lng' => 106.810, 'price' => 150000, 'user' => 0, 'rating' => [5,5,4], 'desc' => 'Cuci AC, isi freon, perbaikan bocor. Teknisi bersertifikat, garansi 3 bulan sparepart.'],
            ['title' => 'Instalasi Listrik Baru', 'cat' => 'listrik', 'city' => 'Jakarta Barat', 'lat' => -6.170, 'lng' => 106.760, 'price' => 350000, 'user' => 0, 'rating' => [5,5,5], 'desc' => 'Instalasi listrik rumah/ruko baru, tambah daya, perbaikan konslet. Bergaransi dan SLO-ready.'],
            ['title' => 'Cuci Sofa & Karpet Express', 'cat' => 'kebersihan', 'city' => 'Tangerang Kota', 'lat' => -6.180, 'lng' => 106.630, 'price' => 200000, 'user' => 1, 'rating' => [5,4,5], 'desc' => 'Cuci sofa, springbed, karpet dengan mesin ekstraktor. Eco-friendly, kering 2-3 jam.'],
            ['title' => 'Tukang Ledeng Panggilan Bekasi', 'cat' => 'plumbing', 'city' => 'Bekasi', 'lat' => -6.240, 'lng' => 106.990, 'price' => 120000, 'user' => 0, 'rating' => [5,5,4], 'desc' => 'Atasi mampet, bocor, ganti keran & pompa air. Datang same-day area Bekasi.'],
            ['title' => 'Laundry Kiloan Antar-Jemput', 'cat' => 'laundry', 'city' => 'Jakarta Timur', 'lat' => -6.230, 'lng' => 106.870, 'price' => 12000, 'user' => 1, 'rating' => [4,5,4], 'desc' => 'Laundry kiloan wangi, setrika uap, antar-jemput gratis radius 3km.'],
            ['title' => 'Bengkel Mobil Panggilan', 'cat' => 'auto-care', 'city' => 'Depok', 'lat' => -6.390, 'lng' => 106.820, 'price' => 250000, 'user' => 0, 'rating' => [5,4,4], 'desc' => 'Tune-up, ganti oli, scan ECU di rumah Anda. Montir berpengalaman 10 tahun.'],
            ['title' => 'Grooming Kucing & Anjing', 'cat' => 'pet-care', 'city' => 'Jakarta Pusat', 'lat' => -6.190, 'lng' => 106.830, 'price' => 100000, 'user' => 1, 'rating' => [5,5,5], 'desc' => 'Grooming, vaksin, penitipan harian. Ditangani dokter hewan.'],
            ['title' => 'Yoga Private di Rumah', 'cat' => 'wellness', 'city' => 'Jakarta Selatan', 'lat' => -6.280, 'lng' => 106.790, 'price' => 180000, 'user' => 1, 'rating' => [5,5,4], 'desc' => 'Yoga, pilates, dan mindfulness bersama instruktur bersertifikat. Fleksibel pagi/sore.'],
            ['title' => 'Bersih Rumah Total (General Cleaning)', 'cat' => 'kebersihan', 'city' => 'Jakarta Selatan', 'lat' => -6.270, 'lng' => 106.800, 'price' => 300000, 'user' => 0, 'rating' => [4,5,5], 'desc' => 'General cleaning rumah/kos: debu, kamar mandi, dapur. Tim 2-3 orang + alat sendiri.'],
            ['title' => 'Perbaikan Kulkas & Mesin Cuci', 'cat' => 'listrik', 'city' => 'Tangerang Selatan', 'lat' => -6.290, 'lng' => 106.720, 'price' => 175000, 'user' => 0, 'rating' => [4,4,5], 'desc' => 'Servis kulkas tidak dingin, mesin cuci mati, ganti sparepart ori. Garansi 90 hari.'],
            ['title' => 'Cuci AC + Anti Bakteri', 'cat' => 'servis-ac', 'city' => 'Jakarta Utara', 'lat' => -6.130, 'lng' => 106.830, 'price' => 90000, 'user' => 1, 'rating' => [5,4,5], 'desc' => 'Cuci AC split/cassette + desinfektan anti bakteri. Dingin maksimal, hemat listrik.'],
            ['title' => 'Poles & Coating Mobil', 'cat' => 'auto-care', 'city' => 'Jakarta Barat', 'lat' => -6.160, 'lng' => 106.770, 'price' => 500000, 'user' => 1, 'rating' => [5,5,4], 'desc' => 'Salon mobil: poles jamur kaca, coating 2 lapis, interior detailing. Home service.'],
        ];
        $comments = [
            'Datang tepat waktu, kerja rapi. Recommended!',
            'Harga sesuai, tidak ada biaya siluman.',
            'Teknisi ramah dan menjelaskan kerusakan dengan jujur.',
            'Hasil bersih, akan pakai lagi untuk maintenance rutin.',
        ];
        foreach ($samples as $i => $s) {
            $cat = Category::where('slug', $s['cat'])->first();
            $u = $s['user'] === 0 ? $provider : $provider2;
            $svc = Service::firstOrCreate(['slug' => Str::slug($s['title'])], [
                'user_id' => $u->id, 'category_id' => $cat->id,
                'title' => $s['title'], 'description' => $s['desc'].' Area layanan '.$s['city'].' dan sekitarnya.',
                'whatsapp' => '6281234567890', 'phone' => '0211234567',
                'address' => 'Jl. Contoh No. '.($i+1), 'city' => $s['city'],
                'latitude' => $s['lat'], 'longitude' => $s['lng'], 'price_from' => $s['price'],
                'status' => 'active', 'is_verified' => true, 'views_count' => rand(50, 500),
            ]);
            foreach ($s['rating'] as $j => $r) {
                Review::firstOrCreate(
                    ['service_id' => $svc->id, 'comment' => $comments[($i+$j) % count($comments)]],
                    ['user_id' => null, 'rating' => $r, 'is_verified' => true]
                );
            }
        }
    }
}
