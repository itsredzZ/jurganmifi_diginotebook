<?php

namespace App\Livewire;

use Livewire\Component;

class BusinessStrategy extends Component
{
    /**
     * Konten ini statis (bukan dari database) karena merupakan hasil
     * analisis manual dari Business Strategist tim berdasarkan data
     * forecast KNIME Random Forest. Diletakkan di sini supaya gampang
     * diedit langsung lewat kode tanpa perlu migrasi database baru.
     */
    public array $strategies = [];

    public function mount(): void
    {
        $this->strategies = [
            [
                'icon'        => '🤝',
                'title'       => 'Program Referral Pelanggan',
                'description' => 'Berikan bonus atau diskon kepada pelanggan yang berhasil mengajak pelanggan baru untuk menggunakan layanan Juragan Mifi.',
                'example'     => '"Ajak 1 teman berlangganan Juragan Mifi, dapatkan potongan biaya sewa sebesar sekian persen"',
            ],
            [
                'icon'        => '🎁',
                'title'       => 'Promo Loyalitas Pelanggan',
                'description' => 'Berikan reward khusus kepada pelanggan lama. Jika ada produk baru, tawarkan terlebih dahulu kepada pelanggan lama sebelum dipasarkan secara umum.',
                'example'     => null,
            ],
            [
                'icon'        => '📦',
                'title'       => 'Bundling dan Paket Hemat',
                'description' => 'Buat paket yang lebih menarik dan ekonomis dibandingkan membeli satuan, sehingga pelanggan merasa mendapat nilai lebih.',
                'example'     => 'Paket sewa Mifi + kuota tambahan dengan harga lebih ekonomis.',
            ],
            [
                'icon'        => '📱',
                'title'       => 'Promosi Melalui Media Sosial',
                'description' => 'Konsisten membuat konten edukasi, testimoni pelanggan, tips internet saat traveling, dan informasi promo. Manfaatkan Instagram, TikTok, Facebook, dan WhatsApp Business untuk menjangkau pasar yang lebih luas — termasuk kerja sama endorsement atau paid promote dengan influencer/selebgram.',
                'example'     => null,
            ],
            [
                'icon'        => '⭐',
                'title'       => 'Testimoni dan Ulasan Pelanggan',
                'description' => 'Tampilkan pengalaman pelanggan yang puas untuk meningkatkan kepercayaan calon pelanggan. Testimoni video umumnya lebih efektif dibandingkan teks.',
                'example'     => 'Berikan potongan diskon untuk pelanggan yang mengisi ulasan di Google Review.',
            ],
            [
                'icon'        => '🎉',
                'title'       => 'Promo Musiman',
                'description' => 'Berikan promo khusus saat liburan sekolah, Lebaran, Natal, Tahun Baru, atau musim wisata — disesuaikan dengan momen ketika kebutuhan internet portable meningkat.',
                'example'     => null,
            ],
            [
                'icon'        => '🏢',
                'title'       => 'Kerja Sama dengan Komunitas dan Perusahaan',
                'description' => 'Tawarkan harga khusus untuk komunitas, sekolah, kampus, travel agent, atau perusahaan yang membutuhkan Mifi dalam jumlah banyak.',
                'example'     => null,
            ],
        ];
    }

    public function render()
    {
        return view('livewire.business-strategy');
    }
}