# React Integration Guide

## API Client Setup

### 1. Install Axios
```bash
npm install axios
```

### 2. Create API Client
```javascript
// src/api/client.js
import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'https://your-api.com/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add auth token to requests
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('admin_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle errors
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('admin_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default apiClient;
```


## Products API Service

```javascript
// src/api/products.js
import apiClient from './client';

export const productsAPI = {
  // List products
  getAll: (params) => 
    apiClient.get('/admin/products', { params }),

  // Get single product
  getOne: (id) => 
    apiClient.get(`/admin/products/${id}`),

  // Create product
  create: (data) => {
    const formData = new FormData();
    Object.keys(data).forEach(key => {
      if (key === 'images' && Array.isArray(data[key])) {
        data[key].forEach(file => formData.append('images[]', file));
      } else if (typeof data[key] === 'object') {
        Object.keys(data[key]).forEach(lang => {
          formData.append(`${key}[${lang}]`, data[key][lang]);
        });
      } else {
        formData.append(key, data[key]);
      }
    });
    return apiClient.post('/admin/products', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
  },

  // Update product
  update: (id, data) => 
    apiClient.put(`/admin/products/${id}`, data),

  // Delete product
  delete: (id) => 
    apiClient.delete(`/admin/products/${id}`),
};
```


## React Component Example

```javascript
// src/components/Products/ProductList.jsx
import React, { useState, useEffect } from 'react';
import { productsAPI } from '../../api/products';

function ProductList() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [pagination, setPagination] = useState({});
  const [filters, setFilters] = useState({
    page: 1,
    per_page: 10,
    search: '',
  });

  useEffect(() => {
    fetchProducts();
  }, [filters]);

  const fetchProducts = async () => {
    try {
      setLoading(true);
      const response = await productsAPI.getAll(filters);
      setProducts(response.data.data.items);
      setPagination(response.data.data.pagination);
    } catch (error) {
      console.error('Error fetching products:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure?')) {
      try {
        await productsAPI.delete(id);
        fetchProducts();
      } catch (error) {
        console.error('Error deleting product:', error);
      }
    }
  };

  return (
    <div>
      <h1>Products</h1>
      {/* Add your UI here */}
    </div>
  );
}

export default ProductList;
```
