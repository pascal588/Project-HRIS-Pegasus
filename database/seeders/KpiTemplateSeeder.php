<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KpiTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $this->createGlobalKpis();
            $this->createDivisionKpis();
            
            DB::commit();
            Log::info('KPI TemplateSeeder completed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KPI TemplateSeeder failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createGlobalKpis()
    {
        // ==================== KPI GLOBAL: DISIPLIN (20%) ====================
        $disiplinKpiId = DB::table('kpis')->insertGetId([
            'nama' => 'Disiplin',
            'bobot' => 20.00,
            'is_global' => true,
            'periode_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sub-aspek untuk Disiplin
        $disiplinPoints = [
            [
                'nama' => 'Absensi',
                'bobot' => 10.00,
                'questions' => [] // Absensi tidak perlu questions
            ],
            [
                'nama' => 'Etika dan Penampilan',
                'bobot' => 5.00,
                'questions' => [
                    'Sikap di hadapan publik',
                    'Kerapihan Penampilan',
                    'Kelengkapan Seragam'
                ]
            ],
            [
                'nama' => 'Kepatuhan',
                'bobot' => 5.00,
                'questions' => [
                    'Menjalankan aturan yang berlaku',
                    'Membuat dan Melaporkan hasil kinerja',
                    'Tanggungjawab kinerja'
                ]
            ]
        ];

        $this->createKpiPoints($disiplinKpiId, $disiplinPoints);

        // Link to all divisions
        $divisions = DB::table('divisions')->pluck('id_divisi');
        foreach ($divisions as $divisionId) {
            DB::table('division_has_kpis')->insert([
                'id_divisi' => $divisionId,
                'kpis_id_kpi' => $disiplinKpiId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ==================== KPI GLOBAL: KOMPETENSI UMUM (50%) ====================
        $kompetensiKpiId = DB::table('kpis')->insertGetId([
            'nama' => 'Kompetensi Umum',
            'bobot' => 50.00,
            'is_global' => true,
            'periode_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sub-aspek untuk Kompetensi Umum
        $kompetensiPoints = [
            [
                'nama' => 'INTEGRITAS',
                'bobot' => 5.00,
                'questions' => [
                    'Mampu bertindak dan bersikap secara konsisten sesuai standar minimal aturan dan target perusahaan yang telah diberlakukan',
                    'Mampu mempertanggungjawabkan segala perbuatan yang telah dilakukan atau diputuskan',
                    'Mampu mengaplikasikan nilai-nilai perusahaan baik kepada diri sendiri, rekan tim ataupun kepada publik dan turut menyuarakan kebenaran atau transparansi terhadap situasi yang kurang tepat'
                ]
            ],
            [
                'nama' => 'KERJA SAMA',
                'bobot' => 5.00,
                'questions' => [
                    'Mampu memberikan feedback (masukan) kepada tim kerjanya',
                    'Mampu mengekspresikan gagasannya secara konstruktif',
                    'Mampu menunjukkan partisipasi aktif dalam kerja tim (kolaboratif)',
                    'Mampu menjalin solidaritas di internal departemen dan menciptakan hubungan yang baik dengan orang lain di luar departemennya'
                ]
            ],
            [
                'nama' => 'INISIATIF DAN KREATIVITAS',
                'bobot' => 5.00,
                'questions' => [
                    'Mampu menghasilkan tugas melebihi tugas utama yang telah diberikan',
                    'Mampu menunjukkan keingintahuan atau minat yang tinggi terhadap suatu jenis pekerjaan yang belum dikuasainya',
                    'Mampu mengaplikasikan pengetahuan yang didapat atau dimiliki untuk meningkatkan performa kerja',
                    'Mampu menunjukkan usaha atau upaya yang konsisten saat harus mengatasi permasalahan yang muncul'
                ]
            ],
            [
                'nama' => 'PROFESSIONALISME',
                'bobot' => 5.00,
                'questions' => [
                    'Mampu menjelaskan tujuan dan target kerja di wilayah kerjanya secara jelas dan terukur',
                    'Mampu mempertanggungjawabkan pekerjaan yang menjadi tugasnya',
                    'Mampu mengatasi tugas sulit yang tengah dihadapinya secara efektif',
                    'Mampu untuk tidak membuat berita hoax dan atau membocorkan rahasia kinerja rekan satu tim kepada departemen lainnya dan sebaliknya maupun ke perusahaan lainnya'
                ]
            ],
            [
                'nama' => 'KEMAMPUAN MENGANALISA',
                'bobot' => 5.00,
                'questions' => [
                    'Mampu mengumpulkan dan menggali berbagai informasi dari berbagai sumber yang terkait dengan persoalan yang dihadapi',
                    'Mampu mengetahui hubungan antara beberapa bagian departemen atau sebab-akibat suatu masalah secara runtut dan logis',
                    'Mampu mengenali adanya masalah yang berada di perusahaan',
                    'Mampu fokus pada hal-hal penting dan kritis dalam menjalankan setiap tugasnya'
                ]
            ],
            [
                'nama' => 'KEMAMPUAN BERKOMUNIKASI',
                'bobot' => 5.00,
                'questions' => [
                    'Mampu mempresentasikan kepada tim, misi dan tujuan bersama secara jelas',
                    'Mampu membuat dokumen-dokumen informal untuk menjamin komunikasi internal',
                    'Mampu berkonsentrasi dan mendengarkan dengan baik tanpa memotong pembicaraan pihak lain, untuk menghadapi dan menghindari masalah',
                    'Mampu menggunakan komunikasi non verbal (misalnya: dengan bahasa tubuh, kontak mata, dll) bila diperlukan'
                ]
            ],
            [
                'nama' => 'KEMAMPUAN PEMECAHAN MASALAH',
                'bobot' => 5.00,
                'questions' => [
                    'Mampu memberikan solusi berupa cara alternatif dalam upaya menyelesaikan persoalan yang tengah di hadapi departemen atau perusahaan',
                    'Mampu mengenali permasalahan yang dihadapi dengan membuat ukuran skala prioritas yang harus di selesaikan terlebih dahulu',
                    'Mampu mengantisipasi permasalahan yang terjadi dengan membuat keputusan strategis yang tepat'
                ]
            ],
            [
                'nama' => 'KEMAMPUAN MANAGERIAL',
                'bobot' => 5.00,
                'questions' => [
                    'Mampu mengorganisir dan memodifikasi tugas rutin secara efektif',
                    'Mampu menuliskan rencana kerja individual berdasarkan job desc dan kebutuhan tim atau internal departemen',
                    'Mampu memonitor proses pekerjaan secara rutin',
                    'Mampu mengukur progres/perkembangan kerja harian/mingguan'
                ]
            ],
            [
                'nama' => 'KEMAMPUAN INTERPERSONAL',
                'bobot' => 5.00,
                'questions' => [
                    'Mampu mengenal baik emosi maupun inti pesan yang disampaikan orang lain dengan baik',
                    'Mampu menjalin kerjasama formal di lingkungan kerjanya untuk membantu terlaksananya aktivitas',
                    'Mampu menjalin kerjasama formal di lingkungan kerjanya untuk menciptakan suatu peluang yang lebih baik'
                ]
            ],
            [
                'nama' => 'KESADARAN TERHADAP PERUSAHAAN',
                'bobot' => 5.00,
                'questions' => [
                    'Mampu memahami peraturan dasar, khususnya yang berkaitan dengan hak dan kewajibannya dalam kepegawaian',
                    'Mampu menjelaskan struktur formal pemangku jabatan di perusahaan',
                    'Mampu memanfaatkan struktur formal pemangku jabatan di perusahaan untuk mendukung aktivitas kerjanya (misalnya dengan mengetahui alur perintah otoritas setiap posisi)',
                    'Mampu memahami SOP (Standart Operating Procedure) terhadap aktivitas pekerjaan yang dilakukannya'
                ]
            ]
        ];

        $this->createKpiPoints($kompetensiKpiId, $kompetensiPoints);

        // Link to all divisions
        foreach ($divisions as $divisionId) {
            DB::table('division_has_kpis')->insert([
                'id_divisi' => $divisionId,
                'kpis_id_kpi' => $kompetensiKpiId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function createDivisionKpis()
    {
        $divisions = DB::table('divisions')->get();
        
        foreach ($divisions as $division) {
            $this->createTechnicalCompetencyKpi($division->id_divisi, $division->nama_divisi);
        }
    }

    private function createTechnicalCompetencyKpi($divisionId, $divisionName)
    {
        $technicalKpiId = DB::table('kpis')->insertGetId([
            'nama' => 'Kompetensi Teknikal - ' . $divisionName,
            'bobot' => 30.00,
            'is_global' => false,
            'periode_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sub-aspek untuk Kompetensi Teknikal
        $technicalPoints = [
            [
                'nama' => 'ADMINISTRASI',
                'bobot' => 10.00,
                'questions' => [
                    'Mampu mengontrol proses kegiatan administrasi di wilayah kerjanya (misalnya: proses surat, informasi data, proposal dll)',
                    'Mampu mengidentifikasi berbagai hambatan dalam proses transfer data dari atau kepada konsumen, mitra bisnis ataupun dari departemen lainnya',
                    'Mampu memonitor pengecekan data yang diberikan dan diperoleh dari konsumen, mitra ataupun dari departemen lainnya',
                    'Mampu mengecek kelengkapan berbagai laporan yang diperlukan di wilayah kerjanya hingga kebenaran dari proses data'
                ]
            ],
            [
                'nama' => 'PENCAPAIAN TARGET PENJUALAN',
                'bobot' => 10.00,
                'questions' => [
                    'Mampu memenuhi target penjualan yang telah ditetapkan oleh departemen atau atasan',
                    'Mampu menggunakan alat bantu berjualan secara tepat sasaran',
                    'Mampu memenuhi target aktivitas penjualan, seperti menghubungi konsumen atau mengunjungi konsumen'
                ]
            ],
            [
                'nama' => 'PENGETAHUAN PRODUK DAN PASAR',
                'bobot' => 10.00,
                'questions' => [
                    'Mampu menguasai produk yang dipercayakan oleh atasan',
                    'Mampu menjelaskan produk dengan tepat kepada konsumen',
                    'Mampu mengenali kompetisi persaingan dengan perusahaan lain',
                    'Mampu memahami kebutuhan konsumen dan tren yang berkembang'
                ]
            ]
        ];

        $this->createKpiPoints($technicalKpiId, $technicalPoints);

        // Link to specific division
        DB::table('division_has_kpis')->insert([
            'id_divisi' => $divisionId,
            'kpis_id_kpi' => $technicalKpiId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createKpiPoints($kpiId, array $points)
    {
        foreach ($points as $pointData) {
            $pointId = DB::table('kpi_points')->insertGetId([
                'kpis_id_kpi' => $kpiId,
                'nama' => $pointData['nama'],
                'bobot' => $pointData['bobot'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create questions for this point (if any)
            foreach ($pointData['questions'] as $questionText) {
                DB::table('kpi_questions')->insert([
                    'kpi_point_id' => $pointId,
                    'pertanyaan' => $questionText,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}