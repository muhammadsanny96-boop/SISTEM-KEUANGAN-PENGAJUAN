@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')
@section('page_title', 'Tambah Pengguna Baru')
@section('page_subtitle', 'Daftarkan akun karyawan atau administrator baru ke dalam sistem')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Pengguna
        </a>
    </div>

    <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6 sm:p-10">
        <div class="border-b border-slate-100 pb-5 mb-8">
            <h2 class="text-xl font-extrabold text-slate-900">Formulir Pengguna Baru</h2>
            <p class="text-xs text-slate-500 mt-1">Lengkapi informasi biodata, divisi penugasan, dan kata sandi akun.</p>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
            @csrf

            <!-- Nama & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                           placeholder="Contoh: Muhammad Rizky" 
                           class="w-full text-sm rounded-xl border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 py-2.5 px-3.5 text-slate-900">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required 
                           placeholder="email@perusahaan.com" 
                           class="w-full text-sm rounded-xl border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 py-2.5 px-3.5 text-slate-900">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Role & Divisi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="role" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Role Hak Akses <span class="text-rose-500">*</span>
                    </label>
                    <select name="role" id="role" required 
                            class="w-full text-sm rounded-xl border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 py-2.5 px-3.5 text-slate-900 font-medium">
                        <option value="user" {{ old('role', 'user') === 'user' ? 'selected' : '' }}>User (Karyawan Divisi)</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Administrator Utama)</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="division_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Divisi Penugasan
                    </label>
                    <select name="division_id" id="division_id" 
                            class="w-full text-sm rounded-xl border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 py-2.5 px-3.5 text-slate-900 font-medium">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($divisions as $div)
                            <option value="{{ $div->id }}" {{ old('division_id') == $div->id ? 'selected' : '' }}>
                                {{ $div->nama_divisi }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Wajib untuk user karyawan agar dapat membuat permohonan</p>
                    @error('division_id')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Telepon & Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nomor Telepon / WhatsApp
                    </label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" 
                           placeholder="08123456789" 
                           class="w-full text-sm rounded-xl border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 py-2.5 px-3.5 text-slate-900">
                    @error('phone')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kata Sandi (Password) <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="password" id="password" required 
                           placeholder="Minimal 8 karakter" 
                           class="w-full text-sm rounded-xl border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 py-2.5 px-3.5 text-slate-900">
                    @error('password')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-xs transition-colors">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/30 transition-all">
                    Simpan Pengguna Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
