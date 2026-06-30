import React, { useState, useEffect } from 'react';
import { getAllOrders, updateOrderStatus, getQuotationComparison, selectDistributor } from '../services/apiService';

const AdminOrders: React.FC = () => {
  const [orders, setOrders] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Detail View State
  const [selectedOrder, setSelectedOrder] = useState<any | null>(null);
  const [quotationCompare, setQuotationCompare] = useState<any | null>(null);
  const [loadingCompare, setLoadingCompare] = useState(false);
  const [compareError, setCompareError] = useState<string | null>(null);

  // Status Change State
  const [statusVal, setStatusVal] = useState('');
  const [statusNotes, setStatusNotes] = useState('');
  const [updatingStatus, setUpdatingStatus] = useState(false);

  const loadOrders = async () => {
    setLoading(true);
    try {
      const res = await getAllOrders(1, 100);
      if (res.success) {
        setOrders(res.data.data);
      } else {
        setError(res.message || 'Failed to load orders');
      }
    } catch (e: any) {
      setError(e.message || 'Error loading orders');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadOrders();
  }, []);

  const handleOrderSelect = async (order: any) => {
    setSelectedOrder(order);
    setStatusVal(order.status);
    setStatusNotes(order.notes || '');
    setCompareError(null);
    setQuotationCompare(null);

    // Fetch comparison details
    setLoadingCompare(true);
    try {
      const res = await getQuotationComparison(order.orderId);
      if (res.success) {
        setQuotationCompare(res.data);
      } else {
        setCompareError(res.message || 'No bids found for this order.');
      }
    } catch (e: any) {
      setCompareError(e.message || 'Could not fetch bid comparison details');
    } finally {
      setLoadingCompare(false);
    }
  };

  const handleTriggerAutoSelection = async () => {
    if (!selectedOrder) return;
    setLoadingCompare(true);
    try {
      const res = await selectDistributor(selectedOrder.orderId);
      if (res.success) {
        alert(`Distributor auto-selected successfully: ${res.data?.name || 'Assigned'}`);
        // Reload details
        const compareRes = await getQuotationComparison(selectedOrder.orderId);
        if (compareRes.success) setQuotationCompare(compareRes.data);
        loadOrders();
      } else {
        alert(res.message || 'Auto selection failed');
      }
    } catch (err: any) {
      alert(err.message || 'Error executing auto-selection');
    } finally {
      setLoadingCompare(false);
    }
  };

  const handleStatusUpdateSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedOrder) return;
    setUpdatingStatus(true);
    try {
      const res = await updateOrderStatus(selectedOrder.orderId, statusVal, statusNotes);
      if (res.success) {
        alert('Order status updated successfully');
        loadOrders();
        // Update local state
        setSelectedOrder(prev => ({ ...prev, status: statusVal, notes: statusNotes }));
      } else {
        alert(res.message || 'Update failed');
      }
    } catch (err: any) {
      alert(err.message || 'Error updating status');
    } finally {
      setUpdatingStatus(false);
    }
  };

  return (
    <div className="space-y-10 animate-fade-in text-slate-800">
      <div>
        <h2 className="text-3xl font-bold font-display text-slate-900">Manage Orders</h2>
        <p className="text-slate-500 text-sm mt-1">Review customer orders, inspect distributor bid metrics, and process order fulfillment.</p>
      </div>

      <div className="grid lg:grid-cols-12 gap-8 items-start">
        {/* Orders Table Panel */}
        <div className="lg:col-span-7 bg-white border border-border-dark rounded-3xl overflow-hidden shadow-sm">
          {loading ? (
            <div className="p-12 text-center text-slate-400">
              <span className="material-symbols-outlined text-4xl animate-spin text-primary mb-2">sync</span>
              <p>Loading orders...</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse text-xs">
                <thead>
                  <tr className="bg-slate-50 border-b border-border-dark">
                    <th className="p-4 font-bold text-slate-700 uppercase font-display">Reference</th>
                    <th className="p-4 font-bold text-slate-700 uppercase font-display">Order Date</th>
                    <th className="p-4 font-bold text-slate-700 uppercase font-display">Total</th>
                    <th className="p-4 font-bold text-slate-700 uppercase font-display text-center">Status</th>
                    <th className="p-4 font-bold text-slate-700 uppercase font-display text-center">Distributor</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {orders.length === 0 ? (
                    <tr>
                      <td colSpan={5} className="p-12 text-center text-slate-450">No customer orders found.</td>
                    </tr>
                  ) : (
                    orders.map(o => {
                      const isSelected = selectedOrder?.orderId === o.orderId;
                      return (
                        <tr 
                          key={o.orderId} 
                          onClick={() => handleOrderSelect(o)}
                          className={`cursor-pointer transition-colors ${isSelected ? 'bg-amber-50/50 font-medium' : 'hover:bg-slate-50/40'}`}
                        >
                          <td className="p-4">
                            <span className="font-mono text-primary font-bold">{o.orderReference}</span>
                          </td>
                          <td className="p-4 text-slate-500">
                            {new Date(o.orderDate).toLocaleDateString()}
                          </td>
                          <td className="p-4 font-bold text-slate-800">
                            Rs. {o.totalAmount?.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                          </td>
                          <td className="p-4 text-center">
                            <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                              o.status === 'PENDING' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                              o.status === 'PROCESSING' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' :
                              o.status === 'SHIPPED' ? 'bg-blue-50 text-blue-700 border border-blue-200' :
                              o.status === 'DELIVERED' ? 'bg-green-50 text-green-700 border border-green-200' :
                              'bg-slate-50 text-slate-700 border border-slate-200'
                            }`}>
                              {o.status}
                            </span>
                          </td>
                          <td className="p-4 text-slate-600 text-center font-medium">
                            {o.distributorName || 'Unassigned'}
                          </td>
                        </tr>
                      );
                    })
                  )}
                </tbody>
              </table>
            </div>
          )}
        </div>

        {/* Bidding Comparison & Update Panel */}
        <div className="lg:col-span-5 space-y-6">
          {!selectedOrder ? (
            <div className="border-2 border-dashed border-border-dark rounded-3xl p-12 text-center text-slate-400">
              <span className="material-symbols-outlined text-4xl mb-3 text-slate-300">description</span>
              <p className="text-xs">Select an order from the list to view bid comparisons and update status.</p>
            </div>
          ) : (
            <div className="bg-white border border-border-dark rounded-3xl p-6 shadow-sm space-y-6 animate-fade-in text-xs">
              <div className="flex justify-between items-start border-b border-slate-100 pb-4">
                <div>
                  <h3 className="font-bold font-display text-lg text-slate-900 flex items-center gap-1.5">
                    <span className="font-mono text-primary">{selectedOrder.orderReference}</span>
                  </h3>
                  <p className="text-xs text-slate-400 mt-1">Customer Order Details</p>
                </div>
                <div className="text-right">
                  <p className="text-[10px] text-slate-400 uppercase font-bold">Total Paid</p>
                  <p className="text-base font-extrabold text-slate-800">Rs. {selectedOrder.totalAmount?.toLocaleString()}</p>
                </div>
              </div>

              {/* Status Update Form */}
              <form onSubmit={handleStatusUpdateSubmit} className="bg-slate-50 border border-border-dark/60 p-4 rounded-2xl space-y-3">
                <h4 className="font-bold text-slate-700 uppercase text-[10px]">Update Order Status</h4>
                <div className="grid grid-cols-2 gap-3 items-center">
                  <select 
                    value={statusVal} 
                    onChange={e => setStatusVal(e.target.value)}
                    className="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none"
                  >
                    <option value="PENDING">PENDING</option>
                    <option value="PROCESSING">PROCESSING</option>
                    <option value="SHIPPED">SHIPPED</option>
                    <option value="DELIVERED">DELIVERED</option>
                    <option value="CANCELLED">CANCELLED</option>
                  </select>
                  <button 
                    type="submit" 
                    disabled={updatingStatus}
                    className="bg-primary hover:bg-primary-dark text-white font-bold py-2 rounded-xl text-xs transition-all"
                  >
                    {updatingStatus ? 'Saving...' : 'Apply Status'}
                  </button>
                </div>
              </form>

              {/* Bidding/Quotation Comparison Section */}
              <div className="space-y-4">
                <div className="flex justify-between items-center">
                  <h4 className="font-bold text-slate-800 uppercase text-[10px] tracking-wider">Distributor Bidding Quotations</h4>
                  {!selectedOrder.distributorName && quotationCompare?.quotations?.length > 0 && (
                    <button 
                      onClick={handleTriggerAutoSelection} 
                      className="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1 rounded text-[10px] transition-all flex items-center gap-0.5"
                    >
                      <span className="material-symbols-outlined text-[12px]">auto_awesome</span> Run Auto-Selection
                    </button>
                  )}
                </div>

                {loadingCompare ? (
                  <p className="text-center text-slate-400 py-6 animate-pulse">Fetching bid reports...</p>
                ) : compareError ? (
                  <p className="text-slate-400 text-center py-6">{compareError}</p>
                ) : (
                  <div className="space-y-3">
                    {/* Bids List */}
                    {quotationCompare?.quotations?.map((q: any) => {
                      const isSelected = selectedOrder.selectedDistributorId === q.distributorId;
                      return (
                        <div 
                          key={q.quotationId} 
                          className={`p-3 rounded-xl border transition-all flex justify-between items-center ${
                            isSelected 
                              ? 'bg-green-50 border-green-200' 
                              : 'bg-slate-50 border-slate-200/80 hover:bg-slate-100/50'
                          }`}
                        >
                          <div>
                            <p className="font-bold text-slate-800 flex items-center gap-1">
                              {q.distributorName}
                              {isSelected && (
                                <span className="inline-flex items-center text-[8px] bg-green-200 text-green-800 px-1 rounded uppercase font-bold">Assigned</span>
                              )}
                            </p>
                            <p className="text-[10px] text-slate-400 mt-0.5">
                              Est Delivery: {new Date(q.estimatedDeliveryTime).toLocaleDateString()}
                            </p>
                          </div>
                          <div className="text-right">
                            <p className="font-bold text-slate-800 font-mono">Rs. {q.totalCost?.toLocaleString()}</p>
                            <p className="text-[9px] text-slate-400">Available: {q.availableQuantity || q.availableUnits}/{q.requestedUnits} units</p>
                          </div>
                        </div>
                      );
                    })}

                    {/* Recommendation summary box */}
                    {quotationCompare?.comparisonSummary && (
                      <div className="p-3 bg-amber-50/50 border border-amber-200/60 text-amber-800 rounded-xl">
                        <p className="font-bold text-[9px] uppercase tracking-wider text-amber-700">Cheapest/Fastest Comparison Recommendation</p>
                        <p className="mt-1 text-[11px] leading-relaxed">{quotationCompare.comparisonSummary}</p>
                      </div>
                    )}
                  </div>
                )}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default AdminOrders;
