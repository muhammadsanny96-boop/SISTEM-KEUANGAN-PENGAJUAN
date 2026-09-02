import React, { useState, useEffect, useRef } from 'react';
import api from '../../services/api';
import { useParams, Link } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { StatusBadge } from '../../components/StatusBadge';
import { 
  ArrowLeft, 
  MessageSquare, 
  History, 
  CheckCheck, 
  FileText,
  Shield,
  User as UserIcon,
  Send,
  Loader2
} from 'lucide-react';

export const SubmissionDetail = () => {
  const { id } = useParams();
  const { user } = useAuth();

  const [submission, setSubmission] = useState(null);
  const [loading, setLoading] = useState(true);

  // Chat message state
  const [pesanInput, setPesanInput] = useState('');
  const [sendingMsg, setSendingMsg] = useState(false);
  const [msgError, setMsgError] = useState('');
  const chatBottomRef = useRef(null);

  const fetchDetail = async () => {
    try {
      const res = await api.get(`/user/submissions/${id}`);
      setSubmission(res.data.data);
    } catch (err) {
      console.error('Error fetching detail:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchDetail();
  }, [id]);

  useEffect(() => {
    // Scroll chat to bottom when replies change
    if (chatBottomRef.current) {
      chatBottomRef.current.scrollIntoView({ behavior: 'smooth' });
    }
  }, [submission?.replies]);

  const handleSendComment = async (e) => {
    e.preventDefault();
    if (!pesanInput.trim()) return;

    setSendingMsg(true);
    setMsgError('');

    try {
      const res = await api.post(`/user/submissions/${id}/reply`, {
        pesan: pesanInput.trim()
      });

      const newReply = res.data.data;
      setSubmission(prev => ({
        ...prev,
        replies: [...(prev.replies || []), newReply]
      }));
      setPesanInput('');
    } catch (err) {
      setMsgError(err.response?.data?.message || 'Gagal mengirim pesan.');
    } finally {
      setSendingMsg(false);
    }
  };

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

  if (loading) {
    return (
      <div className="p-8 text-center text-slate-500 font-medium">Memuat detail pengajuan...</div>
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
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <Link
            to="/user/submissions"
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
              Diajukan oleh: <span className="font-semibold text-slate-700">{submission.user?.name}</span> ({submission.division?.nama_divisi})
            </p>
          </div>
        </div>

        {submission.status === 'Menunggu' && (
          <Link
            to={`/user/submissions/${submission.id}/edit`}
            className="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-md transition self-start sm:self-auto"
          >
            Edit Pengajuan
          </Link>
        )}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Kolom Kiri: Rincian & Two-Way WhatsApp Chat Box */}
        <div className="lg:col-span-2 space-y-6">
          {/* Card Rincian Barang */}
          <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h3 className="font-bold text-base text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
              <FileText className="w-4 h-4 text-emerald-700" />
              Rincian Barang & Anggaran
            </h3>

            <div className="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
              <div>
                <span className="text-xs text-slate-400 font-medium">Nama Barang</span>
                <p className="font-bold text-slate-900 mt-0.5">{submission.nama_barang}</p>
              </div>
              <div>
                <span className="text-xs text-slate-400 font-medium">Kategori</span>
                <p className="font-semibold text-slate-800 mt-0.5">{submission.category?.nama_kategori || '-'}</p>
              </div>
              <div>
                <span className="text-xs text-slate-400 font-medium">Prioritas</span>
                <p className="font-semibold text-slate-800 mt-0.5">{submission.prioritas || 'Sedang'}</p>
              </div>
              <div>
                <span className="text-xs text-slate-400 font-medium">Jumlah & Satuan</span>
                <p className="font-semibold text-slate-800 mt-0.5">{submission.jumlah} {submission.satuan}</p>
              </div>
              <div>
                <span className="text-xs text-slate-400 font-medium">Harga Satuan Usulan</span>
                <p className="font-semibold text-slate-800 mt-0.5">{formatRupiah(submission.harga_satuan)}</p>
              </div>
              <div>
                <span className="text-xs text-slate-400 font-medium">Total Estimasi Usulan</span>
                <p className="font-black text-emerald-700 mt-0.5">{formatRupiah(submission.total_biaya)}</p>
              </div>
            </div>

            {submission.biaya_realisasi !== null && (
              <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-xl mt-4">
                <div className="flex justify-between items-center text-sm">
                  <div>
                    <span className="text-xs text-emerald-800 font-bold uppercase tracking-wider">Realisasi Biaya Pembelian</span>
                    <p className="text-lg font-black text-emerald-950">{formatRupiah(submission.biaya_realisasi)}</p>
                  </div>
                  <div className="text-right">
                    <span className="text-xs text-slate-500 font-medium">Selisih Penghematan</span>
                    <p className={`font-bold text-sm ${submission.total_biaya >= submission.biaya_realisasi ? 'text-emerald-700' : 'text-rose-600'}`}>
                      {formatRupiah(submission.total_biaya - submission.biaya_realisasi)}
                    </p>
                  </div>
                </div>
              </div>
            )}

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

          {/* TWO-WAY WHATSAPP STYLE CHAT BOX */}
          <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm flex flex-col">
            {/* Header Chat */}
            <div className="bg-emerald-800 text-white px-5 py-3.5 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                  <MessageSquare className="w-4 h-4 text-emerald-100" />
                </div>
                <div>
                  <h3 className="font-bold text-sm leading-tight">Diskusi & Catatan Verifikasi</h3>
                  <p className="text-[11px] text-emerald-200">Kirim pesan langsung ke Administrator</p>
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
                  <p className="text-xs font-semibold text-slate-700">Belum Ada Pesan</p>
                  <p className="text-[11px] text-slate-500 mt-0.5">Tulis pesan atau pertanyaan Anda kepada Administrator di kotak bawah.</p>
                </div>
              ) : (
                submission.replies.map((reply) => {
                  const isMe = reply.admin_id === user?.id || reply.admin?.role === 'user';
                  return (
                    <div key={reply.id} className={`flex ${isMe ? 'justify-end' : 'justify-start'}`}>
                      {isMe ? (
                        /* Bubble Hijau (Pesan dari User/Anda) */
                        <div className="max-w-[85%] sm:max-w-[70%] bg-[#d9fdd3] rounded-xl rounded-tr-xs p-3 shadow-xs border border-emerald-200 space-y-1">
                          <div className="flex items-center justify-between gap-2 border-b border-emerald-200/60 pb-1">
                            <span className="text-xs font-bold text-emerald-950 flex items-center gap-1">
                              <UserIcon className="w-3 h-3 text-emerald-700" />
                              Anda ({reply.admin?.name || user?.name})
                            </span>
                            <span className="text-[10px] text-emerald-800 font-semibold">Pemohon</span>
                          </div>

                          <p className="text-xs text-slate-900 leading-relaxed whitespace-pre-wrap py-0.5">
                            {reply.pesan}
                          </p>

                          <div className="flex items-center justify-end gap-1 text-[10px] text-emerald-800/80 pt-0.5">
                            <span>{formatChatTime(reply.created_at)}</span>
                            <CheckCheck className="w-3.5 h-3.5 text-emerald-600" />
                          </div>
                        </div>
                      ) : (
                        /* Bubble Putih (Pesan Masuk dari Admin) */
                        <div className="max-w-[85%] sm:max-w-[70%] bg-white rounded-xl rounded-tl-xs p-3 shadow-xs border border-slate-200 space-y-1">
                          <div className="flex items-center justify-between gap-2 border-b border-slate-100 pb-1">
                            <span className="text-xs font-bold text-emerald-800 flex items-center gap-1">
                              <Shield className="w-3 h-3 text-emerald-600" />
                              {reply.admin?.name || 'Administrator'}
                            </span>
                            <span className="text-[10px] px-1.5 py-0.2 bg-emerald-50 text-emerald-700 font-bold rounded">
                              Admin
                            </span>
                          </div>

                          <p className="text-xs text-slate-800 leading-relaxed whitespace-pre-wrap py-0.5">
                            {reply.pesan}
                          </p>

                          {reply.status_setelah_balasan && (
                            <div className="pt-0.5">
                              <span className="inline-block text-[10px] px-2 py-0.5 bg-slate-100 text-slate-700 font-semibold rounded border border-slate-200">
                                Status: <span className="font-bold text-slate-900">{reply.status_setelah_balasan}</span>
                              </span>
                            </div>
                          )}

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

            {/* Input Bar WhatsApp Style */}
            <form onSubmit={handleSendComment} className="bg-slate-100 border-t border-slate-200 p-3 flex items-center gap-2">
              <input
                type="text"
                value={pesanInput}
                onChange={(e) => setPesanInput(e.target.value)}
                placeholder="Tulis pesan atau pertanyaan ke Administrator..."
                className="flex-1 px-4 py-2.5 bg-white border border-slate-300 rounded-full text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-700 shadow-2xs"
              />
              <button
                type="submit"
                disabled={sendingMsg || !pesanInput.trim()}
                className="p-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-full transition shadow-md disabled:opacity-40 flex items-center justify-center"
                title="Kirim Pesan"
              >
                {sendingMsg ? (
                  <Loader2 className="w-4 h-4 animate-spin" />
                ) : (
                  <Send className="w-4 h-4" />
                )}
              </button>
            </form>
            {msgError && (
              <p className="px-4 py-1 text-[11px] text-rose-600 bg-rose-50 border-t border-rose-100">
                {msgError}
              </p>
            )}
          </div>
        </div>

        {/* Kolom Kanan: Informasi Periode & Audit Log */}
        <div className="space-y-6">
          <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4 text-xs">
            <h4 className="font-bold text-sm text-slate-900 border-b border-slate-100 pb-2">
              Informasi Periode Anggaran
            </h4>
            <div>
              <span className="text-slate-400">Target Bulan:</span>
              <p className="font-bold text-slate-800 mt-0.5">{submission.target_bulan || '-'}</p>
            </div>
            <div>
              <span className="text-slate-400">Tanggal Pengajuan:</span>
              <p className="font-bold text-slate-800 mt-0.5">
                {new Date(submission.created_at).toLocaleDateString('id-ID', { dateStyle: 'long' })}
              </p>
            </div>
          </div>

          <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h4 className="font-bold text-sm text-slate-900 mb-3 flex items-center gap-1.5">
              <History className="w-4 h-4 text-slate-500" />
              Riwayat Audit Finansial
            </h4>
            <div className="space-y-3">
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
