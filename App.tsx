import React, { useState, useEffect, useMemo } from 'react';
import { ViewState, Blanket, OrderItem, Order, FulfillmentTraceNode } from './types';
import { 
  login, register, logout, getCurrentUser, getProfile,
  getProducts, getCategories, createOrder, getCustomerOrders,
  getCategoryStats, getDistributors
} from './services/apiService';
import { querySupplyChainWorkflow, executeFulfillmentWorkflow } from './services/orderService';

import TheoryView from './components/TheoryView';
import TestDashboard from './components/TestDashboard';
import SupplyChainVisualizer from './components/SupplyChainVisualizer';
import AdminDashboard from './components/AdminDashboard';
import AdminProducts from './components/AdminProducts';
import AdminDistributors from './components/AdminDistributors';
import AdminOrders from './components/AdminOrders';

const getMockSpecifications = (p: any): Record<string, string> => {
  let specs: Record<string, string> = {};
  if (p.specifications) {
    if (typeof p.specifications === 'string') {
      try {
        specs = JSON.parse(p.specifications);
      } catch {
        specs = {};
      }
    } else {
      specs = p.specifications;
    }
  }

  if (Object.keys(specs).length > 0) {
    return specs;
  }

  const name = p.name || '';
  const category = p.category || '';
  
  let material = 'Premium Microfiber & Polyester';
  if (name.toLowerCase().includes('cotton')) material = '100% Organic Cotton';
  else if (name.toLowerCase().includes('merino') || name.toLowerCase().includes('wool')) material = '100% Australian Merino Wool';
  else if (name.toLowerCase().includes('cashmere')) material = '100% Himalayan Cashmere';
  else if (name.toLowerCase().includes('bamboo')) material = 'Natural Bamboo Viscose';
  else if (name.toLowerCase().includes('plush') || name.toLowerCase().includes('velvet')) material = 'Ultra-Soft Plush Velvet';
  else if (name.toLowerCase().includes('silk')) material = '100% Mulberry Silk';
  else if (name.toLowerCase().includes('fleece')) material = 'Super-Soft Polar Fleece';
  else if (name.toLowerCase().includes('down')) material = 'Goose Down Fill & Cotton Shell';
  else if (name.toLowerCase().includes('sherpa')) material = 'Reversible Sherpa Fleece';

  let dimensions = '50 x 60 inches (Throw)';
  if (name.toLowerCase().includes('king') || (p.basePrice && p.basePrice > 25000)) dimensions = '108 x 90 inches (King)';
  else if (name.toLowerCase().includes('queen') || (p.basePrice && p.basePrice > 15000)) dimensions = '90 x 90 inches (Queen)';
  else if (name.toLowerCase().includes('twin') || name.toLowerCase().includes('single')) dimensions = '66 x 90 inches (Twin)';

  let washable = 'Machine Washable';
  if (name.toLowerCase().includes('wool') || name.toLowerCase().includes('cashmere') || name.toLowerCase().includes('silk') || name.toLowerCase().includes('fur') || name.toLowerCase().includes('down')) {
    washable = 'Dry Clean Only';
  } else if (name.toLowerCase().includes('weighted')) {
    washable = 'Machine Washable Cover Only';
  }

  const generatedSpecs: Record<string, string> = {
    material,
    dimensions,
    washable
  };

  if (category.toLowerCase().includes('heated') || category.toLowerCase().includes('electric') || name.toLowerCase().includes('electric') || name.toLowerCase().includes('heated')) {
    generatedSpecs['settings'] = name.toLowerCase().includes('dual') ? '10 Heat Levels' : '5 Heat Levels';
    generatedSpecs['safety'] = '3-Hour Auto Shutoff';
  } else if (category.toLowerCase().includes('weighted') || name.toLowerCase().includes('weighted')) {
    if (name.toLowerCase().includes('15lbs') || name.toLowerCase().includes('15 lbs')) generatedSpecs['weight'] = '15 lbs';
    else if (name.toLowerCase().includes('20lbs') || name.toLowerCase().includes('20 lbs')) generatedSpecs['weight'] = '20 lbs';
    else if (name.toLowerCase().includes('12lbs') || name.toLowerCase().includes('12 lbs')) generatedSpecs['weight'] = '12 lbs';
    else if (name.toLowerCase().includes('5lbs') || name.toLowerCase().includes('5 lbs')) generatedSpecs['weight'] = '5 lbs';
    else generatedSpecs['weight'] = '15 lbs';
    generatedSpecs['filling'] = 'Premium Glass Microbeads';
  } else {
    generatedSpecs['weight'] = p.basePrice > 10000 ? '4.2 lbs (Heavyweight)' : '2.5 lbs (Lightweight)';
  }

  return generatedSpecs;
};

const App: React.FC = () => {
  const [currentView, setCurrentView] = useState<ViewState>(ViewState.CATALOG);
  const [currentUser, setCurrentUser] = useState<any>(null);
  const [token, setToken] = useState<string | null>(null);

  // Auth Forms State
  const [loginEmail, setLoginEmail] = useState('');
  const [loginPassword, setLoginPassword] = useState('');
  const [regFirstName, setRegFirstName] = useState('');
  const [regLastName, setRegLastName] = useState('');
  const [regEmail, setRegEmail] = useState('');
  const [regPassword, setRegPassword] = useState('');
  const [regConfirmPassword, setRegConfirmPassword] = useState('');
  const [regRole, setRegRole] = useState('Customer');

  // Store Front Catalog State
  const [catalogProducts, setCatalogProducts] = useState<any[]>([]);
  const [categories, setCategories] = useState<string[]>([]);
  const [selectedCategory, setSelectedCategory] = useState('All');
  const [searchTerm, setSearchTerm] = useState('');
  const [debouncedSearchTerm, setDebouncedSearchTerm] = useState('');
  const [loadingProducts, setLoadingProducts] = useState(false);
  const [selectedProduct, setSelectedProduct] = useState<any>(null);

  // Cart & Orders State
  const [cart, setCart] = useState<OrderItem[]>([]);
  const [orders, setOrders] = useState<any[]>([]);
  const [isProcessing, setIsProcessing] = useState(false);
  const [logs, setLogs] = useState<Array<{ status: string; details?: any }>>([]);
  const [currentOrder, setCurrentOrder] = useState<any | null>(null);
  const [showToast, setShowToast] = useState(false);
  const [toastMessage, setToastMessage] = useState('Item added to order bag!');

  // Checkout Trace State
  const [isWaitingConfirmation, setIsWaitingConfirmation] = useState(false);
  const [pendingTraces, setPendingTraces] = useState<FulfillmentTraceNode[]>([]);
  const [totalLeadTime, setTotalLeadTime] = useState(0);

  // Admin Reports Data
  const [catStats, setCatStats] = useState<any[]>([]);
  const [allDists, setAllDists] = useState<any[]>([]);

  // Load auth from localStorage on mount
  useEffect(() => {
    const savedToken = localStorage.getItem('cozy_comfort_token');
    const savedUser = getCurrentUser();
    if (savedToken && savedUser) {
      setToken(savedToken);
      setCurrentUser(savedUser);
    }
    loadCatalog();
  }, []);

  useEffect(() => {
    if (showToast) {
      const timer = setTimeout(() => setShowToast(false), 3000);
      return () => clearTimeout(timer);
    }
  }, [showToast]);

  const cartTotal = useMemo(() =>
    cart.reduce((acc, item) => acc + item.blanket.basePrice * item.quantity, 0)
    , [cart]);

  // Debounce search input
  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedSearchTerm(searchTerm);
    }, 300);
    return () => clearTimeout(handler);
  }, [searchTerm]);

  // Fetch products automatically when filters change (updates search as user types)
  useEffect(() => {
    const fetchFilteredProducts = async () => {
      setLoadingProducts(true);
      try {
        const res = await getProducts({ category: selectedCategory, searchTerm: debouncedSearchTerm, pageSize: 100 });
        if (res.success) {
          setCatalogProducts(res.data.data);
        }
      } catch (err) {
        console.error('Error fetching filtered products', err);
      } finally {
        setLoadingProducts(false);
      }
    };
    fetchFilteredProducts();
  }, [debouncedSearchTerm, selectedCategory]);

  const loadCatalog = async () => {
    try {
      const catRes = await getCategories();
      if (catRes.success) {
        setCategories(catRes.data);
      }
    } catch (e) {
      console.error('Error loading store categories', e);
    }
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
  };

  const handleCategorySelect = (cat: string) => {
    setSelectedCategory(cat);
  };

  // Auth Operations
  const handleLoginSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!loginEmail || !loginPassword) return;

    try {
      const res = await login({ email: loginEmail, password: loginPassword });
      if (res.success) {
        const savedUser = getCurrentUser();
        setToken(res.data.token);
        setCurrentUser(savedUser);
        setLoginEmail('');
        setLoginPassword('');
        setToastMessage('Welcome back to Cozy Comfort!');
        setShowToast(true);

        if (savedUser.role === 'Admin') {
          setCurrentView(ViewState.ADMIN_DASHBOARD);
        } else {
          setCurrentView(ViewState.CATALOG);
        }
      } else {
        alert(res.message || 'Login failed');
      }
    } catch (err: any) {
      alert(err.message || 'Login error occurred');
    }
  };

  const handleRegisterSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (regPassword !== regConfirmPassword) {
      alert('Passwords do not match');
      return;
    }

    const payload = {
      email: regEmail,
      password: regPassword,
      confirmPassword: regConfirmPassword,
      firstName: regFirstName,
      lastName: regLastName,
      role: regRole
    };

    try {
      const res = await register(payload);
      if (res.success) {
        alert('Registration successful! Please log in.');
        setCurrentView(ViewState.LOGIN);
        // Reset registration fields
        setRegEmail('');
        setRegPassword('');
        setRegConfirmPassword('');
        setRegFirstName('');
        setRegLastName('');
      } else {
        alert(res.message || 'Registration failed');
      }
    } catch (err: any) {
      alert(err.message || 'Registration error');
    }
  };

  const handleLogout = () => {
    logout();
    setToken(null);
    setCurrentUser(null);
    setCart([]);
    setOrders([]);
    setCurrentView(ViewState.CATALOG);
    setToastMessage('You have logged out.');
    setShowToast(true);
  };

  // Cart operations
  const addToCart = (product: any) => {
    const blanket: Blanket = {
      id: `blanket-${product.productId.toString().padStart(3, '0')}`,
      name: product.name,
      material: product.specifications?.material || 'Soft Fabric',
      basePrice: product.basePrice,
      image: product.imageUrl || 'https://images.unsplash.com/photo-1580301762395-21ce84d00bc6?q=80&w=400',
      description: product.description,
      category: product.category === 'Premium' ? 'Premium' : 'Standard'
    };

    setCart(prev => {
      const existing = prev.find(i => i.blanket.id === blanket.id);
      if (existing) {
        return prev.map(i => i.blanket.id === blanket.id ? { ...i, quantity: i.quantity + 1 } : i);
      }
      return [...prev, { blanket, quantity: 1 }];
    });
    setToastMessage('Blanket added to order bag!');
    setShowToast(true);
  };

  // Checkout Tracing
  const handleCheckout = async () => {
    if (!currentUser) {
      alert('Please log in or register to proceed to checkout.');
      setCurrentView(ViewState.LOGIN);
      return;
    }

    setIsProcessing(true);
    setCurrentView(ViewState.CHECKOUT_PROCESS);
    setLogs([]);
    setIsWaitingConfirmation(false);
    setCurrentOrder(null);
    setPendingTraces([]);
    setTotalLeadTime(0);

    try {
      const queryResults = await querySupplyChainWorkflow(cart, (status, details) => {
        setLogs(prev => [...prev, { status, details }]);
      });

      if (queryResults.length > 0) {
        setPendingTraces(queryResults[0].traces);
        const maxLead = Math.max(...queryResults.map(r => r.totalLeadTimeDays));
        setTotalLeadTime(maxLead);
      }

      setIsWaitingConfirmation(true);
      setIsProcessing(false);
    } catch (error) {
      console.error(error);
      setLogs(prev => [...prev, { status: `Error: ${error instanceof Error ? error.message : 'Unknown error'}` }]);
      setIsProcessing(false);
    }
  };

  const confirmOrder = async () => {
    setIsProcessing(true);
    setIsWaitingConfirmation(false);

    try {
      // 1. First trigger Seller stock decrement (stays true to the 3-tier check)
      const finalizedOrder = await executeFulfillmentWorkflow(cart, (status, details) => {
        setLogs(prev => [...prev, { status, details }]);
      });

      // 2. Now commit order in the relational SQLite database via the C# API
      setLogs(prev => [...prev, { status: 'Saving order record in central database...' }]);
      
      // Resolve customer ID
      let profileRes = await getProfile();
      let customerId = profileRes?.data?.userId || 1; // Fallback to 1

      const orderItemsPayload = cart.map(item => {
        // Find product ID in catalog
        const match = catalogProducts.find(p => p.name === item.blanket.name);
        return {
          productId: match?.productId || 1,
          quantity: item.quantity,
          unitPrice: item.blanket.basePrice
        };
      });

      const dbOrderPayload = {
        customerId: customerId,
        deliveryAddress: "Customer Shipping Address", // Mocked for simplicity
        phoneNumber: "077-1234567",
        notes: `Fulfillment Lead Time: ${totalLeadTime} Days`,
        items: orderItemsPayload
      };

      const apiOrderRes = await createOrder(dbOrderPayload);
      if (apiOrderRes.success) {
        setLogs(prev => [...prev, { status: 'Order finalized! Bids automatically requested from regional distributors.' }]);
      }

      setCurrentOrder(finalizedOrder);
      setOrders(prev => [finalizedOrder, ...prev]);
      setCart([]);
      setIsProcessing(false);
    } catch (error) {
      console.error(error);
      setLogs(prev => [...prev, { status: `Error: ${error instanceof Error ? error.message : 'Unknown error'}` }]);
      setIsProcessing(false);
    }
  };

  const loadCustomerOrders = async () => {
    if (!currentUser) return;
    try {
      const profileRes = await getProfile();
      const customerId = profileRes?.data?.userId;
      if (customerId) {
        const res = await getCustomerOrders(customerId);
        if (res.success) {
          setOrders(res.data.data);
        }
      }
    } catch (e) {
      console.error('Error fetching user orders', e);
    }
  };

  useEffect(() => {
    if (currentView === ViewState.ORDERS && currentUser) {
      loadCustomerOrders();
    }
  }, [currentView, currentUser]);

  // Load Admin reports data
  const loadReports = async () => {
    try {
      const statsRes = await getCategoryStats();
      if (statsRes.success) setCatStats(statsRes.data);

      const distRes = await getDistributors();
      if (distRes.success) setAllDists(distRes.data);
    } catch (err) {
      console.error(err);
    }
  };

  useEffect(() => {
    if (currentView === ViewState.ADMIN_REPORTS) {
      loadReports();
    }
  }, [currentView]);

  return (
    <div className="min-h-screen flex flex-col bg-[#faf6f0] text-[#332215] selection:bg-amber-100 selection:text-[#332215]">
      {/* Premium Header */}
      <header className="sticky top-0 z-50 bg-[#faf6f0]/90 backdrop-blur-md border-b border-[#eed9c4] h-20 px-6 lg:px-12 flex items-center justify-between shadow-sm">
        <div className="flex items-center gap-3 cursor-pointer" onClick={() => setCurrentView(ViewState.CATALOG)}>
          <div className="size-11 bg-amber-600/10 flex items-center justify-center rounded-xl text-amber-700">
            <span className="material-symbols-outlined text-3xl">king_bed</span>
          </div>
          <div>
            <h1 className="text-xl font-bold font-display text-[#2e1e12] tracking-tight">Cozy Comfort</h1>
            <p className="text-[10px] uppercase tracking-widest text-amber-700 font-bold">SOC supply chain client</p>
          </div>
        </div>

        {/* Dynamic Navigation */}
        <nav className="hidden md:flex items-center gap-6 text-xs font-bold uppercase tracking-wider">
          <button onClick={() => setCurrentView(ViewState.CATALOG)} className={`${currentView === ViewState.CATALOG ? 'text-amber-700 border-b-2 border-amber-700' : 'text-slate-500 hover:text-[#2e1e12]'} transition-all py-1.5`}>Store</button>
          
          {currentUser && currentUser.role === 'Customer' && (
            <button onClick={() => setCurrentView(ViewState.ORDERS)} className={`${currentView === ViewState.ORDERS ? 'text-amber-700 border-b-2 border-amber-700' : 'text-slate-500 hover:text-[#2e1e12]'} transition-all py-1.5`}>My Orders</button>
          )}

          {currentUser && currentUser.role === 'Admin' && (
            <div className="relative group py-1.5">
              <button className="text-slate-500 hover:text-[#2e1e12] flex items-center gap-1">
                Admin Panel <span className="material-symbols-outlined text-sm">arrow_drop_down</span>
              </button>
              <div className="absolute top-8 left-0 w-48 bg-white border border-[#eed9c4] rounded-xl shadow-lg p-2 hidden group-hover:block animate-fade-in text-[10px] tracking-normal">
                <button onClick={() => setCurrentView(ViewState.ADMIN_DASHBOARD)} className="w-full text-left px-3 py-2 rounded-lg hover:bg-amber-50 text-slate-700 font-bold">Dashboard</button>
                <button onClick={() => setCurrentView(ViewState.ADMIN_PRODUCTS)} className="w-full text-left px-3 py-2 rounded-lg hover:bg-amber-50 text-slate-700 font-bold">Products Manager</button>
                <button onClick={() => setCurrentView(ViewState.ADMIN_DISTRIBUTORS)} className="w-full text-left px-3 py-2 rounded-lg hover:bg-amber-50 text-slate-700 font-bold">Distributors Manager</button>
                <button onClick={() => setCurrentView(ViewState.ADMIN_ORDERS)} className="w-full text-left px-3 py-2 rounded-lg hover:bg-amber-50 text-slate-700 font-bold">Orders & Bids</button>
                <button onClick={() => setCurrentView(ViewState.ADMIN_REPORTS)} className="w-full text-left px-3 py-2 rounded-lg hover:bg-amber-50 text-slate-700 font-bold">Logistics Reports</button>
              </div>
            </div>
          )}

          <button onClick={() => setCurrentView(ViewState.TESTING)} className={`${currentView === ViewState.TESTING ? 'text-amber-700 border-b-2 border-amber-700' : 'text-slate-500 hover:text-[#2e1e12]'} transition-all py-1.5`}>Diagnostics</button>
          <button onClick={() => setCurrentView(ViewState.ARCHITECTURE)} className={`${currentView === ViewState.ARCHITECTURE ? 'text-amber-700 border-b-2 border-amber-700' : 'text-slate-500 hover:text-[#2e1e12]'} transition-all py-1.5`}>Theory</button>
        </nav>

        {/* User Account Controls */}
        <div className="flex items-center gap-6">
          <button onClick={() => setCurrentView(ViewState.CART)} className="relative p-2 text-slate-500 hover:text-[#2e1e12] transition-colors">
            <span className="material-symbols-outlined">shopping_bag</span>
            {cart.length > 0 && (
              <span className="absolute -top-1 -right-1 bg-amber-600 text-white text-[9px] font-bold w-5 h-5 flex items-center justify-center rounded-full border border-[#faf6f0]">
                {cart.reduce((a, b) => a + b.quantity, 0)}
              </span>
            )}
          </button>

          {currentUser ? (
            <div className="flex items-center gap-3">
              <div className="hidden lg:block text-right">
                <p className="text-xs font-bold text-[#2e1e12]">{currentUser.firstName} {currentUser.lastName}</p>
                <p className="text-[9px] text-amber-700 uppercase font-bold tracking-wider">{currentUser.role}</p>
              </div>
              <button onClick={handleLogout} className="border border-[#eed9c4] hover:bg-red-50 hover:border-red-200 text-slate-600 hover:text-red-700 px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                <span className="material-symbols-outlined text-sm">logout</span> Logout
              </button>
            </div>
          ) : (
            <button onClick={() => setCurrentView(ViewState.LOGIN)} className="bg-amber-600 hover:bg-amber-750 text-white px-5 py-2.5 rounded-xl text-xs font-bold font-display shadow-md shadow-amber-600/10 transition-all flex items-center gap-1.5">
              <span className="material-symbols-outlined text-sm">login</span> Sign In
            </button>
          )}
        </div>
      </header>

      {/* Admin Panel Sub-Navbar */}
      {currentUser && currentUser.role === 'Admin' && (
        <div className="bg-amber-50/60 backdrop-blur-sm border-b border-[#eed9c4] px-6 lg:px-12 py-2 flex items-center justify-between text-xs font-bold transition-all shadow-sm">
          <div className="max-w-[1440px] w-full mx-auto flex items-center justify-between">
            <div className="flex items-center gap-2 text-amber-850">
              <span className="material-symbols-outlined text-sm font-bold">admin_panel_settings</span>
              <span className="uppercase tracking-wider text-[10px] hidden sm:inline">Admin Panel:</span>
            </div>
            <div className="flex gap-2 sm:gap-4 overflow-x-auto scrollbar-hide py-1">
              <button
                onClick={() => setCurrentView(ViewState.ADMIN_DASHBOARD)}
                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-all ${
                  currentView === ViewState.ADMIN_DASHBOARD
                    ? 'bg-amber-600 text-white shadow-sm'
                    : 'text-slate-650 hover:bg-amber-100/50 hover:text-slate-800'
                }`}
              >
                <span className="material-symbols-outlined text-sm">dashboard</span> Dashboard
              </button>
              <button
                onClick={() => setCurrentView(ViewState.ADMIN_PRODUCTS)}
                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-all ${
                  currentView === ViewState.ADMIN_PRODUCTS
                    ? 'bg-amber-600 text-white shadow-sm'
                    : 'text-slate-650 hover:bg-amber-100/50 hover:text-slate-800'
                }`}
              >
                <span className="material-symbols-outlined text-sm">shopping_bag</span> Products
              </button>
              <button
                onClick={() => setCurrentView(ViewState.ADMIN_DISTRIBUTORS)}
                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-all ${
                  currentView === ViewState.ADMIN_DISTRIBUTORS
                    ? 'bg-amber-600 text-white shadow-sm'
                    : 'text-slate-650 hover:bg-amber-100/50 hover:text-slate-800'
                }`}
              >
                <span className="material-symbols-outlined text-sm">local_shipping</span> Distributors
              </button>
              <button
                onClick={() => setCurrentView(ViewState.ADMIN_ORDERS)}
                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-all ${
                  currentView === ViewState.ADMIN_ORDERS
                    ? 'bg-amber-600 text-white shadow-sm'
                    : 'text-slate-650 hover:bg-amber-100/50 hover:text-slate-800'
                }`}
              >
                <span className="material-symbols-outlined text-sm">receipt_long</span> Orders & Bids
              </button>
              <button
                onClick={() => setCurrentView(ViewState.ADMIN_REPORTS)}
                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-all ${
                  currentView === ViewState.ADMIN_REPORTS
                    ? 'bg-amber-600 text-white shadow-sm'
                    : 'text-slate-650 hover:bg-amber-100/50 hover:text-slate-800'
                }`}
              >
                <span className="material-symbols-outlined text-sm">assessment</span> Reports
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Main Container */}
      <main className="flex-grow w-full max-w-[1440px] mx-auto px-6 lg:px-12 py-12">
        
        {/* View Router */}
        {currentView === ViewState.CATALOG && (
          <div className="space-y-12 animate-fade-in">
            {/* Cozy Hero Section */}
            <section className="relative overflow-hidden rounded-3xl bg-white border border-[#eed9c4] p-8 md:p-12 flex flex-col lg:flex-row items-center gap-12 shadow-sm">
              <div className="relative z-10 max-w-xl space-y-6 flex-1">
                <span className="inline-block px-3 py-1 bg-amber-50 border border-amber-200 text-amber-700 text-[10px] font-bold uppercase tracking-wider rounded-full">Premium Bedding Solutions</span>
                <h2 className="text-4xl md:text-5xl font-black text-[#2e1e12] leading-[1.1] font-display">Wrap Yourself In Pure <span className="text-amber-600 italic">Comfort</span></h2>
                <p className="text-sm text-slate-600 leading-relaxed">Orchestrating Seller inventories, regional Distributor shipping, and factory Manufacturer capacity lines via secure REST API workflows.</p>
                <div className="flex gap-4 pt-2">
                  <button onClick={() => document.getElementById('catalog-grid')?.scrollIntoView({ behavior: 'smooth' })} className="bg-amber-600 hover:bg-amber-750 text-white px-7 py-3.5 rounded-xl font-bold font-display shadow-lg shadow-amber-600/20 transition-all flex items-center gap-2 text-xs">
                    Explore Catalog <span className="material-symbols-outlined text-sm">arrow_downward</span>
                  </button>
                  <button onClick={() => setCurrentView(ViewState.ARCHITECTURE)} className="bg-slate-100 hover:bg-slate-200 text-slate-700 px-7 py-3.5 rounded-xl font-bold text-xs transition-all border border-slate-200">
                    SOA Architecture Specs
                  </button>
                </div>
              </div>
              <div className="flex-1 w-full lg:max-w-md relative">
                <div className="absolute -inset-4 bg-amber-600/5 blur-3xl rounded-full"></div>
                <img src="https://images.unsplash.com/photo-1580301762395-21ce84d00bc6?auto=format&fit=crop&w=600&q=80" alt="Cozy setup" className="relative z-10 rounded-2xl border border-[#eed9c4] shadow-md hover:scale-[1.01] transition-all duration-500 mx-auto" />
              </div>
            </section>

            {/* Filter bar */}
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pt-4 border-t border-[#eed9c4]/60">
              <div className="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
                <button
                  onClick={() => handleCategorySelect('All')}
                  className={`px-5 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all border ${selectedCategory === 'All' ? 'bg-amber-600 border-amber-600 text-white' : 'bg-white border-[#eed9c4] text-slate-600 hover:border-amber-600/40'}`}
                >
                  All Blankets
                </button>
                {categories.map(cat => (
                  <button
                    key={cat}
                    onClick={() => handleCategorySelect(cat)}
                    className={`px-5 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all border ${selectedCategory === cat ? 'bg-amber-600 border-amber-600 text-white' : 'bg-white border-[#eed9c4] text-slate-600 hover:border-amber-600/40'}`}
                  >
                    {cat}
                  </button>
                ))}
              </div>
              <form onSubmit={handleSearch} className="flex gap-2 w-full md:w-80">
                <div className="relative flex-1">
                  <input 
                    type="text" 
                    value={searchTerm} 
                    onChange={e => setSearchTerm(e.target.value)}
                    placeholder="Search blankets..." 
                    className="w-full bg-white border border-[#eed9c4] rounded-xl pl-4 pr-10 py-2 text-xs focus:outline-none"
                  />
                  {searchTerm && (
                    <button
                      type="button"
                      onClick={() => setSearchTerm('')}
                      className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 flex items-center justify-center size-5 rounded-full hover:bg-slate-100 transition-colors"
                      aria-label="Clear search"
                    >
                      <span className="material-symbols-outlined text-[16px] font-bold">close</span>
                    </button>
                  )}
                </div>
                <button type="submit" className="bg-[#2e1e12] hover:bg-[#422c1b] text-white px-4 py-2 rounded-xl text-xs font-bold transition-all">Search</button>
              </form>
            </div>

            {/* Catalog Grid */}
            {loadingProducts ? (
              <div className="flex justify-center items-center py-20">
                <span className="material-symbols-outlined text-4xl text-primary animate-spin">sync</span>
                <span className="ml-2 font-display text-slate-500">Loading catalog items...</span>
              </div>
            ) : (
              <div id="catalog-grid" className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                {catalogProducts.map(p => (
                  <div 
                    key={p.productId} 
                    onClick={() => setSelectedProduct(p)}
                    className="group bg-white border border-[#eed9c4]/80 rounded-2xl overflow-hidden hover:border-amber-600/50 hover:cursor-pointer transition-all hover:-translate-y-1 shadow-sm"
                  >
                    <div className="aspect-[4/3] bg-slate-50 relative overflow-hidden">
                      <img src={p.imageUrl || 'https://images.unsplash.com/photo-1580301762395-21ce84d00bc6?q=80&w=400'} alt={p.name} className="w-full h-full object-cover group-hover:scale-105 transition-all duration-500" />
                    </div>
                    <div className="p-5 space-y-4">
                      <div>
                        <p className="text-[9px] font-bold text-amber-700 uppercase tracking-wider">{p.category}</p>
                        <h3 className="text-base font-bold text-[#2e1e12] font-display line-clamp-1">{p.name}</h3>
                      </div>
                      <p className="text-xs text-slate-500 h-10 line-clamp-2 leading-relaxed">{p.description}</p>
                      <div className="flex justify-between items-center pt-3 border-t border-slate-100" onClick={e => e.stopPropagation()}>
                        <span className="text-base font-bold text-[#2e1e12]">Rs. {p.basePrice?.toLocaleString()}</span>
                        <button 
                          onClick={() => addToCart(p)} 
                          className="bg-amber-600/10 hover:bg-amber-600 text-amber-800 hover:text-white px-3.5 py-1.5 rounded-lg font-bold transition-all flex items-center gap-1.5 text-[10px]"
                        >
                          <span className="material-symbols-outlined text-xs">add_shopping_cart</span> Add
                        </button>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}

        {currentView === ViewState.LOGIN && (
          <div className="max-w-md mx-auto bg-white border border-[#eed9c4] rounded-3xl p-8 shadow-sm space-y-6 animate-fade-in">
            <div className="text-center">
              <h2 className="text-2xl font-bold font-display text-[#2e1e12]">Welcome Back</h2>
              <p className="text-slate-400 text-xs mt-1">Log in to place orders and manage invoices.</p>
            </div>
            <form onSubmit={handleLoginSubmit} className="space-y-4 text-xs">
              <div className="space-y-1">
                <label className="font-bold text-slate-500 uppercase">Email Address</label>
                <input 
                  type="email" 
                  value={loginEmail} 
                  onChange={e => setLoginEmail(e.target.value)} 
                  required
                  className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:outline-none"
                  placeholder="name@example.com"
                />
              </div>
              <div className="space-y-1">
                <label className="font-bold text-slate-500 uppercase">Password</label>
                <input 
                  type="password" 
                  value={loginPassword} 
                  onChange={e => setLoginPassword(e.target.value)} 
                  required
                  className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:outline-none"
                  placeholder="******"
                />
              </div>
              <button type="submit" className="w-full bg-amber-600 hover:bg-amber-750 text-white font-display font-bold py-3.5 rounded-xl transition-all shadow-md shadow-amber-600/15">
                Login
              </button>
            </form>
            <div className="text-center text-xs text-slate-500 border-t border-slate-100 pt-4">
              Don't have an account? <button onClick={() => setCurrentView(ViewState.REGISTER)} className="text-amber-700 font-bold hover:underline">Register here</button>
            </div>
          </div>
        )}

        {currentView === ViewState.REGISTER && (
          <div className="max-w-md mx-auto bg-white border border-[#eed9c4] rounded-3xl p-8 shadow-sm space-y-6 animate-fade-in">
            <div className="text-center">
              <h2 className="text-2xl font-bold font-display text-[#2e1e12]">Create Account</h2>
              <p className="text-slate-400 text-xs mt-1">Sign up to buy blankets and track logistics.</p>
            </div>
            <form onSubmit={handleRegisterSubmit} className="space-y-4 text-xs">
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1">
                  <label className="font-bold text-slate-500 uppercase">First Name</label>
                  <input 
                    type="text" 
                    value={regFirstName} 
                    onChange={e => setRegFirstName(e.target.value)} 
                    required
                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:outline-none"
                  />
                </div>
                <div className="space-y-1">
                  <label className="font-bold text-slate-500 uppercase">Last Name</label>
                  <input 
                    type="text" 
                    value={regLastName} 
                    onChange={e => setRegLastName(e.target.value)} 
                    required
                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:outline-none"
                  />
                </div>
              </div>

              <div className="space-y-1">
                <label className="font-bold text-slate-500 uppercase">Email Address</label>
                <input 
                  type="email" 
                  value={regEmail} 
                  onChange={e => setRegEmail(e.target.value)} 
                  required
                  className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:outline-none"
                  placeholder="email@example.com"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1">
                  <label className="font-bold text-slate-500 uppercase">Password</label>
                  <input 
                    type="password" 
                    value={regPassword} 
                    onChange={e => setRegPassword(e.target.value)} 
                    required
                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:outline-none"
                    placeholder="Min 6 chars"
                  />
                </div>
                <div className="space-y-1">
                  <label className="font-bold text-slate-500 uppercase">Confirm Password</label>
                  <input 
                    type="password" 
                    value={regConfirmPassword} 
                    onChange={e => setRegConfirmPassword(e.target.value)} 
                    required
                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:outline-none"
                  />
                </div>
              </div>

              <div className="space-y-1">
                <label className="font-bold text-slate-500 uppercase">Role Type</label>
                <select 
                  value={regRole} 
                  onChange={e => setRegRole(e.target.value)}
                  className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:outline-none"
                >
                  <option value="Customer">Customer</option>
                  <option value="Admin">Admin</option>
                </select>
              </div>

              <button type="submit" className="w-full bg-amber-600 hover:bg-amber-750 text-white font-display font-bold py-3.5 rounded-xl transition-all shadow-md shadow-amber-600/15">
                Register
              </button>
            </form>
            <div className="text-center text-xs text-slate-500 border-t border-slate-100 pt-4">
              Already have an account? <button onClick={() => setCurrentView(ViewState.LOGIN)} className="text-amber-700 font-bold hover:underline">Log in here</button>
            </div>
          </div>
        )}

        {currentView === ViewState.CHECKOUT_PROCESS && (
          <div className="grid lg:grid-cols-12 gap-12 animate-fade-in text-xs">
            <div className="lg:col-span-8 space-y-8">
              <div>
                <h2 className="text-2xl font-bold font-display text-slate-900 flex items-center gap-2">
                  <span className="material-symbols-outlined text-amber-700">settings_suggest</span>
                  SOC 3-Tier Fulfillment Trace
                </h2>
                <p className="text-slate-500">Checking stock levels and shipping constraints recursively down the supply chain.</p>
              </div>

              {/* Dynamic Console Logs */}
              <div className="bg-[#2e1e12] border border-[#eed9c4] rounded-2xl p-6 font-mono text-[10px] text-[#eed9c4] space-y-3.5 max-h-60 overflow-y-auto shadow-inner">
                <div className="flex items-center gap-2 text-amber-500 border-b border-[#eed9c4]/20 pb-2 mb-2">
                  <span className="h-2 w-2 bg-amber-500 rounded-full animate-ping"></span>
                  <span className="font-bold uppercase tracking-wider">Trace console log</span>
                </div>
                {logs.map((log, idx) => (
                  <p key={idx} className={log.status.startsWith('Error') ? 'text-red-400 font-semibold' : log.status.includes('complete') || log.status.includes('finalized') ? 'text-green-400 font-bold' : 'text-[#eed9c4]'}>
                    &gt; {log.status}
                  </p>
                ))}
                {isProcessing && (
                  <p className="text-slate-400 animate-pulse">&gt; Querying upstream database servers...</p>
                )}
              </div>

              {/* Interactive Supply Chain Trace Map */}
              <SupplyChainVisualizer traces={pendingTraces} totalLeadTime={totalLeadTime} />

              {isWaitingConfirmation && (
                <div className="bg-white border border-amber-600/30 rounded-2xl p-8 shadow-md">
                  <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                      <h3 className="text-xl font-bold text-slate-900 font-display">Orchestrated Fulfillment Path Approved</h3>
                      <p className="text-slate-500 mt-1">Your order can be fulfilled through the path visualised above.</p>
                    </div>
                    <div className="text-left sm:text-right bg-slate-50 px-4 py-2 border border-slate-200 rounded-xl">
                      <p className="text-[10px] text-slate-400 uppercase font-bold">Total Est. Lead Time</p>
                      <p className="text-xl font-bold font-display text-amber-700">{totalLeadTime} {totalLeadTime === 1 ? 'Day' : 'Days'}</p>
                    </div>
                  </div>

                  <div className="flex gap-4">
                    <button onClick={confirmOrder} className="flex-1 bg-amber-600 hover:bg-amber-700 text-white py-3.5 rounded-xl font-bold font-display tracking-wide transition-all flex items-center justify-center gap-2">
                      <span className="material-symbols-outlined text-sm">check_circle</span> Commit & Finalize Order
                    </button>
                    <button onClick={() => setCurrentView(ViewState.CART)} className="px-8 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-all border border-slate-200">
                      Cancel
                    </button>
                  </div>
                </div>
              )}
            </div>

            {/* Side summary panel */}
            <div className="lg:col-span-4 space-y-6">
              <div className="bg-white border border-[#eed9c4] rounded-2xl p-6 space-y-6 shadow-sm">
                <h3 className="font-bold text-slate-800 border-b border-slate-100 pb-4 font-display text-[10px] uppercase tracking-wider">Bag Items</h3>
                <div className="space-y-4">
                  {cart.map((item, idx) => {
                    const finalItem = currentOrder?.items.find((i: any) => i.productId === item.blanket.id);
                    return (
                      <div key={idx} className="p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <div className="flex justify-between items-center mb-2">
                          <span className="font-bold text-slate-800">{item.blanket.name}</span>
                          <span className="text-slate-400">Qty: {item.quantity}</span>
                        </div>
                        <div className="flex justify-between items-center pt-2 border-t border-slate-200 text-[10px]">
                          <div className="flex flex-col">
                            <span className="text-slate-400 uppercase">Unit Lead Time</span>
                            <span className="font-bold text-slate-600">
                              {finalItem ? `${finalItem.leadTimeDays} days` : 'Calculating...'}
                            </span>
                          </div>
                          <div className="text-right">
                            <span className="text-slate-400 uppercase">Subtotal</span>
                            <p className="text-amber-700 font-bold">Rs. {(item.blanket.basePrice * item.quantity).toLocaleString()}</p>
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>

                {currentOrder && (
                  <button onClick={() => setCurrentView(ViewState.ORDERS)} className="w-full bg-[#2e1e12] py-3.5 rounded-xl font-bold font-display text-white tracking-wide hover:bg-[#422c1b] transition-all mt-4">
                    View Invoices & Fulfillment Logs
                  </button>
                )}
              </div>
            </div>
          </div>
        )}

        {currentView === ViewState.CART && (
          <div className="max-w-4xl mx-auto space-y-8 animate-fade-in text-xs">
            <h2 className="text-3xl font-bold font-display text-slate-900">Your Shopping Bag</h2>
            {cart.length === 0 ? (
              <div className="bg-white p-20 text-center border border-[#eed9c4] rounded-3xl space-y-4">
                <span className="material-symbols-outlined text-5xl text-slate-300">shopping_bag</span>
                <p className="text-slate-500 font-medium">Your shopping bag is currently empty.</p>
                <button onClick={() => setCurrentView(ViewState.CATALOG)} className="text-amber-700 font-bold font-display hover:underline">Browse blankets here</button>
              </div>
            ) : (
              <div className="grid lg:grid-cols-3 gap-10">
                <div className="lg:col-span-2 space-y-4">
                  {cart.map(item => (
                    <div key={item.blanket.id} className="bg-white p-5 rounded-2xl border border-[#eed9c4] flex justify-between items-center shadow-sm">
                      <div className="flex gap-4 items-center">
                        <img src={item.blanket.image} className="size-16 rounded object-cover border border-slate-100" />
                        <div>
                          <h4 className="font-bold text-slate-800 font-display">{item.blanket.name}</h4>
                          <p className="text-[10px] text-slate-400">Rs. {item.blanket.basePrice.toLocaleString()} each</p>
                        </div>
                      </div>
                      <div className="flex items-center gap-6">
                        <div className="flex items-center gap-3">
                          <button onClick={() => setCart(prev => prev.map(i => i.blanket.id === item.blanket.id ? { ...i, quantity: Math.max(1, i.quantity - 1) } : i))} className="size-8 bg-slate-50 rounded border border-slate-200 text-slate-600 hover:text-slate-900 font-bold transition-all">-</button>
                          <span className="font-bold text-slate-800">{item.quantity}</span>
                          <button onClick={() => setCart(prev => prev.map(i => i.blanket.id === item.blanket.id ? { ...i, quantity: i.quantity + 1 } : i))} className="size-8 bg-slate-50 rounded border border-slate-200 text-slate-600 hover:text-slate-900 font-bold transition-all">+</button>
                        </div>
                        <button onClick={() => setCart(prev => prev.filter(i => i.blanket.id !== item.blanket.id))} className="text-red-500 hover:text-red-700">
                          <span className="material-symbols-outlined">delete</span>
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
                <div className="bg-white p-8 rounded-2xl border border-[#eed9c4] h-fit sticky top-28 space-y-6 shadow-sm">
                  <h3 className="font-bold font-display border-b border-slate-100 pb-2 text-slate-800 text-sm">Checkout Summary</h3>
                  <div className="space-y-4 text-xs">
                    <div className="flex justify-between text-slate-500"><span>Bag Subtotal</span><span>Rs. {cartTotal.toLocaleString(undefined, { minimumFractionDigits: 2 })}</span></div>
                    <div className="flex justify-between font-bold text-base pt-4 border-t border-slate-100 text-slate-900"><span>Total Cost</span><span className="text-amber-700 font-display">Rs. {cartTotal.toLocaleString(undefined, { minimumFractionDigits: 2 })}</span></div>
                  </div>

                  <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 flex items-center gap-2">
                    <span className="material-symbols-outlined text-amber-600">route</span>
                    <p className="text-[10px] text-slate-500 leading-normal">
                      <strong>3-Tier Check Sequence:</strong> Seller ➡️ Distributor ➡️ Cozy Comfort Factory
                    </p>
                  </div>

                  <button onClick={handleCheckout} className="w-full bg-amber-600 py-3.5 rounded-xl font-bold font-display text-white shadow-lg shadow-amber-600/25 hover:bg-amber-750 transition-all text-xs">
                    Initiate SOC Stock Check
                  </button>
                </div>
              </div>
            )}
          </div>
        )}

        {currentView === ViewState.ORDERS && (
          <div className="max-w-4xl mx-auto space-y-8 animate-fade-in text-xs">
            <h2 className="text-3xl font-bold font-display text-slate-900">Fulfillment Invoices</h2>
            {orders.length === 0 ? (
              <div className="bg-white border border-[#eed9c4] rounded-3xl p-20 text-center text-slate-400 space-y-4">
                <span className="material-symbols-outlined text-4xl text-slate-300">receipt_long</span>
                <p>No orders placed under this account yet.</p>
              </div>
            ) : (
              orders.map(order => (
                <div key={order.orderId || order.id} className="bg-white rounded-2xl border border-[#eed9c4] overflow-hidden shadow-sm">
                  <div className="p-6 bg-slate-50 flex flex-col sm:flex-row justify-between border-b border-[#eed9c4] gap-4">
                    <div className="flex flex-wrap gap-8">
                      <div>
                        <p className="text-[10px] text-slate-400 uppercase font-bold">Order ID</p>
                        <p className="font-mono text-xs text-amber-700 font-bold">{order.orderReference || order.id}</p>
                      </div>
                      <div>
                        <p className="text-[10px] text-slate-400 uppercase font-bold">Fulfillment Date</p>
                        <p className="text-xs text-slate-700">{order.orderDate ? new Date(order.orderDate).toLocaleDateString() : new Date(order.createdAt).toLocaleDateString()}</p>
                      </div>
                      {order.notes && (
                        <div>
                          <p className="text-[10px] text-slate-400 uppercase font-bold">Fulfillment Logistics Details</p>
                          <p className="text-xs text-slate-700 font-medium">{order.notes}</p>
                        </div>
                      )}
                    </div>
                    <div className="sm:text-right">
                      <p className="text-[10px] text-slate-400 uppercase font-bold">Status</p>
                      <span className="text-green-700 text-[10px] font-bold px-2.5 py-1 bg-green-50 rounded border border-green-200">{order.status}</span>
                    </div>
                  </div>
                  <div className="p-6">
                    {(order.orderItems || order.items || []).map((item: any, i: number) => (
                      <div key={i} className="grid grid-cols-12 gap-4 py-4 text-xs border-b border-slate-100 last:border-0 items-center">
                        <div className="col-span-6 font-medium text-slate-800">
                          {item.productName || item.blanket?.name} <span className="text-slate-400 text-[10px] ml-1">(x{item.quantity})</span>
                        </div>
                        <div className="col-span-6 text-right font-bold text-slate-800">
                          Rs. {((item.unitPrice || 0) * item.quantity).toLocaleString()}
                        </div>
                      </div>
                    ))}
                    <div className="mt-6 pt-6 border-t border-[#eed9c4] flex justify-between items-center">
                      <button 
                        onClick={() => {
                          const element = document.createElement("a");
                          const file = new Blob([JSON.stringify(order, null, 2)], { type: 'application/json' });
                          element.href = URL.createObjectURL(file);
                          element.download = `cozy-invoice-${order.orderReference || order.id}.json`;
                          document.body.appendChild(element);
                          element.click();
                          document.body.removeChild(element);
                        }} 
                        className="text-[10px] text-amber-700 font-bold flex items-center gap-1 hover:underline"
                      >
                        <span className="material-symbols-outlined text-xs">download</span> Export JSON Invoice
                      </button>
                      <p className="text-lg font-bold text-[#2e1e12] font-display">Total Paid: Rs. {order.totalAmount?.toLocaleString(undefined, { minimumFractionDigits: 2 })}</p>
                    </div>
                  </div>
                </div>
              ))
            )}
          </div>
        )}

        {currentView === ViewState.TESTING && <TestDashboard />}
        {currentView === ViewState.ARCHITECTURE && <TheoryView />}

        {/* Admin Panels */}
        {currentView === ViewState.ADMIN_DASHBOARD && <AdminDashboard />}
        {currentView === ViewState.ADMIN_PRODUCTS && <AdminProducts />}
        {currentView === ViewState.ADMIN_DISTRIBUTORS && <AdminDistributors />}
        {currentView === ViewState.ADMIN_ORDERS && <AdminOrders />}
        
        {currentView === ViewState.ADMIN_REPORTS && (
          <div className="space-y-10 animate-fade-in text-slate-800 text-xs">
            <div>
              <h2 className="text-3xl font-bold font-display text-slate-900">Logistics Bidding Reports</h2>
              <p className="text-slate-500 text-sm mt-1">Comparative summaries of supplier bid response speeds and category breakdowns.</p>
            </div>

            <div className="grid md:grid-cols-2 gap-8">
              {/* Category stats */}
              <div className="bg-white border border-border-dark rounded-3xl p-6 shadow-sm space-y-6">
                <div>
                  <h3 className="font-display font-bold text-base text-slate-900 flex items-center gap-2">
                    <span className="material-symbols-outlined text-primary">pie_chart</span>
                    Sales Category Volume
                  </h3>
                  <p className="text-[10px] text-slate-400">Total volume and revenue mapped per catalog category.</p>
                </div>

                <div className="space-y-4">
                  {catStats.length === 0 ? (
                    <p className="text-slate-400 text-center py-6">No sales logs found.</p>
                  ) : (
                    catStats.map((cat: any, idx: number) => (
                      <div key={idx} className="p-4 bg-slate-50 border border-slate-100 rounded-2xl flex justify-between items-center">
                        <div>
                          <p className="font-bold text-slate-800 text-xs font-display">{cat.category}</p>
                          <p className="text-[10px] text-slate-400">Product Count: {cat.productCount}</p>
                        </div>
                        <div className="text-right">
                          <p className="font-bold text-slate-800 font-mono">Rs. {cat.totalValue?.toLocaleString() || '0.00'}</p>
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>

              {/* Distributors bidding status list */}
              <div className="bg-white border border-border-dark rounded-3xl p-6 shadow-sm space-y-6">
                <div>
                  <h3 className="font-display font-bold text-base text-slate-900 flex items-center gap-2">
                    <span className="material-symbols-outlined text-primary">bar_chart</span>
                    Supplier Performance Logs
                  </h3>
                  <p className="text-[10px] text-slate-400">Active quotation bids success statistics.</p>
                </div>

                <div className="space-y-4">
                  {allDists.length === 0 ? (
                    <p className="text-slate-400 text-center py-6 font-medium">No active supplier configurations.</p>
                  ) : (
                    allDists.map((d: any) => {
                      const rating = d.rating || 4.5;
                      return (
                        <div key={d.distributorId} className="p-4 bg-slate-50 border border-slate-100 rounded-2xl flex justify-between items-center">
                          <div>
                            <p className="font-bold text-slate-800 text-xs font-display">{d.name}</p>
                            <p className="text-[10px] text-slate-400">Fulfillment orders processed: {d.totalOrders}</p>
                          </div>
                          <div className="text-right space-y-1">
                            <p className="font-bold text-slate-700">Rating: ⭐ {rating.toFixed(1)}/5</p>
                            <p className="text-[9px] uppercase tracking-wider text-green-700 font-bold bg-green-50 px-2 py-0.5 rounded border border-green-200">
                              Success: {d.successRate || 100}%
                            </p>
                          </div>
                        </div>
                      );
                    })
                  )}
                </div>
              </div>
            </div>
          </div>
        )}

      </main>

      {/* Premium Brand Footer */}
      <footer className="bg-[#2e1e12] text-[#eed9c4]/70 border-t border-amber-950 py-12 px-6 lg:px-12 mt-20">
        <div className="max-w-[1440px] mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
          <div className="space-y-4">
            <div className="flex items-center gap-3">
              <div className="size-9 bg-amber-600/10 flex items-center justify-center rounded-xl text-amber-500 border border-amber-900/40">
                <span className="material-symbols-outlined text-xl">king_bed</span>
              </div>
              <h3 className="text-lg font-bold font-display text-[#faf6f0] tracking-tight">Cozy Comfort</h3>
            </div>
            <p className="text-[11px] leading-relaxed text-[#eed9c4]/50">
              Orchestrating premium home decor supply chains using Service-Oriented Computing architectures.
            </p>
          </div>
          <div>
            <h4 className="text-xs font-bold uppercase tracking-wider text-[#faf6f0] mb-4">Quick Links</h4>
            <div className="flex flex-col gap-2.5 text-[11px]">
              <button onClick={() => setCurrentView(ViewState.CATALOG)} className="text-left hover:text-amber-500 transition-colors">Blanket Store</button>
              <button onClick={() => setCurrentView(ViewState.TESTING)} className="text-left hover:text-amber-500 transition-colors">Diagnostics Suite</button>
              <button onClick={() => setCurrentView(ViewState.ARCHITECTURE)} className="text-left hover:text-amber-500 transition-colors">Architecture Theory</button>
            </div>
          </div>
          <div>
            <h4 className="text-xs font-bold uppercase tracking-wider text-[#faf6f0] mb-4">Supply Chain Stack</h4>
            <div className="flex flex-col gap-2 text-[10px] uppercase font-bold text-[#eed9c4]/40">
              <span className="flex items-center gap-1.5"><span className="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Seller Tier (Retail)</span>
              <span className="flex items-center gap-1.5"><span className="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Distributor Tier (Logistics)</span>
              <span className="flex items-center gap-1.5"><span className="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Manufacturer Tier (Production)</span>
            </div>
          </div>
          <div>
            <h4 className="text-xs font-bold uppercase tracking-wider text-[#faf6f0] mb-4">SOC Architecture</h4>
            <p className="text-[10px] leading-relaxed text-[#eed9c4]/50">
              Built on React, ASP.NET Core, and SQLite, utilizing message routing protocols to coordinate inventory fulfillment.
            </p>
          </div>
        </div>
        <div className="max-w-[1440px] mx-auto border-t border-amber-950 mt-8 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-[10px] text-[#eed9c4]/40 font-bold uppercase tracking-widest">
          <span>&copy; 2026 Cozy Comfort Systems. All rights reserved.</span>
          <span>Designed under SOA &amp; SOC paradigms</span>
        </div>
      </footer>

      {/* Floating notifications toast */}
      {showToast && (
        <div className="fixed bottom-8 right-8 bg-[#2e1e12] text-[#eed9c4] font-bold font-display px-6 py-4 rounded-xl shadow-lg border border-[#eed9c4] animate-fade-in flex items-center gap-3 z-50 text-xs">
          <span className="material-symbols-outlined text-amber-500">check_circle</span>
          {toastMessage}
        </div>
      )}

      {/* Product Detail Modal */}
      {selectedProduct && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#2e1e12]/60 backdrop-blur-sm animate-fade-in" onClick={() => setSelectedProduct(null)}>
          <div 
            className="bg-white border border-[#eed9c4] rounded-3xl overflow-hidden shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col md:flex-row animate-fade-in relative"
            onClick={e => e.stopPropagation()}
          >
            {/* Close Button */}
            <button 
              onClick={() => setSelectedProduct(null)} 
              className="absolute top-4 right-4 z-10 bg-white/80 hover:bg-white text-slate-800 p-2 rounded-full border border-slate-200 shadow-md transition-all flex items-center justify-center size-9 hover:scale-105"
              aria-label="Close details"
            >
              <span className="material-symbols-outlined text-xl">close</span>
            </button>

            {/* Product Image Frame (Auto-sized based on image aspect ratio) */}
            <div className="flex-1 bg-slate-50 flex items-center justify-center p-6 border-b md:border-b-0 md:border-r border-[#eed9c4]/50 max-h-[40vh] md:max-h-none md:w-1/2">
              <img 
                src={selectedProduct.imageUrl || 'https://images.unsplash.com/photo-1580301762395-21ce84d00bc6?q=80&w=600'} 
                alt={selectedProduct.name} 
                className="w-full max-w-md h-auto max-h-[30vh] md:max-h-[60vh] object-contain rounded-2xl shadow-sm transition-transform hover:scale-[1.01]"
              />
            </div>

            {/* Product Details Section */}
            <div className="flex-1 p-6 md:p-8 flex flex-col justify-between overflow-y-auto max-h-[50vh] md:max-h-[80vh] md:w-1/2 space-y-6">
              <div className="space-y-4">
                <div>
                  <span className="px-3 py-1 bg-amber-50 border border-amber-200 text-amber-700 text-[10px] font-bold uppercase tracking-wider rounded-full">
                    {selectedProduct.category}
                  </span>
                  <h3 className="text-xl md:text-2xl font-black text-[#2e1e12] font-display mt-2.5 leading-tight">
                    {selectedProduct.name}
                  </h3>
                </div>

                <p className="text-xs text-slate-600 leading-relaxed">
                  {selectedProduct.description}
                </p>

                {/* Specifications List */}
                <div className="space-y-2.5 pt-4 border-t border-slate-100">
                  <h4 className="text-xs font-bold text-[#2e1e12] uppercase tracking-wider">Specifications</h4>
                  <div className="grid grid-cols-2 gap-3 text-[11px]">
                    {Object.entries(getMockSpecifications(selectedProduct)).map(([key, val]) => (
                      <div key={key} className="p-2.5 bg-slate-50 border border-slate-100 rounded-xl space-y-0.5 animate-fade-in">
                        <span className="text-[9px] uppercase font-bold text-slate-400 block">{key}</span>
                        <span className="font-bold text-slate-700">{val as string}</span>
                      </div>
                    ))}
                    {Object.keys(getMockSpecifications(selectedProduct)).length === 0 && (
                      <div className="col-span-2 text-slate-400 text-xs italic">No specifications listed.</div>
                    )}
                  </div>
                </div>
              </div>

              {/* Price and Add to Cart Action */}
              <div className="pt-6 border-t border-slate-100 flex items-center justify-between gap-4">
                <div className="space-y-0.5">
                  <span className="text-[9px] uppercase font-bold text-slate-400">Base Price</span>
                  <p className="text-xl font-black text-[#2e1e12]">
                    Rs. {selectedProduct.basePrice?.toLocaleString()}
                  </p>
                </div>
                <button 
                  onClick={() => {
                    addToCart(selectedProduct);
                    setSelectedProduct(null);
                  }}
                  className="bg-amber-600 hover:bg-amber-750 text-white px-6 py-3 rounded-xl font-bold font-display shadow-md shadow-amber-600/10 transition-all flex items-center gap-2 text-xs"
                >
                  <span className="material-symbols-outlined text-sm">add_shopping_cart</span> Add To Cart
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default App;
