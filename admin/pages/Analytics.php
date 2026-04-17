import { TrendingUp, TrendingDown, DollarSign, ShoppingBag } from 'lucide-react';

export function AnalyticsPage() {
  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl text-gray-800 mb-2">Analytics</h1>
        <p className="text-gray-600">Track your business performance and insights</p>
      </div>

      {/* Key Metrics */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {[
          { label: 'Revenue Growth', value: '23.5%', icon: TrendingUp, trend: 'up' },
          { label: 'Conversion Rate', value: '3.2%', icon: DollarSign, trend: 'up' },
          { label: 'Avg. Order Value', value: '$125', icon: ShoppingBag, trend: 'down' },
          { label: 'Customer Retention', value: '84%', icon: TrendingUp, trend: 'up' },
        ].map((metric, i) => (
          <div key={i} className="border-2 border-gray-300 bg-white p-6 rounded">
            <div className="flex items-center justify-between mb-4">
              <metric.icon className={`w-6 h-6 ${metric.trend === 'up' ? 'text-green-600' : 'text-red-600'}`} />
              <span className={`text-sm px-2 py-1 rounded border ${
                metric.trend === 'up' 
                  ? 'bg-green-100 text-green-800 border-green-300' 
                  : 'bg-red-100 text-red-800 border-red-300'
              }`}>
                {metric.trend === 'up' ? '+12%' : '-5%'}
              </span>
            </div>
            <p className="text-sm text-gray-600 mb-1">{metric.label}</p>
            <p className="text-2xl text-gray-800">{metric.value}</p>
          </div>
        ))}
      </div>

      {/* Large Chart Area */}
      <div className="border-2 border-gray-300 bg-white p-6 rounded">
        <div className="flex items-center justify-between mb-6">
          <div>
            <h2 className="text-xl text-gray-800 mb-1">Revenue Overview</h2>
            <p className="text-sm text-gray-600">Monthly revenue for the past 12 months</p>
          </div>
          <div className="flex gap-2">
            <button className="border-2 border-gray-800 bg-gray-800 text-white px-4 py-2 rounded text-sm">
              Month
            </button>
            <button className="border-2 border-gray-300 bg-white text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-50">
              Year
            </button>
          </div>
        </div>
        <div className="h-96 border-2 border-gray-300 bg-gray-50 rounded flex items-end justify-around gap-2 p-6">
          {[65, 80, 45, 90, 70, 55, 85, 95, 60, 75, 88, 92].map((height, i) => (
            <div key={i} className="flex-1 bg-gray-400 rounded-t" style={{ height: `${height}%` }} />
          ))}
        </div>
        <div className="flex justify-around mt-4 text-xs text-gray-500">
          <span>Jan</span>
          <span>Feb</span>
          <span>Mar</span>
          <span>Apr</span>
          <span>May</span>
          <span>Jun</span>
          <span>Jul</span>
          <span>Aug</span>
          <span>Sep</span>
          <span>Oct</span>
          <span>Nov</span>
          <span>Dec</span>
        </div>
      </div>

      {/* Additional Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="border-2 border-gray-300 bg-white p-6 rounded">
          <h3 className="text-lg text-gray-800 mb-4">Top Products</h3>
          <div className="space-y-4">
            {[
              { name: 'Wireless Headphones', sales: 234, percentage: 85 },
              { name: 'Smart Watch', sales: 189, percentage: 68 },
              { name: 'Bluetooth Speaker', sales: 145, percentage: 52 },
              { name: 'Laptop Sleeve', sales: 98, percentage: 35 },
            ].map((product, i) => (
              <div key={i}>
                <div className="flex justify-between text-sm mb-2">
                  <span className="text-gray-700">{product.name}</span>
                  <span className="text-gray-600">{product.sales} sales</span>
                </div>
                <div className="h-3 bg-gray-200 rounded overflow-hidden">
                  <div 
                    className="h-full bg-gray-600 rounded" 
                    style={{ width: `${product.percentage}%` }}
                  />
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="border-2 border-gray-300 bg-white p-6 rounded">
          <h3 className="text-lg text-gray-800 mb-4">Traffic Sources</h3>
          <div className="flex items-center justify-center h-64">
            <div className="relative w-48 h-48">
              <svg viewBox="0 0 100 100" className="transform -rotate-90">
                <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" strokeWidth="20" />
                <circle 
                  cx="50" cy="50" r="40" 
                  fill="none" 
                  stroke="#6b7280" 
                  strokeWidth="20"
                  strokeDasharray="251.2"
                  strokeDashoffset="62.8"
                />
                <circle 
                  cx="50" cy="50" r="40" 
                  fill="none" 
                  stroke="#9ca3af" 
                  strokeWidth="20"
                  strokeDasharray="251.2"
                  strokeDashoffset="125.6"
                />
              </svg>
            </div>
          </div>
          <div className="space-y-2">
            <div className="flex items-center justify-between text-sm">
              <div className="flex items-center gap-2">
                <div className="w-3 h-3 bg-gray-600 rounded" />
                <span className="text-gray-700">Direct</span>
              </div>
              <span className="text-gray-600">45%</span>
            </div>
            <div className="flex items-center justify-between text-sm">
              <div className="flex items-center gap-2">
                <div className="w-3 h-3 bg-gray-400 rounded" />
                <span className="text-gray-700">Social</span>
              </div>
              <span className="text-gray-600">30%</span>
            </div>
            <div className="flex items-center justify-between text-sm">
              <div className="flex items-center gap-2">
                <div className="w-3 h-3 bg-gray-200 rounded" />
                <span className="text-gray-700">Referral</span>
              </div>
              <span className="text-gray-600">25%</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
