import React, { useEffect, useState } from 'react';
import api from '../../services/api';
import { DollarSign, Wallet, TrendingUp, History, Building2, RefreshCw } from 'lucide-react';

export const AdminExpenses = () => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchExpenses = async () => {
    setLoading(true);
    try {
      const res = await api.get('/admin/expenses');
      setData(res.data.data);
    } catch (err) {
      console.error('Error fetching expenses:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchExpenses();
  }, []);

  const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(number || 0);
  };

  if (loading) {
    return (
      <div className="p-8 text-center text-slate-500 font-medium">Memuat data rekapitulasi dana...</div>
    );
  }

  const metrics = data?.metrics || {};
  const divisionData = data?.division_expense_data || [];
  const logs = data?.expense_logs?.data || [];

  return (
    <div className="p-8 space-y-8">
      <div>
        <h2 className="text-2xl font-black text-slate-900">Rekapitulasi Keuangan & Anggaran</h2>
        <p className="text-xs text-slate-500 mt-1">
          Laporan komparasi dana usulan per divisi, realisasi faktur belanja, dan audit log finansial.
        </p>
      </div>

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

      <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
        <h3 className="font-bold text-base text-slate-900 flex items-center gap-2">
          <Building2 className="w-5 h-5 text-emerald-700" />
          Komparasi Anggaran per Divisi Kerja
        </h3>
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm">
            <thead className="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
              <tr>
                <th className="py-3 px-4">Nama Divisi</th>
                <th className="py-3 px-4">Kepala Divisi</th>
                <th className="py-3 px-4">Usulan {metrics.current_month_name}</th>
                <th className="py-3 px-4">Realisasi {metrics.current_month_name}</th>
                <th className="py-3 px-4">Proyeksi {metrics.next_month_name}</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 text-slate-700">
              {divisionData.map((d) => (
                <tr key={d.id} className="hover:bg-slate-50/80 transition">
                  <td className="py-3.5 px-4 font-bold text-slate-900">{d.nama_divisi}</td>
                  <td className="py-3.5 px-4 text-xs font-medium text-slate-600">{d.head_user}</td>
                  <td className="py-3.5 px-4 font-semibold text-slate-800">{formatRupiah(d.this_month_total)}</td>
                  <td className="py-3.5 px-4 font-bold text-emerald-700">{formatRupiah(d.this_month_realized)}</td>
                  <td className="py-3.5 px-4 font-semibold text-teal-700">{formatRupiah(d.next_month_total)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
        <h3 className="font-bold text-base text-slate-900 flex items-center gap-2">
          <History className="w-5 h-5 text-slate-600" />
          Riwayat Audit Finansial Sistem
        </h3>
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
              {logs.map((log) => (
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
