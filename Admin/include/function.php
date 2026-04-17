<?php
// Utility functions for the application
// Database functions are already loaded from config/db.php

// Format price with currency
function formatPrice($price) {
    return '₱' . number_format($price, 2);
}

// ============================================
// DASHBOARD FUNCTIONS - WITH CHART DATA
// ============================================

// Get dashboard statistics - WITH CHART DATA
function getDashboardStats() {
    $pdo = getDBConnection();
    
    $stats = [
        'products' => 0,
        'categories' => 0,
        'customers' => 0,
        'revenue' => 0,
        'orders' => 0,
        'low_stock' => 0,
        'revenue_labels' => [],
        'revenue_data' => [],
        'orders_labels' => [],
        'orders_data' => [],
        'category_labels' => [],
        'category_data' => [],
        'status_labels' => [],
        'status_data' => []
    ];
    
    try {
        // Total products
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
        if ($stmt) {
            $result = $stmt->fetch();
            $stats['products'] = $result['count'] ?? 0;
        }
        
        // Total categories
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories WHERE is_active = 1");
        if ($stmt) {
            $result = $stmt->fetch();
            $stats['categories'] = $result['count'] ?? 0;
        }
        
        // Total customers
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM customers");
        if ($stmt) {
            $result = $stmt->fetch();
            $stats['customers'] = $result['count'] ?? 0;
        }
        
        // Low stock items
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity < 10 AND stock_quantity > 0");
        if ($stmt) {
            $result = $stmt->fetch();
            $stats['low_stock'] = $result['count'] ?? 0;
        }
        
        // Total revenue and orders
        $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as revenue, COUNT(*) as orders FROM orders WHERE status != 'cancelled'");
        if ($stmt) {
            $result = $stmt->fetch();
            $stats['revenue'] = $result['revenue'] ?? 0;
            $stats['orders'] = $result['orders'] ?? 0;
        }
        
        // Monthly revenue for line chart (last 6 months)
        $stmt = $pdo->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COALESCE(SUM(total_amount), 0) AS total
            FROM orders
            WHERE status != 'cancelled'
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY month
            ORDER BY month
        ");
        if ($stmt) {
            $rows = $stmt->fetchAll();
            $periods = [];
            for ($i = 5; $i >= 0; $i--) {
                $periods[] = date('Y-m', strtotime("-{$i} months"));
            }
            $totals = array_fill(0, count($periods), 0);
            foreach ($rows as $row) {
                $index = array_search($row['month'], $periods, true);
                if ($index !== false) {
                    $totals[$index] = (float) $row['total'];
                }
            }
            $stats['revenue_labels'] = array_map(function ($month) {
                return date('M', strtotime($month . '-01'));
            }, $periods);
            $stats['revenue_data'] = $totals;
        } else {
            $stats['revenue_labels'] = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            $stats['revenue_data'] = [0, 0, 0, 0, 0, 0];
        }
        
        // Weekly orders for bar chart (last 7 days)
        $stmt = $pdo->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m-%d') AS day, COUNT(*) AS count
            FROM orders
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY day
            ORDER BY day
        ");
        if ($stmt) {
            $rows = $stmt->fetchAll();
            $periods = [];
            for ($i = 6; $i >= 0; $i--) {
                $periods[] = date('Y-m-d', strtotime("-{$i} days"));
            }
            $totals = array_fill(0, count($periods), 0);
            foreach ($rows as $row) {
                $index = array_search($row['day'], $periods, true);
                if ($index !== false) {
                    $totals[$index] = (int) $row['count'];
                }
            }
            $stats['orders_labels'] = array_map(function ($day) {
                return date('D', strtotime($day));
            }, $periods);
            $stats['orders_data'] = $totals;
        } else {
            $stats['orders_labels'] = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $stats['orders_data'] = [0, 0, 0, 0, 0, 0, 0];
        }
        
        // Products by category for doughnut chart
        $stmt = $pdo->query("
            SELECT c.name, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            WHERE c.is_active = 1 
            GROUP BY c.id, c.name 
            ORDER BY product_count DESC 
            LIMIT 5
        ");
        if ($stmt) {
            $rows = $stmt->fetchAll();
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $stats['category_labels'][] = $row['name'];
                    $stats['category_data'][] = (int) $row['product_count'];
                }
            } else {
                $stats['category_labels'] = ['No Categories'];
                $stats['category_data'] = [1];
            }
        }
        
        // Order status for pie chart
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
        if ($stmt) {
            $rows = $stmt->fetchAll();
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $stats['status_labels'][] = ucfirst($row['status']);
                    $stats['status_data'][] = (int) $row['count'];
                }
            } else {
                $stats['status_labels'] = ['No Orders'];
                $stats['status_data'] = [1];
            }
        }
        
    } catch (PDOException $e) {
        error_log("Error getting dashboard stats: " . $e->getMessage());
    }
    
    return $stats;
}

// Get recent products
function getRecentProducts($limit = 5) {
    $pdo = getDBConnection();
    $products = [];
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting recent products: " . $e->getMessage());
    }
    
    return $products ?: [];
}

// ============================================
// ANALYTICS FUNCTIONS
// ============================================

// Get monthly revenue for analytics
function getMonthlyRevenue($months = 12) {
    $pdo = getDBConnection();
    $revenue = [
        'labels' => [],
        'data' => []
    ];

    try {
        $stmt = $pdo->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COALESCE(SUM(total_amount), 0) AS total
             FROM orders
             WHERE status != 'cancelled'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
             GROUP BY month
             ORDER BY month"
        );
        $stmt->bindParam(':months', $months, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $periods = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $periods[] = date('Y-m', strtotime("-{$i} months"));
        }

        $totals = array_fill(0, count($periods), 0);
        foreach ($rows as $row) {
            $index = array_search($row['month'], $periods, true);
            if ($index !== false) {
                $totals[$index] = (float) $row['total'];
            }
        }

        $revenue['labels'] = array_map(function ($month) {
            return date('M Y', strtotime($month . '-01'));
        }, $periods);
        $revenue['data'] = $totals;
    } catch (PDOException $e) {
        error_log("Error getting monthly revenue: " . $e->getMessage());
        $revenue['labels'] = ['No Data'];
        $revenue['data'] = [0];
    }

    return $revenue;
}

// Get order status counts
function getOrderStatusCounts() {
    $pdo = getDBConnection();
    $statusCounts = [
        'labels' => [],
        'data' => []
    ];

    try {
        $stmt = $pdo->query("SELECT status, COUNT(*) AS count FROM orders GROUP BY status");
        $rows = $stmt->fetchAll();
        
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $statusCounts['labels'][] = ucfirst($row['status']);
                $statusCounts['data'][] = (int) $row['count'];
            }
        } else {
            $statusCounts['labels'] = ['No Orders'];
            $statusCounts['data'] = [1];
        }
    } catch (PDOException $e) {
        error_log("Error getting order status counts: " . $e->getMessage());
    }

    return $statusCounts;
}

// Get recent orders
function getRecentOrders($limit = 10) {
    $pdo = getDBConnection();
    $orders = [];
    
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, CONCAT(c.first_name, ' ', c.last_name) as customer_name 
            FROM orders o 
            LEFT JOIN customers c ON o.customer_id = c.id 
            ORDER BY o.created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $orders = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting recent orders: " . $e->getMessage());
    }
    
    return $orders ?: [];
}

// Get daily revenue
function getDailyRevenue($days = 30) {
    $pdo = getDBConnection();
    $revenue = [
        'labels' => [],
        'data' => []
    ];

    try {
        $stmt = $pdo->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m-%d') AS day, COALESCE(SUM(total_amount), 0) AS total
             FROM orders
             WHERE status != 'cancelled'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY day
             ORDER BY day"
        );
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $periods = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $periods[] = date('Y-m-d', strtotime("-{$i} days"));
        }

        $totals = array_fill(0, count($periods), 0);
        foreach ($rows as $row) {
            $index = array_search($row['day'], $periods, true);
            if ($index !== false) {
                $totals[$index] = (float) $row['total'];
            }
        }

        $revenue['labels'] = array_map(function ($day) {
            return date('M d', strtotime($day));
        }, $periods);
        $revenue['data'] = $totals;
    } catch (PDOException $e) {
        error_log("Error getting daily revenue: " . $e->getMessage());
    }

    return $revenue;
}

// Get daily order count
function getDailyOrderCount($days = 30) {
    $pdo = getDBConnection();
    $orders = [
        'labels' => [],
        'data' => []
    ];

    try {
        $stmt = $pdo->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m-%d') AS day, COUNT(*) AS count
             FROM orders
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY day
             ORDER BY day"
        );
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $periods = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $periods[] = date('Y-m-d', strtotime("-{$i} days"));
        }

        $totals = array_fill(0, count($periods), 0);
        foreach ($rows as $row) {
            $index = array_search($row['day'], $periods, true);
            if ($index !== false) {
                $totals[$index] = (int) $row['count'];
            }
        }

        $orders['labels'] = array_map(function ($day) {
            return date('M d', strtotime($day));
        }, $periods);
        $orders['data'] = $totals;
    } catch (PDOException $e) {
        error_log("Error getting daily order count: " . $e->getMessage());
    }

    return $orders;
}

// Get top products by revenue
function getTopProductsByRevenue($limit = 10) {
    $pdo = getDBConnection();
    $products = [
        'labels' => [],
        'data' => []
    ];

    try {
        $stmt = $pdo->prepare(
            "SELECT p.name, COALESCE(SUM(oi.price * oi.quantity), 0) as revenue
             FROM products p
             LEFT JOIN order_items oi ON p.id = oi.product_id
             LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled'
             GROUP BY p.id, p.name
             ORDER BY revenue DESC
             LIMIT :limit"
        );
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $products['labels'][] = $row['name'];
            $products['data'][] = (float) $row['revenue'];
        }
    } catch (PDOException $e) {
        error_log("Error getting top products: " . $e->getMessage());
    }

    return $products;
}

// Get category revenue breakdown
function getCategoryRevenueBreakdown() {
    $pdo = getDBConnection();
    $categories = [
        'labels' => [],
        'data' => []
    ];

    try {
        $stmt = $pdo->query(
            "SELECT c.name, COALESCE(SUM(oi.price * oi.quantity), 0) as revenue
             FROM categories c
             LEFT JOIN products p ON c.id = p.category_id
             LEFT JOIN order_items oi ON p.id = oi.product_id
             LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled'
             GROUP BY c.id, c.name
             ORDER BY revenue DESC"
        );
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $categories['labels'][] = $row['name'];
            $categories['data'][] = (float) $row['revenue'];
        }
    } catch (PDOException $e) {
        error_log("Error getting category revenue: " . $e->getMessage());
    }

    return $categories;
}

// Get top customers by spending
function getTopCustomersBySpending($limit = 8) {
    $pdo = getDBConnection();
    $customers = [
        'labels' => [],
        'data' => []
    ];

    try {
        $stmt = $pdo->prepare(
            "SELECT CONCAT(c.first_name, ' ', c.last_name) as customer_name, COALESCE(SUM(o.total_amount), 0) as total_spent
             FROM customers c
             LEFT JOIN orders o ON c.id = o.customer_id AND o.status != 'cancelled'
             GROUP BY c.id, c.first_name, c.last_name
             ORDER BY total_spent DESC
             LIMIT :limit"
        );
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $customers['labels'][] = $row['customer_name'];
            $customers['data'][] = (float) $row['total_spent'];
        }
    } catch (PDOException $e) {
        error_log("Error getting top customers: " . $e->getMessage());
    }

    return $customers;
}

// Get customer growth
function getCustomerGrowth($months = 12) {
    $pdo = getDBConnection();
    $growth = [
        'labels' => [],
        'data' => []
    ];

    try {
        $stmt = $pdo->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count
             FROM customers
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
             GROUP BY month
             ORDER BY month"
        );
        $stmt->bindParam(':months', $months, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $periods = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $periods[] = date('Y-m', strtotime("-{$i} months"));
        }

        $totals = array_fill(0, count($periods), 0);
        foreach ($rows as $row) {
            $index = array_search($row['month'], $periods, true);
            if ($index !== false) {
                $totals[$index] = (int) $row['count'];
            }
        }

        $growth['labels'] = array_map(function ($month) {
            return date('M Y', strtotime($month . '-01'));
        }, $periods);
        $growth['data'] = $totals;
    } catch (PDOException $e) {
        error_log("Error getting customer growth: " . $e->getMessage());
    }

    return $growth;
}

// ============================================
// GENERAL FUNCTIONS
// ============================================

// Get all products
function getAllProducts($limit = null) {
    $pdo = getDBConnection();
    $products = [];
    
    try {
        $query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC";
        if ($limit) {
            $query .= " LIMIT " . intval($limit);
        }
        $stmt = $pdo->query($query);
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting products: " . $e->getMessage());
    }
    
    return $products;
}

// Get all categories
function getAllCategories() {
    $pdo = getDBConnection();
    $categories = [];
    
    try {
        $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting categories: " . $e->getMessage());
    }
    
    return $categories;
}

// Get product by ID
function getProductById($id) {
    $pdo = getDBConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting product: " . $e->getMessage());
        return null;
    }
}

// Get category by ID
function getCategoryById($id) {
    $pdo = getDBConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting category: " . $e->getMessage());
        return null;
    }
}

// Get customer by ID
function getCustomerById($id) {
    $pdo = getDBConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting customer: " . $e->getMessage());
        return null;
    }
}

// Get order by ID
function getOrderById($id) {
    $pdo = getDBConnection();
    
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, c.first_name, c.last_name, c.email
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            WHERE o.id = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting order: " . $e->getMessage());
        return null;
    }
}

// Generate slug from string
function generateSlug($string) {
    $string = trim($string);
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

// Generate a unique slug for a table column
function generateUniqueSlug($string, $table = 'products', $column = 'slug', $excludeId = null) {
    $pdo = getDBConnection();
    $baseSlug = generateSlug($string);
    $slug = $baseSlug;
    $counter = 1;

    while (true) {
        $query = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :slug";
        if ($excludeId !== null) {
            $query .= " AND id != :excludeId";
        }

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':slug', $slug);
        if ($excludeId !== null) {
            $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();

        if ($stmt->fetchColumn() == 0) {
            return $slug;
        }

        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
}

// Get setting value
function getSetting($key, $default = null) {
    $pdo = getDBConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = :key");
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result) {
            return $result['setting_value'];
        }
        return $default;
    } catch (PDOException $e) {
        error_log("Error getting setting: " . $e->getMessage());
        return $default;
    }
}

// Update setting value
function updateSetting($key, $value) {
    $pdo = getDBConnection();
    
    try {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = :key");
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)");
            $stmt->bindParam(':key', $key);
            $stmt->bindParam(':value', $value);
            $stmt->execute();
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Error updating setting: " . $e->getMessage());
        return false;
    }
}

// Get CSS class for status badges
function getStatusBadgeClass($status) {
    $classes = [
        'Active' => 'bg-green-100 text-green-800 border-green-200',
        'Inactive' => 'bg-red-100 text-red-800 border-red-200',
        'Pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'Processing' => 'bg-blue-100 text-blue-800 border-blue-200',
        'Completed' => 'bg-green-100 text-green-800 border-green-200',
        'Cancelled' => 'bg-red-100 text-red-800 border-red-200',
        'Shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
        'Out of Stock' => 'bg-red-100 text-red-800 border-red-200',
        'Regular' => 'bg-gray-100 text-gray-800 border-gray-200',
        'Vip' => 'bg-purple-100 text-purple-800 border-purple-200',
        'Wholesale' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
    ];
    
    return $classes[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Upload file
function uploadFile($file, $targetDir) {
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $targetDir . '/' . $fileName;
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    if ($file['size'] > 5000000) {
        return false;
    }
    
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    
    return false;
}
?>