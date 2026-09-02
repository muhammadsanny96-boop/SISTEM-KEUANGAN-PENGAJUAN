import React from 'react';
import {
  FilePlus,
  CheckCircle2,
  XCircle,
  PackageCheck,
  Clock,
} from 'lucide-react';

export const SubmissionTimeline = ({ submission }) => {
  if (!submission) return null;

  const currentStatus = submission.status || 'Menunggu';
  const isRejected = currentStatus === 'Ditolak';

  const formatDate = (dateStr) => {
    if (!dateStr) return null;
    try {
      return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    } catch {
      return dateStr;
    }
  };

  // Cari balasan terakhir dari admin untuk tanggal disposisi
  const lastAdminReply = submission.replies?.slice().reverse().find(r => r.admin?.role !== 'user');

  // 3 TAHAPAN UTAMA ALUR PENGAJUAN
  const steps = [
    {
      id: 'step-1',
      title: '1. Pengajuan Diajukan',
      description: `Diajukan oleh ${submission.user?.name || 'Pemohon'} (${submission.division?.nama_divisi || 'Divisi'})`,
      date: formatDate(submission.created_at),
      isCompleted: true,
      isActive: currentStatus === 'Menunggu',
      icon: FilePlus,
    },
    {
      id: 'step-2',
      title: isRejected ? '2. Pengajuan Ditolak' : '2. Persetujuan Anggaran',
      description: isRejected 
        ? (lastAdminReply?.pesan ? `Alasan: "${lastAdminReply.pesan}"` : 'Usulan anggaran tidak disetujui')
        : (['Disetujui', 'Selesai'].includes(currentStatus)
            ? 'Anggaran disetujui oleh Administrator'
            : currentStatus === 'Diproses'
              ? 'Sedang ditinjau & diverifikasi kelayakannya'
              : 'Menunggu keputusan persetujuan Admin'),
      date: (['Disetujui', 'Selesai', 'Ditolak'].includes(currentStatus)) 
        ? (formatDate(lastAdminReply?.created_at) || formatDate(submission.updated_at)) 
        : null,
      isCompleted: ['Disetujui', 'Selesai', 'Ditolak'].includes(currentStatus),
      isActive: ['Menunggu', 'Diproses', 'Disetujui'].includes(currentStatus) && currentStatus !== 'Selesai',
      isFailed: isRejected,
      icon: isRejected ? XCircle : CheckCircle2,
    },
    {
      id: 'step-3',
      title: '3. Pembelian & Selesai',
      description: currentStatus === 'Selesai' 
        ? `Barang telah dibeli & nota sah tercatat (${submission.biaya_realisasi ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(submission.biaya_realisasi) : 'Selesai'})` 
        : 'Menunggu proses pembelian barang & unggah nota',
      date: formatDate(submission.tanggal_realisasi),
      isCompleted: currentStatus === 'Selesai',
      isActive: currentStatus === 'Selesai',
      icon: PackageCheck,
    },
  ];

  return (
    <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
      <div className="flex items-center justify-between border-b border-slate-100 pb-3 mb-5">
        <h4 className="font-bold text-sm text-slate-900 flex items-center gap-2">
          <Clock className="w-4 h-4 text-emerald-700" />
          Pelacakan Alur Status
        </h4>
        <span className="text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
          {currentStatus}
        </span>
      </div>

      <div className="relative pl-6 space-y-6 before:absolute before:left-3 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
        {steps.map((step) => {
          const Icon = step.icon;
          
          let bulletColor = 'bg-slate-100 border-slate-300 text-slate-400';
          if (step.isFailed) {
            bulletColor = 'bg-rose-500 border-rose-600 text-white shadow-md shadow-rose-500/20';
          } else if (step.isCompleted) {
            bulletColor = 'bg-emerald-700 border-emerald-800 text-white shadow-md shadow-emerald-700/20';
          } else if (step.isActive && step.id === 'step-2' && currentStatus === 'Diproses') {
            bulletColor = 'bg-blue-600 border-blue-700 text-white shadow-md shadow-blue-600/20 ring-4 ring-blue-100';
          }

          return (
            <div key={step.id} className="relative group">
              {/* Bullet Icon */}
              <div
                className={`absolute -left-[30px] top-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all ${bulletColor}`}
              >
                <Icon className="w-3 h-3" />
              </div>

              {/* Konten Tahapan */}
              <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-1">
                <div>
                  <h5 className={`text-xs font-bold ${step.isFailed ? 'text-rose-600' : step.isCompleted || step.isActive ? 'text-slate-900' : 'text-slate-400'}`}>
                    {step.title}
                  </h5>
                  <p className="text-[11px] text-slate-500 mt-0.5 max-w-sm leading-relaxed">
                    {step.description}
                  </p>
                </div>
                {step.date && (
                  <span className="text-[10px] font-medium text-slate-400 whitespace-nowrap sm:text-right">
                    {step.date}
                  </span>
                )}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default SubmissionTimeline;
