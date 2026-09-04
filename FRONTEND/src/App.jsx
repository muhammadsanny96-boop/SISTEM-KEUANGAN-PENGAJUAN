import React from 'react';
import { Toaster } from 'react-hot-toast';
import { Routes, Route, Navigate } from 'react-router-dom';
import { useAuth } from './context/AuthContext';
import { ProtectedRoute } from './components/ProtectedRoute';
import { Navbar } from './components/Navbar';
import { Sidebar } from './components/Sidebar';

// Auth Pages
import { Login } from './pages/auth/Login';
import { Register } from './pages/auth/Register';

// User Pages
import { UserDashboard } from './pages/user/UserDashboard';
import { UserSubmissions } from './pages/user/UserSubmissions';
import { CreateSubmission } from './pages/user/CreateSubmission';
import { EditSubmission } from './pages/user/EditSubmission';
import { SubmissionDetail } from './pages/user/SubmissionDetail';

// Admin Pages
import { AdminDashboard } from './pages/admin/AdminDashboard';
import { AdminSubmissions } from './pages/admin/AdminSubmissions';
import { AdminSubmissionReview } from './pages/admin/AdminSubmissionReview';
import { AdminExpenses } from './pages/admin/AdminExpenses';
import { AdminCategories } from './pages/admin/AdminCategories';
import { AdminDivisions } from './pages/admin/AdminDivisions';
import { AdminUsers } from './pages/admin/AdminUsers';

const Layout = ({ children }) => {
  return (
    <div className="min-h-screen bg-slate-50 flex flex-col">
      <Navbar />
      <div className="flex flex-1 items-start w-full">
        <Sidebar />
        <main className="flex-1 min-w-0 pb-16">
          {children}
        </main>
      </div>
    </div>
  );
};

export const App = () => {
  const { user } = useAuth();

  return (
    <>
    <Toaster
        position="top-right"
        toastOptions={{
          duration: 3000,
          success: {
            iconTheme: { primary: '#10b981', secondary: 'white' },
            style: { borderRadius: '1rem', border: '1px solid #10b981', background: '#fff' },
          },
          error: {
            iconTheme: { primary: '#ef4444', secondary: 'white' },
            style: { borderRadius: '1rem', border: '1px solid #ef4444', background: '#fff' },
          },
          loading: { style: { borderRadius: '1rem' } },
        }}
      />
    <Routes>
      {/* Public Landing & Auth */}
      <Route
        path="/"
        element={
          user ? (
            <Navigate to={user.role === 'admin' ? '/admin/dashboard' : '/user/dashboard'} replace />
          ) : (
            <Navigate to="/login" replace />
          )
        }
      />
      <Route path="/login" element={<Login />} />
      <Route path="/register" element={<Register />} />

      {/* User Routes */}
      <Route
        path="/user/*"
        element={
          <ProtectedRoute requiredRole="user">
            <Layout>
              <Routes>
                <Route path="dashboard" element={<UserDashboard />} />
                <Route path="submissions" element={<UserSubmissions />} />
                <Route path="submissions/create" element={<CreateSubmission />} />
                <Route path="submissions/:id" element={<SubmissionDetail />} />
                <Route path="submissions/:id/edit" element={<EditSubmission />} />
                <Route path="*" element={<Navigate to="/user/dashboard" replace />} />
              </Routes>
            </Layout>
          </ProtectedRoute>
        }
      />

      {/* Admin Routes */}
      <Route
        path="/admin/*"
        element={
          <ProtectedRoute requiredRole="admin">
            <Layout>
              <Routes>
                <Route path="dashboard" element={<AdminDashboard />} />
                <Route path="submissions" element={<AdminSubmissions />} />
                <Route path="submissions/:id" element={<AdminSubmissionReview />} />
                <Route path="expenses" element={<AdminExpenses />} />
                <Route path="categories" element={<AdminCategories />} />
                <Route path="divisions" element={<AdminDivisions />} />
                <Route path="users" element={<AdminUsers />} />
                <Route path="*" element={<Navigate to="/admin/dashboard" replace />} />
              </Routes>
            </Layout>
          </ProtectedRoute>
        }
      />

           {/* 404 Fallback */}
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  </>
  );
};

export default App;
   
