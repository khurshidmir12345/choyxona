<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chek - {{ $order->company->name ?? 'Choyxona' }}</title>
    
    <!-- Print-optimized CSS -->
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        /* Receipt container */
        .receipt {
            max-width: 300px;
            margin: 0 auto;
            padding: 10px;
            border: 1px solid #ccc;
        }

        /* Header styles */
        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .receipt-title {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .order-info {
            font-size: 11px;
            color: #666;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .items-table th,
        .items-table td {
            padding: 3px 0;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .items-table th {
            font-weight: bold;
            border-bottom: 1px solid #ccc;
        }

        .item-name {
            width: 50%;
        }

        .item-qty {
            width: 15%;
            text-align: center;
        }

        .item-price {
            width: 35%;
            text-align: right;
        }

        /* Total section */
        .total-section {
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            margin-top: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        .total-amount {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        /* Footer */
        .receipt-footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            font-size: 10px;
            color: #666;
        }

        /* Print button (hidden when printing) */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        /* Hide print button when printing */
        @media print {
            .print-button {
                display: none;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .receipt {
                border: none;
                max-width: none;
            }
        }

        /* Discount styling */
        .discount {
            color: #dc3545;
            font-size: 10px;
        }

        /* Worker info */
        .worker-info {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Print button -->
    <button class="print-button" onclick="window.print()">
        🖨️ Bosib chiqarish (Ctrl+P)
    </button>

    <!-- Receipt content -->
    <div class="receipt">
        {{ $slot }}
    </div>

    <!-- JavaScript for print functionality -->
    <script>
        // Listen for print event from Livewire
        document.addEventListener('livewire:init', () => {
            Livewire.on('print-receipt', () => {
                window.print();
            });
        });

        // Auto print when page loads (optional)
        // window.onload = function() {
        //     setTimeout(() => {
        //         window.print();
        //     }, 500);
        // };
    </script>
</body>
</html> 