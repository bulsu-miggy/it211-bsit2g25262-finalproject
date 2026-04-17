<?php
require_once __DIR__ . '/../db/conn.php';

class ProductManager {
    private $conn;
    private $session_id;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->initSession();
    }
    
    private function initSession() {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        
        if (session_status() !== PHP_SESSION_NONE) {
            if (!isset($_SESSION['cart_id'])) {
                $_SESSION['cart_id'] = session_id() . '_' . time();
            }
            $this->session_id = $_SESSION['cart_id'];
        } else {
            $this->session_id = session_id() ?: uniqid('cart_', true);
        }
    }
    
    public function getAllProducts() {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 ORDER BY p.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getProductById($product_id) {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = :product_id AND p.is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If not found in DB, check sample products
        if (!$product) {
            $sampleProducts = $this->getSampleProducts();
            if (isset($sampleProducts[$product_id])) {
                $product = $sampleProducts[$product_id];
            }
        }
        
        return $product;
    }
    
    private function getSampleProducts() {
        return [
            // Dishes (10001-10010)
            10001 => ['id' => 10001, 'name' => 'Chicken Adobo', 'category_name' => 'Dishes', 'category_slug' => 'dishes', 'price' => 250, 'description' => 'Chicken braised in vinegar, soy sauce, garlic, and bay leaves - the national dish of the Philippines.', 'image_emoji' => '🍗', 'is_active' => 1, 'brand' => ''],
            10002 => ['id' => 10002, 'name' => 'Pork Sinigang', 'category_name' => 'Dishes', 'category_slug' => 'dishes', 'price' => 280, 'description' => 'Pork belly in sour tamarind broth with vegetables - a comforting sour soup.', 'image_emoji' => '🥣', 'is_active' => 1, 'brand' => ''],
            10003 => ['id' => 10003, 'name' => 'Lechon Kawali', 'category_name' => 'Dishes', 'category_slug' => 'dishes', 'price' => 320, 'description' => 'Deep-fried crispy pork belly served with liver sauce - crispy on the outside, tender inside.', 'image_emoji' => '🥓', 'is_active' => 1, 'brand' => ''],
            10004 => ['id' => 10004, 'name' => 'Kare-Kare', 'category_name' => 'Dishes', 'category_slug' => 'dishes', 'price' => 350, 'description' => 'Oxtail stew in peanut sauce with vegetables - rich and creamy.', 'image_emoji' => '🥜', 'is_active' => 1, 'brand' => ''],
            10005 => ['id' => 10005, 'name' => 'Crispy Pata', 'category_name' => 'Dishes', 'category_slug' => 'dishes', 'price' => 450, 'description' => 'Deep-fried pork leg served with soy-vinegar dip - crunchy skin, tender meat.', 'image_emoji' => '🍖', 'is_active' => 1, 'brand' => ''],
            10006 => ['id' => 10006, 'name' => 'Beef Bulalo', 'category_name' => 'Dishes', 'category_slug' => 'dishes', 'price' => 380, 'description' => 'Beef shank soup with bone marrow and vegetables - hearty and warming.', 'image_emoji' => '🥩', 'is_active' => 1, 'brand' => ''],
            10007 => ['id' => 10007, 'name' => 'Chicken Inasal', 'category_name' => 'Dishes', 'category_slug' => 'dishes', 'price' => 260, 'description' => 'Grilled chicken marinated in annatto, calamansi, and spices - smoky and flavorful.', 'image_emoji' => '🍗', 'is_active' => 1, 'brand' => ''],
            10008 => ['id' => 10008, 'name' => 'Sisig', 'category_name' => 'Dishes', 'category_slug' => 'dishes', 'price' => 290, 'description' => 'Sizzling chopped pork face and ears with chili and calamansi - spicy and tangy.', 'image_emoji' => '🍳', 'is_active' => 1, 'brand' => ''],
            10009 => ['id' => 10009, 'name' => 'Bicol Express', 'category_name' => 'Dishes', 'category_slug' => 'dishes', 'price' => 270, 'description' => 'Pork cooked in coconut milk and chili peppers - creamy and spicy.', 'image_emoji' => '🌶️', 'is_active' => 1, 'brand' => ''],
            10010 => ['id' => 10010, 'name' => 'Kaldereta', 'category_name' => 'Dishes', 'category_slug' => 'dishes', 'price' => 340, 'description' => 'Goat meat stew in tomato sauce with liver spread and olives - rich and savory.', 'image_emoji' => '🥘', 'is_active' => 1, 'brand' => ''],
            
            // Beverages (11001-11010)
            11001 => ['id' => 11001, 'name' => 'Calamansi Juice', 'category_name' => 'Beverages', 'category_slug' => 'beverages', 'price' => 80, 'description' => 'Freshly squeezed calamansi juice - sweet, sour, and refreshing.', 'image_emoji' => '🍊', 'is_active' => 1, 'brand' => ''],
            11002 => ['id' => 11002, 'name' => 'Buko Juice', 'category_name' => 'Beverages', 'category_slug' => 'beverages', 'price' => 70, 'description' => 'Fresh coconut water straight from young coconuts - naturally sweet.', 'image_emoji' => '🥥', 'is_active' => 1, 'brand' => ''],
            11003 => ['id' => 11003, 'name' => 'Sago\'t Gulaman', 'category_name' => 'Beverages', 'category_slug' => 'beverages', 'price' => 65, 'description' => 'Brown sugar drink with tapioca pearls and jelly - sweet and chewy.', 'image_emoji' => '🧋', 'is_active' => 1, 'brand' => ''],
            11004 => ['id' => 11004, 'name' => 'Halo-Halo Shake', 'category_name' => 'Beverages', 'category_slug' => 'beverages', 'price' => 120, 'description' => 'Famous Filipino dessert turned into a shake - creamy and fruity.', 'image_emoji' => '🥤', 'is_active' => 1, 'brand' => ''],
            11005 => ['id' => 11005, 'name' => 'Mango Shake', 'category_name' => 'Beverages', 'category_slug' => 'beverages', 'price' => 95, 'description' => 'Fresh Philippine mango shake - sweet and creamy.', 'image_emoji' => '🥭', 'is_active' => 1, 'brand' => ''],
            11006 => ['id' => 11006, 'name' => 'Tsokolate Eh', 'category_name' => 'Beverages', 'category_slug' => 'beverages', 'price' => 85, 'description' => 'Thick Filipino hot chocolate made with tablea - rich and creamy.', 'image_emoji' => '☕', 'is_active' => 1, 'brand' => ''],
            11007 => ['id' => 11007, 'name' => 'Kapeng Barako', 'category_name' => 'Beverages', 'category_slug' => 'beverages', 'price' => 80, 'description' => 'Strong Filipino coffee - bold and aromatic.', 'image_emoji' => '☕', 'is_active' => 1, 'brand' => ''],
            11008 => ['id' => 11008, 'name' => 'Buko Pandan Drink', 'category_name' => 'Beverages', 'category_slug' => 'beverages', 'price' => 85, 'description' => 'Coconut water with pandan flavor - fragrant and sweet.', 'image_emoji' => '🌴', 'is_active' => 1, 'brand' => ''],
            11009 => ['id' => 11009, 'name' => 'Four Seasons Juice', 'category_name' => 'Beverages', 'category_slug' => 'beverages', 'price' => 95, 'description' => 'Mix of apple, orange, pineapple, and calamansi - sweet and sour.', 'image_emoji' => '🍎', 'is_active' => 1, 'brand' => ''],
            11010 => ['id' => 11010, 'name' => 'Avocado Shake', 'category_name' => 'Beverages', 'category_slug' => 'beverages', 'price' => 100, 'description' => 'Creamy avocado shake - rich and satisfying.', 'image_emoji' => '🥑', 'is_active' => 1, 'brand' => ''],
            
            // Desserts (12001-12010)
            12001 => ['id' => 12001, 'name' => 'Leche Flan', 'category_name' => 'Desserts', 'category_slug' => 'desserts', 'price' => 120, 'description' => 'Creamy caramel custard - smooth and decadent.', 'image_emoji' => '🍮', 'is_active' => 1, 'brand' => ''],
            12002 => ['id' => 12002, 'name' => 'Ube Halaya', 'category_name' => 'Desserts', 'category_slug' => 'desserts', 'price' => 150, 'description' => 'Purple yam jam - sweet, creamy, and vibrant.', 'image_emoji' => '🍠', 'is_active' => 1, 'brand' => ''],
            12003 => ['id' => 12003, 'name' => 'Buko Pandan', 'category_name' => 'Desserts', 'category_slug' => 'desserts', 'price' => 100, 'description' => 'Coconut and pandan jelly salad - refreshing and sweet.', 'image_emoji' => '🥥', 'is_active' => 1, 'brand' => ''],
            12004 => ['id' => 12004, 'name' => 'Halo-Halo', 'category_name' => 'Desserts', 'category_slug' => 'desserts', 'price' => 130, 'description' => 'Famous Filipino shaved ice dessert with various toppings.', 'image_emoji' => '🍧', 'is_active' => 1, 'brand' => ''],
            12005 => ['id' => 12005, 'name' => 'Turon', 'category_name' => 'Desserts', 'category_slug' => 'desserts', 'price' => 60, 'description' => 'Fried banana spring rolls with jackfruit - crispy and sweet.', 'image_emoji' => '🍌', 'is_active' => 1, 'brand' => ''],
            12006 => ['id' => 12006, 'name' => 'Cassava Cake', 'category_name' => 'Desserts', 'category_slug' => 'desserts', 'price' => 140, 'description' => 'Baked cassava cake with coconut milk - dense and chewy.', 'image_emoji' => '🍰', 'is_active' => 1, 'brand' => ''],
            12007 => ['id' => 12007, 'name' => 'Mango Float', 'category_name' => 'Desserts', 'category_slug' => 'desserts', 'price' => 160, 'description' => 'Layered graham crackers, cream, and fresh mangoes - no-bake delight.', 'image_emoji' => '🥭', 'is_active' => 1, 'brand' => ''],
            12008 => ['id' => 12008, 'name' => 'Bibingka', 'category_name' => 'Desserts', 'category_slug' => 'desserts', 'price' => 110, 'description' => 'Rice cake baked with salted egg and cheese - soft and flavorful.', 'image_emoji' => '🍰', 'is_active' => 1, 'brand' => ''],
            12009 => ['id' => 12009, 'name' => 'Suman', 'category_name' => 'Desserts', 'category_slug' => 'desserts', 'price' => 70, 'description' => 'Glutinous rice wrapped in banana leaves - served with cocoa.', 'image_emoji' => '🍙', 'is_active' => 1, 'brand' => ''],
            12010 => ['id' => 12010, 'name' => 'Maja Blanca', 'category_name' => 'Desserts', 'category_slug' => 'desserts', 'price' => 110, 'description' => 'Coconut pudding with corn - creamy and light.', 'image_emoji' => '🍮', 'is_active' => 1, 'brand' => ''],
        ];
    }

    private function productExists($product_id) {
        $query = "SELECT id FROM products WHERE id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getCategoryIdBySlug($slug) {
        if (empty($slug)) {
            return null;
        }

        $query = "SELECT id FROM categories WHERE slug = :slug LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int) $result['id'] : null;
    }

    private function slugify($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return $text ?: 'product-' . uniqid();
    }

    private function seedSampleProduct($product) {
        if (empty($product) || empty($product['id'])) {
            return false;
        }

        $category_id = $this->getCategoryIdBySlug($product['category_slug'] ?? '');
        $slug = $this->slugify(($product['name'] ?? 'sample-product') . '-' . $product['id']);
        $sku = 'SAMPLE-' . $product['id'];

        $query = "INSERT INTO products (id, name, slug, description, price, sku, category_id, brand, image_emoji, is_active)
                  VALUES (:id, :name, :slug, :description, :price, :sku, :category_id, :brand, :image_emoji, :is_active)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $product['id'], PDO::PARAM_INT);
        $stmt->bindParam(':name', $product['name']);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':description', $product['description']);
        $stmt->bindParam(':price', $product['price']);
        $stmt->bindParam(':sku', $sku);
        $stmt->bindValue(':category_id', $category_id, $category_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':brand', $product['brand'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':image_emoji', $product['image_emoji'] ?? null, PDO::PARAM_STR);
        $stmt->bindParam(':is_active', $product['is_active'], PDO::PARAM_BOOL);

        return $stmt->execute();
    }
    
    public function getCartItems() {
        $query = "SELECT c.session_id, c.product_id, c.quantity FROM cart_items c WHERE c.session_id = :session_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':session_id', $this->session_id);
        $stmt->execute();
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add product details for each cart item
        foreach ($cartItems as &$item) {
            $product = $this->getProductById($item['product_id']);
            if ($product) {
                $item = array_merge($item, [
                    'id' => $product['id'],
                    'title' => $product['name'],
                    'brand' => $product['brand'] ?? '',
                    'price' => $product['price'],
                    'image_emoji' => $product['image_emoji'] ?? ''
                ]);
            }
        }
        
        return $cartItems;
    }
    
    public function addToCart($product_id, $quantity = 1) {
        $product = $this->getProductById($product_id);
        if (!$product) {
            return false; // Product doesn't exist in DB or sample data
        }

        if (!$this->productExists($product_id)) {
            if (!$this->seedSampleProduct($product)) {
                return false;
            }
        }

        $checkQuery = "SELECT product_id, quantity FROM cart_items 
                       WHERE session_id = :session_id AND product_id = :product_id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':session_id', $this->session_id);
        $checkStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $checkStmt->execute();

        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $newQuantity = $existing['quantity'] + $quantity;
            $query = "UPDATE cart_items SET quantity = :quantity 
                      WHERE session_id = :session_id AND product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':quantity', $newQuantity, PDO::PARAM_INT);
        } else {
            $query = "INSERT INTO cart_items (session_id, product_id, quantity) 
                      VALUES (:session_id, :product_id, :quantity)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        }

        $stmt->bindParam(':session_id', $this->session_id);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);

        return $stmt->execute();
    }
    
    public function updateQuantity($product_id, $quantity) {
        if ($quantity <= 0) {
            return $this->removeCartItem($product_id);
        }
        
        $query = "UPDATE cart_items SET quantity = :quantity 
                  WHERE session_id = :session_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':session_id', $this->session_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':quantity', $quantity);
        return $stmt->execute();
    }
    
    public function removeCartItem($product_id) {
        $query = "DELETE FROM cart_items 
                  WHERE session_id = :session_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':session_id', $this->session_id);
        $stmt->bindParam(':product_id', $product_id);
        return $stmt->execute();
    }
    
    public function clearCart() {
        $query = "DELETE FROM cart_items WHERE session_id = :session_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':session_id', $this->session_id);
        return $stmt->execute();
    }
    
    public function getCartTotal() {
        $items = $this->getCartItems();
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }
    
    public function createOrder($order_data) {
        try {
            $this->conn->beginTransaction();
            
            $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
            
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            
            $query = "INSERT INTO orders (order_number, user_id, session_id, subtotal, delivery_fee, total, payment_mode, payment_details, location, status)
                      VALUES (:order_number, :user_id, :session_id, :subtotal, :delivery_fee, :total, :payment_mode, :payment_details, :location, 'pending')";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':order_number' => $order_number,
                ':user_id' => $user_id,
                ':session_id' => $this->session_id,
                ':subtotal' => $order_data['subtotal'],
                ':delivery_fee' => $order_data['delivery_fee'],
                ':total' => $order_data['total'],
                ':payment_mode' => $order_data['payment_mode'],
                ':payment_details' => $order_data['payment_details'],
                ':location' => $order_data['location']
            ]);
            
            $order_id = $this->conn->lastInsertId();
            
            foreach ($order_data['items'] as $item) {
                $query = "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal)
                          VALUES (:order_id, :product_id, :product_name, :product_price, :quantity, :subtotal)";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    ':order_id' => $order_id,
                    ':product_id' => $item['product_id'],
                    ':product_name' => $item['title'],
                    ':product_price' => $item['price'],
                    ':quantity' => $item['quantity'],
                    ':subtotal' => $item['price'] * $item['quantity']
                ]);
            }
            
            $this->clearCart();
            $this->conn->commit();
            
            return ['success' => true, 'order_number' => $order_number];
        } catch(Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>