import { Search, Filter, Download } from 'lucide-react';
import { useState } from 'react';

const orders = [
  { id: '#ORD-001', customer: 'John Doe', date: '2026-03-25', amount: '$250.00', status: 'Completed' },
  { id: '#ORD-002', customer: 'Jane Smith', date: '2026-03-25', amount: '$180.50', status: 'Processing' },
  { id: '#ORD-003', customer: 'Bob Johnson', date: '2026-03-24', amount: '$320.00', status: 'Shipped' },
  { id: '#ORD-004', customer: 'Alice Brown', date: '2026-03-24', amount: '$95.00', status: 'Completed' },
  { id: '#ORD-005', customer: 'Charlie Wilson', date: '2026-03-23', amount: '$410.00', status: 'Processing' },
  { id: '#ORD-006', customer: 'Emma Davis', date: '2026-03-23', amount: '$155.00', status: 'Cancelled' },
  { id: '#ORD-007', customer: 'Frank Miller', date: '2026-03-22', amount: '$275.50', status: 'Completed' },
  { id: '#ORD-008', customer: 'Grace Lee', date: '2026-03-22', amount: '$199.99', status: 'Shipped' },
];

export function OrdersPage() {
  const [selectedStatus, setSelectedStatus] = useState<string>('all');

  const filteredOrders = selectedStatus === 'all' 
    ? orders 
    : orders.filter(order => order.status.toLowerCase() === selectedStatus);

  const getStatusStyle = (status: string) => {
    switch (status) {
      case 'Completed':
        return 'bg-green-100 text-green-800 border-green-300';
      case 'Processing':
        return 'bg-blue-100 text-blue-800 border-blue-300';
      case 'Shipped':
        return 'bg-purple-100 text-purple-800 border-purple-300';
      case 'Cancelled':
        return 'bg-red-100 text-red-800 border-red-300';
      default:
        return 'bg-gray-100 text-gray-800 border-gray-300';
    }
  };

  return (
    <div className="space-y-8">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl text-gray-800 mb-2">Orders</h1>
          <p className="text-gray-600">Manage and track all customer orders</p>
        </div>
        <button className="border-2 border-gray-800 bg-gray-800 text-white px-6 py-2.5 rounded hover:bg-gray-700 transition-colors flex items-center gap-2">
          <Download className="w-4 h-4" />
          Export
        </button>
      </div>

      {/* Filters */}
      <div className="border-2 border-gray-300 bg-white p-6 rounded">
        <div className="flex flex-col md:flex-row gap-4">
          <div className="flex-1 flex items-center gap-2 border-2 border-gray-300 bg-gray-50 px-4 py-2 rounded">
            <Search className="w-4 h-4 text-gray-400" />
            <input 
              type="text" 
              placeholder="Search orders..." 
              className="flex-1 bg-transparent outline-none text-gray-800"
            />
          </div>
          <div className="flex gap-2">
            <select 
              value={selectedStatus}
              onChange={(e) => setSelectedStatus(e.target.value)}
              className="border-2 border-gray-300 px-4 py-2 rounded bg-white text-gray-800 outline-none"
            >
              <option value="all">All Status</option>
              <option value="completed">Completed</option>
              <option value="processing">Processing</option>
              <option value="shipped">Shipped</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <button className="border-2 border-gray-300 px-4 py-2 rounded bg-white hover:bg-gray-50 transition-colors flex items-center gap-2">
              <Filter className="w-4 h-4" />
              Filters
            </button>
          </div>
        </div>
      </div>

      {/* Orders Table */}
      <div className="border-2 border-gray-300 bg-white rounded overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="border-b-2 border-gray-300 bg-gray-50">
              <tr>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Order ID</th>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Customer</th>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Date</th>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Amount</th>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Status</th>
                <th className="text-left px-6 py-4 text-sm text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody>
              {filteredOrders.map((order) => (
                <tr key={order.id} className="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                  <td className="px-6 py-4 text-gray-800">{order.id}</td>
                  <td className="px-6 py-4 text-gray-800">{order.customer}</td>
                  <td className="px-6 py-4 text-gray-600">{order.date}</td>
                  <td className="px-6 py-4 text-gray-800">{order.amount}</td>
                  <td className="px-6 py-4">
                    <span className={`px-3 py-1 rounded text-sm border ${getStatusStyle(order.status)}`}>
                      {order.status}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <button className="text-gray-600 hover:text-gray-800 underline text-sm">
                      View
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
