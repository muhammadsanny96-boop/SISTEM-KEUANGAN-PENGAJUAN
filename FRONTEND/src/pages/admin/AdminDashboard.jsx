import React, { useEffect, useState } from 'react';
import api from '../../services/api';
import { StatusBadge } from '../../components/StatusBadge';
import { Link } from 'react-router-dom';
import {
  BarChart,
  Bar,
  Area,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
} from 'recharts';
import {
  CheckSquare,
  Clock,
  CheckCircle2,
  XCircle,
  DollarSign,
  Building,
  Users,
  Layers,
  ArrowRight,
  RefreshCw,
  TrendingUp,
  ShieldCheck,
  Download,
  FileSpreadsheet,
  Printer,
  X,
  FileText,
  Building2,
  Calendar,
} from 'lucide-react';

export const AdminDashboard = () => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showReportModal, setShowReportModal] = useState(false);
  const [selectedPeriod, setSelectedPeriod] = useState(''); // '' = Bulan Berjalan
  const [availableMonths, setAvailableMonths] = useState([]);

  // Fungsi Fetch Data Dashboard dengan Filter Periode/Arsip
  const fetchDashboard = async (periode = selectedPeriod) => {
    setLoading(true);
    try {
      const params = periode ? { target_bulan: periode } : {};
      const [dashRes, optRes] = await Promise.all([
        api.get('/admin/dashboard', { params }),
        availableMonths.length === 0 ? api.get('/options') : Promise.resolve(null),
      ]);

      setData(dashRes.data.data);

      if (optRes && optRes.data.months) {
        setAvailableMonths(optRes.data.months.map((m) => m.value || m));
      }
    } catch (err) {
      console.error('Error fetching admin dashboard:', err);
    } finally {
      setLoading(false);
    }
  };

  // Panggil fetch setiap kali Admin memilih bulan yang berbeda di Dropdown
  useEffect(() => {
    fetchDashboard(selectedPeriod);
  }, [selectedPeriod]);

  useEffect(() => {
    fetchDashboard();
  }, []);

  const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(number || 0);
  };

  const counts = data?.counts || {};
  const finances = data?.finances || {};
  const divisionStats = data?.division_stats || [];
  const recent = data?.recent_submissions || [];

  // Fungsi Export Excel / CSV dengan Format Rapi & Terstruktur (BOM UTF-8 & Delimiter ;)
  const handleDownloadExcel = () => {
    if (!data) return;

    const periode = finances.current_month_name || 'Bulan Ini';
    const tanggalCetak = new Date().toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });

    // \uFEFF (UTF-8 BOM) dan sep=;\n agar Excel di Windows memecah kolom dengan rapi
    let csvContent = '\uFEFFsep=;\n';

    // Header Resmi
    csvContent += `PT JAMKRIDA KALIMANTAN SELATAN\n`;
    csvContent += `LAPORAN REKAPITULASI USULAN PENGADAAN & REALISASI ANGGARAN\n`;
    csvContent += `Periode:;${periode};Tanggal Unduh:;${tanggalCetak}\n\n`;

    // 1. RINGKASAN METRIK UTAMA
    csvContent += `1. RINGKASAN METRIK EKSEKUTIF\n`;
    csvContent += `Indikator;Jumlah / Nilai;Keterangan\n`;
    csvContent += `Total Usulan Masuk;${counts.total || 0};Seluruh usulan dari divisi kerja\n`;
    csvContent += `Menunggu Review (Pending);${counts.pending || 0};Perlu verifikasi & persetujuan\n`;
    csvContent += `Disetujui & Selesai;${(counts.approved || 0) + (counts.completed || 0)};Usulan yang telah disetujui / sah\n`;
    csvContent += `Ditolak;${counts.rejected || 0};Tidak memenuhi syarat / revisi\n`;
    csvContent += `Total Estimasi Pengeluaran Bulan Ini;"${formatRupiah(finances.expense_this_month)}";Usulan aktif periode berjalan\n`;
    csvContent += `Realisasi Anggaran Disetujui;"${formatRupiah(finances.realized_this_month)}";Realisasi nota faktur sah\n`;
    csvContent += `Proyeksi Pengeluaran Bulan Depan;"${formatRupiah(finances.expense_next_month)}";Estimasi periode berikutnya\n\n`;

    // 2. REKAPITULASI DIVISI
    csvContent += `2. REKAPITULASI USULAN PER DIVISI KERJA\n`;
    csvContent += `No;Nama Divisi;Jumlah Usulan;Estimasi Total Anggaran;Status\n`;
    divisionStats.forEach((d, idx) => {
      const totalDivisi = Number(d.total_biaya || d.submissions_count * 1500000);
      csvContent += `${idx + 1};"${d.nama_divisi}";${d.submissions_count};"${formatRupiah(totalDivisi)}";Aktif\n`;
    });
    csvContent += `\n`;

    // 3. DAFTAR USULAN TERBARU
    csvContent += `3. DAFTAR USULAN TERBARU MASUK\n`;
    csvContent += `No;No. Pengajuan;Divisi;Pemohon;Nama Barang / Jasa;Estimasi Biaya;Status\n`;
    recent.forEach((s, idx) => {
      csvContent += `${idx + 1};"${s.nomor_pengajuan}";"${s.division?.nama_divisi || '-'}";"${s.user?.name || '-'}";"${s.nama_barang}";"${formatRupiah(s.total_biaya)}";"${s.status}"\n`;
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `Laporan_Rekap_Pengadaan_Jamkrida_${periode.replace(/\s+/g, '_')}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  };

  // Fungsi Cetak / Simpan PDF via Browser Print
  const handlePrintPDF = () => {
    window.print();
  };

  if (loading) {
    return (
      <div className="p-8 flex items-center justify-center min-h-[50vh]">
        <div className="flex items-center gap-3 text-slate-500 font-medium">
          <RefreshCw className="w-5 h-5 animate-spin text-emerald-700" />
          Memuat data analitik pengadaan...
        </div>
      </div>
    );
  }

  const defaultMonths = [
    { short : 'Jan', full: 'Januari' },
    { short: 'Feb', full: 'Februari' },
    { short: 'Mar', full: 'Maret' },
    { short: 'Apr', full: 'April' },
    { short: 'Mei', full: 'Mei' },
    { short: 'Jun', full: 'Juni' },
    { short: 'Jul', full: 'Juli' },
    { short: 'Agu', full: 'Agustus' },
    { short: 'Sep', full: 'September' },
    { short: 'Okt', full: 'Oktober' },
    { short: 'Nov', full: 'November' },
    { short: 'Des', full: 'Desember' },
  ]

      const monthlyChartData = data?.monthly_stats || defaultMonths.map((m) => {
    // 1. Cari & hitung otomatis semua pengajuan yang target bulannya cocok dengan bulan ini
    const submissionsInMonth = recent.filter((s) =>
      s.target_bulan?.toLowerCase().includes(m.full.toLowerCase())
    );

    let usulan = submissionsInMonth.reduce((sum, s) => sum + Number(s.total_biaya || 0), 0);
    let realisasi = submissionsInMonth.reduce((sum, s) => sum + Number(s.biaya_realisasi || 0), 0);

    // 2. Gabungkan dengan data ringkasan finansial
    const isCurrent = finances.current_month_name?.toLowerCase().includes(m.full.toLowerCase());
    const isNext = finances.next_month_name?.toLowerCase().includes(m.full.toLowerCase());

    if (isCurrent && finances.expense_this_month) {
      usulan = Math.max(usulan, Number(finances.expense_this_month || 0));
      realisasi = Math.max(realisasi, Number(finances.realized_this_month || 0));
    }
    if (isNext && (finances.expense_next_month || finances.projected_expense_next_month)) {
      usulan = Math.max(usulan, Number(finances.expense_next_month || finances.projected_expense_next_month || 0));
    }

    return { 
      name: m.short, 
      fullMonth: m.full,
      usulan: usulan, 
      realisasi: realisasi,
    };
  });

  
  
  const tanggalSekarang = new Date().toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });

  return (
    <div className="p-8 space-y-8">
      {/* Banner Corporate PT Jamkrida Kalsel */}
      <div className="bg-gradient-to-r from-emerald-950 via-emerald-800 to-teal-900 rounded-2xl p-7 text-white shadow-xl shadow-emerald-950/15 border border-emerald-700/40 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
          <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-500/20 border border-emerald-400/30 rounded-full text-xs font-bold uppercase tracking-wider mb-2 text-emerald-200">
            <ShieldCheck className="w-3.5 h-3.5" />
            Control Center Administrator
          </span>
          <h2 className="text-2xl font-black">Dashboard & Analitik Pengadaan</h2>
          <p className="text-emerald-100/80 text-sm mt-1">
            Pantau seluruh usulan anggaran divisi kerja, status verifikasi pengadaan, dan laporan realisasi.
          </p>
        </div>
        
        {/* Sisi Kanan Banner: Filter Periode & Tombol Aksi Terstruktur */}
        <div className="flex flex-col sm:items-end gap-2.5 shrink-0">
          {/* Baris 1: Filter Periode / Arsip */}
          <div className="flex items-center gap-2 bg-emerald-950/80 border border-emerald-500/30 rounded-xl px-3.5 py-1.5 text-xs text-emerald-100 shadow-inner w-fit">
            <Calendar className="w-3.5 h-3.5 text-emerald-300" />
            <span className="text-emerald-300 font-medium">Periode:</span>
            <select
              value={selectedPeriod}
              onChange={(e) => setSelectedPeriod(e.target.value)}
              className="bg-transparent text-white font-bold outline-none cursor-pointer text-xs"
            >
              <option value="" className="bg-slate-900 text-white">
                {finances.current_month_name || 'Bulan Berjalan'} (Aktif)
              </option>
              {availableMonths.map((m) => (
                <option key={m} value={m} className="bg-slate-900 text-white">
                  Arsip: {m}
                </option>
              ))}
            </select>
          </div>

          {/* Baris 2: Tombol Aksi Berdampingan Rapi */}
          <div className="flex items-center gap-2">
            <button
              onClick={() => setShowReportModal(true)}
              className="px-3.5 py-2 bg-emerald-900/60 hover:bg-emerald-900 text-emerald-100 font-semibold rounded-xl text-xs border border-emerald-500/40 shadow-sm flex items-center gap-1.5 transition"
            >
              <FileText className="w-3.5 h-3.5 text-emerald-300" />
              Export Laporan
            </button>

            <Link
              to="/admin/submissions"
              className="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-1.5 transition"
            >
              <CheckSquare className="w-3.5 h-3.5" />
              Tinjau Pengajuan ({counts.pending || 0})
            </Link>
          </div>
        </div>
      </div>

      {/* Notifikasi Mode Arsip (Hanya muncul jika memilih bulan masa lalu) */}
      {selectedPeriod && (
        <div className="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-between text-amber-900 text-xs shadow-sm">
          <div className="flex items-center gap-2 font-medium">
            <span className="p-1 bg-amber-200 text-amber-900 rounded-md font-bold uppercase text-[10px]">Arsip Historis</span>
            <span>Anda sedang melihat data rekapitulasi arsip pengadaan untuk periode <strong>{selectedPeriod}</strong>.</span>
          </div>
          <button
            onClick={() => setSelectedPeriod('')}
            className="text-amber-800 font-bold underline hover:text-amber-950"
          >
            Kembali ke Bulan Berjalan
          </button>
        </div>
      )}
      

      {/* KPI Cards Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <div className="flex items-center justify-between text-slate-500">
            <span className="text-xs font-bold uppercase tracking-wider">Total Usulan</span>
            <div className="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
              <CheckSquare className="w-4 h-4" />
            </div>
          </div>
          <p className="text-2xl font-black text-slate-900 mt-2">{counts.total || 0}</p>
          <p className="text-xs text-slate-400 mt-1">Semua data masuk</p>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <div className="flex items-center justify-between text-amber-600">
            <span className="text-xs font-bold uppercase tracking-wider">Menunggu Review</span>
            <div className="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
              <Clock className="w-4 h-4" />
            </div>
          </div>
          <p className="text-2xl font-black text-amber-600 mt-2">{counts.pending || 0}</p>
          <p className="text-xs text-slate-400 mt-1">Perlu tindakan persetujuan</p>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <div className="flex items-center justify-between text-emerald-700">
            <span className="text-xs font-bold uppercase tracking-wider">Disetujui / Selesai</span>
            <div className="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
              <CheckCircle2 className="w-4 h-4" />
            </div>
          </div>
          <p className="text-2xl font-black text-emerald-700 mt-2">
            {(counts.approved || 0) + (counts.completed || 0)}
          </p>
          <p className="text-xs text-slate-400 mt-1">Pengadaan diproses/sah</p>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <div className="flex items-center justify-between text-rose-600">
            <span className="text-xs font-bold uppercase tracking-wider">Ditolak</span>
            <div className="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
              <XCircle className="w-4 h-4" />
            </div>
          </div>
          <p className="text-2xl font-black text-rose-600 mt-2">{counts.rejected || 0}</p>
          <p className="text-xs text-slate-400 mt-1">Tidak memenuhi syarat</p>
        </div>
      </div>

      {/* Financial Summary */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
          <div className="flex items-center gap-3">
            <div className="p-2.5 rounded-xl bg-emerald-50 text-emerald-800">
              <DollarSign className="w-5 h-5" />
            </div>
            <div>
              <h3 className="font-bold text-sm text-slate-900">Total Pengeluaran Bulan Ini</h3>
              <p className="text-xs text-slate-400">{finances.current_month_name}</p>
            </div>
          </div>
          <p className="text-2xl font-black text-emerald-800">{formatRupiah(finances.expense_this_month)}</p>
          <p className="text-xs text-slate-500">Realisasi Disetujui: <span className="font-bold text-slate-800">{formatRupiah(finances.realized_this_month)}</span></p>
        </div>

        <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
          <div className="flex items-center gap-3">
            <div className="p-2.5 rounded-xl bg-teal-50 text-teal-800">
              <TrendingUp className="w-5 h-5" />
            </div>
            <div>
              <h3 className="font-bold text-sm text-slate-900">Proyeksi Anggaran Bulan Depan</h3>
              <p className="text-xs text-slate-400">{finances.next_month_name}</p>
            </div>
          </div>
          <p className="text-2xl font-black text-teal-800">{formatRupiah(finances.expense_next_month)}</p>
          <p className="text-xs text-slate-500">Estimasi usulan aktif bulan depan</p>
        </div>

                {/* Kartu 3: Total Master Terdaftar */}
        <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
          <div className="flex items-center gap-3">
            <div className="p-2.5 rounded-xl bg-slate-100 text-slate-700">
              <Building className="w-5 h-5" />
            </div>
            <div>
              <h3 className="font-bold text-sm text-slate-900">Total Master Terdaftar</h3>
              <p className="text-xs text-slate-400">Divisi Kerja & Pegawai</p>
            </div>
          </div>
          <div className="grid grid-cols-2 gap-2 text-center pt-1">
            <div className="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
              <span className="text-xs text-slate-500">Divisi</span>
              <p className="font-black text-slate-900 text-lg">{counts.divisions || 0}</p>
            </div>
            <div className="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
              <span className="text-xs text-slate-500">Pegawai</span>
              <p className="font-black text-slate-900 text-lg">{counts.users || 0}</p>
            </div>
          </div>
        </div>
      </div>

     {/* Grafik Kolom: Perbandingan Anggaran Bulanan */}
      <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
          <div>
            <h3 className="font-bold text-base text-slate-900">
              Perbandingan Anggaran Bulanan (Tahun Berjalan)
            </h3>
            <p className="text-xs text-slate-500 mt-0.5">
              Diagram kolom komparasi total usulan anggaran vs realisasi faktur sah tiap bulan
            </p>
          </div>

          {/* Legend / Keterangan Warna Batang Kolom */}
          <div className="flex items-center gap-4 text-xs font-semibold">
            <div className="flex items-center gap-1.5">
              <span className="w-3 h-3 rounded-md bg-emerald-600"></span>
              <span className="text-slate-700">Estimasi Usulan</span>
            </div>
            <div className="flex items-center gap-1.5">
              <span className="w-3 h-3 rounded-md bg-teal-500"></span>
              <span className="text-slate-700">Realisasi Faktur Sah</span>
            </div>
          </div>
        </div>

        {/* Diagram Batang / Kolom Responsive */}
        <div className="w-full h-72 pt-2">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart 
              data={monthlyChartData} 
              margin={{ top: 10, right: 20, left: 10, bottom: 0 }}
              barGap={4}
            >
              {/* Garis Grid Latar Belakang */}
              <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#F1F5F9" />

              {/* Sumbu X: Nama Bulan */}
              <XAxis 
                dataKey="name"
                tick={{ fontSize: 12, fill: '#64748B', fontWeight: 600 }}
                axisLine={{ stroke: '#E2E8F0' }} 
                tickLine={false}
              />

              {/* Sumbu Y: Nilai Rupiah Singkat */}
              <YAxis
                tick={{ fontSize: 11, fill: '#64748B' }}
                axisLine={false}
                tickLine={false}
                tickFormatter={(val) => `Rp ${(val / 1000000).toFixed(0)}Jt`}
              />

              {/* Tooltip Hover */}
              <Tooltip
                cursor={{ fill: '#F8FAFC' }}
                content={({ active, payload }) => {                        
                  if (active && payload && payload.length) {
                    const itemData = payload[0].payload;
                    return (
                      <div className="bg-slate-900 text-white p-3.5 rounded-xl shadow-xl text-xs space-y-1.5 border border-slate-700">
                        <p className="font-bold text-emerald-400 border-b border-slate-700 pb-1">
                          Periode: {itemData.fullMonth}
                        </p>
                        <div className="space-y-1 pt-0.5">
                          <p className="flex justify-between gap-4 text-slate-300">
                            <span>Estimasi Usulan:</span>
                            <span className="font-bold text-emerald-400">{formatRupiah(itemData.usulan)}</span>
                          </p>
                          <p className="flex justify-between gap-4 text-slate-300">
                            <span>Realisasi Faktur:</span>
                            <span className="font-bold text-teal-300">{formatRupiah(itemData.realisasi)}</span>
                          </p>
                        </div>
                      </div>
                    );                          
                  }
                  return null;
                }}
              />  

              {/* Batang Kolom 1: Estimasi Usulan (Hijau dengan ujung membulat) */}
              <Bar 
                dataKey="usulan" 
                fill="#059669" 
                radius={[6, 6, 0, 0]} 
                maxBarSize={28}
              />

              {/* Batang Kolom 2: Realisasi Faktur (Teal dengan ujung membulat) */}
              <Bar 
                dataKey="realisasi" 
                fill="#0d9488" 
                radius={[6, 6, 0, 0]} 
                maxBarSize={28}
              />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>


      {/* Grid Divisi & Pengajuan Terbaru */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Kolom Kiri: Distribusi & Proporsi Anggaran per Divisi */}
        <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="font-bold text-base text-slate-900">Distribusi Anggaran Divisi</h3>
            <span className="text-xs font-semibold text-slate-400">{divisionStats.length} Divisi</span>
          </div>

          <div className="space-y-3">
            {divisionStats.map((d) => {
              const totalAllCost = divisionStats.reduce(
                (acc, curr) => acc + Number(curr.total_biaya || 0),
                0
              ) || 1;
              const biaya = Number(d.total_biaya || 0);
              const percentage = totalAllCost > 1 ? Math.round((biaya / totalAllCost) * 100) : 0;

              return (
                <div key={d.id} className="space-y-1.5 p-3 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-100 transition">
                  <div className="flex items-center justify-between text-xs">
                    <span className="font-bold text-slate-800">{d.nama_divisi}</span>
                    <span className="font-bold text-emerald-800">{formatRupiah(biaya)}</span>
                  </div>

                  {/* Progress Bar Proporsi Anggaran */}
                  <div className="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                    <div
                      className="bg-emerald-600 h-1.5 rounded-full transition-all duration-500"
                      style={{ width: `${percentage > 0 ? Math.max(percentage, 5) : 0}%` }}
                    ></div>
                  </div>

                  <div className="flex items-center justify-between text-[10px] text-slate-500">
                    <span>{d.submissions_count || 0} pengajuan ({selectedPeriod || finances.current_month_name || 'Bulan Ini'})</span>
                    <span className="font-bold text-emerald-700">{percentage}% porsi</span>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Kolom Kanan: Tabel Pengajuan Terbaru Masuk */}
        <div className="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="font-bold text-base text-slate-900">Pengajuan Terbaru Masuk</h3>
            <Link
              to="/admin/submissions"
              className="text-xs font-bold text-emerald-700 hover:text-emerald-800 flex items-center gap-1"
            >
              Kelola Semua <ArrowRight className="w-3.5 h-3.5" />
            </Link>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
                <tr>
                  <th className="py-3 px-3">No. Pengajuan</th>
                  <th className="py-3 px-3">Divisi & Pemohon</th>
                  <th className="py-3 px-3">Barang & Estimasi</th>
                  <th className="py-3 px-3">Status</th>
                  <th className="py-3 px-3 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {recent.map((s) => (
                  <tr key={s.id} className="hover:bg-slate-50/80 transition">
                    <td className="py-3 px-3">
                      <span className="font-bold text-slate-900 block">{s.nomor_pengajuan}</span>
                      <span className="text-[10px] text-slate-400">{s.target_bulan || 'Bulan Ini'}</span>
                    </td>
                    <td className="py-3 px-3">
                      <span className="font-semibold text-slate-800 block">{s.division?.nama_divisi}</span>
                      <span className="text-xs text-slate-400">{s.user?.name}</span>
                    </td>
                    <td className="py-3 px-3">
                      <span className="font-medium text-slate-800 block">{s.nama_barang}</span>
                      <span className="text-xs font-bold text-emerald-700">{formatRupiah(s.total_biaya)}</span>
                    </td>
                    <td className="py-3 px-3">
                      <StatusBadge status={s.status} />
                    </td>
                    <td className="py-3 px-3 text-right">
                      <Link
                        to={`/admin/submissions/${s.id}`}
                        className="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs rounded-lg transition inline-flex items-center gap-1 shadow-sm"
                      >
                        Review
                        <ArrowRight className="w-3 h-3" />
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {/* Modal Pratinjau & Cetak Laporan Eksekutif */}
      {showReportModal && (
        <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[92vh] flex flex-col overflow-hidden border border-slate-200">
            {/* Header Modal (No Print) */}
            <div className="p-4 sm:px-6 bg-slate-900 text-white flex items-center justify-between no-print">
              <div className="flex items-center gap-2">
                <FileText className="w-5 h-5 text-emerald-400" />
                <div>
                  <h3 className="font-bold text-sm sm:text-base">Pratinjau Dokumen Laporan Rekapitulasi</h3>
                  <p className="text-[11px] text-slate-400">Siap dicetak ke PDF atau diunduh ke Excel (.csv)</p>
                </div>
              </div>
              <div className="flex items-center gap-2">
                <button
                  onClick={handleDownloadExcel}
                  className="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-emerald-300 font-bold text-xs rounded-lg border border-slate-700 flex items-center gap-1.5 transition"
                >
                  <FileSpreadsheet className="w-4 h-4 text-emerald-400" />
                  Unduh Excel
                </button>
                <button
                  onClick={handlePrintPDF}
                  className="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-lg shadow flex items-center gap-1.5 transition"
                >
                  <Printer className="w-4 h-4" />
                  Cetak / Simpan PDF
                </button>
                <button
                  onClick={() => setShowReportModal(false)}
                  className="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>
            </div>

            {/* Isi Dokumen Cetak (#printable-report) */}
            <div className="p-4 sm:p-8 overflow-y-auto flex-1 bg-slate-100 flex justify-center">
              <div
                id="printable-report"
                className="bg-white p-6 sm:p-10 shadow-sm rounded-xl max-w-3xl w-full text-slate-900 border border-slate-200"
              >
                {/* KOP Surat Resmi PT Jamkrida Kalsel */}
                <div className="text-center pb-3 border-b-2 border-black">
                  <h2 className="text-base sm:text-lg font-black uppercase tracking-wider text-black">
                    PT Penjaminan Kredit Daerah Kalimantan Selatan
                  </h2>
                  <h3 className="text-sm sm:text-base font-black text-black">
                    (PT JAMKRIDA KALSEL)
                  </h3>
                  <p className="text-[11px] text-slate-700 mt-0.5 font-medium">
                    Gedung Menara Jamkrida, Jl. Jend. A. Yani KM. 4.5, Banjarmasin, Kalimantan Selatan
                  </p>
                  <p className="text-[10px] text-slate-600">
                    Telepon: (0511) 3258888 | Website: www.jamkridakalsel.co.id | Email: pengadaan@jamkridakalsel.co.id
                  </p>
                </div>
                <div className="border-b border-black mt-0.5 mb-5"></div>

                {/* Judul Dokumen & Periode */}
                <div className="text-center my-4 page-break-avoid">
                  <h4 className="text-sm sm:text-base font-black uppercase tracking-wide underline underline-offset-4 text-black">
                    Laporan Rekapitulasi Usulan Pengadaan & Anggaran
                  </h4>
                  <p className="text-xs font-bold text-black mt-1">
                    Periode: {finances.current_month_name || 'Bulan Berjalan'}
                  </p>
                  <p className="text-[10px] text-slate-600">
                    Tanggal Dicetak: {tanggalSekarang}
                  </p>
                </div>

                {/* Bagian 1: Ringkasan Metrik & Finansial */}
                <div className="mb-5 space-y-2 page-break-avoid">
                  <h5 className="font-bold text-[11px] uppercase tracking-wider text-black border-b border-black pb-1">
                    I. Ringkasan Eksekutif & Posisi Anggaran
                  </h5>
                  <div className="grid grid-cols-4 gap-2 text-xs">
                    <div className="p-2 bg-white border border-black rounded">
                      <span className="text-[9px] text-slate-700 uppercase font-bold">Total Usulan</span>
                      <p className="font-black text-black text-xs mt-0.5">{counts.total || 0} Usulan</p>
                    </div>
                    <div className="p-2 bg-white border border-black rounded">
                      <span className="text-[9px] text-slate-700 uppercase font-bold">Menunggu Review</span>
                      <p className="font-black text-black text-xs mt-0.5">{counts.pending || 0} Usulan</p>
                    </div>
                    <div className="p-2 bg-white border border-black rounded">
                      <span className="text-[9px] text-slate-700 uppercase font-bold">Disetujui / Selesai</span>
                      <p className="font-black text-black text-xs mt-0.5">{(counts.approved || 0) + (counts.completed || 0)} Usulan</p>
                    </div>
                    <div className="p-2 bg-white border border-black rounded">
                      <span className="text-[9px] text-slate-700 uppercase font-bold">Ditolak</span>
                      <p className="font-black text-black text-xs mt-0.5">{counts.rejected || 0} Usulan</p>
                    </div>
                  </div>

                  <div className="grid grid-cols-2 gap-2 pt-1 text-xs">
                    <div className="p-2.5 bg-slate-50 border border-black rounded">
                      <span className="text-[10px] text-slate-800 font-bold">Total Usulan Biaya Bulan Ini:</span>
                      <p className="font-black text-black text-sm">{formatRupiah(finances.expense_this_month)}</p>
                      <p className="text-[9px] text-slate-700 mt-0.5">Realisasi Disetujui: {formatRupiah(finances.realized_this_month)}</p>
                    </div>
                    <div className="p-2.5 bg-slate-50 border border-black rounded">
                      <span className="text-[10px] text-slate-800 font-bold">Proyeksi Anggaran Bulan Depan:</span>
                      <p className="font-black text-black text-sm">{formatRupiah(finances.expense_next_month)}</p>
                      <p className="text-[9px] text-slate-700 mt-0.5">Estimasi kebutuhan {finances.next_month_name}</p>
                    </div>
                  </div>
                </div>

                {/* Bagian 2: Rekapitulasi per Divisi */}
                <div className="mb-5 space-y-2 page-break-avoid">
                  <h5 className="font-bold text-[11px] uppercase tracking-wider text-black border-b border-black pb-1">
                    II. Rekapitulasi Usulan Berdasarkan Divisi Kerja
                  </h5>
                  <table className="w-full text-[11px] text-left border-collapse border border-black">
                    <thead className="bg-slate-100 font-black text-black">
                      <tr>
                        <th className="border border-black p-1.5 text-center w-10">No</th>
                        <th className="border border-black p-1.5">Nama Divisi Kerja</th>
                        <th className="border border-black p-1.5 text-center w-32">Jumlah Usulan</th>
                        <th className="border border-black p-1.5 text-right w-44">Estimasi Anggaran</th>
                      </tr>
                    </thead>
                    <tbody>
                      {divisionStats.map((d, index) => (
                        <tr key={d.id}>
                          <td className="border border-black p-1.5 text-center font-bold">{index + 1}</td>
                          <td className="border border-black p-1.5 font-bold text-black">{d.nama_divisi}</td>
                          <td className="border border-black p-1.5 text-center">{d.submissions_count} usulan</td>
                          <td className="border border-black p-1.5 text-right font-black text-black">
                            {formatRupiah(d.total_biaya || d.submissions_count * 1500000)}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>

                {/* Bagian 3: Daftar Pengajuan Masuk */}
                <div className="mb-6 space-y-2 page-break-avoid">
                  <h5 className="font-bold text-[11px] uppercase tracking-wider text-black border-b border-black pb-1">
                    III. Usulan Pengadaan Barang & Jasa Terbaru
                  </h5>
                  <table className="w-full text-[10px] text-left border-collapse border border-black">
                    <thead className="bg-slate-100 font-black text-black">
                      <tr>
                        <th className="border border-black p-1.5 text-center w-8">No</th>
                        <th className="border border-black p-1.5 w-28">No. Pengajuan</th>
                        <th className="border border-black p-1.5">Divisi & Pemohon</th>
                        <th className="border border-black p-1.5">Barang / Kebutuhan</th>
                        <th className="border border-black p-1.5 text-right w-28">Estimasi Biaya</th>
                        <th className="border border-black p-1.5 text-center w-20">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      {recent.slice(0, 6).map((s, index) => (
                        <tr key={s.id}>
                          <td className="border border-black p-1.5 text-center font-bold">{index + 1}</td>
                          <td className="border border-black p-1.5 font-bold">{s.nomor_pengajuan}</td>
                          <td className="border border-black p-1.5">{s.division?.nama_divisi} ({s.user?.name})</td>
                          <td className="border border-black p-1.5">{s.nama_barang}</td>
                          <td className="border border-black p-1.5 text-right font-bold">{formatRupiah(s.total_biaya)}</td>
                          <td className="border border-black p-1.5 text-center font-bold uppercase text-[9px]">[{s.status}]</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>

                {/* Lembar Tanda Tangan Pengesahan */}
                <div className="pt-4 grid grid-cols-2 text-xs text-center page-break-avoid">
                  <div>
                    <p className="text-black text-[11px]">Disiapkan Oleh,</p>
                    <p className="font-bold text-black text-[11px]">Admin Pengadaan & Keuangan</p>
                    <div className="h-14"></div>
                    <p className="font-bold underline text-black text-xs">( Administrator PT Jamkrida )</p>
                  </div>
                  <div>
                    <p className="text-black text-[11px]">Banjarmasin, {tanggalSekarang}</p>
                    <p className="font-bold text-black text-[11px]">Mengetahui / Menyetujui,</p>
                    <div className="h-14"></div>
                    <p className="font-bold underline text-black text-xs">( Direksi PT Jamkrida Kalsel )</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
    
  );
};
