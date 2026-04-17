import { Plus, Search } from 'lucide-react';
import { useNavigate } from 'react-router';

const categories = [
  { id: 1, name: 'Electronics', description: 'Electronic devices and gadgets', productCount: 24, status: 'Active' },
  { id: 2, name: 'Accessories', description: 'Phone cases, cables, and other accessories', productCount: 45, status: 'Active' },
  { id: 3, name: 'Clothing', description: 'Apparel and fashion items', productCount: 12, status: 'Active' },
  { id: 4, name: 'Home & Garden', description: 'Home decor and garden supplies', productCount: 8, status: 'Inactive' },
  { id: 5, name: 'Sports & Outdoors', description: 'Sports equipment and outdoor gear', productCount: 31, status: 'Active' },
];

export function CategoriesPage() {
  const navigate = useNavigate();

  const handleDelete = (id: number, name: string) => {
    if (window.confirm(`Are you sure you want to delete category "${name}"?`)) {
      console.log('Delete category:', id);
    }
  };

  return (
    <div className="space-y-8">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl text-gray-800 mb-2">Categories</h1>
          <p className="text-gray-600">Organize your products into categories</p>
        </div>
        <button 
          onClick={() => navigate('/categories/add')}
          className="border-2 border-gray-800 bg-gray-800 text-white px-6 py-2.5 rounded hover:bg-gray-700 transition-colors flex items-center gap-2"
        >
          <Plus className="w-4 h-4" />
          Add Category
        </button>
      </div>

      {/* Search */}
      <div className="border-2 border-gray-300 bg-white p-6 rounded">
        <div className="flex items-center gap-2 border-2 border-gray-300 bg-gray-50 px-4 py-2 rounded">
          <Search className="w-4 h-4 text-gray-400" />
          <input 
            type="text" 
            placeholder="Search categories..." 
            className="flex-1 bg-transparent outline-none text-gray-800"
          />
        </div>
      </div>

      {/* Categories Table */}
      <div className="border-2 border-gray-300 bg-white rounded overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="border-b-2 border-gray-300 bg-gray-50">
              <tr>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Category Name</th>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Description</th>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Products</th>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Status</th>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody>
              {categories.map((category) => (
                <tr key={category.id} className="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 bg-gray-200 border-2 border-gray-300 rounded" />
                      <span className="text-gray-800">{category.name}</span>
                    </div>
                  </td>
                  <td className="px-6 py-4 text-gray-600">{category.description}</td>
                  <td className="px-6 py-4 text-gray-600">{category.productCount}</td>
                  <td className="px-6 py-4">
                    <span className={`px-3 py-1 rounded text-sm border ${
                      category.status === 'Active' 
                        ? 'bg-green-100 text-green-800 border-green-300' 
                        : 'bg-gray-100 text-gray-800 border-gray-300'
                    }`}>
                      {category.status}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <button 
                      onClick={() => navigate(`/categories/${category.id}/edit`)}
                      className="text-gray-600 hover:text-gray-800 underline text-sm"
                    >
                      Edit
                    </button>
                    <button 
                      onClick={() => handleDelete(category.id, category.name)}
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
    </div>
  );
}
