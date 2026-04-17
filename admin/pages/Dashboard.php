import { StatCard } from '../components/StatCard';
import { ChartWireframe } from '../components/ChartWireframe';
import { RecentOrdersTable } from '../components/RecentOrdersTable';
import { ActivityFeed } from '../components/ActivityFeed';
import { DollarSign, ShoppingCart, Users, Package } from 'lucide-react';

export function DashboardPage() {
  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl text-gray-800 mb-2">Dashboard</h1>
        <p className="text-gray-600">Welcome back! Here's what's happening today.</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          icon={DollarSign}
          label="Total Revenue"
          value="$45,231"
          change="+20.1%"
          trend="up"
        />
        <StatCard
          icon={ShoppingCart}
          label="Orders"
          value="1,234"
          change="+12.5%"
          trend="up"
        />
        <StatCard
          icon={Users}
          label="Customers"
          value="3,456"
          change="+8.3%"
          trend="up"
        />
        <StatCard
          icon={Package}
          label="Products"
          value="567"
          change="-2.4%"
          trend="down"
        />
      </div>

      {/* Charts and Tables Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2">
          <ChartWireframe />
        </div>
        <div>
          <ActivityFeed />
        </div>
      </div>

      {/* Recent Orders */}
      <RecentOrdersTable />
    </div>
  );
}
