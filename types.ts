export interface Blanket {
  id: string;
  name: string;
  material: string;
  basePrice: number;
  image: string;
  description: string;
  category: 'Standard' | 'Premium';
}

export interface FulfillmentTraceNode {
  tierName: string; // "Seller", "Distributor", "Manufacturer"
  checkedQuantity: number;
  availableFromStock: number;
  deficit: number;
  leadTimeDays: number;
  details: string;
}

export interface SupplyChainQueryResponse {
  productId: string;
  requestedQuantity: number;
  availableQuantity: number;
  totalLeadTimeDays: number;
  pricePerUnit: number;
  totalPrice: number;
  traces: FulfillmentTraceNode[];
}

export interface OrderItem {
  blanket: Blanket;
  quantity: number;
  unitPrice?: number;
  leadTimeDays?: number;
  status?: string;
}

export interface Order {
  id: string;
  customerId: string;
  items: OrderItem[];
  status: 'Pending' | 'Querying' | 'Orchestrating' | 'Confirmed' | 'Shipped' | 'Delivered';
  totalAmount: number;
  createdAt: string;
  trackingId?: string;
}

export interface TestResult {
  id: string;
  name: string;
  status: 'ready' | 'pending' | 'passed' | 'failed';
  message: string;
  duration: number;
}

export enum ViewState {
  CATALOG = 'catalog',
  CART = 'cart',
  CHECKOUT_PROCESS = 'checkout_process',
  ORDERS = 'orders',
  ARCHITECTURE = 'architecture',
  TESTING = 'testing',
  LOGIN = 'login',
  REGISTER = 'register',
  ADMIN_DASHBOARD = 'admin_dashboard',
  ADMIN_PRODUCTS = 'admin_products',
  ADMIN_DISTRIBUTORS = 'admin_distributors',
  ADMIN_ORDERS = 'admin_orders',
  ADMIN_REPORTS = 'admin_reports'
}
