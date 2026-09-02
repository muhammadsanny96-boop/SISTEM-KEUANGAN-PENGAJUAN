import React from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';
import { LogOut, User, Bell } from 'lucide-react';

export const Navbar = () => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  const getJabatan = () => {
    if (!user) return '';
    if (user.role === 'admin') return 'Administrator Sistem';
    return user.division?.nama_divisi 
      ? `Kepala Divisi ${user.division.nama_divisi}` 
      : 'Kepala Divisi';
  };

  return (
    <header className="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/90 shadow-2xs">
      <div className="px-4 sm:px-6 h-[61px] flex items-center justify-between">
        {/* Brand & Logo */}
        <div className="flex items-center gap-3">
          <img
            src="/logo-jamkrida.png"
            alt="Logo PT Jamkrida Kalsel"
            className="w-8 h-8 object-contain"
          />
          <div>
            <h1 className="font-extrabold text-slate-900 text-sm tracking-tight leading-none flex items-center gap-1.5">
              <span>PT JAMKRIDA KALSEL</span>
              <span className="text-[10px] px-1.5 py-0.2 bg-emerald-100 text-emerald-800 rounded font-semibold">
                SIPENG
              </span>
            </h1>
            <p className="text-[11px] text-slate-500 font-medium">
              Sistem Informasi Pengajuan Pengadaan Barang & Anggaran
            </p>
          </div>
        </div>

        {/* User Info & Actions */}
        <div className="flex items-center gap-3">
          {user && (
            <div className="flex items-center gap-3 pl-3 border-l border-slate-200">
              <div className="text-right hidden sm:block">
                <p className="text-xs font-bold text-slate-800 leading-tight">{user.name}</p>
                <p className="text-[10px] text-emerald-700 font-bold uppercase tracking-wider">
                  {getJabatan()}
                </p>
              </div>

              <div className="w-8 h-8 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center font-bold text-xs">
                {user.name ? user.name.charAt(0).toUpperCase() : 'U'}
              </div>

              <button
                onClick={handleLogout}
                title="Keluar / Logout"
                className="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition ml-1"
              >
                <LogOut className="w-4 h-4" />
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  );
};
