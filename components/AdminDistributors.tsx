import React, { useState, useEffect } from 'react';
import { getDistributors, createDistributor, updateDistributor, deleteDistributor, testDistributorAvailability } from '../services/apiService';

const AdminDistributors: React.FC = () => {
  const [distributors, setDistributors] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Form State
  const [isEditing, setIsEditing] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [name, setName] = useState('');
  const [contactEmail, setContactEmail] = useState('');
  const [contactPhone, setContactPhone] = useState('');
  const [apiEndpoint, setApiEndpoint] = useState('');
  const [apiKey, setApiKey] = useState('');

  // Ping States
  const [pingingMap, setPingingMap] = useState<Record<number, boolean>>({});
  const [pingResults, setPingResults] = useState<Record<number, { success: boolean; message: string }>>({});

  const loadDistributors = async () => {
    setLoading(true);
    try {
      const res = await getDistributors();
      if (res.success) {
        setDistributors(res.data);
      } else {
        setError(res.message || 'Failed to load distributors');
      }
    } catch (e: any) {
      setError(e.message || 'Error loading distributors');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadDistributors();
  }, []);

  const resetForm = () => {
    setIsEditing(false);
    setEditingId(null);
    setName('');
    setContactEmail('');
    setContactPhone('');
    setApiEndpoint('');
    setApiKey('');
  };

  const handleEditClick = (dist: any) => {
    setIsEditing(true);
    setEditingId(dist.distributorId);
    setName(dist.name);
    setContactEmail(dist.contactEmail || '');
    setContactPhone(dist.contactPhone || '');
    setApiEndpoint(dist.apiEndpoint || '');
    setApiKey(dist.apiKey || '');
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name || !apiEndpoint) {
      alert('Name and API Endpoint are required');
      return;
    }

    const payload = {
      name,
      contactEmail,
      contactPhone,
      apiEndpoint,
      apiKey
    };

    try {
      let res;
      if (isEditing && editingId !== null) {
        res = await updateDistributor(editingId, payload);
      } else {
        res = await createDistributor(payload);
      }

      if (res.success) {
        alert(isEditing ? 'Distributor updated successfully' : 'Distributor created successfully');
        resetForm();
        loadDistributors();
      } else {
        alert(res.message || 'Action failed');
      }
    } catch (err: any) {
      alert(err.message || 'Error occurred during save');
    }
  };

  const handleDeleteClick = async (id: number) => {
    if (!confirm('Are you sure you want to delete this distributor?')) return;

    try {
      const res = await deleteDistributor(id);
      if (res.success) {
        alert('Distributor deleted successfully');
        loadDistributors();
      } else {
        alert(res.message || 'Delete failed');
      }
    } catch (err: any) {
      alert(err.message || 'Error deleting distributor');
    }
  };

  const handlePingTest = async (id: number) => {
    setPingingMap(prev => ({ ...prev, [id]: true }));
    try {
      const res = await testDistributorAvailability(id);
      const isOnline = res.success && res.data === true;
      setPingResults(prev => ({
        ...prev,
        [id]: {
          success: isOnline,
          message: isOnline ? 'Endpoint Online' : 'Endpoint Offline'
        }
      }));
    } catch (err: any) {
      setPingResults(prev => ({
        ...prev,
        [id]: { success: false, message: err.message || 'Connection failed' }
      }));
    } finally {
      setPingingMap(prev => ({ ...prev, [id]: false }));
    }
  };

  return (
    <div className="space-y-10 animate-fade-in text-slate-800">
      <div>
        <h2 className="text-3xl font-bold font-display text-slate-900">Manage Distributors</h2>
        <p className="text-slate-500 text-sm mt-1">Configure external distributor logistics services, REST endpoint bindings, and test connectivity.</p>
      </div>

      <div className="grid lg:grid-cols-12 gap-8 items-start">
        {/* Editor Form Panel */}
        <div className="lg:col-span-4 bg-white border border-border-dark rounded-3xl p-6 shadow-sm space-y-6">
          <h3 className="font-bold font-display text-lg text-slate-900">
            {isEditing ? 'Edit Distributor' : 'Add New Distributor'}
          </h3>

          <form onSubmit={handleSubmit} className="space-y-4 text-xs">
            <div className="space-y-1">
              <label className="font-bold text-slate-500 uppercase">Distributor Name</label>
              <input 
                type="text" 
                value={name} 
                onChange={e => setName(e.target.value)} 
                required 
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:outline-none" 
                placeholder="e.g. Kandy Regional Logistics"
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="font-bold text-slate-500 uppercase">Contact Email</label>
                <input 
                  type="email" 
                  value={contactEmail} 
                  onChange={e => setContactEmail(e.target.value)} 
                  className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:outline-none" 
                  placeholder="contact@logistics.lk"
                />
              </div>
              <div className="space-y-1">
                <label className="font-bold text-slate-500 uppercase">Contact Phone</label>
                <input 
                  type="text" 
                  value={contactPhone} 
                  onChange={e => setContactPhone(e.target.value)} 
                  className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:outline-none" 
                  placeholder="077-1234567"
                />
              </div>
            </div>

            <div className="space-y-1">
              <label className="font-bold text-slate-500 uppercase">API Endpoint URL</label>
              <input 
                type="url" 
                value={apiEndpoint} 
                onChange={e => setApiEndpoint(e.target.value)} 
                required 
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:outline-none" 
                placeholder="http://localhost:5001/api/fulfillment"
              />
            </div>

            <div className="space-y-1">
              <label className="font-bold text-slate-500 uppercase">Security API Key / JWT Token</label>
              <input 
                type="text" 
                value={apiKey} 
                onChange={e => setApiKey(e.target.value)} 
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:outline-none" 
                placeholder="Secure access token string"
              />
            </div>

            <div className="flex gap-3 pt-4">
              <button type="submit" className="flex-1 bg-primary hover:bg-primary-dark text-white font-display font-bold py-3 rounded-xl transition-all">
                {isEditing ? 'Update Distributor' : 'Add Distributor'}
              </button>
              {isEditing && (
                <button type="button" onClick={resetForm} className="px-5 bg-slate-100 hover:bg-slate-200 border border-slate-200 font-bold rounded-xl text-slate-700">
                  Cancel
                </button>
              )}
            </div>
          </form>
        </div>

        {/* Distributor Table List Panel */}
        <div className="lg:col-span-8 bg-white border border-border-dark rounded-3xl overflow-hidden shadow-sm">
          {loading ? (
            <div className="p-12 text-center text-slate-400">
              <span className="material-symbols-outlined text-4xl animate-spin text-primary mb-2">sync</span>
              <p>Loading logistics settings...</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse text-xs">
                <thead>
                  <tr className="bg-slate-50 border-b border-border-dark">
                    <th className="p-4 font-bold text-slate-700 uppercase font-display">Distributor</th>
                    <th className="p-4 font-bold text-slate-700 uppercase font-display">API Endpoint</th>
                    <th className="p-4 font-bold text-slate-700 uppercase font-display text-center">Status</th>
                    <th className="p-4 font-bold text-slate-700 uppercase font-display text-center">Diagnostics</th>
                    <th className="p-4 font-bold text-slate-700 uppercase font-display text-center">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {distributors.length === 0 ? (
                    <tr>
                      <td colSpan={5} className="p-12 text-center text-slate-450">No distributors configured in database.</td>
                    </tr>
                  ) : (
                    distributors.map(d => {
                      const isPinging = pingingMap[d.distributorId];
                      const pingRes = pingResults[d.distributorId];
                      return (
                        <tr key={d.distributorId} className="hover:bg-slate-50/50 transition-colors">
                          <td className="p-4">
                            <div>
                              <p className="font-bold text-slate-800 font-display">{d.name}</p>
                              <p className="text-[10px] text-slate-400">{d.contactEmail} • {d.contactPhone}</p>
                            </div>
                          </td>
                          <td className="p-4">
                            <code className="bg-slate-50 border border-slate-200 px-2 py-1 rounded text-[10px] text-slate-600 block truncate max-w-xs" title={d.apiEndpoint}>
                              {d.apiEndpoint}
                            </code>
                          </td>
                          <td className="p-4 text-center">
                            <span className={`px-2.5 py-0.5 rounded-full font-semibold border text-[10px] ${d.isActive ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'}`}>
                              {d.isActive ? 'Active' : 'Inactive'}
                            </span>
                          </td>
                          <td className="p-4 text-center">
                            <div className="flex flex-col items-center gap-1.5">
                              <button 
                                onClick={() => handlePingTest(d.distributorId)} 
                                disabled={isPinging}
                                className="px-3 py-1 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded font-bold transition-all text-[10px] flex items-center gap-1"
                              >
                                {isPinging ? (
                                  <>
                                    <span className="material-symbols-outlined text-[10px] animate-spin">sync</span> Testing
                                  </>
                                ) : (
                                  <>
                                    <span className="material-symbols-outlined text-[10px]">wifi_tethering</span> Test Connection
                                  </>
                                )}
                              </button>
                              {pingRes && (
                                <span className={`text-[10px] font-bold ${pingRes.success ? 'text-green-600' : 'text-red-500'}`}>
                                  {pingRes.success ? '✅ Online' : '❌ Offline'}
                                </span>
                              )}
                            </div>
                          </td>
                          <td className="p-4 text-center">
                            <div className="flex justify-center gap-2">
                              <button onClick={() => handleEditClick(d)} className="p-1.5 text-primary hover:bg-amber-50 rounded-lg border border-amber-200">
                                <span className="material-symbols-outlined text-sm">edit</span>
                              </button>
                              <button onClick={() => handleDeleteClick(d.distributorId)} className="p-1.5 text-red-500 hover:bg-red-50 rounded-lg border border-red-200">
                                <span className="material-symbols-outlined text-sm">delete</span>
                              </button>
                            </div>
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
      </div>
    </div>
  );
};

export default AdminDistributors;
