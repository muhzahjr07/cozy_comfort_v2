import React from 'react';
import { FulfillmentTraceNode } from '../types';

interface SupplyChainVisualizerProps {
  traces: FulfillmentTraceNode[];
  totalLeadTime: number;
}

const SupplyChainVisualizer: React.FC<SupplyChainVisualizerProps> = ({ traces, totalLeadTime }) => {
  if (!traces || traces.length === 0) {
    return (
      <div className="border-2 border-dashed border-border-dark rounded-2xl p-12 text-center text-slate-400">
        <span className="material-symbols-outlined text-5xl text-slate-300 mb-4 animate-pulse">flowsheet</span>
        <p className="text-sm">Initiate the SOC checkout sequence to watch the inventory orchestration flow here in real time.</p>
      </div>
    );
  }

  return (
    <div className="space-y-8 animate-fade-in bg-white border border-border-dark rounded-2xl p-6 shadow-sm">
      <div className="flex justify-between items-center border-b border-slate-100 pb-4">
        <div>
          <h4 className="text-lg font-bold text-slate-900 font-display flex items-center gap-2">
            <span className="material-symbols-outlined text-primary">hub</span>
            SOC Supply Chain Traversal Map
          </h4>
          <p className="text-xs text-slate-500">Visual mapping of recursive stock orchestration across endpoints.</p>
        </div>
        <div className="text-right">
          <p className="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Estimated Order Delivery</p>
          <p className="text-2xl font-display font-extrabold text-primary-dark">{totalLeadTime} {totalLeadTime === 1 ? 'Day' : 'Days'}</p>
        </div>
      </div>

      <div className="flex flex-col md:flex-row items-stretch gap-6 relative">
        {traces.map((trace, index) => {
          const isSeller = trace.tierName === 'Seller';
          const isDist = trace.tierName === 'Distributor';
          const isMfg = trace.tierName === 'Manufacturer';

          let icon = 'storefront';
          let borderClr = 'border-border-dark';
          let badgeClr = 'bg-slate-100 text-slate-500';
          if (isSeller) {
            icon = 'shopping_cart';
            borderClr = 'border-amber-400/60 hover:border-amber-500';
            badgeClr = 'bg-amber-50 text-amber-700';
          } else if (isDist) {
            icon = 'local_shipping';
            borderClr = 'border-indigo-400/60 hover:border-indigo-500';
            badgeClr = 'bg-indigo-50 text-indigo-700';
          } else if (isMfg) {
            icon = 'precision_manufacturing';
            borderClr = 'border-rose-400/60 hover:border-rose-500';
            badgeClr = 'bg-rose-50 text-rose-700';
          }

          return (
            <React.Fragment key={index}>
              {/* Connector between nodes */}
              {index > 0 && (
                <div className="hidden md:flex flex-col items-center justify-center self-center text-slate-400 gap-1 px-2">
                  <span className="text-xs font-mono font-bold text-slate-400">Query</span>
                  <span className="material-symbols-outlined text-2xl animate-pulse">arrow_forward</span>
                  <span className="text-[10px] font-mono text-slate-400">+1 Day</span>
                </div>
              )}

              {/* Node Card */}
              <div className={`flex-1 bg-slate-50 border ${borderClr} rounded-2xl p-5 flex flex-col justify-between transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm`}>
                <div className="space-y-4">
                  <div className="flex justify-between items-center">
                    <div className="flex items-center gap-2">
                      <span className={`material-symbols-outlined text-xl ${isSeller ? 'text-amber-600' : isDist ? 'text-indigo-600' : 'text-rose-600'}`}>{icon}</span>
                      <h5 className="font-bold text-slate-800 text-sm font-display">{trace.tierName} Service</h5>
                    </div>
                    <span className={`text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded ${badgeClr}`}>
                      Tier {index + 1}
                    </span>
                  </div>

                  <p className="text-xs text-slate-600 leading-relaxed min-h-[50px]">{trace.details}</p>

                  <div className="grid grid-cols-2 gap-3 pt-3 border-t border-slate-200 text-xs">
                    <div className="bg-white p-2.5 rounded-lg text-center border border-slate-100">
                      <p className="text-slate-400 text-[10px] uppercase font-bold tracking-wider">Checked Qty</p>
                      <p className="text-sm font-bold text-slate-850">{trace.checkedQuantity}</p>
                    </div>
                    <div className="bg-white p-2.5 rounded-lg text-center border border-slate-100">
                      <p className="text-slate-400 text-[10px] uppercase font-bold tracking-wider">Sourced Stock</p>
                      <p className="text-sm font-bold text-green-600">{trace.availableFromStock}</p>
                    </div>
                  </div>
                </div>

                <div className="mt-4 pt-3 border-t border-slate-200 flex justify-between items-center text-xs">
                  <span className="text-slate-500">Deficit: <strong className={trace.deficit > 0 ? 'text-red-600' : 'text-slate-500'}>{trace.deficit}</strong></span>
                  <span className="text-slate-500 font-mono">Lead Time: <strong className="text-primary-dark font-bold">{trace.leadTimeDays} {trace.leadTimeDays === 1 ? 'day' : 'days'}</strong></span>
                </div>
              </div>
            </React.Fragment>
          );
        })}
      </div>
    </div>
  );
};

export default SupplyChainVisualizer;
