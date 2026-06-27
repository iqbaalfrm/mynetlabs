<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ═══ KELAS ═══
        $kelasXtkj1 = Kelas::updateOrCreate(['nama_kelas' => 'X TKJ 1']);
        $kelasXtkj2 = Kelas::updateOrCreate(['nama_kelas' => 'X TKJ 2']);
        $kelasXitkj1 = Kelas::updateOrCreate(['nama_kelas' => 'XI TKJ 1']);
        $kelasXitkj2 = Kelas::updateOrCreate(['nama_kelas' => 'XI TKJ 2']);
        $kelasXiitkj1 = Kelas::updateOrCreate(['nama_kelas' => 'XII TKJ 1']);
        $kelasXiitkj2 = Kelas::updateOrCreate(['nama_kelas' => 'XII TKJ 2']);

        // ═══ GURU (3) ═══
        $guru1 = User::updateOrCreate(
            ['username' => '19950812001'],
            ['nama' => 'Budi Hartono, S.Kom.', 'password' => Hash::make('guru123'), 'role' => 'guru', 'kelas' => null, 'kelas_id' => null]
        );
        $guru2 = User::updateOrCreate(
            ['username' => '19900315002'],
            ['nama' => 'Rina Sulistyowati, S.Pd., M.T.', 'password' => Hash::make('guru123'), 'role' => 'guru', 'kelas' => null, 'kelas_id' => null]
        );
        $guru3 = User::updateOrCreate(
            ['username' => '19871220003'],
            ['nama' => 'Agus Setiawan, S.Kom., M.Kom.', 'password' => Hash::make('guru123'), 'role' => 'guru', 'kelas' => null, 'kelas_id' => null]
        );

        // ═══ WALI KELAS ═══
        $kelasXtkj1->update(['wali_kelas_id' => $guru1->id]);
        $kelasXtkj2->update(['wali_kelas_id' => $guru2->id]);
        $kelasXitkj1->update(['wali_kelas_id' => $guru1->id]);
        $kelasXitkj2->update(['wali_kelas_id' => $guru2->id]);
        $kelasXiitkj1->update(['wali_kelas_id' => $guru3->id]);
        $kelasXiitkj2->update(['wali_kelas_id' => $guru3->id]);

        // ═══ SISWA (48 — 8 per kelas) ═══
        $siswaData = [
            ['nis'=>'24001','nama'=>'Ahmad Fauzi','kelas'=>$kelasXtkj1],
            ['nis'=>'24002','nama'=>'Bella Anggraini','kelas'=>$kelasXtkj1],
            ['nis'=>'24003','nama'=>'Cahya Pratama','kelas'=>$kelasXtkj1],
            ['nis'=>'24004','nama'=>'Dewi Lestari','kelas'=>$kelasXtkj1],
            ['nis'=>'24005','nama'=>'Eko Nugroho','kelas'=>$kelasXtkj1],
            ['nis'=>'24006','nama'=>'Fitri Handayani','kelas'=>$kelasXtkj1],
            ['nis'=>'24007','nama'=>'Gilang Ramadhan','kelas'=>$kelasXtkj1],
            ['nis'=>'24008','nama'=>'Hana Safitri','kelas'=>$kelasXtkj1],
            ['nis'=>'24009','nama'=>'Ilham Saputra','kelas'=>$kelasXtkj2],
            ['nis'=>'24010','nama'=>'Jasmine Aulia','kelas'=>$kelasXtkj2],
            ['nis'=>'24011','nama'=>'Kevin Ardiansyah','kelas'=>$kelasXtkj2],
            ['nis'=>'24012','nama'=>'Laila Maharani','kelas'=>$kelasXtkj2],
            ['nis'=>'24013','nama'=>'Muhammad Rizky','kelas'=>$kelasXtkj2],
            ['nis'=>'24014','nama'=>'Nadia Putri','kelas'=>$kelasXtkj2],
            ['nis'=>'24015','nama'=>'Oscar Wibowo','kelas'=>$kelasXtkj2],
            ['nis'=>'24016','nama'=>'Putri Amalia','kelas'=>$kelasXtkj2],
            ['nis'=>'23001','nama'=>'Moch Iqbal Firmansyah','kelas'=>$kelasXitkj1],
            ['nis'=>'23002','nama'=>'Rani Kusuma','kelas'=>$kelasXitkj1],
            ['nis'=>'23003','nama'=>'Sandi Permana','kelas'=>$kelasXitkj1],
            ['nis'=>'23004','nama'=>'Tika Rahmawati','kelas'=>$kelasXitkj1],
            ['nis'=>'23005','nama'=>'Umar Hasan','kelas'=>$kelasXitkj1],
            ['nis'=>'23006','nama'=>'Vina Damayanti','kelas'=>$kelasXitkj1],
            ['nis'=>'23007','nama'=>'Wahyu Setiawan','kelas'=>$kelasXitkj1],
            ['nis'=>'23008','nama'=>'Xenia Oktavia','kelas'=>$kelasXitkj1],
            ['nis'=>'23009','nama'=>'Yoga Firmansyah','kelas'=>$kelasXitkj2],
            ['nis'=>'23010','nama'=>'Zahra Nuraini','kelas'=>$kelasXitkj2],
            ['nis'=>'23011','nama'=>'Andi Maulana','kelas'=>$kelasXitkj2],
            ['nis'=>'23012','nama'=>'Bunga Citra','kelas'=>$kelasXitkj2],
            ['nis'=>'23013','nama'=>'Candra Aditya','kelas'=>$kelasXitkj2],
            ['nis'=>'23014','nama'=>'Dian Permata','kelas'=>$kelasXitkj2],
            ['nis'=>'23015','nama'=>'Erlangga Dwi','kelas'=>$kelasXitkj2],
            ['nis'=>'23016','nama'=>'Fara Nur Azizah','kelas'=>$kelasXitkj2],
            ['nis'=>'22001','nama'=>'Rudi Hartanto','kelas'=>$kelasXiitkj1],
            ['nis'=>'22002','nama'=>'Sari Wulandari','kelas'=>$kelasXiitkj1],
            ['nis'=>'22003','nama'=>'Tegar Prasetyo','kelas'=>$kelasXiitkj1],
            ['nis'=>'22004','nama'=>'Utami Ningrum','kelas'=>$kelasXiitkj1],
            ['nis'=>'22005','nama'=>'Victor Manurung','kelas'=>$kelasXiitkj1],
            ['nis'=>'22006','nama'=>'Winda Pratiwi','kelas'=>$kelasXiitkj1],
            ['nis'=>'22007','nama'=>'Yusuf Hidayat','kelas'=>$kelasXiitkj1],
            ['nis'=>'22008','nama'=>'Zulfa Khairunnisa','kelas'=>$kelasXiitkj1],
            ['nis'=>'22009','nama'=>'Aditya Nugraha','kelas'=>$kelasXiitkj2],
            ['nis'=>'22010','nama'=>'Bunga Lestari','kelas'=>$kelasXiitkj2],
            ['nis'=>'22011','nama'=>'Candra Kirana','kelas'=>$kelasXiitkj2],
            ['nis'=>'22012','nama'=>'Dimas Ardian','kelas'=>$kelasXiitkj2],
            ['nis'=>'22013','nama'=>'Eva Marlina','kelas'=>$kelasXiitkj2],
            ['nis'=>'22014','nama'=>'Farhan Maulana','kelas'=>$kelasXiitkj2],
            ['nis'=>'22015','nama'=>'Gita Savitri','kelas'=>$kelasXiitkj2],
            ['nis'=>'22016','nama'=>'Hendra Gunawan','kelas'=>$kelasXiitkj2],
        ];
        foreach ($siswaData as $s) {
            User::updateOrCreate(
                ['username' => $s['nis']],
                ['nama' => $s['nama'], 'password' => Hash::make('siswa123'), 'role' => 'siswa', 'kelas' => $s['kelas']->nama_kelas, 'kelas_id' => $s['kelas']->id]
            );
        }
    }
}