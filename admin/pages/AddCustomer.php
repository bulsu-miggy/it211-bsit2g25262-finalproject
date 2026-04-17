import { ArrowLeft, Upload } from 'lucide-react';
import { useNavigate } from 'react-router';

export function AddCustomerPage() {
  const navigate = useNavigate();

  return (
    <div className="space-y-8">
      {/* Header */}
      <div className="flex items-center gap-4">
        <button 
          onClick={() => navigate('/customers')}
          className="p-2 hover:bg-gray-100 rounded transition-colors"
        >
          <ArrowLeft className="w-5 h-5 text-gray-600" />
        </button>
        <div>
          <h1 className="text-3xl text-gray-800 mb-2">Add New Customer</h1>
          <p className="text-gray-600">Create a new customer profile</p>
        </div>
      </div>

      {/* Form */}
      <div className="border-2 border-gray-300 bg-white rounded p-8">
        <div className="space-y-6 max-w-4xl">
          {/* Profile Picture */}
          <div>
            <label className="block text-sm text-gray-700 mb-2">Profile Picture</label>
            <div className="flex items-center gap-4">
              <div className="w-24 h-24 rounded-full bg-gray-200 border-2 border-gray-300" />
              <button className="border-2 border-gray-300 bg-white text-gray-700 px-4 py-2 rounded hover:bg-gray-50 transition-colors flex items-center gap-2">
                <Upload className="w-4 h-4" />
                Upload Photo
              </button>
            </div>
          </div>

          {/* Personal Information */}
          <div>
            <h3 className="text-lg text-gray-800 mb-4 pb-2 border-b-2 border-gray-300">Personal Information</h3>
            
            <div className="space-y-6">
              {/* First Name and Last Name */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm text-gray-700 mb-2">First Name *</label>
                  <input 
                    type="text" 
                    placeholder="John"
                    className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
                  />
                </div>
                <div>
                  <label className="block text-sm text-gray-700 mb-2">Last Name *</label>
                  <input 
                    type="text" 
                    placeholder="Doe"
                    className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
                  />
                </div>
              </div>

              {/* Email and Phone */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm text-gray-700 mb-2">Email *</label>
                  <input 
                    type="email" 
                    placeholder="john.doe@email.com"
                    className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
                  />
                </div>
                <div>
                  <label className="block text-sm text-gray-700 mb-2">Phone</label>
                  <input 
                    type="tel" 
                    placeholder="+1 (555) 123-4567"
                    className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
                  />
                </div>
              </div>

              {/* Date of Birth and Gender */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm text-gray-700 mb-2">Date of Birth</label>
                  <input 
                    type="date" 
                    className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800"
                  />
                </div>
                <div>
                  <label className="block text-sm text-gray-700 mb-2">Gender</label>
                  <select className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 bg-white text-gray-700">
                    <option value="">Select gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                    <option value="prefer-not">Prefer not to say</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          {/* Address Information */}
          <div>
            <h3 className="text-lg text-gray-800 mb-4 pb-2 border-b-2 border-gray-300">Address Information</h3>
            
            <div className="space-y-6">
              {/* Street Address */}
              <div>
                <label className="block text-sm text-gray-700 mb-2">Street Address</label>
                <input 
                  type="text" 
                  placeholder="123 Main Street"
                  className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
                />
              </div>

              {/* City and State */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm text-gray-700 mb-2">City</label>
                  <input 
                    type="text" 
                    placeholder="New York"
                    className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
                  />
                </div>
                <div>
                  <label className="block text-sm text-gray-700 mb-2">State/Province</label>
                  <input 
                    type="text" 
                    placeholder="NY"
                    className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
                  />
                </div>
              </div>

              {/* Postal Code and Country */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm text-gray-700 mb-2">Postal Code</label>
                  <input 
                    type="text" 
                    placeholder="10001"
                    className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 placeholder:text-gray-400"
                  />
                </div>
                <div>
                  <label className="block text-sm text-gray-700 mb-2">Country</label>
                  <select className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 bg-white text-gray-700">
                    <option value="">Select country</option>
                    <option value="us">United States</option>
                    <option value="ca">Canada</option>
                    <option value="uk">United Kingdom</option>
                    <option value="au">Australia</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          {/* Additional Information */}
          <div>
            <h3 className="text-lg text-gray-800 mb-4 pb-2 border-b-2 border-gray-300">Additional Information</h3>
            
            <div className="space-y-6">
              {/* Customer Type and Status */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm text-gray-700 mb-2">Customer Type</label>
                  <select className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 bg-white text-gray-700">
                    <option value="regular">Regular</option>
                    <option value="vip">VIP</option>
                    <option value="wholesale">Wholesale</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm text-gray-700 mb-2">Status</label>
                  <select className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 bg-white text-gray-700">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
              </div>

              {/* Notes */}
              <div>
                <label className="block text-sm text-gray-700 mb-2">Notes</label>
                <textarea 
                  rows={4}
                  placeholder="Add any additional notes about this customer..."
                  className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 resize-none placeholder:text-gray-400"
                />
              </div>
            </div>
          </div>

          {/* Email Preferences */}
          <div>
            <h3 className="text-lg text-gray-800 mb-4 pb-2 border-b-2 border-gray-300">Marketing Preferences</h3>
            
            <div className="space-y-3">
              <label className="flex items-center gap-3 cursor-pointer">
                <input 
                  type="checkbox" 
                  defaultChecked
                  className="w-5 h-5 border-2 border-gray-300 rounded cursor-pointer"
                />
                <span className="text-gray-700">Subscribe to newsletter</span>
              </label>
              <label className="flex items-center gap-3 cursor-pointer">
                <input 
                  type="checkbox" 
                  defaultChecked
                  className="w-5 h-5 border-2 border-gray-300 rounded cursor-pointer"
                />
                <span className="text-gray-700">Send promotional emails</span>
              </label>
              <label className="flex items-center gap-3 cursor-pointer">
                <input 
                  type="checkbox" 
                  className="w-5 h-5 border-2 border-gray-300 rounded cursor-pointer"
                />
                <span className="text-gray-700">Send SMS notifications</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      {/* Action Buttons */}
      <div className="flex items-center justify-end gap-3">
        <button 
          onClick={() => navigate('/customers')}
          className="border-2 border-gray-300 bg-white text-gray-700 px-6 py-2.5 rounded hover:bg-gray-100 transition-colors"
        >
          Cancel
        </button>
        <button 
          className="border-2 border-gray-800 bg-gray-800 text-white px-6 py-2.5 rounded hover:bg-gray-700 transition-colors"
        >
          Add Customer
        </button>
      </div>
    </div>
  );
}
