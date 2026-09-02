import React, { useEffect, useState } from 'react';
import api from '../../services/api';
import { 
  DollarSign, 
  Wallet, 
  TrendingUp, 
  History, 
  Building2, 
  RefreshCw,
  Calendar,
  Printer,
  Download,
  CheckCircle2,
  Search,
  Filter 
   } from 'lucide-react';

export const AdminExpenses = () => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  
  // State Filter & Pencarian
  const [selectedMonth, setSelectedMonth] = useState(''); // '' = Bulan Berjalan
  const [logSearch, setLogSearch] = useState('');

  const fetchExpenses = async (monthParam = selectedMonth) => {
    setLoading(true);
    try {
      const params = monthParam ? { month: monthParam } : {};
      const res = await api.get('/admin/expenses', { params });
      setData(res.data.data);
    } catch (err) {
      console.error('Error fetching expenses:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchExpenses(selectedMonth);
  }, [selectedMonth]);


    const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(number || 0);
  };

  // Fungsi Export Data Rekapitulasi ke Excel/CSV
  const handleExportCSV = () => {
    if (!data) return;
    const divisionData = data?.division_expense_data || [];
    let csv = '\uFEFFsep=;\n';
    csv += 'REKAPITULASI ANGGARAN & REALISASI PER DIVISI\n';
    csv += `Periode:;${data.metrics?.current_month_name || 'Bulan Berjalan'}\n\n`;
    csv += 'No;Nama Divisi;Kepala Divisi;Usulan Anggaran;Realisasi Belanja;Selisih Hemat/Lebih\n';

    divisionData.forEach((d, idx) => {
      const selisih = d.this_month_total - d.this_month_realized;
      csv += `${idx + 1};"${d.nama_divisi}";"${d.head_user}";"${formatRupiah(d.this_month_total)}";"${formatRupiah(d.this_month_realized)}";"${formatRupiah(selisih)}"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `Rekapitulasi_Anggaran_${Date.now()}.csv`;
    link.click();
    URL.revokeObjectURL(url);
  };

  // Fungsi Cetak Dokumen via Browser
  const handlePrint = () => {
    window.print();
  };


    const metrics = data?.metrics || {};
  const divisionData = data?.division_expense_data || [];
  const logs = data?.expense_logs?.data || [];

  // Filter pencarian log audit
  const filteredLogs = logs.filter((l) =>
    l.keterangan?.toLowerCase().includes(logSearch.toLowerCase()) ||
    l.division?.nama_divisi?.toLowerCase().includes(logSearch.toLowerCase()) ||
    l.tipe?.toLowerCase().includes(logSearch.toLowerCase())
  );

  return (
    <div className="p-8 space-y-8">
      {/* Header Banner Rekapitulasi */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
          <h2 className="text-2xl font-black text-slate-900">Rekapitulasi Keuangan & Anggaran</h2>
          <p className="text-xs text-slate-500 mt-1">
            Laporan komparasi dana usulan per divisi, realisasi faktur belanja, dan audit log finansial.
          </p>
        </div>

        {/* Tombol Aksi & Filter */}
        <div className="flex items-center gap-2.5">
          <button
            onClick={handleExportCSV}
            className="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs"
          >
            <Download className="w-3.5 h-3.5 text-slate-600" />
            Export Excel
          </button>

          <button
            onClick={handlePrint}
            className="px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-md shadow-emerald-700/20"
          >
            <Printer className="w-3.5 h-3.5" />
            Cetak Laporan
          </button>
        </div>
      </div>

      {/* KPI 4 Kotak Finansial Utama */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <span className="text-xs font-bold uppercase tracking-wider text-slate-500">Usulan Bulan Ini</span>
          <p className="text-xl font-black text-slate-900 mt-2">{formatRupiah(metrics.total_expense_this_month)}</p>
          <p className="text-xs text-slate-400 mt-1">{metrics.current_month_name}</p>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <span className="text-xs font-bold uppercase tracking-wider text-emerald-700">Realisasi Bulan Ini</span>
          <p className="text-xl font-black text-emerald-700 mt-2">{formatRupiah(metrics.realized_expense_this_month)}</p>
          <p className="text-xs text-slate-400 mt-1">Disetujui & Selesai</p>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <span className="text-xs font-bold uppercase tracking-wider text-teal-700">Proyeksi Bulan Depan</span>
          <p className="text-xl font-black text-teal-700 mt-2">{formatRupiah(metrics.projected_expense_next_month)}</p>
          <p className="text-xs text-slate-400 mt-1">{metrics.next_month_name}</p>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <span className="text-xs font-bold uppercase tracking-wider text-emerald-800">Total Penghematan</span>
          <p className="text-xl font-black text-emerald-800 mt-2">{formatRupiah(metrics.total_all_time_savings)}</p>
          <p className="text-xs text-slate-400 mt-1">Selisih usulan vs nota sah</p>
        </div>
      </div>

      {/* Tabel Komparasi Divisi Lengkap dengan Bar Proporsi */}
      <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
        <div className="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 className="font-bold text-base text-slate-900 flex items-center gap-2">
            <Building2 className="w-5 h-5 text-emerald-700" />
            Komparasi Anggaran per Divisi Kerja
          </h3>
          <span className="text-xs font-semibold text-slate-400">{divisionData.length} Divisi Aktif</span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm">
            <thead className="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
              <tr>
                <th className="py-3 px-4">Nama Divisi</th>
                <th className="py-3 px-4">Kepala Divisi</th>
                <th className="py-3 px-4">Usulan {metrics.current_month_name}</th>
                <th className="py-3 px-4">Realisasi Faktur</th>
                <th className="py-3 px-4">Efisiensi / Hemat</th>
                <th className="py-3 px-4">Proyeksi {metrics.next_month_name}</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 text-slate-700">
              {divisionData.map((d) => {
                const selisih = d.this_month_total - d.this_month_realized;
                return (
                  <tr key={d.id} className="hover:bg-slate-50/80 transition">
                    <td className="py-3.5 px-4 font-bold text-slate-900">{d.nama_divisi}</td>
                    <td className="py-3.5 px-4 text-xs font-medium text-slate-600">{d.head_user}</td>
                    <td className="py-3.5 px-4 font-semibold text-slate-800">{formatRupiah(d.this_month_total)}</td>
                    <td className="py-3.5 px-4 font-bold text-emerald-700">{formatRupiah(d.this_month_realized)}</td>
                    <td className="py-3.5 px-4 text-xs font-semibold">
                      {d.this_month_realized > 0 ? (
                        <span className={selisih >= 0 ? 'text-emerald-700' : 'text-rose-600'}>
                          {selisih >= 0 ? `✓ Hemat ${formatRupiah(selisih)}` : `⚠ Lebih ${formatRupiah(Math.abs(selisih))}`}
                        </span>
                      ) : (
                        <span className="text-slate-400">-</span>
                      )}
                    </td>
                    <td className="py-3.5 px-4 font-semibold text-teal-700">{formatRupiah(d.next_month_total)}</td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>

      {/* Tabel Riwayat Audit Finansial Sistem dengan Live Search */}
      <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
          <h3 className="font-bold text-base text-slate-900 flex items-center gap-2">
            <History className="w-5 h-5 text-slate-600" />
            Riwayat Audit Finansial Sistem
          </h3>

          <div className="relative">
            <Search className="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              value={logSearch}
              onChange={(e) => setLogSearch(e.target.value)}
              placeholder="Cari divisi, aksi, keterangan..."
              className="pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700 w-60"
            />
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm">
            <thead className="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
              <tr>
                <th className="py-3 px-4">Waktu</th>
                <th className="py-3 px-4">Tipe Aksi</th>
                <th className="py-3 px-4">Divisi & Pelaku</th>
                <th className="py-3 px-4">Nominal Tercatat</th>
                <th className="py-3 px-4">Keterangan</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 text-slate-700 text-xs">
              {filteredLogs.map((log) => (
                <tr key={log.id} className="hover:bg-slate-50 transition">
                  <td className="py-3 px-4 text-slate-500 whitespace-nowrap">
                    {new Date(log.created_at).toLocaleString('id-ID')}
                  </td>
                  <td className="py-3 px-4 font-bold text-emerald-800">{log.tipe}</td>
                  <td className="py-3 px-4">
                    <span className="font-semibold text-slate-800">{log.division?.nama_divisi || '-'}</span>
                    <span className="block text-[11px] text-slate-400">{log.user?.name}</span>
                  </td>
                  <td className="py-3 px-4 font-bold text-slate-900">{formatRupiah(log.nominal)}</td>
                  <td className="py-3 px-4 text-slate-600">{log.keterangan}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default AdminExpenses;

