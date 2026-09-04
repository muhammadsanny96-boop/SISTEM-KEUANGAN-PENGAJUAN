import React, { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import api from '../../services/api';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { PlusCircle, ArrowLeft, Upload, Calculator, AlertCircle, Building2, CheckCircle2 } from 'lucide-react';

export const CreateSubmission = () => {
  const { user } = useAuth();
  const navigate = useNavigate();

  const [categories, setCategories] = useState([]);
  const [months, setMonths] = useState([]);
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});
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
    const fetchOptions = async () => {
      try {
        const res = await api.get('/options');
        setCategories(res.data.categories || []);
        setMonths(res.data.months || []);
        if (res.data.categories?.length > 0) {
          setFormData((prev) => ({
            ...prev,
            category_id: res.data.categories[0].id,
            target_bulan: res.data.months[0]?.value || '',
          }));
        }
      } catch (err) {
        console.error('Error fetching options:', err);
      }
    };
    fetchOptions();
  }, []);

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
    setLoading(true);

    try {
      const data = new FormData();
      Object.keys(formData).forEach((key) => {
        if (formData[key] !== null && formData[key] !== undefined && formData[key] !== '') {
          data.append(key, formData[key]);
        }
      });
      if (file) {
        data.append('foto_barang', file);
      }

      const res = await api.post('/user/submissions', data, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      toast.success('Pengajuan berhasil dibuat!');
      navigate(`/user/submissions/${res.data.data.id}`);
    } catch (err) {
      if (err.response?.data?.errors) {
        setErrors(err.response.data.errors);
      } else if (err.response?.data?.message) {
        setGeneralError(err.response.data.message);
      } else {
        setGeneralError('Gagal membuat pengajuan. Periksa kembali kelengkapan formulir Anda.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="p-8 max-w-4xl mx-auto space-y-6">
      <div className="flex items-center gap-3">
        <Link
          to="/user/submissions"
          className="p-2 bg-white border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50 transition shadow-xs"
        >
          <ArrowLeft className="w-4 h-4" />
        </Link>
        <div>
          <h2 className="text-2xl font-black text-slate-900">Buat Formulir Usulan Pengadaan</h2>
          <p className="text-xs text-slate-500 mt-0.5">
            Diajukan oleh <span className="font-semibold text-slate-700">{user?.name}</span> (Kepala Divisi {user?.division?.nama_divisi || ''})
          </p>
        </div>
      </div>

      <div className="bg-white border border-slate-200 rounded-2xl p-7 shadow-sm">
        {generalError && (
          <div className="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-3 text-sm text-rose-800">
            <AlertCircle className="w-5 h-5 flex-shrink-0 mt-0.5 text-rose-600" />
            <div className="space-y-1">
              <p className="font-bold">Terjadi Kesalahan! Silahkan Check Kembali</p>
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
                placeholder="Contoh: Laptop Kerja Developer 16GB"
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
              />
              {errors.nama_barang && (<p className="text-rose-600 text-xs mt-1">{errors.nama_barang[0]}</p>)}
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
              {errors.category_id && (<p className="text-rose-600 text-xs mt-1">{errors.category_id[0]}</p>)}
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
              {errors.jumlah && (<p className="text-rose-600 text-xs mt-1">{errors.jumlah[0]}</p>)}
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
                placeholder="Unit / Pcs / Rim / Box"
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
              />
              {errors.satuan && (<p className="text-rose-600 text-xs mt-1">{errors.satuan[0]}</p>)}
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
                placeholder="Contoh: 15000000"
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
              />
              {errors.harga_satuan && (<p className="text-rose-600 text-xs mt-1">{errors.harga_satuan[0]}</p>)}
            </div>
          </div>

          {/* Banner Total Estimasi */}
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
              placeholder="Jelaskan alasan kebutuhan pengadaan barang/jasa ini bagi operasional divisi..."
              className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
            />
            {errors.alasan && (<p className="text-rose-600 text-xs mt-1">{errors.alasan[0]}</p>)}
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
              placeholder="Tuliskan spesifikasi teknis, merek yang direkomendasikan, atau ketentuan barang..."
              className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
            />
            {errors.spesifikasi && (<p className="text-rose-600 text-xs mt-1">{errors.spesifikasi[0]}</p>)}
          </div>

          <div>
            <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              Upload Foto / Brosur Barang (Opsional, Max 2MB)
            </label>
            <input
              type="file"
              accept="image/*"
              onChange={handleFileChange}
              className="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer"
            />
            {errors.foto_barang && (<p className="text-rose-600 text-xs mt-1">{errors.foto_barang[0]}</p>)}
          </div>

          <div className="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <Link
              to="/user/submissions"
              className="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition"
            >
              Batal
            </Link>
            <button
              type="submit"
              disabled={loading}
              className="px-6 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-200 flex items-center gap-2 transition disabled:opacity-50"
            >
              {loading ? (
                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              ) : (
                <>
                  <CheckCircle2 className="w-4 h-4" />
                  Kirim Usulan Pengadaan
                </>
              )}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
