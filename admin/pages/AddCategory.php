import { Upload, ArrowLeft, Save } from 'lucide-react';
import { useNavigate } from 'react-router';
import { useState } from 'react';

export function AddCategoryPage() {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    name: '',
    slug: '',
    description: '',
    parentCategory: '',
    metaTitle: '',
    metaDescription: '',
    isActive: true,
    isFeatured: false
  });

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value, type } = e.target;
    
    if (type === 'checkbox') {
      const checked = (e.target as HTMLInputElement).checked;
      setFormData(prev => ({ ...prev, [name]: checked }));
    } else {
      setFormData(prev => ({ ...prev, [name]: value }));
      
      // Auto-generate slug from name
      if (name === 'name') {
        const slug = value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
        setFormData(prev => ({ ...prev, slug }));
      }
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    console.log('Form submitted:', formData);
    // Here you would typically send the data to your backend
    // For now, we'll just navigate back to categories
    navigate('/categories');
  };

  const handleCancel = () => {
    if (window.confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
      navigate('/categories');
    }
  };

  return (
    <div className="space-y-8">
      {/* Header */}
      <div className="flex items-center gap-4">
        <button 
          onClick={() => navigate('/categories')}
          className="p-2 hover:bg-gray-100 rounded transition-colors"
        >
          <ArrowLeft className="w-5 h-5 text-gray-600" />
        </button>
        <div>
          <h1 className="text-3xl text-gray-800 mb-2">Add New Category</h1>
          <p className="text-gray-600">Create a new product category</p>
        </div>
      </div>

      <form onSubmit={handleSubmit}>
        {/* Main Form */}
        <div className="border-2 border-gray-300 bg-white rounded p-8 mb-8">
          <div className="space-y-6 max-w-4xl">
            {/* Category Icon/Image */}
            <div>
              <label className="block text-sm text-gray-700 mb-2">Category Icon</label>
              <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 transition-colors cursor-pointer">
                <div className="flex flex-col items-center gap-3">
                  <div className="w-16 h-16 bg-gray-100 border-2 border-gray-300 rounded flex items-center justify-center">
                    <Upload className="w-8 h-8 text-gray-400" />
                  </div>
                  <div>
                    <p className="text-gray-700">Click to upload or drag and drop</p>
                    <p className="text-sm text-gray-500 mt-1">PNG, JPG, SVG up to 5MB</p>
                  </div>
                </div>
              </div>
            </div>

            {/* Category Name */}
            <div>
              <label className="block text-sm text-gray-700 mb-2">Category Name *</label>
              <input 
                type="text" 
                name="name"
                value={formData.name}
                onChange={handleInputChange}
                placeholder="e.g. Electronics, Clothing, Home & Garden"
                className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
                required
              />
            </div>

            {/* Slug */}
            <div>
              <label className="block text-sm text-gray-700 mb-2">Slug *</label>
              <input 
                type="text" 
                name="slug"
                value={formData.slug}
                onChange={handleInputChange}
                placeholder="category-url-slug"
                className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400 font-mono text-sm"
                required
              />
              <p className="text-xs text-gray-500 mt-1">URL-friendly version of the name (auto-generated)</p>
            </div>

            {/* Description */}
            <div>
              <label className="block text-sm text-gray-700 mb-2">Description</label>
              <textarea 
                name="description"
                value={formData.description}
                onChange={handleInputChange}
                rows={4}
                placeholder="Brief description of this category..."
                className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 resize-none placeholder:text-gray-400"
              />
            </div>

            {/* Parent Category */}
            <div>
              <label className="block text-sm text-gray-700 mb-2">Parent Category</label>
              <select 
                name="parentCategory"
                value={formData.parentCategory}
                onChange={handleInputChange}
                className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 bg-white text-gray-700"
              >
                <option value="">None (Top Level Category)</option>
                <option value="electronics">Electronics</option>
                <option value="accessories">Accessories</option>
                <option value="clothing">Clothing</option>
                <option value="home">Home & Garden</option>
                <option value="sports">Sports & Outdoors</option>
              </select>
              <p className="text-xs text-gray-500 mt-1">Optional: Select a parent category to create a subcategory</p>
            </div>

            {/* SEO Section */}
            <div className="pt-6 border-t-2 border-gray-200">
              <h3 className="text-lg text-gray-800 mb-4">SEO Settings</h3>
              
              <div className="space-y-4">
                <div>
                  <label className="block text-sm text-gray-700 mb-2">Meta Title</label>
                  <input 
                    type="text" 
                    name="metaTitle"
                    value={formData.metaTitle}
                    onChange={handleInputChange}
                    placeholder="Category page title for search engines"
                    className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
                    maxLength={60}
                  />
                  <p className="text-xs text-gray-500 mt-1">{formData.metaTitle.length}/60 characters</p>
                </div>

                <div>
                  <label className="block text-sm text-gray-700 mb-2">Meta Description</label>
                  <textarea 
                    name="metaDescription"
                    value={formData.metaDescription}
                    onChange={handleInputChange}
                    rows={3}
                    placeholder="Brief description for search engine results"
                    className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 resize-none placeholder:text-gray-400"
                    maxLength={160}
                  />
                  <p className="text-xs text-gray-500 mt-1">{formData.metaDescription.length}/160 characters</p>
                </div>
              </div>
            </div>

            {/* Category Settings */}
            <div className="pt-6 border-t-2 border-gray-200 space-y-4">
              <h3 className="text-lg text-gray-800 mb-4">Category Settings</h3>
              
              <div>
                <label className="flex items-center gap-3 cursor-pointer">
                  <input 
                    type="checkbox" 
                    name="isActive"
                    checked={formData.isActive}
                    onChange={handleInputChange}
                    className="w-5 h-5 border-2 border-gray-300 rounded cursor-pointer"
                  />
                  <div>
                    <span className="text-gray-700">Active Category</span>
                    <p className="text-xs text-gray-500">Category will be visible on the storefront</p>
                  </div>
                </label>
              </div>

              <div>
                <label className="flex items-center gap-3 cursor-pointer">
                  <input 
                    type="checkbox" 
                    name="isFeatured"
                    checked={formData.isFeatured}
                    onChange={handleInputChange}
                    className="w-5 h-5 border-2 border-gray-300 rounded cursor-pointer"
                  />
                  <div>
                    <span className="text-gray-700">Featured Category</span>
                    <p className="text-xs text-gray-500">Display this category prominently on the homepage</p>
                  </div>
                </label>
              </div>
            </div>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="flex items-center justify-end gap-3">
          <button 
            type="button"
            onClick={handleCancel}
            className="border-2 border-gray-300 bg-white text-gray-700 px-6 py-2.5 rounded hover:bg-gray-100 transition-colors"
          >
            Cancel
          </button>
          <button 
            type="submit"
            className="border-2 border-gray-800 bg-gray-800 text-white px-6 py-2.5 rounded hover:bg-gray-700 transition-colors flex items-center gap-2"
          >
            <Save className="w-4 h-4" />
            Create Category
          </button>
        </div>
      </form>
    </div>
  );
}
