import React, { useEffect, useState } from 'react';
import api from '../../services/api';
import { StatusBadge } from '../../components/StatusBadge';
import { Link } from 'react-router-dom';
import { Search, Filter, RefreshCw, CheckSquare, Trash2 } from 'lucide-react';

export const AdminSubmissions = () => {
  const [submissions, setSubmissions] = useState([]);
  const [divisions, setDivisions] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);

  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [divisionId, setDivisionId] = useState('');
  const [categoryId, setCategoryId] = useState('');
  const [priority, setPriority] = useState('');
  const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });

  const fetchSubmissions = async (page = 1) => {
    setLoading(true);
    try {
      const params = {
        page,
        search: search || undefined,
        status: status || undefined,
        division_id: divisionId || undefined,
        category_id: categoryId || undefined,
        prioritas: priority || undefined,
      };

      const [subRes, optRes] = await Promise.all([
        api.get('/admin/submissions', { params }),
        divisions.length === 0 ? api.get('/options') : Promise.resolve(null),
      ]);

      setSubmissions(subRes.data.data.data || []);
      setPagination({
        current_page: subRes.data.data.current_page,
        last_page: subRes.data.data.last_page,
        total: subRes.data.data.total,
      });

      if (optRes) {
        setDivisions(optRes.data.divisions || []);
        setCategories(optRes.data.categories || []);
      }
    } catch (err) {
      console.error('Error fetching admin submissions:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchSubmissions(1);
  }, [status, divisionId, categoryId, priority]);

  const handleSearchSubmit = (e) => {
    e.preventDefault();
    fetchSubmissions(1);
  };

  const handleDelete = async (id, nomor) => {
    if (!window.confirm(`Hapus pengajuan [${nomor}] dari sistem?`)) return;

    try {
      await api.delete(`/admin/submissions/${id}`);
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
      <div>
        <h2 className="text-2xl font-black text-slate-900">Manajemen & Tinjauan Pengajuan</h2>
        <p className="text-xs text-slate-500 mt-1">
          Tinjau, setujui, tolak, atau catat realisasi nota pengeluaran dana barang pengadaan divisi.
        </p>
      </div>

      <div className="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <form onSubmit={handleSearchSubmit} className="flex gap-2">
          <div className="relative flex-1">
            <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari nomor pengajuan, nama pemohon, atau nama barang..."
              className="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
            />
          </div>
          <button
            type="submit"
            className="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm rounded-xl transition shadow-sm"
          >
            Cari
          </button>
        </form>

        <div className="grid grid-cols-2 sm:grid-cols-5 gap-2 pt-1 border-t border-slate-100">
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
            value={divisionId}
            onChange={(e) => setDivisionId(e.target.value)}
            className="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
          >
            <option value="">Semua Divisi</option>
            {divisions.map((d) => (
              <option key={d.id} value={d.id}>{d.nama_divisi}</option>
            ))}
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
              setDivisionId('');
              setCategoryId('');
              setPriority('');
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
            <RefreshCw className="w-5 h-5 animate-spin text-emerald-700" />
            Memuat daftar pengajuan...
          </div>
        ) : submissions.length === 0 ? (
          <div className="p-12 text-center text-slate-400">
            <p className="text-base font-semibold text-slate-700">Tidak Ada Data</p>
            <p className="text-xs text-slate-400 mt-1">Tidak ditemukan pengajuan dengan kriteria pencarian.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
                <tr>
                  <th className="py-3.5 px-4">No. Pengajuan</th>
                  <th className="py-3.5 px-4">Divisi & Pemohon</th>
                  <th className="py-3.5 px-4">Barang / Jasa</th>
                  <th className="py-3.5 px-4">Periode</th>
                  <th className="py-3.5 px-4">Estimasi Usulan</th>
                  <th className="py-3.5 px-4">Status</th>
                  <th className="py-3.5 px-4 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {submissions.map((s) => (
                  <tr key={s.id} className="hover:bg-slate-50/80 transition">
                    <td className="py-4 px-4 font-bold text-slate-900">
                      <Link to={`/admin/submissions/${s.id}`} className="hover:text-emerald-700">
                        {s.nomor_pengajuan}
                      </Link>
                    </td>
                    <td className="py-4 px-4">
                      <span className="font-semibold text-slate-800">{s.division?.nama_divisi}</span>
                      <span className="block text-xs text-slate-400">{s.user?.name}</span>
                    </td>
                    <td className="py-4 px-4">
                      <span className="font-medium text-slate-800">{s.nama_barang}</span>
                      <span className="block text-xs text-slate-400">{s.jumlah} {s.satuan}</span>
                    </td>
                    <td className="py-4 px-4 text-xs font-semibold text-slate-600">
                      {s.target_bulan || '-'}
                    </td>
                    <td className="py-4 px-4 font-black text-emerald-700">
                      {formatRupiah(s.total_biaya)}
                    </td>
                    <td className="py-4 px-4">
                      <StatusBadge status={s.status} />
                    </td>
                    <td className="py-4 px-4 text-right space-x-1.5">
                      <Link
                        to={`/admin/submissions/${s.id}`}
                        className="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs rounded-lg transition"
                      >
                        Tinjau
                      </Link>
                      <button
                        onClick={() => handleDelete(s.id, s.nomor_pengajuan)}
                        className="px-2 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-lg transition"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
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
