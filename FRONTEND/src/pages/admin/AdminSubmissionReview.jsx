import React, { useState, useEffect, useRef } from 'react';
import api from '../../services/api';
import { useParams, Link } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { StatusBadge } from '../../components/StatusBadge';
import { SubmissionTimeline } from '../../components/SubmissionTimeline';
import { 
  ArrowLeft, 
  CheckCircle2, 
  MessageSquare, 
  History, 
  AlertCircle,
  ShieldCheck,
  CheckCheck,
  Send,
  FileText,
  User as UserIcon,
  Loader2,
  XCircle,
  Receipt,
  ExternalLink,
  Clock,
  HelpCircle
} from 'lucide-react';

export const AdminSubmissionReview = () => {
  const { id } = useParams();
  const { user: currentAdmin } = useAuth();

  const [submission, setSubmission] = useState(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  // Form states untuk penyelesaian & realisasi jika admin membantu input manual
  const [showManualForm, setShowManualForm] = useState(false);
  const [priority, setPriority] = useState('Sedang');
  const [pesan, setPesan] = useState('');
  const [biayaRealisasi, setBiayaRealisasi] = useState('');
  const [tanggalRealisasi, setTanggalRealisasi] = useState('');
  const [receiptFile, setReceiptFile] = useState(null);

  // Quick chat state
  const [quickMsg, setQuickMsg] = useState('');
  const [sendingQuick, setSendingQuick] = useState(false);
  const chatBottomRef = useRef(null);

  const fetchDetail = async () => {
    try {
      const res = await api.get(`/admin/submissions/${id}`);
      const s = res.data.data;
      setSubmission(s);
      setPriority(s.prioritas || 'Sedang');
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

  const getStorageUrl = (path) => {
    if (!path) return null;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return `http://backend.test/storage/${path.replace(/^\//, '')}`;
  };

  // Helper Aksi Cepat: Langsung ubah status (Setujui / Proses / Buka Kembali / Tolak / Selesai)
  const handleDirectAction = async (targetStatus, note = '') => {
    setSubmitting(true);
    setError('');
    setSuccess('');

    const defaultNotes = {
      'Disetujui': 'Usulan anggaran telah disetujui. Pemohon dipersilakan melakukan pembelian dan mengunggah nota.',
      'Diproses': 'Pengajuan sedang dalam proses peninjauan dan pengadaan barang.',
      'Ditolak': note || 'Pengajuan tidak disetujui.',
      'Selesai': 'Laporan nota pembelian telah diverifikasi sah dan pengadaan diselesaikan.',
      'Menunggu': 'Status pengajuan dibuka kembali untuk ditinjau ulang.',
    };

    try {
      const formData = new FormData();
      formData.append('status', targetStatus);
      formData.append('prioritas', priority || submission?.prioritas || 'Sedang');
      formData.append('pesan', note || defaultNotes[targetStatus] || `Status diubah menjadi ${targetStatus}`);

      await api.post(`/admin/submissions/${id}/reply`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      setSuccess(`Status pengajuan berhasil diubah menjadi: ${targetStatus}`);
      fetchDetail();
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal memproses tindakan.');
    } finally {
      setSubmitting(false);
    }
  };

  // Form Submit untuk Realisasi & Penyelesaian Manual oleh Admin
  const handleManualStatusSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setSubmitting(true);

    try {
      const formData = new FormData();
      formData.append('status', 'Selesai');
      formData.append('prioritas', priority || submission?.prioritas || 'Sedang');
      formData.append('pesan', pesan.trim() || 'Admin telah memverifikasi dan mengesahkan biaya pembelian.');
      
      if (biayaRealisasi) formData.append('biaya_realisasi', biayaRealisasi);
      if (tanggalRealisasi) formData.append('tanggal_realisasi', tanggalRealisasi);
      if (receiptFile) formData.append('bukti_pembelian', receiptFile);

      await api.post(`/admin/submissions/${id}/reply`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      setSuccess('Pengadaan berhasil diselesaikan dan biaya realisasi sah telah tercatat!');
      setPesan('');
      setShowManualForm(false);
      fetchDetail();
    } catch (err) {
      if (err.response?.data?.errors) {
        setError(Object.values(err.response.data.errors)[0][0]);
      } else if (err.response?.data?.message) {
        setError(err.response.data.message);
      } else {
        setError('Gagal menyelesaikan pengadaan.');
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

  // Cek apakah user sudah mengunggah nota pembelian
  const userHasUploadedReceipt = Boolean(submission.biaya_realisasi || submission.bukti_pembelian);

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
          <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0" />
          <span>{success}</span>
        </div>
      )}

      {error && (
        <div className="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-sm font-semibold flex items-center gap-2">
          <AlertCircle className="w-4 h-4 text-rose-600 shrink-0" />
          <span>{error}</span>
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Kolom Kiri: Form Tindakan Berdasarkan Status & Two-Way WhatsApp Chat */}
        <div className="lg:col-span-2 space-y-6">
          
          {/* KOTAK TINDAKAN ADMINISTRATOR (ALUR 1: VERIFIKASI & PERSETUJUAN) */}
          <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            
            {/* KONDISI 1: STATUS 'MENUNGGU' ATAU 'DIPROSES' (TOMBOL KEPUTUSAN ANGGARAN) */}
            {['Menunggu', 'Diproses'].includes(submission.status) && (
              <div className="space-y-4">
                <div className="border-b border-slate-100 pb-3">
                  <h3 className="font-bold text-base text-slate-900 flex items-center gap-2">
                    <ShieldCheck className="w-5 h-5 text-emerald-700" />
                    Persetujuan Anggaran Belanja Pemohon
                  </h3>
                  <p className="text-xs text-slate-500 mt-0.5">
                    Tinjau usulan pengadaan sebesar <strong className="text-emerald-700 font-bold">{formatRupiah(submission.total_biaya)}</strong> dari {submission.division?.nama_divisi}.
                  </p>
                </div>

                <div className="flex flex-wrap items-center gap-3 pt-1">
                  {/* Tombol Setujui */}
                  <button
                    type="button"
                    disabled={submitting}
                    onClick={() => handleDirectAction('Disetujui', 'Usulan anggaran telah disetujui. Pemohon dipersilakan melakukan pembelian dan mengunggah nota.')}
                    className="flex-1 min-w-[150px] py-3 px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2 disabled:opacity-50"
                  >
                    <CheckCircle2 className="w-4 h-4" />
                    Setujui Anggaran Belanja
                  </button>

                  {/* Tombol Proses */}
                  {submission.status === 'Menunggu' && (
                    <button
                      type="button"
                      disabled={submitting}
                      onClick={() => handleDirectAction('Diproses', 'Pengajuan sedang dalam proses peninjauan.')}
                      className="py-3 px-4 bg-blue-50 hover:bg-blue-100 text-blue-800 font-bold text-xs rounded-xl border border-blue-200 transition flex items-center justify-center gap-2 disabled:opacity-50"
                    >
                      Tandai Diproses
                    </button>
                  )}

                  {/* Tombol Tolak Usulan */}
                  <button
                    type="button"
                    disabled={submitting}
                    onClick={() => {
                      const alasan = window.prompt('Masukkan alasan penolakan untuk pemohon:');
                      if (alasan) handleDirectAction('Ditolak', alasan);
                    }}
                    className="py-3 px-4 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl border border-rose-200 transition flex items-center justify-center gap-2 disabled:opacity-50"
                  >
                    Tolak Usulan
                  </button>
                </div>
              </div>
            )}

            {/* KONDISI 2: STATUS 'DISETUJUI' (VERIFIKASI NOTA DARI USER ATAU INPUT MANUAL) */}
            {submission.status === 'Disetujui' && (
              <div className="space-y-4">
                <div className="border-b border-slate-100 pb-3">
                  <div className="flex items-center gap-2 text-emerald-800 font-bold text-sm">
                    <span className="p-1 bg-emerald-100 rounded-md text-emerald-800 text-xs font-semibold">Tahap Realisasi</span>
                    <span>Verifikasi Nota Pembelian Pemohon</span>
                  </div>
                  <p className="text-xs text-slate-500 mt-1">
                    Anggaran telah disetujui. Pemohon ({submission.user?.name}) bertugas membeli barang dan mengunggah nota kuitansi sah.
                  </p>
                </div>

                {/* Sub-Kondisi A: User SUDAH mengunggah nota */}
                {userHasUploadedReceipt ? (
                  <div className="p-5 bg-emerald-50/80 border border-emerald-200 rounded-2xl space-y-4">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2 text-emerald-950 font-bold text-sm">
                        <Receipt className="w-5 h-5 text-emerald-700" />
                        Laporan Nota Pembelian dari Pemohon Siap Diverifikasi
                      </div>
                      <span className="text-[11px] font-bold text-emerald-800 bg-emerald-100/90 px-2.5 py-0.5 rounded-full">
                        Perlu Disahkan
                      </span>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-white p-4 rounded-xl border border-emerald-200 text-xs">
                      <div>
                        <span className="text-slate-500 font-medium">Total Nominal Nota Asli:</span>
                        <p className="text-base font-black text-emerald-950 mt-0.5">
                          {formatRupiah(submission.biaya_realisasi)}
                        </p>
                        <p className={`text-[11px] font-medium mt-0.5 ${submission.total_biaya >= submission.biaya_realisasi ? 'text-emerald-800' : 'text-rose-700'}`}>
                          {submission.total_biaya >= submission.biaya_realisasi
                            ? `✓ Hemat ${formatRupiah(submission.total_biaya - submission.biaya_realisasi)} dari usulan`
                            : `⚠ Melebihi usulan sebesar ${formatRupiah(submission.biaya_realisasi - submission.total_biaya)}`}
                        </p>
                      </div>

                      <div>
                        <span className="text-slate-500 font-medium">Tanggal Pembelian:</span>
                        <p className="font-bold text-slate-900 mt-0.5">
                          {submission.tanggal_realisasi ? new Date(submission.tanggal_realisasi).toLocaleDateString('id-ID', { dateStyle: 'long' }) : '-'}
                        </p>
                        {submission.bukti_pembelian && (
                          <div className="mt-2">
                            <a
                              href={getStorageUrl(submission.bukti_pembelian)}
                              target="_blank"
                              rel="noreferrer"
                              className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-bold transition shadow-xs"
                            >
                              <ExternalLink className="w-3.5 h-3.5" />
                              Buka & Periksa Foto Nota
                            </a>
                          </div>
                        )}
                      </div>
                    </div>

                    <div className="flex flex-wrap items-center justify-between gap-3 pt-2">
                      <button
                        type="button"
                        onClick={() => {
                          const revisiNote = window.prompt('Tuliskan pesan perbaikan/revisi nota untuk pemohon:');
                          if (revisiNote) {
                            setQuickMsg(`[Revisi Nota]: ${revisiNote}`);
                          }
                        }}
                        className="text-xs text-slate-600 hover:text-slate-900 font-semibold underline"
                      >
                        Minta Revisi Nota ke Pemohon
                      </button>

                      <button
                        type="button"
                        disabled={submitting}
                        onClick={() => handleDirectAction('Selesai', 'Nota pembelian telah diverifikasi sah dan pengadaan selesai.')}
                        className="py-2.5 px-5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2 disabled:opacity-50"
                      >
                        <CheckCheck className="w-4 h-4" />
                        Verifikasi Nota & Sahkan Selesai
                      </button>
                    </div>
                  </div>
                ) : (
                  /* Sub-Kondisi B: User BELUM mengunggah nota */
                  <div className="p-5 bg-amber-50/80 border border-amber-200 rounded-2xl space-y-3">
                    <div className="flex items-start gap-3">
                      <Clock className="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                      <div>
                        <h4 className="font-bold text-sm text-amber-950">
                          Menunggu Pemohon Mengunggah Nota Pembelian
                        </h4>
                        <p className="text-xs text-amber-800 mt-1 leading-relaxed">
                          Pemohon ({submission.user?.name}) saat ini sedang melakukan proses pembelian. Setelah barang dibeli, pemohon akan mengunggah foto nota langsung dari akunnya.
                        </p>
                      </div>
                    </div>

                    <div className="pt-2 border-t border-amber-200/60 flex items-center justify-between text-xs">
                      <span className="text-amber-900 font-medium">Ingin membantu input nota sekarang?</span>
                      <button
                        type="button"
                        onClick={() => setShowManualForm(!showManualForm)}
                        className="text-emerald-700 hover:text-emerald-900 font-bold underline"
                      >
                        {showManualForm ? 'Tutup Form Manual' : 'Input Nota Manual oleh Admin'}
                      </button>
                    </div>
                  </div>
                )}

                {/* Form Alternatif Manual Input oleh Admin */}
                {showManualForm && (
                  <form onSubmit={handleManualStatusSubmit} className="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                    <h5 className="font-bold text-xs text-slate-800">Form Input Nota Manual Admin</h5>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                      <div>
                        <label className="block text-xs font-bold text-slate-700 mb-1">
                          Total Biaya Nota (Rp) *
                        </label>
                        <input
                          type="number"
                          required
                          value={biayaRealisasi}
                          onChange={(e) => setBiayaRealisasi(e.target.value)}
                          placeholder="Contoh: 12000000"
                          className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
                        />
                      </div>
                      <div>
                        <label className="block text-xs font-bold text-slate-700 mb-1">
                          Tanggal Pembelian *
                        </label>
                        <input
                          type="date"
                          required
                          value={tanggalRealisasi}
                          onChange={(e) => setTanggalRealisasi(e.target.value)}
                          className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-700"
                        />
                      </div>
                    </div>
                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">
                        Upload Foto Struk / Nota Sah
                      </label>
                      <input
                        type="file"
                        accept="image/*,.pdf"
                        onChange={(e) => setReceiptFile(e.target.files[0])}
                        className="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                      />
                    </div>
                    <div className="pt-2 flex justify-end">
                      <button
                        type="submit"
                        disabled={submitting || !biayaRealisasi}
                        className="py-2 px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-md transition"
                      >
                        Simpan & Sahkan Selesai
                      </button>
                    </div>
                  </form>
                )}
              </div>
            )}

            {/* KONDISI 3: STATUS 'SELESAI' (PENGADAAN SAH & DOKUMEN TERCATAT) */}
            {submission.status === 'Selesai' && (
              <div className="p-5 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs space-y-3">
                <div className="flex items-center gap-2 text-emerald-950 font-bold text-sm">
                  <CheckCheck className="w-5 h-5 text-emerald-700" />
                  Pengadaan Telah Selesai & Terverifikasi Sah
                </div>
                <p className="text-slate-600">
                  Seluruh proses pengadaan telah diselesaikan. Realisasi biaya tercatat sebesar{' '}
                  <strong className="text-emerald-900 font-bold">{formatRupiah(submission.biaya_realisasi)}</strong>.
                </p>
                {submission.bukti_pembelian && (
                  <div>
                    <a
                      href={getStorageUrl(submission.bukti_pembelian)}
                      target="_blank"
                      rel="noreferrer"
                      className="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 hover:text-emerald-950 underline"
                    >
                      <ExternalLink className="w-3.5 h-3.5" />
                      Lihat Bukti Nota Sah
                    </a>
                  </div>
                )}
                <div className="pt-2 border-t border-emerald-200/60">
                  <button
                    type="button"
                    onClick={() => handleDirectAction('Menunggu', 'Status dibuka kembali untuk verifikasi ulang.')}
                    className="text-slate-500 hover:text-slate-800 font-semibold underline text-[11px]"
                  >
                    Buka Kembali Pengajuan (Revisi)
                  </button>
                </div>
              </div>
            )}

            {/* KONDISI 4: STATUS 'DITOLAK' */}
            {submission.status === 'Ditolak' && (
              <div className="p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs space-y-2">
                <div className="flex items-center gap-2 text-rose-900 font-bold text-sm">
                  <XCircle className="w-5 h-5 text-rose-600" />
                  Pengajuan Telah Ditolak
                </div>
                <p className="text-slate-600">
                  Pengajuan ini tidak disetujui untuk diproses lebih lanjut.
                </p>
                <div className="pt-2">
                  <button
                    type="button"
                    onClick={() => handleDirectAction('Menunggu', 'Pengajuan dibuka kembali untuk ditinjau ulang.')}
                    className="text-rose-700 hover:text-rose-900 font-bold underline text-[11px]"
                  >
                    Buka Kembali Pengajuan (Tinjau Ulang)
                  </button>
                </div>
              </div>
            )}

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

        {/* Kolom Kanan: Timeline Alur, Rincian Barang & Audit Log */}
        <div className="space-y-6">
          <SubmissionTimeline submission={submission} />

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

export default AdminSubmissionReview;
