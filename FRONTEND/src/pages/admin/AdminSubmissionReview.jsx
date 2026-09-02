import React, { useState, useEffect, useRef } from 'react';
import api from '../../services/api';
import { useParams, Link } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { StatusBadge } from '../../components/StatusBadge';
import { 
  ArrowLeft, 
  CheckCircle2, 
  MessageSquare, 
  DollarSign, 
  History,
  AlertCircle,
  ShieldCheck,
  CheckCheck,
  Send,
  FileText,
  User as UserIcon,
  Loader2
} from 'lucide-react';

export const AdminSubmissionReview = () => {
  const { id } = useParams();
  const { user: currentAdmin } = useAuth();

  const [submission, setSubmission] = useState(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  // Status and cost forms
  const [status, setStatus] = useState('Disetujui');
  const [priority, setPriority] = useState('Sedang');
  const [pesan, setPesan] = useState('');
  const [hargaBeliSatuan, setHargaBeliSatuan] = useState('');
  const [biayaRealisasi, setBiayaRealisasi] = useState('');
  const [tanggalRealisasi, setTanggalRealisasi] = useState('');
  const [receiptFile, setReceiptFile] = useState(null);

  // Quick chat input
  const [quickMsg, setQuickMsg] = useState('');
  const [sendingQuick, setSendingQuick] = useState(false);
  const chatBottomRef = useRef(null);

  const fetchDetail = async () => {
    try {
      const res = await api.get(`/admin/submissions/${id}`);
      const s = res.data.data;
      setSubmission(s);
      setStatus(s.status);
      setPriority(s.prioritas || 'Sedang');
      setHargaBeliSatuan(s.harga_beli_satuan || '');
      setBiayaRealisasi(s.biaya_realisasi || '');
      setTanggalRealisasi(s.tanggal_realisasi || '');
    } catch (err) {
      console.error('Error fetching submission review detail:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchDetail();
  }, [id]);

  useEffect(() => {
    if (chatBottomRef.current) {
      chatBottomRef.current.scrollIntoView({ behavior: 'smooth' });
    }
  }, [submission?.replies]);

  const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(number || 0);
  };

  const formatChatTime = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  };

  const handleStatusSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setSubmitting(true);

    try {
      const formData = new FormData();
      formData.append('status', status);
      formData.append('prioritas', priority);
      if (pesan) formData.append('pesan', pesan);
      if (hargaBeliSatuan) formData.append('harga_beli_satuan', hargaBeliSatuan);
      if (biayaRealisasi) formData.append('biaya_realisasi', biayaRealisasi);
      if (tanggalRealisasi) formData.append('tanggal_realisasi', tanggalRealisasi);
      if (receiptFile) formData.append('bukti_pembelian', receiptFile);

      await api.post(`/admin/submissions/${id}/reply`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      setSuccess('Status pengajuan berhasil diperbarui.');
      setPesan('');
      fetchDetail();
    } catch (err) {
      if (err.response?.data?.errors) {
        setError(Object.values(err.response.data.errors)[0][0]);
      } else if (err.response?.data?.message) {
        setError(err.response.data.message);
      } else {
        setError('Gagal memperbarui status pengajuan.');
      }
    } finally {
      setSubmitting(false);
    }
  };

  const handleQuickChat = async (e) => {
    e.preventDefault();
    if (!quickMsg.trim()) return;

    setSendingQuick(true);
    try {
      const formData = new FormData();
      formData.append('status', submission.status);
      formData.append('prioritas', submission.prioritas || 'Sedang');
      formData.append('pesan', quickMsg.trim());

      await api.post(`/admin/submissions/${id}/reply`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      setQuickMsg('');
      fetchDetail();
    } catch (err) {
      alert('Gagal mengirim pesan chat.');
    } finally {
      setSendingQuick(false);
    }
  };

  if (loading) {
    return (
      <div className="p-8 text-center text-slate-500 font-medium">Memuat data pengajuan...</div>
    );
  }

  if (!submission) {
    return (
      <div className="p-8 text-center text-slate-500">Pengajuan tidak ditemukan.</div>
    );
  }

  return (
    <div className="p-8 max-w-5xl mx-auto space-y-6">
      {/* Header Bar */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <Link
            to="/admin/submissions"
            className="p-2 bg-white border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50 transition shadow-xs"
          >
            <ArrowLeft className="w-4 h-4" />
          </Link>
          <div>
            <div className="flex items-center gap-2.5">
              <h2 className="text-2xl font-black text-slate-900">{submission.nomor_pengajuan}</h2>
              <StatusBadge status={submission.status} />
            </div>
            <p className="text-xs text-slate-500 mt-0.5">
              Pemohon: <span className="font-semibold text-slate-700">{submission.user?.name}</span> ({submission.division?.nama_divisi})
            </p>
          </div>
        </div>
      </div>

      {success && (
        <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-semibold flex items-center gap-2">
          <CheckCircle2 className="w-4 h-4 text-emerald-600 flex-shrink-0" />
          <span>{success}</span>
        </div>
      )}

      {error && (
        <div className="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-sm font-semibold flex items-center gap-2">
          <AlertCircle className="w-4 h-4 text-rose-600 flex-shrink-0" />
          <span>{error}</span>
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Kolom Kiri: Form Tindakan & Two-Way WhatsApp Chat */}
        <div className="lg:col-span-2 space-y-6">
          {/* Form Tindakan Administrator */}
          <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h3 className="font-bold text-base text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
              <ShieldCheck className="w-5 h-5 text-emerald-700" />
              Tindakan Persetujuan & Penyesuaian Anggaran
            </h3>

            <form onSubmit={handleStatusSubmit} className="space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Ubah Status Pengajuan *
                  </label>
                  <select
                    value={status}
                    onChange={(e) => setStatus(e.target.value)}
                    required
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
                  >
                    <option value="Menunggu">Menunggu</option>
                    <option value="Diproses">Diproses (Pengadaan Berjalan)</option>
                    <option value="Disetujui">Disetujui (Anggaran Disetujui)</option>
                    <option value="Ditolak">Ditolak</option>
                    <option value="Selesai">Selesai (Barang Diterima & Sah)</option>
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Tingkat Prioritas
                  </label>
                  <select
                    value={priority}
                    onChange={(e) => setPriority(e.target.value)}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
                  >
                    <option value="Rendah">Rendah</option>
                    <option value="Sedang">Sedang</option>
                    <option value="Tinggi">Tinggi</option>
                    <option value="Darurat">Darurat</option>
                  </select>
                </div>
              </div>

              {/* Realisasi Keuangan */}
              <div className="p-4 bg-emerald-50/60 border border-emerald-200/80 rounded-xl space-y-3">
                <p className="text-xs font-bold uppercase tracking-wider text-emerald-900 flex items-center gap-1.5">
                  <DollarSign className="w-4 h-4 text-emerald-700" />
                  Pencatatan Biaya Realisasi Faktur Sah (Opsional)
                </p>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label className="block text-xs font-medium text-slate-600 mb-1">
                      Total Biaya Realisasi (Rp)
                    </label>
                    <input
                      type="number"
                      value={biayaRealisasi}
                      onChange={(e) => setBiayaRealisasi(e.target.value)}
                      placeholder="Contoh: 14500000"
                      className="w-full px-3 py-2 bg-white border border-emerald-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-slate-600 mb-1">
                      Tanggal Realisasi
                    </label>
                    <input
                      type="date"
                      value={tanggalRealisasi}
                      onChange={(e) => setTanggalRealisasi(e.target.value)}
                      className="w-full px-3 py-2 bg-white border border-emerald-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
                    />
                  </div>
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                  Catatan Persetujuan / Alasan Penolakan
                </label>
                <textarea
                  rows="2"
                  value={pesan}
                  onChange={(e) => setPesan(e.target.value)}
                  placeholder="Tuliskan catatan tindak lanjut status..."
                  className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
                />
              </div>

              <div>
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                  Upload Nota / Kuitansi Pembelian Sah (Opsional)
                </label>
                <input
                  type="file"
                  accept="image/*,.pdf"
                  onChange={(e) => setReceiptFile(e.target.files[0])}
                  className="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                />
              </div>

              <div className="pt-2 flex justify-end">
                <button
                  type="submit"
                  disabled={submitting}
                  className="px-6 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-200 flex items-center gap-2 transition disabled:opacity-50"
                >
                  {submitting ? (
                    <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                  ) : (
                    <>
                      <CheckCircle2 className="w-4 h-4" />
                      Simpan & Terapkan Status
                    </>
                  )}
                </button>
              </div>
            </form>
          </div>

          {/* TWO-WAY WHATSAPP STYLE CHAT BOX FOR ADMIN */}
          <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm flex flex-col">
            {/* Header Chat */}
            <div className="bg-emerald-800 text-white px-5 py-3.5 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                  <MessageSquare className="w-4 h-4 text-emerald-100" />
                </div>
                <div>
                  <h3 className="font-bold text-sm leading-tight">Diskusi & Catatan Verifikasi</h3>
                  <p className="text-[11px] text-emerald-200">Percakapan dua arah dengan Pemohon</p>
                </div>
              </div>
              <span className="text-xs bg-emerald-700 px-2.5 py-0.5 rounded-full font-medium">
                {submission.replies?.length || 0} Pesan
              </span>
            </div>

            {/* Chat Body (WhatsApp Background) */}
            <div className="bg-[#efeae2] p-4 sm:p-5 space-y-3 min-h-[260px] max-h-[380px] overflow-y-auto">
              <div className="flex justify-center my-1">
                <span className="px-3 py-0.5 bg-white/90 text-slate-500 text-[10px] font-semibold rounded-full shadow-2xs border border-slate-200">
                  {new Date(submission.created_at).toLocaleDateString('id-ID', { dateStyle: 'full' })}
                </span>
              </div>

              {(!submission.replies || submission.replies.length === 0) ? (
                <div className="text-center py-10 text-slate-400">
                  <div className="w-10 h-10 mx-auto rounded-full bg-white/80 flex items-center justify-center text-slate-400 mb-2">
                    <MessageSquare className="w-5 h-5 opacity-40" />
                  </div>
                  <p className="text-xs font-semibold text-slate-700">Belum Ada Percakapan</p>
                  <p className="text-[11px] text-slate-500 mt-0.5">Tulis pesan cepat di bawah untuk mengirim pesan langsung ke pemohon.</p>
                </div>
              ) : (
                submission.replies.map((reply) => {
                  const isMe = reply.admin?.role === 'admin' || reply.admin_id === currentAdmin?.id;
                  return (
                    <div key={reply.id} className={`flex ${isMe ? 'justify-end' : 'justify-start'}`}>
                      {isMe ? (
                        /* Bubble Hijau (Pesan Keluar dari Admin/Anda) */
                        <div className="max-w-[85%] sm:max-w-[70%] bg-[#d9fdd3] rounded-xl rounded-tr-xs p-3 shadow-xs border border-emerald-200 space-y-1">
                          <div className="flex items-center justify-between gap-2 border-b border-emerald-200/60 pb-1">
                            <span className="text-xs font-bold text-emerald-950 flex items-center gap-1">
                              <ShieldCheck className="w-3 h-3 text-emerald-700" />
                              Anda ({reply.admin?.name || currentAdmin?.name})
                            </span>
                            <span className="text-[10px] text-emerald-800 font-semibold">Admin</span>
                          </div>

                          <p className="text-xs text-slate-900 leading-relaxed whitespace-pre-wrap py-0.5">
                            {reply.pesan}
                          </p>

                          {reply.status_setelah_balasan && (
                            <div className="pt-0.5">
                              <span className="inline-block text-[10px] px-2 py-0.5 bg-white text-emerald-900 font-semibold rounded border border-emerald-200 shadow-2xs">
                                Status diubah: <span className="font-bold">{reply.status_setelah_balasan}</span>
                              </span>
                            </div>
                          )}

                          <div className="flex items-center justify-end gap-1 text-[10px] text-emerald-800/80 pt-0.5">
                            <span>{formatChatTime(reply.created_at)}</span>
                            <CheckCheck className="w-3.5 h-3.5 text-emerald-600" />
                          </div>
                        </div>
                      ) : (
                        /* Bubble Putih (Pesan Masuk dari Pemohon) */
                        <div className="max-w-[85%] sm:max-w-[70%] bg-white rounded-xl rounded-tl-xs p-3 shadow-xs border border-slate-200 space-y-1">
                          <div className="flex items-center justify-between gap-2 border-b border-slate-100 pb-1">
                            <span className="text-xs font-bold text-slate-900 flex items-center gap-1">
                              <UserIcon className="w-3 h-3 text-emerald-600" />
                              {reply.admin?.name || submission.user?.name}
                            </span>
                            <span className="text-[10px] px-1.5 py-0.2 bg-slate-100 text-slate-700 font-bold rounded">
                              Pemohon
                            </span>
                          </div>

                          <p className="text-xs text-slate-800 leading-relaxed whitespace-pre-wrap py-0.5">
                            {reply.pesan}
                          </p>

                          <div className="flex items-center justify-end text-[10px] text-slate-400 pt-0.5">
                            <span>{formatChatTime(reply.created_at)}</span>
                          </div>
                        </div>
                      )}
                    </div>
                  );
                })
              )}
              <div ref={chatBottomRef} />
            </div>

            {/* Quick Chat Input Bar for Admin */}
            <form onSubmit={handleQuickChat} className="bg-slate-100 border-t border-slate-200 p-3 flex items-center gap-2">
              <input
                type="text"
                value={quickMsg}
                onChange={(e) => setQuickMsg(e.target.value)}
                placeholder="Kirim pesan balasan cepat ke Pemohon..."
                className="flex-1 px-4 py-2.5 bg-white border border-slate-300 rounded-full text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-700 shadow-2xs"
              />
              <button
                type="submit"
                disabled={sendingQuick || !quickMsg.trim()}
                className="p-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-full transition shadow-md disabled:opacity-40 flex items-center justify-center"
                title="Kirim Pesan Cepat"
              >
                {sendingQuick ? (
                  <Loader2 className="w-4 h-4 animate-spin" />
                ) : (
                  <Send className="w-4 h-4" />
                )}
              </button>
            </form>
          </div>
        </div>

        {/* Kolom Kanan: Rincian Barang & Audit Log */}
        <div className="space-y-6">
          <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h3 className="font-bold text-base text-slate-900 border-b border-slate-100 pb-2 flex items-center gap-2">
              <FileText className="w-4 h-4 text-emerald-700" />
              Spesifikasi Usulan Barang
            </h3>
            <div className="grid grid-cols-2 gap-3 text-xs">
              <div>
                <span className="text-slate-400 font-medium">Nama Barang</span>
                <p className="font-bold text-slate-900 mt-0.5">{submission.nama_barang}</p>
              </div>
              <div>
                <span className="text-slate-400 font-medium">Jumlah</span>
                <p className="font-semibold text-slate-800 mt-0.5">{submission.jumlah} {submission.satuan}</p>
              </div>
              <div>
                <span className="text-slate-400 font-medium">Kategori</span>
                <p className="font-semibold text-slate-800 mt-0.5">{submission.category?.nama_kategori || '-'}</p>
              </div>
              <div>
                <span className="text-slate-400 font-medium">Harga Satuan</span>
                <p className="font-semibold text-slate-800 mt-0.5">{formatRupiah(submission.harga_satuan)}</p>
              </div>
              <div className="col-span-2 pt-1 border-t border-slate-100 flex justify-between items-center">
                <span className="text-slate-500 font-semibold">Total Estimasi Usulan:</span>
                <span className="font-black text-emerald-700 text-sm">{formatRupiah(submission.total_biaya)}</span>
              </div>
            </div>

            <div className="pt-2 border-t border-slate-100 space-y-2 text-xs">
              <div>
                <span className="text-slate-400 font-medium">Alasan Kebutuhan:</span>
                <p className="text-slate-700 mt-0.5 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                  {submission.alasan}
                </p>
              </div>
              {submission.spesifikasi && (
                <div>
                  <span className="text-slate-400 font-medium">Spesifikasi Detail:</span>
                  <p className="text-slate-700 mt-0.5 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                    {submission.spesifikasi}
                  </p>
                </div>
              )}
            </div>
          </div>

          <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
            <h4 className="font-bold text-sm text-slate-900 flex items-center gap-1.5">
              <History className="w-4 h-4 text-slate-500" />
              Audit Log Pengeluaran
            </h4>
            <div className="space-y-2.5">
              {submission.expense_logs?.map((log) => (
                <div key={log.id} className="text-xs border-l-2 border-emerald-600 pl-3 py-1">
                  <p className="font-bold text-slate-800">{log.tipe}</p>
                  <p className="text-slate-500 mt-0.5">{log.keterangan}</p>
                  <span className="text-[10px] text-slate-400">{new Date(log.created_at).toLocaleString('id-ID')}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
