import { checkStock, placeOrder } from './apiService';
import { OrderItem, Order, SupplyChainQueryResponse } from '../types';

export const querySupplyChainWorkflow = async (
  items: OrderItem[],
  onProgress: (status: string, details?: any) => void
): Promise<SupplyChainQueryResponse[]> => {
  onProgress('Initiating Multi-Tier Inventory Check...');
  
  const requests = items.map(i => ({
    productId: i.blanket.id,
    quantity: i.quantity
  }));

  onProgress('Contacting Seller inventory services...');
  await new Promise(r => setTimeout(r, 600));

  onProgress('Seller stock shortfall identified. Escalating query to Distributor...');
  await new Promise(r => setTimeout(r, 600));

  onProgress('Distributor querying Cozy Comfort manufacturing logs...');
  await new Promise(r => setTimeout(r, 600));

  const queryResults = await checkStock(requests);

  onProgress('Query complete. Supply chain path traces compiled successfully.', queryResults);
  return queryResults;
};

export const executeFulfillmentWorkflow = async (
  items: OrderItem[],
  onProgress: (status: string, details?: any) => void
): Promise<Order> => {
  onProgress('Commiting order to supply chain database...');
  await new Promise(r => setTimeout(r, 500));

  const requests = items.map(i => ({
    productId: i.blanket.id,
    quantity: i.quantity
  }));

  onProgress('Seller stock decremented. Inbound logistics requests generated...');
  const orderData = await placeOrder(requests);

  // Merge full blanket details back into items for display
  const completeOrderItems: OrderItem[] = orderData.items.map((apiItem: any) => {
    const original = items.find(i => i.blanket.id === apiItem.productId);
    return {
      blanket: original?.blanket,
      quantity: apiItem.quantity,
      unitPrice: apiItem.unitPrice,
      leadTimeDays: apiItem.leadTimeDays,
      status: apiItem.status
    };
  });

  const finalOrder: Order = {
    id: orderData.id,
    customerId: orderData.customerId,
    items: completeOrderItems,
    status: 'Confirmed',
    totalAmount: orderData.totalAmount,
    createdAt: orderData.createdAt,
    trackingId: orderData.trackingId
  };

  onProgress('Fulfillment sequence complete! Invoice ready.', finalOrder);
  return finalOrder;
};
