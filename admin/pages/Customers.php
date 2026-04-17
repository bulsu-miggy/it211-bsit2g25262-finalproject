import { Search, Download } from 'lucide-react';

const customers = [
  { id: 1, name: 'John Doe', email: 'john.doe@email.com', orders: 12, spent: '$1,245.00', joined: '2026-01-15' },
  { id: 2, name: 'Jane Smith', email: 'jane.smith@email.com', orders: 8, spent: '$890.50', joined: '2026-02-03' },
  { id: 3, name: 'Bob Johnson', email: 'bob.j@email.com', orders: 23, spent: '$2,310.00', joined: '2025-11-20' },
  { id: 4, name: 'Alice Brown', email: 'alice.b@email.com', orders: 5, spent: '$445.00', joined: '2026-03-10' },
  { id: 5, name: 'Charlie Wilson', email: 'charlie.w@email.com', orders: 31, spent: '$3,890.00', joined: '2025-09-05' },
  { id: 6, name: 'Emma Davis', email: 'emma.d@email.com', orders: 15, spent: '$1,567.00', joined: '2026-01-28' },
];

export function CustomersPage() {
  return (
    <div className="space-y-8">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl text-gray-800 mb-2">Customers</h1>
          <p className="text-gray-600">Manage your customer relationships</p>
        </div>
        <button 
          className="border-2 border-gray-800 bg-gray-800 text-white px-6 py-2.5 rounded hover:bg-gray-700 transition-colors flex items-center gap-2"
        >
          <Download className="w-4 h-4" />
          Export
        </button>
      </div>

      {/* Search */}
      <div className="border-2 border-gray-300 bg-white p-6 rounded">
        <div className="flex items-center gap-2 border-2 border-gray-300 bg-gray-50 px-4 py-2 rounded">
          <Search className="w-4 h-4 text-gray-400" />
          <input 
            type="text" 
            placeholder="Search customers..." 
            className="flex-1 bg-transparent outline-none text-gray-800"
          />
        </div>
      </div>

      {/* Customers Table */}
      <div className="border-2 border-gray-300 bg-white rounded overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="border-b-2 border-gray-300 bg-gray-50">
              <tr>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Customer</th>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Email</th>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Orders</th>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Total Spent</th>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Joined</th>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody>
              {customers.map((customer) => (
                <tr key={customer.id} className="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 rounded-full bg-gray-200 border-2 border-gray-300" />
                      <span className="text-gray-800">{customer.name}</span>
                    </div>
                  </td>
                  <td className="px-6 py-4 text-gray-600">{customer.email}</td>
                  <td className="px-6 py-4 text-gray-800">{customer.orders}</td>
                  <td className="px-6 py-4 text-gray-800">{customer.spent}</td>
                  <td className="px-6 py-4 text-gray-600">{customer.joined}</td>
                  <td className="px-6 py-4">
                    <button className="text-gray-600 hover:text-gray-800 underline text-sm">
                      View Details
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