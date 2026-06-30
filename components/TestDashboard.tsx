import React, { useState, useEffect } from 'react';
import { getInventoryState, resetInventory, checkStock } from '../services/apiService';
import { MOCK_BLANKETS } from '../constants';
import { TestResult } from '../types';

const TestDashboard: React.FC = () => {
  const [inventory, setInventory] = useState<any>(null);
  const [loadingInv, setLoadingInv] = useState(false);
  const [testResults, setTestResults] = useState<TestResult[]>([
    { id: 't1', name: 'Verify Seller Stock Sufficient (blanket-001, Qty 2)', status: 'ready', message: 'Not run', duration: 0 },
    { id: 't2', name: 'Verify Distributor Escalation (blanket-001, Qty 7)', status: 'ready', message: 'Not run', duration: 0 },
    { id: 't3', name: 'Verify Manufacturer Production (blanket-002, Qty 10)', status: 'ready', message: 'Not run', duration: 0 },
    { id: 't4', name: 'Verify Extreme Quantity Production Lead Time (blanket-002, Qty 20)', status: 'ready', message: 'Not run', duration: 0 },
    { id: 't5', name: 'Verify Database Reset Functionality', status: 'ready', message: 'Not run', duration: 0 }
  ]);
  const [runningTests, setRunningTests] = useState(false);
  const [toast, setToast] = useState<string | null>(null);

  useEffect(() => {
    if (toast) {
      const timer = setTimeout(() => setToast(null), 3000);
      return () => clearTimeout(timer);
    }
  }, [toast]);

  const fetchInventory = async (showFeedback = false) => {
    setLoadingInv(true);
    try {
      const data = await getInventoryState();
      setInventory(data);
      if (showFeedback) {
        setToast("Fulfillment stock states refreshed.");
      }
    } catch (error: any) {
      console.error(error);
      if (showFeedback) {
        setToast("Error: " + (error.message || "Failed to fetch stock."));
      }
    } finally {
      setLoadingInv(false);
    }
  };

  useEffect(() => {
    fetchInventory(false);
  }, []);

  const handleReset = async () => {
    try {
      await resetInventory();
      await fetchInventory(false);
      setToast("Supply chain inventories reset to initial state!");
    } catch (e: any) {
      setToast("Error resetting database: " + (e.message || e));
    }
  };

  const runDiagnostics = async () => {
    setRunningTests(true);
    
    await resetInventory();
    await fetchInventory();

    const newResults = [...testResults];

    const runTest = async (index: number, testFn: () => Promise<string>) => {
      const start = performance.now();
      newResults[index] = { ...newResults[index], status: 'pending', message: 'Running...' };
      setTestResults([...newResults]);
      try {
        const msg = await testFn();
        const duration = Math.round(performance.now() - start);
        newResults[index] = { ...newResults[index], status: 'passed', message: msg, duration };
      } catch (error: any) {
        const duration = Math.round(performance.now() - start);
        newResults[index] = { ...newResults[index], status: 'failed', message: error.message || 'Error occurred', duration };
      }
      setTestResults([...newResults]);
    };

    // Test 1: Seller Sufficient (blanket-001, Qty 2)
    await runTest(0, async () => {
      const res = await checkStock([{ productId: 'blanket-001', quantity: 2 }]);
      const trace = res[0];
      if (trace.totalLeadTimeDays !== 0) throw new Error(`Expected 0 lead days, got ${trace.totalLeadTimeDays}`);
      if (trace.traces[0].availableFromStock !== 2) throw new Error(`Expected 2 units from Seller stock, got ${trace.traces[0].availableFromStock}`);
      return "Funnels immediately through Seller. Lead time is 0 days.";
    });

    // Test 2: Distributor escalation (blanket-001, Qty 7)
    await runTest(1, async () => {
      const res = await checkStock([{ productId: 'blanket-001', quantity: 7 }]);
      const trace = res[0];
      if (trace.totalLeadTimeDays !== 1) throw new Error(`Expected 1 lead day (logistics), got ${trace.totalLeadTimeDays}`);
      if (trace.traces[0].deficit !== 2) throw new Error(`Expected Seller deficit of 2, got ${trace.traces[0].deficit}`);
      if (trace.traces[1].availableFromStock !== 2) throw new Error(`Expected 2 from Distributor stock, got ${trace.traces[1].availableFromStock}`);
      return "Escalates to Distributor. 1-day logistics delay mapped correctly.";
    });

    // Test 3: Manufacturer stock needed (blanket-002, Qty 10)
    await runTest(2, async () => {
      const res = await checkStock([{ productId: 'blanket-002', quantity: 10 }]);
      const trace = res[0];
      if (trace.totalLeadTimeDays !== 2) throw new Error(`Expected 2 lead days (logistics), got ${trace.totalLeadTimeDays}`);
      if (trace.traces[1].deficit !== 4) throw new Error(`Expected Distributor deficit of 4, got ${trace.traces[1].deficit}`);
      if (trace.traces[2].availableFromStock !== 4) throw new Error(`Expected 4 from Manufacturer stock, got ${trace.traces[2].availableFromStock}`);
      return "Orchestrates across all 3 tiers. 2-day logistics delays recorded.";
    });

    // Test 4: Extreme Qty (blanket-002, Qty 20)
    await runTest(3, async () => {
      const res = await checkStock([{ productId: 'blanket-002', quantity: 20 }]);
      const trace = res[0];
      if (trace.totalLeadTimeDays !== 5) throw new Error(`Expected 5 lead days (2 logistics + 3 production), got ${trace.totalLeadTimeDays}`);
      return "Triggers manufacturing production queue. Estimated lead time: 5 days.";
    });

    // Test 5: Reset Inventory
    await runTest(4, async () => {
      await resetInventory();
      return "Database states reverted successfully.";
    });

    await fetchInventory();
    setRunningTests(false);
  };

  return (
    <div className="space-y-10 animate-fade-in text-slate-700">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-border-dark pb-6">
        <div>
          <h2 className="text-3xl font-bold text-slate-900 font-display">Testing & Diagnostic Center</h2>
          <p className="text-slate-500 text-sm">Validate backend service APIs and inspect real-time inventory states across the supply chain.</p>
        </div>
        <div className="flex gap-4">
          <button
            onClick={() => fetchInventory(true)}
            className="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-sm font-bold transition-all border border-slate-200 flex items-center gap-2"
          >
            <span className="material-symbols-outlined text-sm">refresh</span> Refresh Stock
          </button>
          <button
            onClick={handleReset}
            className="px-5 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-sm font-bold transition-all border border-red-200 flex items-center gap-2"
          >
            <span className="material-symbols-outlined text-sm">restart_alt</span> Reset DB
          </button>
          <button
            onClick={runDiagnostics}
            disabled={runningTests}
            className="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-display font-extrabold rounded-xl text-sm transition-all flex items-center gap-2 shadow-sm"
          >
            <span className="material-symbols-outlined text-sm">science</span> {runningTests ? 'Running...' : 'Run Diagnostics'}
          </button>
        </div>
      </div>

      <div className="grid lg:grid-cols-3 gap-8">
        {/* Live Inventory Status */}
        <div className="lg:col-span-1 space-y-6">
          <h3 className="font-display font-bold text-lg text-slate-850 flex items-center gap-2">
            <span className="material-symbols-outlined text-primary text-xl">database</span>
            Live Multi-Tier Stocks
          </h3>

          <div className="space-y-4 max-h-[700px] overflow-y-auto pr-2">
            {['seller', 'distributor', 'manufacturer'].map(tier => {
              const tierName = tier.charAt(0).toUpperCase() + tier.slice(1);
              return (
                <div key={tier} className="bg-white border border-border-dark rounded-2xl p-5 space-y-4 shadow-sm">
                  <div className="flex justify-between items-center border-b border-slate-100 pb-2">
                    <span className="font-bold text-sm text-slate-800">{tierName} Stock</span>
                    <span className={`h-2.5 w-2.5 rounded-full ${inventory ? 'bg-green-500' : 'bg-red-500'} animate-pulse`}></span>
                  </div>
                  <div className="space-y-3">
                    {MOCK_BLANKETS.map(blanket => {
                      const count = inventory?.[tier]?.[blanket.id] ?? 0;
                      return (
                        <div key={blanket.id} className="flex justify-between text-xs items-center">
                          <span className="text-slate-600">{blanket.name}</span>
                          <span className={`font-mono font-bold px-2 py-0.5 rounded ${count > 0 ? 'bg-primary/10 text-primary-dark' : 'bg-red-50 text-red-500'}`}>
                            {count} Left
                          </span>
                        </div>
                      );
                    })}
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Diagnostic Run Log */}
        <div className="lg:col-span-2 space-y-6">
          <h3 className="font-display font-bold text-lg text-slate-850 flex items-center gap-2">
            <span className="material-symbols-outlined text-primary text-xl">task_alt</span>
            Automated API Diagnostics
          </h3>

          <div className="bg-white border border-border-dark rounded-2xl overflow-hidden shadow-sm">
            <table className="w-full text-left text-sm text-slate-600 border-collapse">
              <thead>
                <tr className="bg-slate-50 border-b border-border-dark">
                  <th className="p-4 font-bold text-slate-800 font-display text-xs uppercase tracking-wider">Diagnostic Case</th>
                  <th className="p-4 font-bold text-slate-800 font-display text-xs uppercase tracking-wider">Status</th>
                  <th className="p-4 font-bold text-slate-800 font-display text-xs uppercase tracking-wider">Duration</th>
                  <th className="p-4 font-bold text-slate-800 font-display text-xs uppercase tracking-wider">API Message</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {testResults.map(test => (
                  <tr key={test.id} className="hover:bg-slate-50/50 transition-colors">
                    <td className="p-4 font-bold text-xs text-slate-800">{test.name}</td>
                    <td className="p-4">
                      {test.status === 'ready' && (
                        <span className="inline-flex items-center gap-1 text-[10px] font-bold text-slate-500 px-2 py-0.5 bg-slate-50 rounded border border-slate-200">
                          <span className="material-symbols-outlined text-xs">play_circle</span> READY
                        </span>
                      )}
                      {test.status === 'passed' && (
                        <span className="inline-flex items-center gap-1 text-[10px] font-bold text-green-700 px-2 py-0.5 bg-green-50 rounded border border-green-200">
                          <span className="material-symbols-outlined text-xs">check_circle</span> PASSED
                        </span>
                      )}
                      {test.status === 'failed' && (
                        <span className="inline-flex items-center gap-1 text-[10px] font-bold text-red-600 px-2 py-0.5 bg-red-50 rounded border border-red-200">
                          <span className="material-symbols-outlined text-xs">cancel</span> FAILED
                        </span>
                      )}
                      {test.status === 'pending' && (
                        <span className="inline-flex items-center gap-1 text-[10px] font-bold text-slate-500 px-2 py-0.5 bg-slate-50 rounded border border-slate-200">
                          <span className="material-symbols-outlined text-xs animate-spin">sync</span> RUNNING
                        </span>
                      )}
                    </td>
                    <td className="p-4 font-mono text-xs text-slate-500">{test.duration ? `${test.duration}ms` : '-'}</td>
                    <td className="p-4 text-xs text-slate-500 leading-relaxed max-w-xs truncate" title={test.message}>{test.message}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {/* Floating notifications toast */}
      {toast && (
        <div className="fixed bottom-8 right-8 bg-[#2e1e12] text-[#eed9c4] font-bold font-display px-6 py-4 rounded-xl shadow-lg border border-[#eed9c4] animate-fade-in flex items-center gap-3 z-50 text-xs">
          <span className="material-symbols-outlined text-amber-500">
            {toast.startsWith("Error") ? "error" : "check_circle"}
          </span>
          {toast}
        </div>
      )}
    </div>
  );
};

export default TestDashboard;
