import { Blanket } from './types';

export const MOCK_BLANKETS: Blanket[] = [
  {
    id: 'blanket-001',
    name: 'Cozy Flannel Classic',
    material: 'Flannel',
    basePrice: 3500.00,
    image: 'https://images.unsplash.com/photo-1580301762395-21ce84d00bc6?auto=format&fit=crop&w=800&q=80',
    description: 'Ultra-soft flannel blanket perfect for crisp autumn nights. Highly breathable and lightweight.',
    category: 'Standard'
  },
  {
    id: 'blanket-002',
    name: 'Royal Sherpa Luxury',
    material: 'Sherpa',
    basePrice: 6500.00,
    image: 'https://images.unsplash.com/photo-1600121848594-d8644e57abab?auto=format&fit=crop&w=800&q=80',
    description: 'Thick, dual-sided sherpa blanket. Exquisitely plush and designed to lock in warmth.',
    category: 'Premium'
  },
  {
    id: 'blanket-003',
    name: 'Thermal Woolen Heavy',
    material: 'Wool',
    basePrice: 8500.00,
    image: 'https://images.unsplash.com/photo-1563245372-f21724e3856d?auto=format&fit=crop&w=800&q=80',
    description: '100% organic virgin wool. Heavyweight thermal blanket suitable for freezing winter climates.',
    category: 'Premium'
  },
  {
    id: 'blanket-004',
    name: 'Soft Fleece Lite',
    material: 'Fleece',
    basePrice: 2000.00,
    image: 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?auto=format&fit=crop&w=800&q=80',
    description: 'Cozy, quick-drying fleece throw blanket. Ideal for picnics, travel, and light layering.',
    category: 'Standard'
  },
  {
    id: 'blanket-005',
    name: 'Woven Cotton Breeze',
    material: 'Cotton',
    basePrice: 2800.00,
    image: 'https://images.unsplash.com/photo-1545048702-79362596cdc9?auto=format&fit=crop&w=800&q=80',
    description: '100% long-staple cotton blanket. Soft, light, and perfect for warm summer nights.',
    category: 'Standard'
  },
  {
    id: 'blanket-006',
    name: 'Premium Cashmere Throw',
    material: 'Cashmere',
    basePrice: 12500.00,
    image: 'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=800&q=80',
    description: 'Pure, luxurious cashmere throw. Extremely warm, lightweight, and incredibly soft to the touch.',
    category: 'Premium'
  },
  {
    id: 'blanket-007',
    name: 'All-Weather Microfiber',
    material: 'Microfiber',
    basePrice: 1800.00,
    image: 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=800&q=80',
    description: 'Highly durable and hypoallergenic microfiber blanket. Resists shrinking and wrinkling.',
    category: 'Standard'
  },
  {
    id: 'blanket-008',
    name: 'Quilted Down Comfort',
    material: 'Quilt',
    basePrice: 9500.00,
    image: 'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?auto=format&fit=crop&w=800&q=80',
    description: 'Baffle-box quilted blanket filled with premium white goose down. Ultimate loft and insulation.',
    category: 'Premium'
  },
  {
    id: 'blanket-009',
    name: 'Weighted Calm Pressure',
    material: 'Weighted',
    basePrice: 7500.00,
    image: 'https://images.unsplash.com/photo-1565793298595-6a879b1d9492?auto=format&fit=crop&w=800&q=80',
    description: 'Weighted glass bead blanket. Engineered to simulate deep pressure touch for anxiety relief.',
    category: 'Premium'
  },
  {
    id: 'blanket-010',
    name: 'Chenille Luxury Softness',
    material: 'Chenille',
    basePrice: 4200.00,
    image: 'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?auto=format&fit=crop&w=800&q=80',
    description: 'Plush chenille fabric with a velvety ribbed texture. Elegant accent for any bed or sofa.',
    category: 'Standard'
  }
];

export const INITIAL_STOCK = {
  seller: {
    'blanket-001': 5,
    'blanket-002': 2,
    'blanket-003': 2,
    'blanket-004': 8,
    'blanket-005': 6,
    'blanket-006': 1,
    'blanket-007': 10,
    'blanket-008': 2,
    'blanket-009': 3,
    'blanket-010': 4
  },
  distributor: {
    'blanket-001': 8,
    'blanket-002': 4,
    'blanket-003': 4,
    'blanket-004': 12,
    'blanket-005': 10,
    'blanket-006': 3,
    'blanket-007': 15,
    'blanket-008': 5,
    'blanket-009': 6,
    'blanket-010': 8
  },
  manufacturer: {
    'blanket-001': 10,
    'blanket-002': 5,
    'blanket-003': 5,
    'blanket-004': 15,
    'blanket-005': 12,
    'blanket-006': 4,
    'blanket-007': 20,
    'blanket-008': 6,
    'blanket-009': 8,
    'blanket-010': 10
  }
};
