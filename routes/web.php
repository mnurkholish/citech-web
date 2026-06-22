<?php

use App\Models\Timeline;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $activeTimeline = Timeline::where('tanggal_selesai', '>', now())
        ->orderBy('tanggal_selesai', 'asc')
        ->first() ?? Timeline::orderBy('tanggal_selesai', 'desc')->first();

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'activeTimeline' => $activeTimeline,
        'allTimelines' => Timeline::orderBy('tanggal_mulai', 'asc')->get(),
    ]);
});

Route::get('/dashboard', function () {
    if (auth()->user()->is_admin) {
        return redirect()->route('admin.dashboard');
    }

    $user = auth()->user()->load('tim.members', 'tim.dokumen_registrasi', 'tim.pembayaran', 'tim.submission');
    $activeTimeline = Timeline::where('tanggal_selesai', '>', now())
        ->orderBy('tanggal_selesai', 'asc')
        ->first() ?? Timeline::orderBy('tanggal_selesai', 'desc')->first();

    return Inertia::render('Dashboard', [
        'activeTimeline' => $activeTimeline,
        'allTimelines' => Timeline::orderBy('tanggal_mulai', 'asc')->get(),
        'userTeam' => $user->tim,
        'teamMembers' => $user->tim ? $user->tim->members : [],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// Peserta Routes
Route::middleware(['auth', 'verified', 'peserta'])->group(function () {
    Route::get('/dashboard/tim', function () {
        $user = auth()->user()->load('tim.members', 'tim.dokumen_registrasi', 'tim.pembayaran', 'tim.submission');
        $timelineB2 = \App\Models\Timeline::where('tahap', 'pendaftaran_b2')->first();
        $isSubmissionOpen = $timelineB2 ? now()->lte($timelineB2->tanggal_selesai) : false;

        return Inertia::render('peserta/Tim', [
            'userTeam' => $user->tim,
            'teamMembers' => $user->tim ? $user->tim->members : [],
            'isSubmissionOpen' => $isSubmissionOpen,
        ]);
    })->name('peserta.tim');

    Route::post('/dashboard/tim', function (\Illuminate\Http\Request $request) {
        $user = auth()->user();
        $existingTim = $user->tim;

        if ($existingTim && $existingTim->dokumen_registrasi) {
            $statusReg = $existingTim->dokumen_registrasi->status_registrasi;
            if ($statusReg === 'berhasil') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'nama_tim' => 'Data tim tidak dapat diubah karena berkas persyaratan pendaftaran sudah disetujui.'
                ]);
            }
            if ($statusReg === 'pending') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'nama_tim' => 'Data tim tidak dapat diubah saat berkas persyaratan pendaftaran sedang diverifikasi oleh panitia.'
                ]);
            }
        }

        $request->validate([
            'nama_tim' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('tim', 'nama_tim')->ignore($existingTim?->id_tim, 'id_tim')
            ],
            'universitas' => 'required|string|max:255',
            // Ketua details
            'nim_ketua' => 'required|string|max:255',
            'jurusan_ketua' => 'required|string|max:255',
            // Anggota 1
            'nama_anggota1' => 'required|string|max:255',
            'nim_anggota1' => 'required|string|max:255',
            'jurusan_anggota1' => 'required|string|max:255',
            // Anggota 2
            'nama_anggota2' => 'nullable|string|max:255',
            'nim_anggota2' => 'nullable|string|max:255',
            'jurusan_anggota2' => 'nullable|string|max:255',
        ]);

        $universitas = $request->universitas;
        $memberIds = $existingTim ? $existingTim->members->pluck('id_member')->toArray() : [];

        // Check if NIM is unique within the same university (cross-team validation)
        $checkNimUnique = function ($nim, $universitas, $field, $label) use ($memberIds) {
            if (!$nim) return;
            $exists = \App\Models\MemberTim::where('nim_peserta', $nim)
                ->whereNotIn('id_member', $memberIds)
                ->whereHas('tim', function ($query) use ($universitas) {
                    $query->where('universitas', $universitas);
                })
                ->exists();
            if ($exists) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $field => 'NIM ' . $nim . ' sudah terdaftar untuk ' . $label . ' dari universitas ini di tim lain.'
                ]);
            }
        };

        $checkNimUnique($request->nim_ketua, $universitas, 'nim_ketua', 'ketua');
        $checkNimUnique($request->nim_anggota1, $universitas, 'nim_anggota1', 'anggota 1');
        $checkNimUnique($request->nim_anggota2, $universitas, 'nim_anggota2', 'anggota 2');

        // Check duplicate NIM entries within the same team
        if ($request->nim_ketua === $request->nim_anggota1) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'nim_anggota1' => 'NIM anggota 1 tidak boleh sama dengan NIM ketua.'
            ]);
        }
        if ($request->nim_anggota2) {
            if ($request->nim_ketua === $request->nim_anggota2) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'nim_anggota2' => 'NIM anggota 2 tidak boleh sama dengan NIM ketua.'
                ]);
            }
            if ($request->nim_anggota1 === $request->nim_anggota2) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'nim_anggota2' => 'NIM anggota 2 tidak boleh sama dengan NIM anggota 1.'
                ]);
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $user, $existingTim) {
            $tim = \App\Models\Tim::updateOrCreate(
                ['id_user' => $user->id_user],
                [
                    'nama_tim' => $request->nama_tim,
                    'universitas' => $request->universitas,
                    'status_seleksi' => $existingTim ? $existingTim->status_seleksi : 'belum_seleksi',
                    'batch' => $existingTim ? $existingTim->batch : 1,
                ]
            );

            // Recreate members safely
            $tim->members()->delete();

            // Ketua (Leader)
            $tim->members()->create([
                'nama_peserta' => $user->name,
                'nim_peserta' => $request->nim_ketua,
                'jurusan' => $request->jurusan_ketua,
                'role' => 'ketua',
            ]);

            // Anggota 1 (Member 1)
            $tim->members()->create([
                'nama_peserta' => $request->nama_anggota1,
                'nim_peserta' => $request->nim_anggota1,
                'jurusan' => $request->jurusan_anggota1,
                'role' => 'anggota',
            ]);

            // Anggota 2 (Member 2 - optional)
            if ($request->nama_anggota2 && $request->nim_anggota2 && $request->jurusan_anggota2) {
                $tim->members()->create([
                    'nama_peserta' => $request->nama_anggota2,
                    'nim_peserta' => $request->nim_anggota2,
                    'jurusan' => $request->jurusan_anggota2,
                    'role' => 'anggota',
                ]);
            }
        });

        return redirect()->route('peserta.tim')->with('success', 'Data tim berhasil disimpan!');
    })->name('peserta.tim.store');

    Route::post('/dashboard/tim/dokumen', function (\Illuminate\Http\Request $request) {
        $user = auth()->user();
        $tim = $user->tim;
        if (!$tim) {
            return redirect()->back()->withErrors(['message' => 'Anda harus membuat tim terlebih dahulu.']);
        }

        if ($tim->dokumen_registrasi && $tim->dokumen_registrasi->status_registrasi === 'berhasil') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file_dokumen' => 'Berkas persyaratan pendaftaran sudah disetujui dan tidak dapat diubah.'
            ]);
        }

        $request->validate([
            'file_dokumen' => 'required|file|mimes:pdf|max:5120',
        ], [
            'file_dokumen.required' => 'Berkas persyaratan wajib diunggah.',
            'file_dokumen.file' => 'Berkas yang diunggah harus berupa file.',
            'file_dokumen.mimes' => 'Berkas harus dalam format PDF.',
            'file_dokumen.max' => 'Ukuran berkas maksimal adalah 5MB.',
        ]);

        if ($tim->dokumen_registrasi) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($tim->dokumen_registrasi->link_file_registrasi);
        }

        $path = $request->file('file_dokumen')->store('dokumen_registrasi', 'public');

        \App\Models\DokumenRegistrasi::updateOrCreate(
            ['id_tim' => $tim->id_tim],
            [
                'link_file_registrasi' => $path,
                'status_registrasi' => 'pending',
                'catatan_registrasi' => null,
                'uploaded_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Berkas persyaratan berhasil diunggah!');
    })->name('peserta.tim.dokumen.store');

    Route::delete('/dashboard/tim/dokumen', function () {
        $user = auth()->user();
        $tim = $user->tim;
        if (!$tim || !$tim->dokumen_registrasi) {
            return redirect()->back()->withErrors(['message' => 'Dokumen tidak ditemukan.']);
        }

        if ($tim->dokumen_registrasi->status_registrasi === 'berhasil') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file_dokumen' => 'Berkas persyaratan pendaftaran sudah disetujui dan tidak dapat dibatalkan.'
            ]);
        }

        \Illuminate\Support\Facades\Storage::disk('public')->delete($tim->dokumen_registrasi->link_file_registrasi);
        $tim->dokumen_registrasi->delete();

        return redirect()->back()->with('success', 'Unggahan berkas berhasil dibatalkan.');
    })->name('peserta.tim.dokumen.destroy');

    Route::post('/dashboard/tim/pembayaran', function (\Illuminate\Http\Request $request) {
        $user = auth()->user();
        $tim = $user->tim;
        if (!$tim) {
            return redirect()->back()->withErrors(['message' => 'Anda harus membuat tim terlebih dahulu.']);
        }

        if ($tim->pembayaran && $tim->pembayaran->status_pembayaran === 'berhasil') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'bukti_pembayaran' => 'Bukti pembayaran sudah disetujui dan tidak dapat diubah.'
            ]);
        }

        $request->validate([
            'bukti_pembayaran' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ], [
            'bukti_pembayaran.required' => 'Bukti pembayaran wajib diunggah.',
            'bukti_pembayaran.file' => 'Berkas yang diunggah harus berupa file.',
            'bukti_pembayaran.mimes' => 'Berkas harus dalam format JPG, PNG, atau PDF.',
            'bukti_pembayaran.max' => 'Ukuran berkas maksimal adalah 5MB.',
        ]);

        if ($tim->pembayaran) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($tim->pembayaran->bukti_pembayaran);
        }

        $path = $request->file('bukti_pembayaran')->store('bukti_pembayaran', 'public');

        \App\Models\Pembayaran::updateOrCreate(
            ['id_tim' => $tim->id_tim],
            [
                'bukti_pembayaran' => $path,
                'status_pembayaran' => 'pending',
                'catatan_pembayaran' => null,
                'uploaded_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah!');
    })->name('peserta.tim.pembayaran.store');

    Route::delete('/dashboard/tim/pembayaran', function () {
        $user = auth()->user();
        $tim = $user->tim;
        if (!$tim || !$tim->pembayaran) {
            return redirect()->back()->withErrors(['message' => 'Data pembayaran tidak ditemukan.']);
        }

        // Guard: prevent cancellation if payment already approved
        if ($tim->pembayaran->status_pembayaran === 'berhasil') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'bukti_pembayaran' => 'Bukti pembayaran sudah disetujui dan tidak dapat dibatalkan.'
            ]);
        }

        \Illuminate\Support\Facades\Storage::disk('public')->delete($tim->pembayaran->bukti_pembayaran);
        $tim->pembayaran->delete();

        return redirect()->back()->with('success', 'Unggahan bukti pembayaran berhasil dibatalkan.');
    })->name('peserta.tim.pembayaran.destroy');

    Route::post('/dashboard/tim/submission', function (\Illuminate\Http\Request $request) {
        $user = auth()->user();
        $tim = $user->tim;
        if (!$tim) {
            return redirect()->back()->withErrors(['message' => 'Anda harus membuat tim terlebih dahulu.']);
        }

        if ($tim->submission) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'link_file_submission' => 'Anda sudah pernah mengumpulkan proposal.'
            ]);
        }

        if ($tim->status_seleksi !== 'penyisihan') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'link_file_submission' => 'Anda hanya dapat mengumpulkan proposal setelah verifikasi berkas dan pembayaran disetujui oleh panitia.'
            ]);
        }

        $timelineB2 = \App\Models\Timeline::where('tahap', 'pendaftaran_b2')->first();
        if ($timelineB2 && now() > $timelineB2->tanggal_selesai) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'link_file_submission' => 'Batas waktu pengumpulan proposal (Batch 2) telah berakhir.'
            ]);
        }

        $request->validate([
            'link_file_submission' => 'required|url|max:1000',
        ], [
            'link_file_submission.required' => 'Link Google Drive wajib diisi.',
            'link_file_submission.url' => 'Format link harus berupa URL valid.',
            'link_file_submission.max' => 'Link maksimal 1000 karakter.',
        ]);

        \App\Models\DokumenSubmission::create([
            'id_tim' => $tim->id_tim,
            'link_file_submission' => $request->link_file_submission,
            'uploaded_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Proposal berhasil dikumpulkan!');
    })->name('peserta.tim.submission.store');

    Route::get('/dashboard/profil', function () {
        return Inertia::render('peserta/Profil');
    })->name('peserta.profil');
});

// Admin Routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('admin/Dashboard');
    })->name('admin.dashboard');

    Route::get('/konfirmasi-persyaratan', function () {
        $teams = \App\Models\Tim::with(['members', 'dokumen_registrasi'])->get();
        return Inertia::render('admin/KonfirmasiPersyaratan', [
            'teams' => $teams
        ]);
    })->name('admin.persyaratan');

    Route::post('/konfirmasi-persyaratan/{id_registrasi}/status', function (\Illuminate\Http\Request $request, $id_registrasi) {
        $request->validate([
            'status' => 'required|in:berhasil,ditolak',
            'catatan' => 'required_if:status,ditolak|nullable|string|max:1000',
        ], [
            'status.required' => 'Status konfirmasi harus ditentukan.',
            'status.in' => 'Status konfirmasi tidak valid.',
            'catatan.required_if' => 'Catatan penolakan wajib diisi jika status ditolak.',
            'catatan.max' => 'Catatan penolakan maksimal 1000 karakter.',
        ]);

        $dokumen = \App\Models\DokumenRegistrasi::findOrFail($id_registrasi);
        $dokumen->update([
            'status_registrasi' => $request->status,
            'catatan_registrasi' => $request->status === 'ditolak' ? $request->catatan : null,
        ]);

        return redirect()->back()->with('success', 'Status dokumen berhasil diperbarui!');
    })->name('admin.persyaratan.update');

    Route::get('/konfirmasi-pembayaran', function () {
        $teams = \App\Models\Tim::with(['members', 'pembayaran'])->get();
        return Inertia::render('admin/KonfirmasiPembayaran', [
            'teams' => $teams
        ]);
    })->name('admin.pembayaran');

    Route::post('/konfirmasi-pembayaran/{id_pembayaran}/status', function (\Illuminate\Http\Request $request, $id_pembayaran) {
        $request->validate([
            'status' => 'required|in:berhasil,ditolak',
            'catatan' => 'required_if:status,ditolak|nullable|string|max:1000',
        ], [
            'status.required' => 'Status konfirmasi harus ditentukan.',
            'status.in' => 'Status konfirmasi tidak valid.',
            'catatan.required_if' => 'Catatan penolakan wajib diisi jika status ditolak.',
            'catatan.max' => 'Catatan penolakan maksimal 1000 karakter.',
        ]);

        $pembayaran = \App\Models\Pembayaran::findOrFail($id_pembayaran);

        \Illuminate\Support\Facades\DB::transaction(function () use ($pembayaran, $request) {
            $pembayaran->update([
                'status_pembayaran' => $request->status,
                'catatan_pembayaran' => $request->status === 'ditolak' ? $request->catatan : null,
            ]);

            // If approved, update team status_seleksi to 'penyisihan'
            if ($request->status === 'berhasil') {
                $pembayaran->tim->update([
                    'status_seleksi' => 'penyisihan'
                ]);
            }
        });

        return redirect()->back()->with('success', 'Status pembayaran berhasil diperbarui!');
    })->name('admin.pembayaran.update');

    Route::get('/tim-terdaftar', function () {
        $teams = \App\Models\Tim::with(['members', 'dokumen_registrasi', 'pembayaran'])
            ->where('status_seleksi', '!=', 'belum_seleksi')
            ->get();
        return Inertia::render('admin/TimTerdaftar', [
            'teams' => $teams
        ]);
    })->name('admin.tim-terdaftar');

    Route::get('/submission', function () {
        $teams = \App\Models\Tim::with(['members', 'submission'])
            ->whereHas('submission')
            ->get();
        return Inertia::render('admin/Submission', [
            'teams' => $teams
        ]);
    })->name('admin.submission');

    Route::get('/atur-tanggal', function () {
        return Inertia::render('admin/AturTanggal');
    })->name('admin.atur-tanggal');

    Route::get('/kelola-sponsor', function () {
        return Inertia::render('admin/KelolaSponsor');
    })->name('admin.kelola-sponsor');
});

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
