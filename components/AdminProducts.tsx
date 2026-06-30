import React, { useState, useEffect } from 'react';
import { getProducts, createProduct, updateProduct, deleteProduct, getCategories } from '../services/apiService';

const AdminProducts: React.FC = () => {
  const [products, setProducts] = useState<any[]>([]);
  const [categories, setCategories] = useState<string[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Form State
  const [isEditing, setIsEditing] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [category, setCategory] = useState('');
  const [basePrice, setBasePrice] = useState('');
  const [imageUrl, setImageUrl] = useState('');
  const [specs, setSpecs] = useState<Array<{ key: string; value: string }>>([
    { key: 'material', value: '' },
    { key: 'dimensions', value: '' }
  ]);

  const loadProducts = async () => {
    setLoading(true);
    try {
      const res = await getProducts({ pageSize: 100 });
      if (res.success) {
        setProducts(res.data.data);
      } else {
        setError(res.message || 'Failed to load products');
      }

      const catRes = await getCategories();
      if (catRes.success) {
        setCategories(catRes.data);
      }
    } catch (e: any) {
      setError(e.message || 'Error occurred while loading products');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadProducts();
  }, []);

  const handleAddSpecRow = () => {
    setSpecs(prev => [...prev, { key: '', value: '' }]);
  };

  const handleRemoveSpecRow = (idx: number) => {
    setSpecs(prev => prev.filter((_, i) => i !== idx));
  };

  const handleSpecChange = (idx: number, field: 'key' | 'value', val: string) => {
    setSpecs(prev => prev.map((spec, i) => i === idx ? { ...spec, [field]: val } : spec));
  };

  const resetForm = () => {
    setIsEditing(false);
    setEditingId(null);
    setName('');
    setDescription('');
    setCategory('');
    setBasePrice('');
    setImageUrl('');
    setSpecs([
      { key: 'material', value: '' },
      { key: 'dimensions', value: '' }
    ]);
  };

  const handleEditClick = (product: any) => {
    setIsEditing(true);
    setEditingId(product.productId);
    setName(product.name);
    setDescription(product.description || '');
    setCategory(product.category || '');
    setBasePrice(product.basePrice.toString());
    setImageUrl(product.imageUrl || '');

    // Map specs dictionary to array
    const specsDict = product.specifications || {};
    const specsArr = Object.entries(specsDict).map(([k, v]) => ({ key: k, value: String(v) }));
    setSpecs(specsArr.length > 0 ? specsArr : [{ key: 'material', value: '' }]);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name || !basePrice || !category) {
      alert('Please fill in Name, Category, and Price');
      return;
    }

    // Convert specs array to dictionary object
    const specsObj: Record<string, string> = {};
    specs.forEach(s => {
      if (s.key.trim()) {
        specsObj[s.key.trim()] = s.value.trim();
      }
    });

    const payload = {
      name,
      description,
      category,
      basePrice: parseFloat(basePrice),
      imageUrl,
      specifications: specsObj
    };

    try {
      let res;
      if (isEditing && editingId !== null) {
        res = await updateProduct(editingId, payload);
      } else {
        res = await createProduct(payload);
      }

      if (res.success) {
        alert(isEditing ? 'Product updated successfully' : 'Product created successfully');
        resetForm();
        loadProducts();
      } else {
        alert(res.message || 'Action failed');
      }
    } catch (err: any) {
      alert(err.message || 'Error occurred during save');
    }
  };

  const handleDeleteClick = async (id: number) => {
    if (!confirm('Are you sure you want to delete this product?')) return;

    try {
      const res = await deleteProduct(id);
      if (res.success) {
        alert('Product deleted successfully');
        loadProducts();
      } else {
        alert(res.message || 'Delete failed');
      }
    } catch (err: any) {
      alert(err.message || 'Error deleting product');
    }
  };

  return (
    <div className="space-y-10 animate-fade-in text-slate-800">
      <div>
        <h2 className="text-3xl font-bold font-display text-slate-900">Manage Blanket Catalog</h2>
        <p className="text-slate-500 text-sm mt-1">Add, update, or remove blanket items from the online store.</p>
      </div>

      <div className="grid lg:grid-cols-12 gap-8 items-start">
        {/* Editor Form Panel */}
        <div className="lg:col-span-4 bg-white border border-border-dark rounded-3xl p-6 shadow-sm space-y-6">
          <h3 className="font-bold font-display text-lg text-slate-900">
            {isEditing ? 'Edit Product' : 'Add New Product'}
          </h3>

          <form onSubmit={handleSubmit} className="space-y-4 text-xs">
            <div className="space-y-1">
              <label className="font-bold text-slate-500 uppercase">Product Title</label>
              <input 
                type="text" 
                value={name} 
                onChange={e => setName(e.target.value)} 
                required 
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:outline-none" 
                placeholder="e.g. Luxurious Wool Throw"
              />
            </div>

            <div className="space-y-1">
              <label className="font-bold text-slate-500 uppercase">Category</label>
              <input 
                type="text" 
                value={category} 
                onChange={e => setCategory(e.target.value)} 
                required 
                list="cats-list"
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:outline-none" 
                placeholder="e.g. Weighted Blankets"
              />
              <datalist id="cats-list">
                {categories.map(c => <option key={c} value={c} />)}
              </datalist>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="font-bold text-slate-500 uppercase">Base Price (Rs.)</label>
                <input 
                  type="number" 
                  value={basePrice} 
                  onChange={e => setBasePrice(e.target.value)} 
                  required 
                  min="1"
                  className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:outline-none" 
                  placeholder="3500"
                />
              </div>
              <div className="space-y-1">
                <label className="font-bold text-slate-500 uppercase">Image URL</label>
                <input 
                  type="text" 
                  value={imageUrl} 
                  onChange={e => setImageUrl(e.target.value)} 
                  className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:outline-none" 
                  placeholder="https://unsplash.com/..."
                />
              </div>
            </div>

            <div className="space-y-1">
              <label className="font-bold text-slate-500 uppercase">Description</label>
              <textarea 
                value={description} 
                onChange={e => setDescription(e.target.value)} 
                rows={3}
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:bg-white focus:outline-none" 
                placeholder="Blanket description..."
              />
            </div>

            {/* Specifications Key-Value rows */}
            <div className="space-y-2 pt-2 border-t border-slate-100">
              <div className="flex justify-between items-center">
                <label className="font-bold text-slate-500 uppercase">Specifications</label>
                <button type="button" onClick={handleAddSpecRow} className="text-primary font-bold hover:underline flex items-center gap-0.5">
                  <span className="material-symbols-outlined text-sm">add</span> Row
                </button>
              </div>
              <div className="space-y-2">
                {specs.map((s, idx) => (
                  <div key={idx} className="flex gap-2 items-center">
                    <input 
                      type="text" 
                      value={s.key} 
                      onChange={e => handleSpecChange(idx, 'key', e.target.value)} 
                      placeholder="Spec name (e.g. weight)" 
                      className="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:outline-none"
                    />
                    <input 
                      type="text" 
                      value={s.value} 
                      onChange={e => handleSpecChange(idx, 'value', e.target.value)} 
                      placeholder="Value" 
                      className="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:outline-none"
                    />
                    <button type="button" onClick={() => handleRemoveSpecRow(idx)} className="text-red-500 hover:text-red-700">
                      <span className="material-symbols-outlined text-sm">delete</span>
                    </button>
                  </div>
                ))}
              </div>
            </div>

            <div className="flex gap-3 pt-4">
              <button type="submit" className="flex-1 bg-primary hover:bg-primary-dark text-white font-display font-bold py-3 rounded-xl transition-all">
                {isEditing ? 'Update Product' : 'Create Product'}
              </button>
              {isEditing && (
                <button type="button" onClick={resetForm} className="px-5 bg-slate-100 hover:bg-slate-200 border border-slate-200 font-bold rounded-xl text-slate-700">
                  Cancel
                </button>
              )}
            </div>
          </form>
        </div>

        {/* Catalog Table List Panel */}
        <div className="lg:col-span-8 bg-white border border-border-dark rounded-3xl overflow-hidden shadow-sm">
          {loading ? (
            <div className="p-12 text-center text-slate-400">
              <span className="material-symbols-outlined text-4xl animate-spin text-primary mb-2">sync</span>
              <p>Loading blanket catalog...</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse text-xs">
                <thead>
                  <tr className="bg-slate-50 border-b border-border-dark">
                    <th className="p-4 font-bold text-slate-700 uppercase font-display">Blanket</th>
                    <th className="p-4 font-bold text-slate-700 uppercase font-display">Category</th>
                    <th className="p-4 font-bold text-slate-700 uppercase font-display text-right">Base Price</th>
                    <th className="p-4 font-bold text-slate-700 uppercase font-display text-center">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {products.length === 0 ? (
                    <tr>
                      <td colSpan={4} className="p-12 text-center text-slate-450">No blankets registered in the database.</td>
                    </tr>
                  ) : (
                    products.map(p => (
                      <tr key={p.productId} className="hover:bg-slate-50/50 transition-colors">
                        <td className="p-4">
                          <div className="flex gap-3 items-center">
                            <img src={p.imageUrl || 'https://images.unsplash.com/photo-1580301762395-21ce84d00bc6?q=80&w=150'} className="size-10 rounded object-cover border border-slate-200" />
                            <div>
                              <p className="font-bold text-slate-800 font-display">{p.name}</p>
                              <p className="text-[10px] text-slate-400 truncate max-w-xs">{p.description}</p>
                            </div>
                          </div>
                        </td>
                        <td className="p-4">
                          <span className="px-2.5 py-0.5 bg-slate-100 text-slate-600 rounded-full font-semibold border border-slate-200">
                            {p.category}
                          </span>
                        </td>
                        <td className="p-4 text-right font-bold text-slate-800">
                          Rs. {p.basePrice?.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                        </td>
                        <td className="p-4 text-center">
                          <div className="flex justify-center gap-2">
                            <button onClick={() => handleEditClick(p)} className="p-1.5 text-primary hover:bg-amber-50 rounded-lg border border-amber-200">
                              <span className="material-symbols-outlined text-sm">edit</span>
                            </button>
                            <button onClick={() => handleDeleteClick(p.productId)} className="p-1.5 text-red-500 hover:bg-red-50 rounded-lg border border-red-200">
                              <span className="material-symbols-outlined text-sm">delete</span>
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))
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

export default AdminProducts;
