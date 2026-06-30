import { SupplyChainQueryResponse } from '../types';

const API_BASE_URL = 'http://localhost:5262/api';

// Helper to get request headers with auth token
const getHeaders = (contentType: string = 'application/json') => {
  const headers: Record<string, string> = {};
  if (contentType) {
    headers['Content-Type'] = contentType;
  }
  const token = localStorage.getItem('cozy_comfort_token');
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }
  return headers;
};

// Generic response handler
const handleResponse = async (response: Response) => {
  if (!response.ok) {
    const errorText = await response.text();
    let errorMessage = `API request failed with status ${response.status}`;
    try {
      const errorJson = JSON.parse(errorText);
      if (errorJson.message) errorMessage = errorJson.message;
      else if (errorJson.errors && errorJson.errors.length > 0) errorMessage = errorJson.errors.join(', ');
    } catch {
      if (errorText) errorMessage = errorText;
    }
    throw new Error(errorMessage);
  }

  // Handle 204 No Content or empty response
  if (response.status === 204) return null;
  const text = await response.text();
  return text ? JSON.parse(text) : null;
};

// ==========================================
// 1. Authentication Services
// ==========================================
export const login = async (credentials: any) => {
  const response = await fetch(`${API_BASE_URL}/Auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(credentials)
  });
  const res = await handleResponse(response);
  if (res.success && res.data?.token) {
    localStorage.setItem('cozy_comfort_token', res.data.token);
    localStorage.setItem('cozy_comfort_user', JSON.stringify({
      email: res.data.email,
      firstName: res.data.firstName,
      lastName: res.data.lastName,
      role: res.data.role
    }));
  }
  return res;
};

export const register = async (userData: any) => {
  const response = await fetch(`${API_BASE_URL}/Auth/register`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(userData)
  });
  return await handleResponse(response);
};

export const logout = () => {
  localStorage.removeItem('cozy_comfort_token');
  localStorage.removeItem('cozy_comfort_user');
};

export const getCurrentUser = () => {
  const userStr = localStorage.getItem('cozy_comfort_user');
  return userStr ? JSON.parse(userStr) : null;
};

export const getProfile = async () => {
  const response = await fetch(`${API_BASE_URL}/Auth/profile`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

// ==========================================
// 2. Product Catalog Services (CRUD)
// ==========================================
export const getProducts = async (params: { category?: string; searchTerm?: string; page?: number; pageSize?: number } = {}) => {
  const query = new URLSearchParams();
  if (params.category && params.category !== 'All') query.append('Category', params.category);
  if (params.searchTerm) query.append('Search', params.searchTerm);
  if (params.page) query.append('Page', params.page.toString());
  if (params.pageSize) query.append('PageSize', params.pageSize.toString());

  const response = await fetch(`${API_BASE_URL}/Products?${query.toString()}`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

export const getProductById = async (id: number) => {
  const response = await fetch(`${API_BASE_URL}/Products/${id}`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

export const getCategories = async () => {
  const response = await fetch(`${API_BASE_URL}/Products/categories`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

export const getCategoryStats = async () => {
  const response = await fetch(`${API_BASE_URL}/Products/categories/stats`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

export const createProduct = async (productData: any) => {
  const response = await fetch(`${API_BASE_URL}/Products`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(productData)
  });
  return await handleResponse(response);
};

export const updateProduct = async (id: number, productData: any) => {
  const response = await fetch(`${API_BASE_URL}/Products/${id}`, {
    method: 'PUT',
    headers: getHeaders(),
    body: JSON.stringify(productData)
  });
  return await handleResponse(response);
};

export const deleteProduct = async (id: number) => {
  const response = await fetch(`${API_BASE_URL}/Products/${id}`, {
    method: 'DELETE',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

// ==========================================
// 3. Order Management Services (CRUD & Bidding)
// ==========================================
export const createOrder = async (orderData: any) => {
  const response = await fetch(`${API_BASE_URL}/Orders`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(orderData)
  });
  return await handleResponse(response);
};

export const getOrderById = async (id: number) => {
  const response = await fetch(`${API_BASE_URL}/Orders/${id}`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

export const getCustomerOrders = async (customerId: number, page: number = 1, pageSize: number = 10) => {
  const response = await fetch(`${API_BASE_URL}/Orders/customer/${customerId}?page=${page}&pageSize=${pageSize}`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

export const getAllOrders = async (page: number = 1, pageSize: number = 10, status?: string) => {
  const query = new URLSearchParams({ page: page.toString(), pageSize: pageSize.toString() });
  if (status) query.append('status', status);

  const response = await fetch(`${API_BASE_URL}/Orders?${query.toString()}`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

export const updateOrderStatus = async (id: number, status: string, notes?: string) => {
  const response = await fetch(`${API_BASE_URL}/Orders/${id}/status`, {
    method: 'PATCH',
    headers: getHeaders(),
    body: JSON.stringify({ status, notes })
  });
  return await handleResponse(response);
};

export const getDashboardStats = async () => {
  const response = await fetch(`${API_BASE_URL}/Orders/dashboard-stats`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

// ==========================================
// 4. Distributors & Quotations Bidding Services
// ==========================================
export const getDistributors = async () => {
  const response = await fetch(`${API_BASE_URL}/Distributors`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

export const getDistributorById = async (id: number) => {
  const response = await fetch(`${API_BASE_URL}/Distributors/${id}`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

export const createDistributor = async (distData: any) => {
  const response = await fetch(`${API_BASE_URL}/Distributors`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(distData)
  });
  return await handleResponse(response);
};

export const updateDistributor = async (id: number, distData: any) => {
  const response = await fetch(`${API_BASE_URL}/Distributors/${id}`, {
    method: 'PUT',
    headers: getHeaders(),
    body: JSON.stringify(distData)
  });
  return await handleResponse(response);
};

export const deleteDistributor = async (id: number) => {
  const response = await fetch(`${API_BASE_URL}/Distributors/${id}`, {
    method: 'DELETE',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

export const testDistributorAvailability = async (id: number) => {
  const response = await fetch(`${API_BASE_URL}/Distributors/${id}/availability`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

export const getDistributorPerformance = async (id: number, startDate?: string, endDate?: string) => {
  const query = new URLSearchParams();
  if (startDate) query.append('startDate', startDate);
  if (endDate) query.append('endDate', endDate);

  const response = await fetch(`${API_BASE_URL}/Distributors/${id}/performance?${query.toString()}`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

// Quotations Endpoints
export const getQuotationComparison = async (orderId: number) => {
  const response = await fetch(`${API_BASE_URL}/Quotations/${orderId}/compare`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

export const selectDistributor = async (orderId: number) => {
  const response = await fetch(`${API_BASE_URL}/Quotations/${orderId}/select-distributor`, {
    method: 'POST',
    headers: getHeaders()
  });
  return await handleResponse(response);
};

// ==========================================
// 5. 3-Tier Supply Chain Fulfillment / Diagnostics
// ==========================================
export const checkStock = async (
  items: { productId: string; quantity: number }[]
): Promise<SupplyChainQueryResponse[]> => {
  const response = await fetch(`${API_BASE_URL}/Fulfillment/check-stock`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(items),
  });
  return await handleResponse(response);
};

export const placeFulfillmentOrder = async (
  items: { productId: string; quantity: number }[]
): Promise<any> => {
  const response = await fetch(`${API_BASE_URL}/Fulfillment/place-order`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(items),
  });
  return await handleResponse(response);
};

export const resetInventory = async (): Promise<void> => {
  const response = await fetch(`${API_BASE_URL}/Fulfillment/reset`, {
    method: 'POST',
    headers: getHeaders(),
  });
  await handleResponse(response);
};

export const placeOrder = placeFulfillmentOrder;

export const getInventoryState = async (): Promise<{
  seller: Record<string, number>;
  distributor: Record<string, number>;
  manufacturer: Record<string, number>;
}> => {
  const response = await fetch(`${API_BASE_URL}/Fulfillment/inventory`, {
    method: 'GET',
    headers: getHeaders()
  });
  return await handleResponse(response);
};
