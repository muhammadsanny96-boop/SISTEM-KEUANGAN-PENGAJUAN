import React, { useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { useNavigate, Link } from 'react-router-dom';
import { LogIn, Lock, Mail, AlertCircle } from 'lucide-react';

export const Login = () => {
  const { login } = useAuth();
  const navigate = useNavigate();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const user = await login(email, password);
      if (user.role === 'admin') {
        navigate('/admin/dashboard');
      } else {
        navigate('/user/dashboard');
      }
    } catch (err) {
      if (err.response?.data?.errors?.email) {
        setError(err.response.data.errors.email[0]);
      } else if (err.response?.data?.message) {
        setError(err.response.data.message);
      } else {
        setError('Gagal masuk. Periksa kembali email atau kata sandi Anda.');
      }
    } finally {
      setLoading(false);
    }
  };

  const setDemoUser = (demoEmail, demoPass) => {
    setEmail(demoEmail);
    setPassword(demoPass);
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-100 via-slate-50 to-emerald-50/50 p-4">
      <div className="max-w-md w-full">
        {/* Brand Header with PT Jamkrida Logo */}
        <div className="text-center mb-6">
          <div className="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white p-2.5 shadow-xl shadow-slate-200/80 border border-slate-200/80 mb-3.5 hover:scale-105 transition-transform">
            <img 
              src="/logo-jamkrida.png" 
              alt="Logo PT Jamkrida Kalsel" 
              className="w-full h-full object-contain"
            />
          </div>
          <h1 className="text-2xl font-black text-slate-900 tracking-tight">PT JAMKRIDA KALSEL</h1>
          <p className="text-xs font-semibold text-emerald-800 uppercase tracking-widest mt-1">Sistem Pengajuan & Anggaran</p>
          <p className="text-xs text-slate-500 mt-1">Silakan masuk menggunakan akun terdaftar</p>
        </div>

        {/* Card */}
        <div className="bg-white border border-slate-200/80 rounded-2xl p-7 shadow-xl shadow-slate-200/50 backdrop-blur-sm">
          {error && (
            <div className="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-3 text-sm text-rose-700">
              <AlertCircle className="w-5 h-5 flex-shrink-0 mt-0.5" />
              <span>{error}</span>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Alamat Email
              </label>
              <div className="relative">
                <Mail className="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="nama@jamkridakalsel.co.id"
                  className="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition"
                />
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Kata Sandi
              </label>
              <div className="relative">
                <Lock className="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input
                  type="password"
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••"
                  className="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition"
                />
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full py-3 px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-sm shadow-lg shadow-emerald-200/80 flex items-center justify-center gap-2 transition-all disabled:opacity-50 mt-2"
            >
              {loading ? (
                <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              ) : (
                <>
                  <LogIn className="w-4 h-4" />
                  Masuk ke Akun
                </>
              )}
            </button>
          </form>

          {/* Quick Demo Login */}
          <div className="mt-6 pt-5 border-t border-slate-100">
            <p className="text-xs text-slate-400 font-medium mb-2.5 text-center">Coba Akun Demo Cepat (Database):</p>
            <div className="grid grid-cols-2 gap-2">
              <button
                type="button"
                onClick={() => setDemoUser('admin@example.com', 'password')}
                className="py-1.5 px-3 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-semibold rounded-lg border border-purple-200 transition"
              >
                Login Administrator
              </button>
              <button
                type="button"
                onClick={() => setDemoUser('kadiv.it@example.com', 'password')}
                className="py-1.5 px-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg border border-emerald-200 transition"
              >
                Login Kadiv IT (User)
              </button>
            </div>
          </div>
        </div>

        <div className="text-center mt-5">
          <p className="text-sm text-slate-500">
            Belum punya akun?{' '}
            <Link to="/register" className="font-semibold text-emerald-700 hover:text-emerald-800">
              Daftar Pegawai Baru
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
};
