import { Plus, Search, Grid3x3, List } from 'lucide-react';
import { useState } from 'react';
import { useNavigate } from 'react-router';

const products = [
  { id: 1, name: 'Wireless Headphones', category: 'Electronics', price: '$99.99', stock: 45, status: 'Active' },
  { id: 2, name: 'Smart Watch', category: 'Electronics', price: '$249.99', stock: 23, status: 'Active' },
  { id: 3, name: 'Laptop Sleeve', category: 'Accessories', price: '$29.99', stock: 120, status: 'Active' },
  { id: 4, name: 'USB-C Cable', category: 'Accessories', price: '$12.99', stock: 0, status: 'Out of Stock' },
  { id: 5, name: 'Bluetooth Speaker', category: 'Electronics', price: '$79.99', stock: 67, status: 'Active' },
  { id: 6, name: 'Phone Case', category: 'Accessories', price: '$19.99', stock: 234, status: 'Active' },
];

export function ProductsPage() {
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('list');
  const navigate = useNavigate();

  const handleDelete = (id: number, name: string) => {
    if (window.confirm(`Are you sure you want to delete "${name}"?`)) {
      // Handle delete logic here
      console.log('Delete product:', id);
    }
  };

  return (
    <div className="space-y-8">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl text-gray-800 mb-2">Products</h1>
          <p className="text-gray-600">Manage your product catalog</p>
        </div>
        <button 
          onClick={() => navigate('/products/add')}
          className="border-2 border-gray-800 bg-gray-800 text-white px-6 py-2.5 rounded hover:bg-gray-700 transition-colors flex items-center gap-2"
        >
          <Plus className="w-4 h-4" />
          Add Product
        </button>
      </div>

      {/* Filters and View Toggle */}
      <div className="border-2 border-gray-300 bg-white p-6 rounded">
        <div className="flex flex-col md:flex-row gap-4 items-center justify-between">
          <div className="flex-1 flex items-center gap-2 border-2 border-gray-300 bg-gray-50 px-4 py-2 rounded w-full md:w-auto">
            <Search className="w-4 h-4 text-gray-400" />
            <input 
              type="text" 
              placeholder="Search products..." 
              className="flex-1 bg-transparent outline-none text-gray-800"
            />
          </div>
          <div className="flex gap-2">
            <select className="border-2 border-gray-300 px-4 py-2 rounded bg-white text-gray-800 outline-none">
              <option>All Categories</option>
              <option>Electronics</option>
              <option>Accessories</option>
            </select>
            <div className="flex border-2 border-gray-300 rounded overflow-hidden">
              <button 
                onClick={() => setViewMode('grid')}
                className={`px-3 py-2 ${viewMode === 'grid' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'} transition-colors`}
              >
                <Grid3x3 className="w-4 h-4" />
              </button>
              <button 
                onClick={() => setViewMode('list')}
                className={`px-3 py-2 border-l-2 border-gray-300 ${viewMode === 'list' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'} transition-colors`}
              >
                <List className="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Products Display */}
      {viewMode === 'list' ? (
        <div className="border-2 border-gray-300 bg-white rounded overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="border-b-2 border-gray-300 bg-gray-50">
                <tr>
                  <th className="text-left px-6 py-4 text-sm text-gray-700">Product</th>
                  <th className="text-left px-6 py-4 text-sm text-gray-700">Category</th>
                  <th className="text-left px-6 py-4 text-sm text-gray-700">Price</th>
                  <th className="text-left px-6 py-4 text-sm text-gray-700">Stock</th>
                  <th className="text-left px-6 py-4 text-sm text-gray-700">Status</th>
                  <th className="text-left px-6 py-4 text-sm text-gray-700">Actions</th>
                </tr>
              </thead>
              <tbody>
                {products.map((product) => (
                  <tr key={product.id} className="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-3">
                        <div className="w-12 h-12 bg-gray-200 border-2 border-gray-300 rounded" />
                        <span className="text-gray-800">{product.name}</span>
                      </div>
                    </td>
                    <td className="px-6 py-4 text-gray-600">{product.category}</td>
                    <td className="px-6 py-4 text-gray-800">{product.price}</td>
                    <td className="px-6 py-4 text-gray-600">{product.stock}</td>
                    <td className="px-6 py-4">
                      <span className={`px-3 py-1 rounded text-sm border ${
                        product.status === 'Active' 
                          ? 'bg-green-100 text-green-800 border-green-300' 
                          : 'bg-red-100 text-red-800 border-red-300'
                      }`}>
                        {product.status}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <button 
                        onClick={() => navigate(`/products/${product.id}/edit`)}
                        className="text-gray-600 hover:text-gray-800 underline text-sm"
                      >
                        Edit
                      </button>
                      <button 
                        onClick={() => handleDelete(product.id, product.name)}
                        className="text-red-600 hover:text-red-800 underline text-sm ml-3"
                      >
                        Delete
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          {products.map((product) => (
            <div key={product.id} className="border-2 border-gray-300 bg-white rounded overflow-hidden hover:border-gray-400 transition-colors">
              <div className="aspect-square bg-gray-200 border-b-2 border-gray-300" />
              <div className="p-4 space-y-3">
                <h3 className="text-gray-800">{product.name}</h3>
                <p className="text-sm text-gray-600">{product.category}</p>
                <div className="flex items-center justify-between">
                  <span className="text-lg text-gray-800">{product.price}</span>
                  <span className="text-sm text-gray-600">Stock: {product.stock}</span>
                </div>
                <div className="flex gap-2">
                  <button 
                    onClick={() => navigate(`/products/${product.id}/edit`)}
                    className="flex-1 border-2 border-gray-800 bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 transition-colors"
                  >
                    Edit
                  </button>
                  <button 
                    onClick={() => handleDelete(product.id, product.name)}
                    className="border-2 border-red-300 bg-white text-red-600 px-4 py-2 rounded hover:bg-red-50 transition-colors"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}