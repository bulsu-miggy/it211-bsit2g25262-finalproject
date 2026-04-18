<?php
include('../config/db_connect.php');

// Clear any previous output to prevent CSV corruption
if (ob_get_length()) ob_clean();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Sparkverse_Customers_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// CSV Column Headers
fputcsv($output, array('ID', 'Name', 'Email', 'Gender', 'Age', 'Phone', 'Total Orders', 'Total Spent', 'Joined Date'));

// The Query
$query = "SELECT 
            u.id, u.name, u.email, u.gender, u.age, u.phone_number, u.created_at,
            COUNT(o.id) as total_orders, 
            IFNULL(SUM(o.total_amount), 0) as total_spent 
          FROM users u 
          LEFT JOIN orders o ON u.id = o.user_id 
          GROUP BY u.id 
          ORDER BY u.id DESC";

$result = mysqli_query($conn, $query);

if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        // Formatted to show ONLY the date (e.g., Apr 13, 2026)
        $joined_date = ($row['created_at']) 
            ? date('M d, Y', strtotime($row['created_at'])) 
            : 'N/A';

        fputcsv($output, array(
            $row['id'],
            $row['name'],
            $row['email'],
            $row['gender'],
            $row['age'],
            $row['phone_number'],
            $row['total_orders'],
            $row['total_spent'],
            $joined_date
        ));
    }
}

fclose($output);
exit();
?>