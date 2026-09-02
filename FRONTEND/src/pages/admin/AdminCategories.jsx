import React, { useEffect, useState } from 'react';
import api from '../../services/api';
import { Layers, Plus, Trash2, Edit3, Check, X, AlertCircle } from 'lucide-react';

export const AdminCategories = () => {
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const [namaKategori, setNamaKategori] = useState('');
  const [deskripsi, setDeskripsi] = useState('');
  const [editingId, setEditingId] = useState(null);
  const [editName, setEditName] = useState('');
  const [editDesc, setEditDesc] = useState('');

  const fetchCategories = async () => {
    try {
      const res = await api.get('/admin/categories');
      setCategories(res.data.data || []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchCategories();
  }, []);

  const handleAdd = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    try {
      await api.post('/admin/categories', {
        nama_kategori: namaKategori,
        deskripsi,
      });
      setNamaKategori('');
      setDeskripsi('');
      setSuccess('Kategori berhasil ditambahkan.');
      fetchCategories();
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal menambahkan kategori.');
    }
  };

  const handleUpdate = async (id) => {
    try {
      await api.put(`/admin/categories/${id}`, {
        nama_kategori: editName,
        deskripsi: editDesc,
      });
      setEditingId(null);
      setSuccess('Kategori berhasil diperbarui.');
      fetchCategories();
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal memperbarui kategori.');
    }
  };

  const handleDelete = async (id, name) => {
    if (!window.confirm(`Hapus kategori "${name}"?`)) return;
    try {
      await api.delete(`/admin/categories/${id}`);
      setSuccess('Kategori berhasil dihapus.');
      fetchCategories();
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal menghapus kategori.');
    }
  };

  return (
    <div className="p-8 max-w-5xl mx-auto space-y-6">
      <div>
        <h2 className="text-2xl font-black text-slate-900">Kategori Barang & Pengadaan</h2>
        <p className="text-xs text-slate-500 mt-1">Kelola master klasifikasi barang/jasa yang dapat diajukan divisi</p>
      </div>

      {success && (
        <div className="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold">
          {success}
        </div>
      )}

      {error && (
        <div className="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-semibold flex items-center gap-2">
          <AlertCircle className="w-4 h-4" />
          {error}
        </div>
      )}

      <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <h3 className="font-bold text-sm text-slate-900 mb-3 flex items-center gap-2">
          <Plus className="w-4 h-4 text-emerald-700" />
          Tambah Kategori Baru
        </h3>
        <form onSubmit={handleAdd} className="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <input
            type="text"
            required
            value={namaKategori}
            onChange={(e) => setNamaKategori(e.target.value)}
            placeholder="Nama Kategori (contoh: Perangkat IT)"
            className="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
          />
          <input
            type="text"
            value={deskripsi}
            onChange={(e) => setDeskripsi(e.target.value)}
            placeholder="Deskripsi singkat (opsional)"
            className="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
          />
          <button
            type="submit"
            className="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm rounded-xl transition shadow-sm"
          >
            Simpan Kategori
          </button>
        </form>
      </div>

      <div className="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <table className="w-full text-left text-sm">
          <thead className="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
            <tr>
              <th className="py-3 px-4">Nama Kategori</th>
              <th className="py-3 px-4">Deskripsi</th>
              <th className="py-3 px-4">Jumlah Pengajuan</th>
              <th className="py-3 px-4 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 text-slate-700">
            {categories.map((c) => (
              <tr key={c.id} className="hover:bg-slate-50/80 transition">
                <td className="py-3.5 px-4 font-bold text-slate-900">
                  {editingId === c.id ? (
                    <input
                      type="text"
                      value={editName}
                      onChange={(e) => setEditName(e.target.value)}
                      className="px-2 py-1 border rounded text-xs"
                    />
                  ) : (
                    c.nama_kategori
                  )}
                </td>
                <td className="py-3.5 px-4 text-xs text-slate-500">
                  {editingId === c.id ? (
                    <input
                      type="text"
                      value={editDesc}
                      onChange={(e) => setEditDesc(e.target.value)}
                      className="px-2 py-1 border rounded text-xs w-full"
                    />
                  ) : (
                    c.deskripsi || '-'
                  )}
                </td>
                <td className="py-3.5 px-4">
                  <span className="px-2 py-0.5 bg-emerald-50 text-emerald-800 rounded-full font-semibold text-xs border border-emerald-200">
                    {c.submissions_count || 0}
                  </span>
                </td>
                <td className="py-3.5 px-4 text-right space-x-1.5">
                  {editingId === c.id ? (
                    <>
                      <button
                        onClick={() => handleUpdate(c.id)}
                        className="p-1.5 bg-emerald-100 text-emerald-800 rounded-lg"
                      >
                        <Check className="w-3.5 h-3.5" />
                      </button>
                      <button
                        onClick={() => setEditingId(null)}
                        className="p-1.5 bg-slate-100 text-slate-600 rounded-lg"
                      >
                        <X className="w-3.5 h-3.5" />
                      </button>
                    </>
                  ) : (
                    <>
                      <button
                        onClick={() => {
                          setEditingId(c.id);
                          setEditName(c.nama_kategori);
                          setEditDesc(c.deskripsi || '');
                        }}
                        className="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg"
                      >
                        <Edit3 className="w-3.5 h-3.5" />
                      </button>
                      <button
                        onClick={() => handleDelete(c.id, c.nama_kategori)}
                        className="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg"
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
    </div>
  );
};
