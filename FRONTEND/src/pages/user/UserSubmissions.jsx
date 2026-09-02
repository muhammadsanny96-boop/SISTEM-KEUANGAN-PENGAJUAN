import React, { useEffect, useState } from 'react';
import api from '../../services/api';
import { StatusBadge } from '../../components/StatusBadge';
import { Link } from 'react-router-dom';
import { PlusCircle, Search, Filter, RefreshCw, Trash2, Edit3, X } from 'lucide-react';

export const UserSubmissions = () => {
  const [submissions, setSubmissions] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);

  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [categoryId, setCategoryId] = useState('');
  const [priority, setPriority] = useState('');
  const [targetMonth, setTargetMonth] = useState('');

  const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });

  const fetchSubmissions = async (page = 1) => {
    setLoading(true);
    try {
      const params = {
        page,
        search: search || undefined,
        status: status || undefined,
        category_id: categoryId || undefined,
        prioritas: priority || undefined,
        target_bulan: targetMonth || undefined,
      };

      const [subRes, optRes] = await Promise.all([
        api.get('/user/submissions', { params }),
        categories.length === 0 ? api.get('/options') : Promise.resolve(null),
      ]);

      setSubmissions(subRes.data.data.data || []);
      setPagination({
        current_page: subRes.data.data.current_page,
        last_page: subRes.data.data.last_page,
        total: subRes.data.data.total,
      });

      if (optRes) {
        setCategories(optRes.data.categories || []);
      }
    } catch (err) {
      console.error('Error fetching submissions:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const timer = setTimeout(() => {
      fetchSubmissions();
    }, 400);

    return () => clearTimeout(timer);
  }, [search, status, categoryId, priority, targetMonth]);
  const handleDelete = async (id, nomor) => {
    if (!window.confirm(`Yakin ingin membatalkan dan menghapus pengajuan [${nomor}]?`)) return;

    try {
      await api.delete(`/user/submissions/${id}`);
      fetchSubmissions(pagination.current_page);
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal menghapus pengajuan.');
    }
  };

  const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(number || 0);
  };

  return (
    <div className="p-8 space-y-6">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-black text-slate-900">Daftar Riwayat Pengajuan</h2>
          <p className="text-xs text-slate-500 mt-1">
            Kelola dan pantau seluruh pengajuan barang dan pengadaan divisi Anda.
          </p>
        </div>
        <Link
          to="/user/submissions/create"
          className="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-200 flex items-center gap-2 transition"
        >
          <PlusCircle className="w-4 h-4" />
          Ajukan Baru
        </Link>
      </div>

            {/* QUICK STATUS TABS (Tombol Pilihan Status Cepat) */}
      <div className="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none text-xs font-semibold">
        {[
          { label: 'Semua Status', value: '' },
          { label: 'Menunggu', value: 'Menunggu', color: 'text-amber-700 bg-amber-50 border-amber-200' },
          { label: 'Diproses', value: 'Diproses', color: 'text-blue-700 bg-blue-50 border-blue-200' },
          { label: 'Disetujui', value: 'Disetujui', color: 'text-emerald-700 bg-emerald-50 border-emerald-200' },
          { label: 'Selesai', value: 'Selesai', color: 'text-indigo-700 bg-indigo-50 border-indigo-200' },
          { label: 'Ditolak', value: 'Ditolak', color: 'text-rose-700 bg-rose-50 border-rose-200' },
        ].map((tab) => {
          const isActive = status === tab.value;
          return (
            <button
              key={tab.label}
              onClick={() => setStatus(tab.value)}
              className={`px-3.5 py-1.5 rounded-xl border transition-all whitespace-nowrap flex items-center gap-1.5 ${
                isActive
                  ? 'bg-slate-900 text-white border-slate-900 shadow-sm'
                  : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
              }`}
            >
              <span>{tab.label}</span>
            </button>
          );
        })}
      </div>

      {/* SEARCH BAR DENGAN TOMBOL CLEAR (X)  */}
      <div className="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <div className="relative">
          <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Ketik langsung untuk mencari nomor pengajuan, nama barang..."
            className="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition"
          />
          {search && (
            <button
              onClick={() => setSearch('')}
              className="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-200 transition"
              title="Hapus pencarian"
            >
              <X className="w-4 h-4" />
            </button>
          )}
        </div>


        <div className="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 border-t border-slate-100">
          <select
            value={status}
            onChange={(e) => setStatus(e.target.value)}
            className="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
          >
            <option value="">Semua Status</option>
            <option value="Menunggu">Menunggu</option>
            <option value="Diproses">Diproses</option>
            <option value="Disetujui">Disetujui</option>
            <option value="Ditolak">Ditolak</option>
            <option value="Selesai">Selesai</option>
          </select>

          <select
            value={categoryId}
            onChange={(e) => setCategoryId(e.target.value)}
            className="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
          >
            <option value="">Semua Kategori</option>
            {categories.map((c) => (
              <option key={c.id} value={c.id}>{c.nama_kategori}</option>
            ))}
          </select>

          <select
            value={priority}
            onChange={(e) => setPriority(e.target.value)}
            className="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
          >
            <option value="">Semua Prioritas</option>
            <option value="Rendah">Rendah</option>
            <option value="Sedang">Sedang</option>
            <option value="Tinggi">Tinggi</option>
            <option value="Darurat">Darurat</option>
          </select>

          <button
            onClick={() => {
              setSearch('');
              setStatus('');
              setCategoryId('');
              setPriority('');
              setTargetMonth('');
            }}
            className="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs rounded-xl transition"
          >
            Reset Filter
          </button>
        </div>
      </div>

      <div className="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        {loading ? (
          <div className="p-12 text-center text-slate-500 font-medium flex items-center justify-center gap-2">
            <RefreshCw className="w-5 h-5 animate-spin text-emerald-600" />
            Memuat pengajuan...
          </div>
        ) : submissions.length === 0 ? (
          <div className="p-12 text-center text-slate-400">
            <p className="text-base font-semibold text-slate-700">Tidak Ada Data</p>
            <p className="text-xs text-slate-400 mt-1">Tidak ditemukan pengajuan dengan kriteria filter saat ini.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
                <tr>
                  <th className="py-3 px-4">No. Pengajuan</th>
                  <th className="py-3 px-4">Nama Barang & Jumlah</th>
                  <th className="py-3 px-4">Kategori</th>
                  <th className="py-3 px-4">Periode Bulan</th>
                  <th className="py-3 px-4">Total Biaya</th>
                  <th className="py-3 px-4">Status</th>
                  <th className="py-3 px-4 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {submissions.map((s) => (
                  <tr key={s.id} className="hover:bg-slate-50/80 transition">
                    <td className="py-4 px-4 font-bold text-slate-900">
                      <Link to={`/user/submissions/${s.id}`} className="hover:text-emerald-600">
                        {s.nomor_pengajuan}
                      </Link>
                    </td>
                    <td className="py-4 px-4 font-medium text-slate-800">
                      {s.nama_barang}
                      <span className="block text-xs text-slate-400 font-normal mt-0.5">
                        {s.jumlah} {s.satuan} @ {formatRupiah(s.harga_satuan)}
                      </span>
                    </td>
                    <td className="py-4 px-4 text-xs font-semibold text-slate-600">
                      {s.category?.nama_kategori || '-'}
                    </td>
                    <td className="py-4 px-4 text-xs font-medium text-slate-600">
                      {s.target_bulan || '-'}
                    </td>
                    <td className="py-4 px-4 font-black text-slate-900">
                      {formatRupiah(s.total_biaya)}
                    </td>
                    <td className="py-4 px-4">
                      <StatusBadge status={s.status} />
                    </td>
                    <td className="py-4 px-4 text-right space-x-1.5">
                      <Link
                        to={`/user/submissions/${s.id}`}
                        className="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition"
                      >
                        Detail
                      </Link>
                      {s.status === 'Menunggu' && (
                        <>
                          <Link
                            to={`/user/submissions/${s.id}/edit`}
                            className="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold text-xs rounded-lg transition inline-flex items-center"
                          >
                            <Edit3 className="w-3.5 h-3.5" />
                          </Link>
                          <button
                            onClick={() => handleDelete(s.id, s.nomor_pengajuan)}
                            className="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs rounded-lg transition inline-flex items-center"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </button>
                        </>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {pagination.last_page > 1 && (
          <div className="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs text-slate-600">
            <span>Halaman {pagination.current_page} dari {pagination.last_page}</span>
            <div className="flex gap-1.5">
              <button
                disabled={pagination.current_page === 1}
                onClick={() => fetchSubmissions(pagination.current_page - 1)}
                className="px-3 py-1 bg-white border border-slate-200 rounded-lg disabled:opacity-40"
              >
                Sebelumnya
              </button>
              <button
                disabled={pagination.current_page === pagination.last_page}
                onClick={() => fetchSubmissions(pagination.current_page + 1)}
                className="px-3 py-1 bg-white border border-slate-200 rounded-lg disabled:opacity-40"
              >
                Selanjutnya
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};
