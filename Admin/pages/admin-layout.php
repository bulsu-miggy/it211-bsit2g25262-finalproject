<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lasa Filipina Admin Panel</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #fefaf5;
            overflow-x: hidden;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            min-height: 100vh;
            background: #fefaf5;
        }

        .content-area {
            padding: 2rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }

        /* Form Styles matching frontend */
        input, textarea, select {
            font-family: 'Inter', sans-serif;
        }

        /* Card styles */
        .admin-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #f0e2d6;
            overflow: hidden;
        }

        .admin-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f0e2d6;
        }

        .admin-card-body {
            padding: 1.5rem;
        }

        /* Button styles */
        .btn-primary {
            background: linear-gradient(135deg, #bc6f3b, #a55828);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(188, 111, 59, 0.3);
        }

        .btn-secondary {
            background: #f5ede5;
            color: #2f241b;
            border: none;
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #e7cfbc;
        }

        /* Table styles */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th {
            text-align: left;
            padding: 1rem 1.5rem;
            background: #fefaf5;
            color: #6f553e;
            font-weight: 600;
            font-size: 0.85rem;
            border-bottom: 1px solid #f0e2d6;
        }

        .admin-table td {
            padding: 1rem 1.5rem;
            color: #2f241b;
            border-bottom: 1px solid #f0e2d6;
        }

        .admin-table tr:hover {
            background: #fefaf5;
        }

        /* Badge styles */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-danger {
            background: #ffebee;
            color: #c62828;
        }

        .badge-warning {
            background: #fff8e1;
            color: #f57c00;
        }

        .badge-info {
            background: #e3f2fd;
            color: #1565c0;
        }

        /* Stats card */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid #f0e2d6;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>
        <div class="content-area">
            <?php
            // This is where the page content will be injected
            ?>
        </div>
    </div>
</div>

<script>
    // Mobile menu toggle
    document.getElementById('menuToggle')?.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });
</script>
</body>
</html>