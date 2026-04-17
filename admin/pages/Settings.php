import { Save } from 'lucide-react';

export function SettingsPage() {
  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl text-gray-800 mb-2">Settings</h1>
        <p className="text-gray-600">Manage your store settings and preferences</p>
      </div>

      {/* General Settings */}
      <div className="border-2 border-gray-300 bg-white p-6 rounded">
        <h2 className="text-xl text-gray-800 mb-6">General Settings</h2>
        <div className="space-y-6">
          <div>
            <label className="block text-sm text-gray-700 mb-2">Store Name</label>
            <input 
              type="text" 
              defaultValue="My Ecommerce Store"
              className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800"
            />
          </div>
          <div>
            <label className="block text-sm text-gray-700 mb-2">Store Email</label>
            <input 
              type="email" 
              defaultValue="store@example.com"
              className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800"
            />
          </div>
          <div>
            <label className="block text-sm text-gray-700 mb-2">Store Description</label>
            <textarea 
              rows={4}
              defaultValue="Your one-stop shop for quality products"
              className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 resize-none"
            />
          </div>
        </div>
      </div>

      {/* Notifications */}
      <div className="border-2 border-gray-300 bg-white p-6 rounded">
        <h2 className="text-xl text-gray-800 mb-6">Notifications</h2>
        <div className="space-y-4">
          {[
            { label: 'Email notifications for new orders', checked: true },
            { label: 'Email notifications for low stock', checked: true },
            { label: 'Weekly sales reports', checked: false },
            { label: 'Customer review notifications', checked: true },
          ].map((item, i) => (
            <label key={i} className="flex items-center gap-3 cursor-pointer">
              <input 
                type="checkbox" 
                defaultChecked={item.checked}
                className="w-5 h-5 border-2 border-gray-300 rounded cursor-pointer"
              />
              <span className="text-gray-700">{item.label}</span>
            </label>
          ))}
        </div>
      </div>

      {/* Currency & Timezone */}
      <div className="border-2 border-gray-300 bg-white p-6 rounded">
        <h2 className="text-xl text-gray-800 mb-6">Regional Settings</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label className="block text-sm text-gray-700 mb-2">Currency</label>
            <select className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800">
              <option>USD ($)</option>
              <option>EUR (€)</option>
              <option>GBP (£)</option>
              <option>JPY (¥)</option>
            </select>
          </div>
          <div>
            <label className="block text-sm text-gray-700 mb-2">Timezone</label>
            <select className="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800">
              <option>UTC-08:00 (Pacific)</option>
              <option>UTC-05:00 (Eastern)</option>
              <option>UTC+00:00 (GMT)</option>
              <option>UTC+01:00 (CET)</option>
            </select>
          </div>
        </div>
      </div>

      {/* Save Button */}
      <div className="flex justify-end">
        <button className="border-2 border-gray-800 bg-gray-800 text-white px-8 py-3 rounded hover:bg-gray-700 transition-colors flex items-center gap-2">
          <Save className="w-4 h-4" />
          Save Changes
        </button>
      </div>
    </div>
  );
}
