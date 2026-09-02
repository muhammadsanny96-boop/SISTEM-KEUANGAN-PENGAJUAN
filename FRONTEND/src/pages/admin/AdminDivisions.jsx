import React, { useEffect, useState } from 'react';
import api from '../../services/api';
import { Building, Plus, Trash2, Edit3, Check, X, AlertCircle, Sparkles } from 'lucide-react';

export const AdminDivisions = () => {
  const [divisions, setDivisions] = useState([]);
  const [heads, setHeads] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  // Form Tambah
  const [namaDivisi, setNamaDivisi] = useState('');
  const [deskripsi, setDeskripsi] = useState('');
  const [kepalaId, setKepalaId] = useState('');
  const [submittingAdd, setSubmittingAdd] = useState(false);

  // Form Edit Modal
  const [editingDivision, setEditingDivision] = useState(null);
  const [editFormData, setEditFormData] = useState({
    nama_divisi: '',
    deskripsi: '',
    kepala_divisi_id: ''
  });
  const [submittingEdit, setSubmittingEdit] = useState(false);

  const fetchDivisions = async () => {
    try {
      const res = await api.get('/admin/divisions');
      setDivisions(res.data.data || []);
      setHeads(res.data.available_heads || []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchDivisions();
  }, []);

  const handleAdd = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setSubmittingAdd(true);
    try {
      await api.post('/admin/divisions', {
        nama_divisi: namaDivisi,
        deskripsi,
        kepala_divisi_id: kepalaId || null,
      });
      setNamaDivisi('');
      setDeskripsi('');
      setKepalaId('');
      setSuccess('Divisi baru berhasil ditambahkan.');
      fetchDivisions();
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal menambahkan divisi.');
    } finally {
      setSubmittingAdd(false);
    }
  };

  const openEditModal = (div) => {
    setError('');
    setSuccess('');
    setEditingDivision(div);
    setEditFormData({
      nama_divisi: div.nama_divisi,
      deskripsi: div.deskripsi || '',
      kepala_divisi_id: div.kepala_divisi_id || ''
    });
  };

  const handleUpdate = async (e) => {
    e.preventDefault();
    if (!editingDivision) return;
    setError('');
    setSuccess('');
    setSubmittingEdit(true);

    try {
      await api.put(`/admin/divisions/${editingDivision.id}`, {
        nama_divisi: editFormData.nama_divisi,
        deskripsi: editFormData.deskripsi,
        kepala_divisi_id: editFormData.kepala_divisi_id || null,
      });
      setEditingDivision(null);
      setSuccess(`Divisi "${editFormData.nama_divisi}" berhasil diperbarui.`);
      fetchDivisions();
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal memperbarui divisi.');
    } finally {
      setSubmittingEdit(false);
    }
  };

  const handleDelete = async (id, name) => {
    if (!window.confirm(`Yakin ingin menghapus divisi "${name}"?`)) return;
    setError('');
    setSuccess('');
    try {
      await api.delete(`/admin/divisions/${id}`);
      setSuccess(`Divisi "${name}" berhasil dihapus.`);
      fetchDivisions();
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal menghapus divisi. Pastikan tidak ada pengajuan terkait.');
    }
  };

  return (
    <div className="p-8 max-w-5xl mx-auto space-y-6">
      <div>
        <h2 className="text-2xl font-black text-slate-900">Manajemen Divisi Kerja</h2>
        <p className="text-xs text-slate-500 mt-1">Daftar unit kerja dan kepala divisi penanggung jawab usulan anggaran</p>
      </div>

      {success && (
        <div className="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold flex items-center gap-2">
          <Check className="w-4 h-4 text-emerald-600 flex-shrink-0" />
          <span>{success}</span>
        </div>
      )}

      {error && (
        <div className="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-semibold flex items-center gap-2">
          <AlertCircle className="w-4 h-4 text-rose-600 flex-shrink-0" />
          <span>{error}</span>
        </div>
      )}

      {/* Form Tambah Divisi */}
      <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <h3 className="font-bold text-sm text-slate-900 mb-3 flex items-center gap-2">
          <Plus className="w-4 h-4 text-purple-600" />
          Tambah Divisi Baru
        </h3>
        <form onSubmit={handleAdd} className="grid grid-cols-1 sm:grid-cols-4 gap-3">
          <input
            type="text"
            required
            value={namaDivisi}
            onChange={(e) => setNamaDivisi(e.target.value)}
            placeholder="Nama Divisi (contoh: IT & Digital)"
            className="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
          />
          <input
            type="text"
            value={deskripsi}
            onChange={(e) => setDeskripsi(e.target.value)}
            placeholder="Deskripsi divisi (opsional)"
            className="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
          />
          <select
            value={kepalaId}
            onChange={(e) => setKepalaId(e.target.value)}
            className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
          >
            <option value="">Pilih Kepala Divisi (Opsional)</option>
            {heads.map((h) => (
              <option key={h.id} value={h.id}>{h.name}</option>
            ))}
          </select>
          <button
            type="submit"
            disabled={submittingAdd}
            className="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white font-bold text-sm rounded-xl transition shadow-md shadow-purple-200 disabled:opacity-50 flex items-center justify-center gap-1.5"
          >
            {submittingAdd ? 'Menyimpan...' : 'Simpan Divisi'}
          </button>
        </form>
      </div>

      {/* Tabel Daftar Divisi */}
      <div className="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <table className="w-full text-left text-sm">
          <thead className="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
            <tr>
              <th className="py-3 px-4">Nama Divisi</th>
              <th className="py-3 px-4">Kepala Divisi</th>
              <th className="py-3 px-4">Deskripsi</th>
              <th className="py-3 px-4">Jumlah Anggota</th>
              <th className="py-3 px-4">Total Pengajuan</th>
              <th className="py-3 px-4 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 text-slate-700">
            {divisions.map((d) => (
              <tr key={d.id} className="hover:bg-slate-50/80 transition">
                <td className="py-3.5 px-4 font-bold text-slate-900">{d.nama_divisi}</td>
                <td className="py-3.5 px-4 text-xs font-semibold text-purple-700">
                  {d.head_user?.name || (
                    <span className="text-slate-400 font-normal italic">Belum Ditentukan</span>
                  )}
                </td>
                <td className="py-3.5 px-4 text-xs text-slate-500 max-w-xs truncate">
                  {d.deskripsi || '-'}
                </td>
                <td className="py-3.5 px-4 text-xs text-slate-600 font-medium">
                  {d.users_count || 0} Pegawai
                </td>
                <td className="py-3.5 px-4 text-xs text-slate-600 font-medium">
                  {d.submissions_count || 0} Pengajuan
                </td>
                <td className="py-3.5 px-4 text-right space-x-1.5">
                  <button
                    onClick={() => openEditModal(d)}
                    className="p-1.5 bg-slate-100 hover:bg-purple-100 text-slate-700 hover:text-purple-700 rounded-lg transition inline-flex items-center"
                    title="Edit Divisi"
                  >
                    <Edit3 className="w-3.5 h-3.5" />
                  </button>
                  <button
                    onClick={() => handleDelete(d.id, d.nama_divisi)}
                    className="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg transition inline-flex items-center"
                    title="Hapus Divisi"
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Modal Edit Divisi */}
      {editingDivision && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fadeIn">
          <div className="bg-white border border-slate-200 rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between border-b border-slate-100 pb-3">
              <div className="flex items-center gap-2">
                <div className="p-2 bg-purple-50 text-purple-700 rounded-lg">
                  <Edit3 className="w-4 h-4" />
                </div>
                <h3 className="font-bold text-base text-slate-900">Edit Data Divisi</h3>
              </div>
              <button
                onClick={() => setEditingDivision(null)}
                className="p-1 text-slate-400 hover:text-slate-600 rounded-lg transition"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleUpdate} className="space-y-3.5">
              <div>
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                  Nama Divisi *
                </label>
                <input
                  type="text"
                  required
                  value={editFormData.nama_divisi}
                  onChange={(e) => setEditFormData({ ...editFormData, nama_divisi: e.target.value })}
                  className="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
                />
              </div>

              <div>
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                  Kepala Divisi
                </label>
                <select
                  value={editFormData.kepala_divisi_id}
                  onChange={(e) => setEditFormData({ ...editFormData, kepala_divisi_id: e.target.value })}
                  className="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
                >
                  <option value="">Tidak ada / Kosongkan</option>
                  {heads.map((h) => (
                    <option key={h.id} value={h.id}>{h.name}</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                  Deskripsi Divisi
                </label>
                <textarea
                  rows="3"
                  value={editFormData.deskripsi}
                  onChange={(e) => setEditFormData({ ...editFormData, deskripsi: e.target.value })}
                  placeholder="Keterangan tugas dan fungsi divisi..."
                  className="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
                />
              </div>

              <div className="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button
                  type="button"
                  onClick={() => setEditingDivision(null)}
                  className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={submittingEdit}
                  className="px-5 py-2 bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs rounded-xl shadow-md shadow-purple-200 transition disabled:opacity-50"
                >
                  {submittingEdit ? 'Menyimpan...' : 'Simpan Perubahan'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
