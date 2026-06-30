import React, { useState } from 'react';

const TheoryView: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'comparison' | 'services' | 'database' | 'code' | 'deployment'>('comparison');

  return (
    <div className="space-y-8 animate-fade-in max-w-5xl mx-auto text-slate-700">
      {/* Tab Navigation */}
      <div className="flex flex-wrap border-b border-border-dark gap-2">
        <button
          onClick={() => setActiveTab('comparison')}
          className={`px-5 py-3 font-display font-bold text-sm tracking-wide transition-all border-b-2 flex items-center gap-2 ${
            activeTab === 'comparison'
              ? 'border-primary text-primary font-extrabold'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          }`}
        >
          <span className="material-symbols-outlined text-sm">balance</span>
          1. Monolith vs SOA
        </button>
        <button
          onClick={() => setActiveTab('services')}
          className={`px-5 py-3 font-display font-bold text-sm tracking-wide transition-all border-b-2 flex items-center gap-2 ${
            activeTab === 'services'
              ? 'border-primary text-primary font-extrabold'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          }`}
        >
          <span className="material-symbols-outlined text-sm">settings_suggest</span>
          2. Service Design
        </button>
        <button
          onClick={() => setActiveTab('database')}
          className={`px-5 py-3 font-display font-bold text-sm tracking-wide transition-all border-b-2 flex items-center gap-2 ${
            activeTab === 'database'
              ? 'border-primary text-primary font-extrabold'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          }`}
        >
          <span className="material-symbols-outlined text-sm">database</span>
          3. DB Schema
        </button>
        <button
          onClick={() => setActiveTab('code')}
          className={`px-5 py-3 font-display font-bold text-sm tracking-wide transition-all border-b-2 flex items-center gap-2 ${
            activeTab === 'code'
              ? 'border-primary text-primary font-extrabold'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          }`}
        >
          <span className="material-symbols-outlined text-sm">code</span>
          4. C# Web API
        </button>
        <button
          onClick={() => setActiveTab('deployment')}
          className={`px-5 py-3 font-display font-bold text-sm tracking-wide transition-all border-b-2 flex items-center gap-2 ${
            activeTab === 'deployment'
              ? 'border-primary text-primary font-extrabold'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          }`}
        >
          <span className="material-symbols-outlined text-sm">lan</span>
          5. Deployment
        </button>
      </div>

      {/* Tab 1: Monolith vs SOA */}
      {activeTab === 'comparison' && (
        <div className="bg-white border border-border-dark rounded-2xl p-8 space-y-6 animate-fade-in shadow-sm">
          <h2 className="text-2xl font-bold text-slate-900 font-display border-b border-slate-200 pb-4">
            Cozy Comfort Architecture Analysis
          </h2>
          
          <div className="grid md:grid-cols-2 gap-6">
            <div className="bg-slate-50 border border-border-dark rounded-xl p-5 space-y-3">
              <h3 className="text-lg font-bold text-red-600 flex items-center gap-2 font-display">
                <span className="material-symbols-outlined">layers_clear</span>
                Monolithic Model
              </h3>
              <p className="text-xs text-slate-600 leading-relaxed">
                All logic—retail catalog, order routing, warehouse tracking, and manufacturing scheduling—is packed in a single process. Any update to factory schedulers requires deploying the customer web app.
              </p>
              <div className="bg-red-50 p-3 rounded-lg border border-red-100 text-[11px] text-red-700">
                ❌ Single failure points: A heavy manufacturing pipeline query could choke catalog browsing.
              </div>
            </div>

            <div className="bg-slate-50 border border-border-dark rounded-xl p-5 space-y-3">
              <h3 className="text-lg font-bold text-green-600 flex items-center gap-2 font-display">
                <span className="material-symbols-outlined">grid_view</span>
                Service-Oriented (SOA)
              </h3>
              <p className="text-xs text-slate-600 leading-relaxed">
                Deconstructed into autonomous services (Seller, Distributor, Manufacturer) communicating via REST. Each controls its own database schema instance.
              </p>
              <div className="bg-green-50 p-3 rounded-lg border border-green-100 text-[11px] text-green-700">
                ✅ Fault isolation & dynamic scaling: Only the Seller scales up during shopping peaks.
              </div>
            </div>
          </div>

          <div className="pt-6">
            <h3 className="text-lg font-bold text-slate-900 mb-4 font-display">Architecture Matrix</h3>
            <div className="overflow-x-auto border border-border-dark rounded-xl">
              <table className="w-full text-left text-xs text-slate-600 border-collapse">
                <thead>
                  <tr className="border-b border-border-dark bg-slate-50 font-display">
                    <th className="p-3 font-bold text-slate-800">Dimension</th>
                    <th className="p-3 font-bold text-slate-800">Monolithic</th>
                    <th className="p-3 font-bold text-slate-800">Service-Oriented (SOA)</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-border-dark/30">
                  <tr>
                    <td className="p-3 font-bold text-slate-800">Scale Mode</td>
                    <td className="p-3 text-slate-600">Scale whole app horizontally</td>
                    <td className="p-3 text-primary-dark font-bold">Scale isolated bottlenecks individually</td>
                  </tr>
                  <tr>
                    <td className="p-3 font-bold text-slate-800">Db Boundary</td>
                    <td className="p-3 text-slate-600">Unified database; tables joined directly</td>
                    <td className="p-3 text-primary-dark font-bold">Database-per-service (No cross-joins)</td>
                  </tr>
                  <tr>
                    <td className="p-3 font-bold text-slate-800">Failure Containment</td>
                    <td className="p-3 text-red-600 font-semibold">Poor (Database locks take down all systems)</td>
                    <td className="p-3 text-green-600 font-bold">Excellent (Sellers process orders even if factory is down)</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* Tab 2: Service Design */}
      {activeTab === 'services' && (
        <div className="bg-white border border-border-dark rounded-2xl p-8 space-y-6 animate-fade-in shadow-sm">
          <h2 className="text-2xl font-bold text-slate-900 font-display border-b border-slate-200 pb-4">
            Microservice Blueprint & Decoupled Endpoints
          </h2>
          <p className="text-sm text-slate-500">
            For true decoupling, each service manages its own subset of operations and exposes standard contract REST interfaces.
          </p>

          <div className="grid md:grid-cols-3 gap-6">
            <div className="bg-slate-50 border border-border-dark rounded-xl p-5 space-y-3 shadow-sm">
              <h4 className="font-bold text-primary flex items-center gap-1.5 font-display text-sm">
                <span className="material-symbols-outlined text-sm">shopping_basket</span> Seller Service
              </h4>
              <p className="text-xs text-slate-500">Handles order checkouts and retail stock management.</p>
              <ul className="text-[11px] text-slate-700 space-y-1 bg-white p-2.5 rounded border border-border-dark font-mono shadow-inner">
                <li>• POST /api/Fulfillment/check-stock</li>
                <li>• POST /api/Fulfillment/place-order</li>
              </ul>
            </div>

            <div className="bg-slate-50 border border-border-dark rounded-xl p-5 space-y-3 shadow-sm">
              <h4 className="font-bold text-primary flex items-center gap-1.5 font-display text-sm">
                <span className="material-symbols-outlined text-sm">local_shipping</span> Distributor Service
              </h4>
              <p className="text-xs text-slate-500">Manages regional warehouse logs and replenishment shipping routes.</p>
              <ul className="text-[11px] text-slate-700 space-y-1 bg-white p-2.5 rounded border border-border-dark font-mono shadow-inner">
                <li>• POST /api/Distributor/check-stock</li>
                <li>• POST /api/Distributor/order</li>
              </ul>
            </div>

            <div className="bg-slate-50 border border-border-dark rounded-xl p-5 space-y-3 shadow-sm">
              <h4 className="font-bold text-primary flex items-center gap-1.5 font-display text-sm">
                <span className="material-symbols-outlined text-sm">factory</span> Manufacturer Service
              </h4>
              <p className="text-xs text-slate-500">Determines factory capacity and schedules blanket assembly cycles.</p>
              <ul className="text-[11px] text-slate-700 space-y-1 bg-white p-2.5 rounded border border-border-dark font-mono shadow-inner">
                <li>• POST /api/Manufacturer/check-capacity</li>
                <li>• POST /api/Manufacturer/order</li>
              </ul>
            </div>
          </div>
        </div>
      )}

      {/* Tab 3: DB Schema */}
      {activeTab === 'database' && (
        <div className="bg-white border border-border-dark rounded-2xl p-8 space-y-6 animate-fade-in shadow-sm">
          <h2 className="text-2xl font-bold text-slate-900 font-display border-b border-slate-200 pb-4">
            Production Database Schema Scripts (SQL Server)
          </h2>
          <p className="text-sm text-slate-500">
            Below are the DDL queries for setting up separate database catalogs per service layer.
          </p>

          <div className="bg-slate-50 border border-border-dark rounded-xl p-5 font-mono text-[11px] text-amber-800 overflow-x-auto max-h-96 overflow-y-auto space-y-4 shadow-inner">
            <div>
              <p className="text-slate-500 font-bold uppercase border-b border-slate-200 pb-1 mb-2">// 1. SELLER SERVICE DATABASE SCHEMA</p>
              <pre className="text-slate-800 leading-relaxed font-mono">
{`CREATE DATABASE CozyComfort_Seller;
GO
USE CozyComfort_Seller;

CREATE TABLE Blankets (
    Id VARCHAR(50) PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Material VARCHAR(50) NOT NULL,
    BasePrice DECIMAL(18,2) NOT NULL,
    Category VARCHAR(50) NOT NULL
);

CREATE TABLE Orders (
    Id VARCHAR(50) PRIMARY KEY,
    CustomerId VARCHAR(50) NOT NULL,
    Status VARCHAR(30) NOT NULL,
    TotalAmount DECIMAL(18,2) NOT NULL,
    CreatedAt DATETIME DEFAULT GETUTCDATE(),
    TrackingId VARCHAR(50) NOT NULL
);

CREATE TABLE OrderItems (
    Id INT IDENTITY(1,1) PRIMARY KEY,
    OrderId VARCHAR(50) FOREIGN KEY REFERENCES Orders(Id) ON DELETE CASCADE,
    ProductId VARCHAR(50) FOREIGN KEY REFERENCES Blankets(Id),
    Quantity INT NOT NULL CHECK (Quantity > 0),
    UnitPrice DECIMAL(18,2) NOT NULL,
    LeadTimeDays INT NOT NULL
);`}
              </pre>
            </div>

            <div>
              <p className="text-slate-500 font-bold uppercase border-b border-slate-200 pb-1 mb-2">// 2. DISTRIBUTOR DATABASE SCHEMA</p>
              <pre className="text-slate-800 leading-relaxed font-mono">
{`CREATE DATABASE CozyComfort_Distributor;
GO
USE CozyComfort_Distributor;

CREATE TABLE DistributorInventory (
    ProductId VARCHAR(50) PRIMARY KEY,
    StockCount INT NOT NULL CHECK (StockCount >= 0)
);`}
              </pre>
            </div>

            <div>
              <p className="text-slate-500 font-bold uppercase border-b border-slate-200 pb-1 mb-2">// 3. MANUFACTURER DATABASE SCHEMA</p>
              <pre className="text-slate-800 leading-relaxed font-mono">
{`CREATE DATABASE CozyComfort_Manufacturer;
GO
USE CozyComfort_Manufacturer;

CREATE TABLE ManufacturerInventory (
    ProductId VARCHAR(50) PRIMARY KEY,
    StockCount INT NOT NULL CHECK (StockCount >= 0)
);

CREATE TABLE ProductionRequests (
    Id VARCHAR(50) PRIMARY KEY,
    ProductId VARCHAR(50) NOT NULL,
    Quantity INT NOT NULL CHECK (Quantity > 0),
    EstCompleteDate DATETIME NOT NULL,
    Status VARCHAR(30) NOT NULL
);`}
              </pre>
            </div>
          </div>
        </div>
      )}

      {/* Tab 4: C# Web API */}
      {activeTab === 'code' && (
        <div className="bg-white border border-border-dark rounded-2xl p-8 space-y-6 animate-fade-in shadow-sm">
          <h2 className="text-2xl font-bold text-slate-900 font-display border-b border-slate-200 pb-4">
            ASP.NET Core Web API Structure
          </h2>

          <div className="grid md:grid-cols-12 gap-8">
            <div className="md:col-span-4 space-y-3">
              <h4 className="font-bold text-slate-900 font-display text-sm">Folder Architecture</h4>
              <pre className="font-mono text-xs text-slate-700 bg-slate-50 p-4 rounded-xl border border-border-dark shadow-inner">
{`/API
  ├── /Controllers
  │     └── FulfillmentController.cs
  ├── /Services
  │     ├── ISellerService.cs
  │     └── SellerService.cs
  ├── /Models
  │     └── DTOs.cs
  ├── /Middleware
  │     └── ExceptionMiddleware.cs
  └── Program.cs`}
              </pre>
            </div>

            <div className="md:col-span-8 space-y-3">
              <h4 className="font-bold text-slate-900 font-display text-sm">C# Custom Trace Middleware</h4>
              <p className="text-xs text-slate-500">Enforces request scope correlation tracking down the supply chain tiers.</p>
              <pre className="font-mono text-[10px] text-slate-700 bg-slate-50 p-4 rounded-xl border border-border-dark overflow-x-auto max-h-72 overflow-y-auto shadow-inner">
{`public class CorrelationTokenMiddleware
{
    private readonly RequestDelegate _next;
    private readonly ILogger<CorrelationTokenMiddleware> _logger;

    public CorrelationTokenMiddleware(RequestDelegate next, ILogger<CorrelationTokenMiddleware> logger)
    {
        _next = next;
        _logger = logger;
    }

    public async Task InvokeAsync(HttpContext context)
    {
        const string CorrelationHeader = "X-Correlation-ID";
        if (!context.Request.Headers.TryGetValue(CorrelationHeader, out var correlationId))
        {
            correlationId = Guid.NewGuid().ToString();
        }

        context.Response.Headers[CorrelationHeader] = correlationId;
        using (_logger.BeginScope("CorrelationId:{CorrelationId}", correlationId.ToString()))
        {
            await _next(context);
        }
    }
}`}
              </pre>
            </div>
          </div>
        </div>
      )}

      {/* Tab 5: Deployment */}
      {activeTab === 'deployment' && (
        <div className="bg-white border border-border-dark rounded-2xl p-8 space-y-6 animate-fade-in shadow-sm">
          <h2 className="text-2xl font-bold text-slate-900 font-display border-b border-slate-200 pb-4">
            Cloud Container Deployment Specifications
          </h2>
          <p className="text-sm text-slate-500">
            Production configurations for hosting Cozy Comfort services as decoupled, highly-available containers.
          </p>

          <div className="grid md:grid-cols-2 gap-6">
            <div className="space-y-3">
              <h4 className="font-bold text-slate-900 flex items-center gap-1 text-sm font-display">
                <span className="material-symbols-outlined text-sm text-primary">token</span> Docker Compose Spec
              </h4>
              <pre className="font-mono text-[10px] text-slate-700 bg-slate-50 p-4 rounded-xl border border-border-dark overflow-x-auto max-h-60 overflow-y-auto shadow-inner">
{`version: '3.8'
services:
  sql-server:
    image: mcr.microsoft.com/mssql/server:2022-latest
    environment:
      - ACCEPT_EULA=Y
      - MSSQL_SA_PASSWORD=CozySecurePass123!
    ports:
      - "1433:1433"

  seller-service:
    build:
      context: .
      dockerfile: Dockerfile
    environment:
      - ConnectionStrings__SellerDb=Server=sql-server;Database=CozyComfort_Seller;User Id=sa;Password=CozySecurePass123!;TrustServerCertificate=True;
    ports:
      - "5111:8080"
    depends_on:
      - sql-server`}
              </pre>
            </div>

            <div className="space-y-3">
              <h4 className="font-bold text-slate-900 flex items-center gap-1 text-sm font-display">
                <span className="material-symbols-outlined text-sm text-primary">grid_3x3</span> Kubernetes Spec
              </h4>
              <pre className="font-mono text-[10px] text-slate-700 bg-slate-50 p-4 rounded-xl border border-border-dark overflow-x-auto max-h-60 overflow-y-auto shadow-inner">
{`apiVersion: apps/v1
kind: Deployment
metadata:
  name: seller-service-deployment
  namespace: cozy-comfort
spec:
  replicas: 2
  selector:
    matchLabels:
      app: seller-service
  template:
    metadata:
      labels:
        app: seller-service
    spec:
      containers:
      - name: seller-api
        image: cozycomfort/seller-service:1.0.0
        ports:
        - containerPort: 8080
        resources:
          limits:
            cpu: "500m"
            memory: "512Mi"`}
              </pre>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default TheoryView;
