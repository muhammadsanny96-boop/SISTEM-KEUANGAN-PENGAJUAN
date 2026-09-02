import React from 'react';
import { NavLink } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import {
  LayoutDashboard,
  FileText,
  PlusCircle,
  CheckSquare,
  DollarSign,
  Layers,
  Building,
  Users
} from 'lucide-react';

export const Sidebar = () => {
  const { user } = useAuth();
  const isAdmin = user?.role === 'admin';

  const userLinks = [
    { to: '/user/dashboard', label: 'Dashboard Divisi', icon: LayoutDashboard },
    { to: '/user/submissions', label: 'Daftar Pengajuan', icon: FileText },
    { to: '/user/submissions/create', label: 'Buat Usulan Baru', icon: PlusCircle },
  ];

  const adminLinks = [
    { to: '/admin/dashboard', label: 'Dashboard Analitik', icon: LayoutDashboard },
    { to: '/admin/submissions', label: 'Tinjau Pengajuan', icon: CheckSquare },
    { to: '/admin/expenses', label: 'Rekapitulasi Anggaran', icon: DollarSign },
    { to: '/admin/categories', label: 'Kategori Barang', icon: Layers },
    { to: '/admin/divisions', label: 'Divisi Kerja', icon: Building },
    { to: '/admin/users', label: 'Kelola Pengguna', icon: Users },
  ];

  const links = isAdmin ? adminLinks : userLinks;

  return (
    <aside className="w-64 bg-white border-r border-slate-200 sticky top-[61px] h-[calc(100vh-61px)] overflow-y-auto shrink-0 p-4 flex flex-col justify-between hidden md:flex z-30">
      <div className="space-y-6">
        {/* Company Badge */}
        <div className="p-3.5 bg-gradient-to-br from-slate-50 to-emerald-50/50 rounded-2xl border border-slate-200/80 flex items-center gap-3">
          <img 
            src="/logo-jamkrida.png" 
            alt="Logo" 
            className="w-8 h-8 object-contain"
          />
          <div>
            <p className="text-[11px] font-bold text-slate-900 leading-tight">PT JAMKRIDA KALSEL</p>
            <p className="text-[10px] text-emerald-700 font-bold">
              {isAdmin ? 'Mode Administrator' : `Kepala Divisi ${user?.division?.nama_divisi || ''}`}
            </p>
          </div>
        </div>

        {/* Navigation Section */}
        <div>
          <span className="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
            {isAdmin ? 'Menu Manajemen' : 'Menu Kepala Divisi'}
          </span>
          <nav className="mt-2 space-y-1">
            {links.map((link) => {
              const Icon = link.icon;
              return (
                <NavLink
                  key={link.to}
                  to={link.to}
                  className={({ isActive }) =>
                    `flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 ${
                      isActive
                        ? 'bg-emerald-700 text-white shadow-md shadow-emerald-200'
                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                    }`
                  }
                >
                  <Icon className="w-4 h-4 flex-shrink-0" />
                  <span>{link.label}</span>
                </NavLink>
              );
            })}
          </nav>
        </div>
      </div>

      {/* Footer Info */}
      <div className="pt-4 border-t border-slate-100 text-center">
        <p className="text-[10px] text-slate-400 font-medium">
          © 2026 PT Jamkrida Kalsel
        </p>
      </div>
    </aside>
  );
};
