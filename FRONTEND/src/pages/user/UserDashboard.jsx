import React, { useEffect, useState } from 'react';
import api from '../../services/api';
import { useAuth } from '../../context/AuthContext';
import { StatusBadge } from '../../components/StatusBadge';
import { Link } from 'react-router-dom';
import { 
  FileText, 
  Clock, 
  CheckCircle2, 
  XCircle, 
  PlusCircle, 
  ArrowRight,
  TrendingUp,
  DollarSign,
  RefreshCw,
  Building2
} from 'lucide-react';

export const UserDashboard = () => {
  const { user } = useAuth();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchDashboard = async () => {
    setLoading(true);
    try {
      const res = await api.get('/user/dashboard');
      setData(res.data.data);
    } catch (err) {
      console.error('Error fetching dashboard:', err);
    } finally {
      setLoading(false);
    }
  };

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

  if (loading) {
    return (
      <div className="p-8 flex items-center justify-center min-h-[50vh]">
        <div className="flex items-center gap-3 text-slate-500 font-medium">
          <RefreshCw className="w-5 h-5 animate-spin text-emerald-700" />
          Memuat data dashboard...
        </div>
      </div>
    );
  }

  const counts = data?.counts || {};
  const finances = data?.finances || {};
  const recent = data?.recent_submissions || [];

  return (
    <div className="p-8 space-y-8">
      {/* Banner Identitas Kepala Divisi */}
      <div className="bg-gradient-to-r from-emerald-950 via-emerald-900 to-teal-950 rounded-2xl p-7 text-white shadow-xl shadow-emerald-950/15 border border-emerald-800/40 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
          <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-500/20 border border-emerald-400/30 rounded-full text-xs font-bold uppercase tracking-wider mb-2 text-emerald-200">
            <Building2 className="w-3.5 h-3.5" />
            Portal Kepala Divisi {user?.division?.nama_divisi || ''}
          </span>
          <h2 className="text-2xl font-black">Selamat Datang, {user?.name}</h2>
          <p className="text-emerald-100/80 text-sm mt-1">
            Kelola dan ajukan usulan pengadaan barang & estimasi anggaran untuk divisi {user?.division?.nama_divisi || 'Anda'}.
          </p>
        </div>
        <Link
          to="/user/submissions/create"
          className="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-sm shadow-md flex items-center gap-2 transition"
        >
          <PlusCircle className="w-4 h-4" />
          Buat Usulan Baru
        </Link>
      </div>

      {/* KPI Cards Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <div className="flex items-center justify-between text-slate-500">
            <span className="text-xs font-bold uppercase tracking-wider">Total Usulan Divisi</span>
            <div className="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
              <FileText className="w-4 h-4" />
            </div>
          </div>
          <p className="text-2xl font-black text-slate-900 mt-2">{counts.total || 0}</p>
          <p className="text-xs text-slate-400 mt-1">Semua periode</p>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <div className="flex items-center justify-between text-amber-600">
            <span className="text-xs font-bold uppercase tracking-wider">Menunggu Tinjauan</span>
            <div className="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
              <Clock className="w-4 h-4" />
            </div>
          </div>
          <p className="text-2xl font-black text-amber-600 mt-2">{counts.pending || 0}</p>
          <p className="text-xs text-slate-400 mt-1">Dalam antrean verifikasi</p>
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
          <p className="text-xs text-slate-400 mt-1">Pengadaan disetujui/sah</p>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <div className="flex items-center justify-between text-rose-600">
            <span className="text-xs font-bold uppercase tracking-wider">Ditolak</span>
            <div className="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
              <XCircle className="w-4 h-4" />
            </div>
          </div>
          <p className="text-2xl font-black text-rose-600 mt-2">{counts.rejected || 0}</p>
          <p className="text-xs text-slate-400 mt-1">Perlu revisi / dibatalkan</p>
        </div>
      </div>

      {/* Financial Summary */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
          <div className="flex items-center gap-3">
            <div className="p-2.5 rounded-xl bg-emerald-50 text-emerald-800">
              <DollarSign className="w-5 h-5" />
            </div>
            <div>
              <h3 className="font-bold text-sm text-slate-900">Total Anggaran Usulan Bulan Ini</h3>
              <p className="text-xs text-slate-400">{finances.current_month_name}</p>
            </div>
          </div>
          <p className="text-2xl font-black text-emerald-800">{formatRupiah(finances.total_expense_this_month)}</p>
          <p className="text-xs text-slate-500">Estimasi usulan aktif divisi {user?.division?.nama_divisi}</p>
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
          <p className="text-2xl font-black text-teal-800">{formatRupiah(finances.projected_expense_next_month)}</p>
          <p className="text-xs text-slate-500">Estimasi usulan periode bulan berikutnya</p>
        </div>
      </div>

      {/* Recent Submissions */}
      <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
        <div className="flex items-center justify-between">
          <h3 className="font-bold text-base text-slate-900">Pengajuan Terbaru Divisi Anda</h3>
          <Link
            to="/user/submissions"
            className="text-xs font-bold text-emerald-700 hover:text-emerald-800 flex items-center gap-1"
          >
            Lihat Semua <ArrowRight className="w-3.5 h-3.5" />
          </Link>
        </div>

        {recent.length === 0 ? (
          <div className="text-center py-8 text-slate-400">
            <p className="text-sm font-semibold text-slate-600">Belum Ada Pengajuan</p>
            <p className="text-xs text-slate-400 mt-1">Buat usulan pengadaan barang baru untuk divisi Anda.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
                <tr>
                  <th className="py-3 px-3">No. Pengajuan</th>
                  <th className="py-3 px-3">Barang / Jasa</th>
                  <th className="py-3 px-3">Target Bulan</th>
                  <th className="py-3 px-3">Estimasi Biaya</th>
                  <th className="py-3 px-3">Status</th>
                  <th className="py-3 px-3 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {recent.map((s) => (
                  <tr key={s.id} className="hover:bg-slate-50/80 transition">
                    <td className="py-3 px-3 font-bold text-slate-900">{s.nomor_pengajuan}</td>
                    <td className="py-3 px-3">
                      <span className="font-semibold text-slate-800">{s.nama_barang}</span>
                      <span className="block text-xs text-slate-400">{s.jumlah} {s.satuan}</span>
                    </td>
                    <td className="py-3 px-3 text-xs font-medium text-slate-600">{s.target_bulan || '-'}</td>
                    <td className="py-3 px-3 font-bold text-emerald-700">{formatRupiah(s.total_biaya)}</td>
                    <td className="py-3 px-3">
                      <StatusBadge status={s.status} />
                    </td>
                    <td className="py-3 px-3 text-right">
                      <Link
                        to={`/user/submissions/${s.id}`}
                        className="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition"
                      >
                        Detail
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
};
