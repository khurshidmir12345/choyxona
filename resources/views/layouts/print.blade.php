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
            font-size: 10px;
            line-height: 1.2;
            color: #000;
            background: #fff;
        }

        /* Receipt container */
        .receipt {
            max-width: 280px;
            margin: 0 auto;
            padding: 8px;
            border: 1px solid #ccc;
        }

        /* Header styles */
        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .receipt-title {
            font-size: 12px;
            margin-bottom: 3px;
        }

        .order-info {
            font-size: 9px;
            color: #666;
            line-height: 1.1;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 9px;
        }

        .items-table th,
        .items-table td {
            padding: 2px 1px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .items-table th {
            font-weight: bold;
            border-bottom: 1px solid #ccc;
            font-size: 9px;
        }

        .item-name {
            width: 45%;
            word-wrap: break-word;
            word-break: break-word;
        }

        .item-price-qty {
            width: 35%;
            text-align: center;
        }

        .item-total {
            width: 20%;
            text-align: right;
            white-space: nowrap;
        }



        /* Total section */
        .total-section {
            border-top: 1px dashed #ccc;
            padding-top: 8px;
            margin-top: 8px;
            font-size: 9px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            align-items: center;
        }

        .total-amount {
            font-size: 11px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 3px;
        }

        /* Footer */
        .receipt-footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #ccc;
            font-size: 8px;
            color: #666;
            line-height: 1.1;
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
            font-size: 8px;
            display: block;
            margin-top: 1px;
        }

        /* Worker info */
        .worker-info {
            font-size: 8px;
            color: #666;
            margin-top: 3px;
        }

        /* Ensure text doesn't overflow */
        .receipt * {
            max-width: 100%;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        /* Compact spacing for better fit */
        .receipt > div {
            margin-bottom: 5px;
        }

        .receipt > div:last-child {
            margin-bottom: 0;
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