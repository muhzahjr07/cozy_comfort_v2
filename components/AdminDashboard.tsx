import React, { useState, useEffect } from 'react';
import { getDashboardStats } from '../services/apiService';

const AdminDashboard: React.FC = () => {
  const [stats, setStats] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchStats = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await getDashboardStats();
      if (res.success) {
        setStats(res.data);
      } else {
        setError(res.message || 'Failed to fetch statistics');
      }
    } catch (e: any) {
      setError(e.message || 'Error occurred while loading data');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchStats();
  }, []);

  if (loading) {
    return (
      <div className="flex justify-center items-center py-20">
        <span className="material-symbols-outlined text-4xl text-primary animate-spin">sync</span>
        <span className="ml-2 font-display font-medium text-slate-600">Loading Dashboard Metrics...</span>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-2xl p-6 text-red-700 flex items-center gap-3">
        <span className="material-symbols-outlined">error</span>
        <div>
          <p className="font-bold">Error loading stats</p>
          <p className="text-xs">{error}</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-10 animate-fade-in text-slate-800">
      <div>
        <h2 className="text-3xl font-bold font-display text-slate-900">Admin Dashboard</h2>
        <p className="text-slate-500 text-sm mt-1">Real-time statistics of retail sales, customers, and vendor logistics.</p>
      </div>

      {/* Stats Summary Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white border border-border-dark p-6 rounded-2xl shadow-sm hover:shadow-md transition-all">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-[10px] uppercase font-bold tracking-wider text-slate-400">Total Revenue</p>
              <p className="text-3xl font-extrabold font-display text-slate-900 mt-2">Rs. {stats?.totalRevenue?.toLocaleString(undefined, { minimumFractionDigits: 2 })}</p>
            </div>
            <div className="p-3 bg-amber-50 rounded-xl text-amber-700">
              <span className="material-symbols-outlined">payments</span>
            </div>
          </div>
          <div className="text-xs text-green-600 font-bold mt-4 flex items-center gap-1">
            <span className="material-symbols-outlined text-sm">trending_up</span>
            All-time earnings
          </div>
        </div>

        <div className="bg-white border border-border-dark p-6 rounded-2xl shadow-sm hover:shadow-md transition-all">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-[10px] uppercase font-bold tracking-wider text-slate-400">Monthly Revenue</p>
              <p className="text-3xl font-extrabold font-display text-slate-900 mt-2">Rs. {stats?.monthlyRevenue?.toLocaleString(undefined, { minimumFractionDigits: 2 })}</p>
            </div>
            <div className="p-3 bg-emerald-50 rounded-xl text-emerald-700">
              <span className="material-symbols-outlined">calendar_month</span>
            </div>
          </div>
          <div className="text-xs text-emerald-600 font-bold mt-4 flex items-center gap-1">
            <span className="material-symbols-outlined text-sm">info</span>
            Current billing cycle
          </div>
        </div>

        <div className="bg-white border border-border-dark p-6 rounded-2xl shadow-sm hover:shadow-md transition-all">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-[10px] uppercase font-bold tracking-wider text-slate-400">Total Orders</p>
              <p className="text-3xl font-extrabold font-display text-slate-900 mt-2">{stats?.totalOrders}</p>
            </div>
            <div className="p-3 bg-indigo-50 rounded-xl text-indigo-700">
              <span className="material-symbols-outlined">receipt_long</span>
            </div>
          </div>
          <div className="text-xs text-slate-500 font-medium mt-4">
            Pending: <strong className="text-amber-600 font-bold">{stats?.pendingOrders}</strong> | Completed: <strong className="text-green-600 font-bold">{stats?.completedOrders}</strong>
          </div>
        </div>

        <div className="bg-white border border-border-dark p-6 rounded-2xl shadow-sm hover:shadow-md transition-all">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-[10px] uppercase font-bold tracking-wider text-slate-400">Active Customers</p>
              <p className="text-3xl font-extrabold font-display text-slate-900 mt-2">{stats?.totalCustomers}</p>
            </div>
            <div className="p-3 bg-rose-50 rounded-xl text-rose-700">
              <span className="material-symbols-outlined">group</span>
            </div>
          </div>
          <div className="text-xs text-slate-500 font-medium mt-4">
            Registered accounts
          </div>
        </div>
      </div>

      <div className="grid lg:grid-cols-12 gap-8">
        {/* Distributor Logistics performance list */}
        <div className="lg:col-span-8 bg-white border border-border-dark rounded-3xl p-6 shadow-sm space-y-6">
          <div>
            <h3 className="font-display font-bold text-lg text-slate-900 flex items-center gap-2">
              <span className="material-symbols-outlined text-primary">local_shipping</span>
              Distributor Bidding & Bidding Success Rates
            </h3>
            <p className="text-xs text-slate-400 mt-1">Average response speeds and order fulfillment performance metrics.</p>
          </div>

          <div className="space-y-4">
            {(!stats?.distributorPerformance || stats.distributorPerformance.length === 0) ? (
              <p className="text-sm text-slate-400 text-center py-6">No distributor transactions recorded yet.</p>
            ) : (
              stats.distributorPerformance.map((dist: any, idx: number) => {
                const percentage = Math.round(dist.successRate * 100);
                return (
                  <div key={idx} className="p-4 bg-slate-50 border border-border-dark/60 rounded-xl grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                    <div className="space-y-1 md:col-span-5">
                      <p className="font-bold text-slate-800 text-sm font-display">{dist.distributorName}</p>
                      <p className="text-xs text-slate-400">Total Bids/Orders: <strong>{dist.orderCount}</strong></p>
                    </div>
                    <div className="w-full md:col-span-4 space-y-1.5">
                      <div className="flex justify-between text-xs font-bold">
                        <span className="text-slate-500">Success Rate</span>
                        <span className="text-slate-800">{percentage}%</span>
                      </div>
                      <div className="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                        <div className="bg-primary h-full rounded-full" style={{ width: `${percentage}%` }}></div>
                      </div>
                    </div>
                    <div className="md:col-span-3 text-left md:text-right">
                      <p className="text-[10px] text-slate-400 uppercase font-bold">Avg Bid Time</p>
                      <p className="text-sm font-bold font-mono text-slate-700">{dist.averageResponseTime}ms</p>
                    </div>
                  </div>
                );
              })
            )}
          </div>
        </div>

        {/* Orders by Status chart mockup/progress bars */}
        <div className="lg:col-span-4 bg-white border border-border-dark rounded-3xl p-6 shadow-sm space-y-6">
          <div>
            <h3 className="font-display font-bold text-lg text-slate-900 flex items-center gap-2">
              <span className="material-symbols-outlined text-primary">analytics</span>
              Orders Status
            </h3>
            <p className="text-xs text-slate-400 mt-1">Breakdown of orders in progress.</p>
          </div>

          <div className="space-y-4">
            {(!stats?.ordersByStatus || stats.ordersByStatus.length === 0) ? (
              <p className="text-sm text-slate-400 text-center py-6">No order logs found.</p>
            ) : (
              stats.ordersByStatus.map((status: any, idx: number) => {
                const total = stats.totalOrders || 1;
                const ratio = Math.round((status.count / total) * 100);
                return (
                  <div key={idx} className="space-y-2">
                    <div className="flex justify-between text-xs font-bold text-slate-700">
                      <span>{status.status}</span>
                      <span>{status.count} ({ratio}%)</span>
                    </div>
                    <div className="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                      <div 
                        className={`h-full rounded-full ${
                          status.status === 'PENDING' ? 'bg-amber-400' :
                          status.status === 'PROCESSING' ? 'bg-indigo-400' :
                          status.status === 'SHIPPED' ? 'bg-blue-400' :
                          status.status === 'DELIVERED' ? 'bg-green-500' : 'bg-slate-400'
                        }`} 
                        style={{ width: `${ratio}%` }}
                      ></div>
                    </div>
                  </div>
                );
              })
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default AdminDashboard;
