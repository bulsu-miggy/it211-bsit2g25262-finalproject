import { Upload, ArrowLeft } from 'lucide-react';
import { useNavigate } from 'react-router';

export function AddProductPage() {
  const navigate = useNavigate();

  return (
    <div className="space-y-8">
      {/* Header */}
      <div className="flex items-center gap-4">
        <button 
          onClick={() => navigate('/products')}
          className="p-2 hover:bg-gray-100 rounded transition-colors"
        >
          <ArrowLeft className="w-5 h-5 text-gray-600" />
        </button>
        <div>
          <h1 className="text-3xl text-gray-800 mb-2">Add New Product</h1>
          <p className="text-gray-600">Fill in the product details below</p>
        </div>
      </div>

      {/* Form */}
      <div className="border-2 border-gray-300 bg-white rounded p-8">
        <div className="space-y-6 max-w-4xl">
          {/* Product Image */}
          <div>
            <label className="block text-sm text-gray-700 mb-2">Product Images</label>
            <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 transition-colors cursor-pointer">
              <div className="flex flex-col items-center gap-3">
                <div className="w-16 h-16 bg-gray-100 border-2 border-gray-300 rounded flex items-center justify-center">
                  <Upload className="w-8 h-8 text-gray-400" />
                </div>
                <div>
                  <p className="text-gray-700">Click to upload or drag and drop</p>
                  <p className="text-sm text-gray-500 mt-1">PNG, JPG up to 10MB</p>
                </div>
              </div>
            </div>
          </div>

          {/* Product Name */}
          <div>
            <label className="block text-sm text-gray-700 mb-2">Product Name *</label>
            <input 
              type="text" 
              placeholder="Enter product name"
              className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
            />
          </div>

          {/* Description */}
          <div>
            <label className="block text-sm text-gray-700 mb-2">Description</label>
            <textarea 
              rows={4}
              placeholder="Enter product description"
              className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 resize-none placeholder:text-gray-400"
            />
          </div>

          {/* Price and SKU */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm text-gray-700 mb-2">Price *</label>
              <div className="relative">
                <span className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-600">$</span>
                <input 
                  type="text" 
                  placeholder="0.00"
                  className="w-full border-2 border-gray-300 pl-8 pr-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
                />
              </div>
            </div>
            <div>
              <label className="block text-sm text-gray-700 mb-2">SKU</label>
              <input 
                type="text" 
                placeholder="PROD-001"
                className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
              />
            </div>
          </div>

          {/* Category and Stock */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm text-gray-700 mb-2">Category *</label>
              <select className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 bg-white text-gray-700">
                <option value="">Select category</option>
                <option value="electronics">Electronics</option>
                <option value="accessories">Accessories</option>
                <option value="clothing">Clothing</option>
                <option value="home">Home & Garden</option>
                <option value="sports">Sports & Outdoors</option>
              </select>
            </div>
            <div>
              <label className="block text-sm text-gray-700 mb-2">Stock Quantity *</label>
              <input 
                type="number" 
                placeholder="0"
                className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
              />
            </div>
          </div>

          {/* Weight and Dimensions */}
          <div>
            <label className="block text-sm text-gray-700 mb-3">Dimensions (optional)</label>
            <div className="grid grid-cols-3 gap-4">
              <div>
                <input 
                  type="text" 
                  placeholder="Length"
                  className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
                />
                <p className="text-xs text-gray-500 mt-1">cm</p>
              </div>
              <div>
                <input 
                  type="text" 
                  placeholder="Width"
                  className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
                />
                <p className="text-xs text-gray-500 mt-1">cm</p>
              </div>
              <div>
                <input 
                  type="text" 
                  placeholder="Height"
                  className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
                />
                <p className="text-xs text-gray-500 mt-1">cm</p>
              </div>
            </div>
          </div>

          {/* Tags */}
          <div>
            <label className="block text-sm text-gray-700 mb-2">Tags</label>
            <input 
              type="text" 
              placeholder="e.g. wireless, portable, premium"
              className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
            />
            <p className="text-xs text-gray-500 mt-1">Separate tags with commas</p>
          </div>

          {/* Status */}
          <div>
            <label className="flex items-center gap-3 cursor-pointer">
              <input 
                type="checkbox" 
                defaultChecked
                className="w-5 h-5 border-2 border-gray-300 rounded cursor-pointer"
              />
              <span className="text-gray-700">Set as active product</span>
            </label>
          </div>
        </div>
      </div>

      {/* Action Buttons */}
      <div className="flex items-center justify-end gap-3">
        <button 
          onClick={() => navigate('/products')}
          className="border-2 border-gray-300 bg-white text-gray-700 px-6 py-2.5 rounded hover:bg-gray-100 transition-colors"
        >
          Cancel
        </button>
        <button 
          className="border-2 border-gray-800 bg-gray-800 text-white px-6 py-2.5 rounded hover:bg-gray-700 transition-colors"
        >
          Add Product
        </button>
      </div>
    </div>
  );
}
