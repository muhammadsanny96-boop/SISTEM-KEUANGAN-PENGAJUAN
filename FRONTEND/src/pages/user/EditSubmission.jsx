import React, { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import api from '../../services/api';
import { useNavigate, useParams, Link } from 'react-router-dom';
import { ArrowLeft, Calculator, AlertCircle, CheckCircle2 } from 'lucide-react';

export const EditSubmission = () => {
  const { id } = useParams();
  const navigate = useNavigate();

  const [categories, setCategories] = useState([]);
  const [months, setMonths] = useState([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setErrors] = useState({});
  const [generalError, setGeneralError] = useState('');

  const [formData, setFormData] = useState({
    nama_barang: '',
    category_id: '',
    jumlah: 1,
    satuan: 'Unit',
    harga_satuan: '',
    target_bulan: '',
    jenis_pengajuan: 'Barang Baru',
    prioritas: 'Sedang',
    alasan: '',
    spesifikasi: '',
  });

  const [file, setFile] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [subRes, optRes] = await Promise.all([
          api.get(`/user/submissions/${id}`),
          api.get('/options'),
        ]);

        const s = subRes.data.data;
        if (s.status !== 'Menunggu') {
          toast.error('Pengajuan yang sudah diproses atau disetujui tidak dapat diedit.');
          navigate(`/user/submissions/${id}`);
          return;
        }

        setFormData({
          nama_barang: s.nama_barang || '',
          category_id: s.category_id || '',
          jumlah: s.jumlah || 1,
          satuan: s.satuan || 'Unit',
          harga_satuan: s.harga_satuan || '',
          target_bulan: s.target_bulan || '',
          jenis_pengajuan: s.jenis_pengajuan || 'Barang Baru',
          prioritas: s.prioritas || 'Sedang',
          alasan: s.alasan || '',
          spesifikasi: s.spesifikasi || '',
        });

        setCategories(optRes.data.categories || []);
        setMonths(optRes.data.months || []);
      } catch (err) {
        console.error('Error fetching edit submission:', err);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [id, navigate]);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleFileChange = (e) => {
    if (e.target.files && e.target.files[0]) {
      setFile(e.target.files[0]);
    }
  };

  const totalBiaya = (Number(formData.jumlah) || 0) * (Number(formData.harga_satuan) || 0);

  const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(number || 0);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});
    setGeneralError('');
    setSubmitting(true);

    try {
      const data = new FormData();
      data.append('_method', 'PUT');
      Object.keys(formData).forEach((key) => {
        if (formData[key] !== null && formData[key] !== undefined && formData[key] !== '') {
          data.append(key, formData[key]);
        }
      });
      if (file) {
        data.append('foto_barang', file);
      }

      await api.post(`/user/submissions/${id}`, data, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      toast.success('Usulan pengadaan berhasil diperbarui!');
      navigate(`/user/submissions/${id}`);
    } catch (err) {
      if (err.response?.data?.errors) {
        setErrors(err.response.data.errors);  // <-- error akan berupa object
        setGeneralError('');
      } else if (err.response?.data?.message) {
        setErrors({});
        setGeneralError(err.response.data.message);
      } else {
        setErrors({});
        setGeneralError('Gagal memperbarui pengajuan. Periksa kembali kelengkapan formulir Anda.');
      }
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return (
      <div className="p-8 text-center text-slate-500 font-medium">Memuat data pengajuan...</div>
    );
  }

  return (
    <div className="p-8 max-w-4xl mx-auto space-y-6">
      <div className="flex items-center gap-3">
        <Link
          to={`/user/submissions/${id}`}
          className="p-2 bg-white border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50 transition shadow-xs"
        >
          <ArrowLeft className="w-4 h-4" />
        </Link>
        <div>
          <h2 className="text-2xl font-black text-slate-900">Edit Usulan Pengadaan</h2>
          <p className="text-xs text-slate-500">Perbarui spesifikasi atau estimasi biaya usulan barang</p>
        </div>
      </div>

      <div className="bg-white border border-slate-200 rounded-2xl p-7 shadow-sm">
        {generalError && (
          <div className="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-3 text-sm text-rose-800">
            <AlertCircle className="w-5 h-5 flex-shrink-0 mt-0.5 text-rose-600" />
            <div className="space-y-1">
              <p className="font-bold">Formulir Belum Lengkap / Valid:</p>
              <p className="text-xs leading-relaxed">{generalError}</p>
            </div>
          </div>
)}


        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                Nama Barang / Jasa *
              </label>
              <input
                type="text"
                required
                name="nama_barang"
                value={formData.nama_barang}
                onChange={handleChange}
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
              />
              {error.nama_barang && (
                <p className="mt-1 text-xs text-rose-600">{error.nama_barang[0]}</p>
              )}
            </div>

            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                Kategori Pengadaan *
              </label>
              <select
                name="category_id"
                value={formData.category_id}
                onChange={handleChange}
                required
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
              >
                {categories.map((c) => (
                  <option key={c.id} value={c.id}>{c.nama_kategori}</option>
                ))}
              </select>
              {error.category_id && (
                <p className="mt-1 text-xs text-rose-600">{error.category_id[0]}</p>
              )}
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                Jumlah Unit *
              </label>
              <input
                type="number"
                min="1"
                required
                name="jumlah"
                value={formData.jumlah}
                onChange={handleChange}
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
              />
              {error.jumlah && (
                <p className="mt-1 text-xs text-rose-600">{error.jumlah[0]}</p>
              )}
            </div>

            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                Satuan *
              </label>
              <input
                type="text"
                required
                name="satuan"
                value={formData.satuan}
                onChange={handleChange}
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
              />
              {error.satuan && (
                <p className="mt-1 text-xs text-rose-600">{error.satuan[0]}</p>
              )}
            </div>

            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                Estimasi Harga Satuan (Rp) *
              </label>
              <input
                type="number"
                min="0"
                required
                name="harga_satuan"
                value={formData.harga_satuan}
                onChange={handleChange}
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
              />
              {error.harga_satuan && (
                <p className="mt-1 text-xs text-rose-600">{error.harga_satuan[0]}</p>
              )}
            </div>
          </div>

          <div className="p-4 bg-emerald-50/80 border border-emerald-200 rounded-2xl flex items-center justify-between shadow-2xs">
            <div className="flex items-center gap-2 text-emerald-900 font-bold text-sm">
              <Calculator className="w-5 h-5 text-emerald-700" />
              Total Estimasi Anggaran Usulan:
            </div>
            <span className="text-xl font-black text-emerald-900">{formatRupiah(totalBiaya)}</span>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                Jenis Pengajuan *
              </label>
              <select
                name="jenis_pengajuan"
                value={formData.jenis_pengajuan}
                onChange={handleChange}
                required
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
              >
                <option value="Barang Baru">Barang Baru</option>
                <option value="Barang Habis">Barang Habis</option>
                <option value="Barang Rusak">Barang Rusak</option>
                <option value="Barang Perlu Diganti">Barang Perlu Diganti</option>
                <option value="Barang Perlu Dibeli">Barang Perlu Dibeli</option>
              </select>
              {error.jenis_pengajuan && (
                <p className="mt-1 text-xs text-rose-600">{error.jenis_pengajuan[0]}</p>
              )}
            </div>

            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                Target Periode Bulan *
              </label>
              <select
                name="target_bulan"
                value={formData.target_bulan}
                onChange={handleChange}
                required
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
              >
                {months.map((m) => (
                  <option key={m.value} value={m.value}>{m.label}</option>
                ))}
              </select>
              {error.target_bulan && (
                <p className="mt-1 text-xs text-rose-600">{error.target_bulan[0]}</p>
              )}
            </div>

            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                Tingkat Prioritas *
              </label>
              <select
                name="prioritas"
                value={formData.prioritas}
                onChange={handleChange}
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
              >
                <option value="Sedang">Sedang</option>
                <option value="Rendah">Rendah</option>
                <option value="Tinggi">Tinggi</option>
                <option value="Mendesak">Mendesak</option>
              </select>
              {error.prioritas && (
                <p className="mt-1 text-xs text-rose-600">{error.prioritas[0]}</p>
              )}
            </div>
          </div>

          <div>
            <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              Alasan & Justifikasi Kebutuhan *
            </label>
            <textarea
              required
              rows="3"
              name="alasan"
              value={formData.alasan}
              onChange={handleChange}
              className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
            />
            {error.alasan && (
              <p className="mt-1 text-xs text-rose-600">{error.alasan[0]}</p>
            )}
          </div>

          <div>
            <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              Spesifikasi Lengkap / Tipe / Merek (Opsional)
            </label>
            <textarea
              rows="2"
              name="spesifikasi"
              value={formData.spesifikasi}
              onChange={handleChange}
              className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
            />
            {error.spesifikasi && (
              <p className="mt-1 text-xs text-rose-600">{error.spesifikasi[0]}</p>
            )}
          </div>

          <div>
            <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              Ganti Foto / Brosur Barang (Opsional, Max 2MB)
            </label>
            <input
              type="file"
              accept="image/*"
              onChange={handleFileChange}
              className="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer"
            />
            {error.gambar && (
              <p className="mt-1 text-xs text-rose-600">{error.foto_barang[0]}</p>
            )}
          </div>

          <div className="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <Link
              to={`/user/submissions/${id}`}
              className="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition"
            >
              Batal
            </Link>
            <button
              type="submit"
              disabled={submitting}
              className="px-6 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-200 flex items-center gap-2 transition disabled:opacity-50"
            >
              {submitting ? (
                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              ) : (
                <>
                  <CheckCircle2 className="w-4 h-4" />
                  Simpan Perubahan
                </>
              )}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
