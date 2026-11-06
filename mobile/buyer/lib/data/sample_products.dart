import '../models/product.dart';

const List<Product> sampleProducts = [
  Product(
    id: 1,
    name: 'Wireless Headphones',
    price: 79.99,
    imageUrl: 'https://picsum.photos/seed/headphones/300/300',
    category: 'Electronics',
    rating: 4.5,
    reviewCount: 128,
  ),
  Product(
    id: 2,
    name: 'Running Sneakers',
    price: 59.99,
    imageUrl: 'https://picsum.photos/seed/sneakers/300/300',
    category: 'Fashion',
    rating: 4.2,
    reviewCount: 85,
  ),
  Product(
    id: 3,
    name: 'Minimalist Watch',
    price: 129.99,
    imageUrl: 'https://picsum.photos/seed/watch/300/300',
    category: 'Fashion',
    rating: 4.8,
    reviewCount: 210,
  ),
  Product(
    id: 4,
    name: 'Portable Charger',
    price: 34.99,
    imageUrl: 'https://picsum.photos/seed/charger/300/300',
    category: 'Electronics',
    rating: 4.3,
    reviewCount: 67,
  ),
  Product(
    id: 5,
    name: 'Yoga Mat',
    price: 24.99,
    imageUrl: 'https://picsum.photos/seed/yoga/300/300',
    category: 'Sports',
    rating: 4.6,
    reviewCount: 154,
  ),
  Product(
    id: 6,
    name: 'Ceramic Coffee Mug',
    price: 14.99,
    imageUrl: 'https://picsum.photos/seed/mug/300/300',
    category: 'Home',
    rating: 4.1,
    reviewCount: 43,
  ),
];

const List<String> categories = [
  'All',
  'Electronics',
  'Fashion',
  'Sports',
  'Home',
];
