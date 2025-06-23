@extends('layout.admin.header')

@section('styles')
    <style>
        /* Place Cards Styling */
        .place-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            border: 2px solid transparent;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .place-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: #3b82f6;
        }

        .place-card:active {
            transform: translateY(-4px) scale(1.01);
        }

        .status-indicator {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.8);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }

        .table-action {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
            opacity: 0.8;
            transition: all 0.3s ease;
            padding: 8px 12px;
            border-radius: 8px;
            background: rgba(59, 130, 246, 0.05);
        }

        .place-card:hover .table-action {
            opacity: 1;
            color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
            transform: translateX(5px);
        }

        /* Status-specific styling */
        .place-card[data-status="occupied"] {
            border-left: 4px solid #ef4444;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        }

        .place-card[data-status="available"] {
            border-left: 4px solid #10b981;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }

        .place-card[data-status="reserved"] {
            border-left: 4px solid #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        }

        /* Category Buttons Styling */
        .category-buttons {
            scrollbar-width: thin;
            scrollbar-color: #d1d5db #f9fafb;
            padding: 4px;
        }
        
        .category-buttons::-webkit-scrollbar {
            height: 8px;
        }
        
        .category-buttons::-webkit-scrollbar-track {
            background: #f9fafb;
            border-radius: 4px;
        }
        
        .category-buttons::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, #d1d5db, #9ca3af);
            border-radius: 4px;
            transition: background 0.3s ease;
        }
        
        .category-buttons::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(90deg, #9ca3af, #6b7280);
        }
        
        .category-buttons .btn {
            border-radius: 25px;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .category-buttons .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .category-buttons .btn:hover::before {
            left: 100%;
        }

        .category-buttons .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.39);
        }

        .category-buttons .btn-outline-primary {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .category-buttons .btn-outline-primary:hover {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px 0 rgba(59, 130, 246, 0.3);
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .modal-fullscreen .modal-content {
            border-radius: 0;
            min-height: 100vh;
        }

        .modal-fullscreen .modal-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border-bottom: none;
            padding: 0.75rem 1rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            min-height: auto;
        }

        .modal-fullscreen .modal-title {
            font-weight: 600;
            font-size: 1rem;
            margin: 0;
            line-height: 1.2;
        }

        .modal-fullscreen .modal-body {
            padding: 0.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: calc(100vh - 100px);
        }

        .modal-fullscreen .modal-footer {
            border-top: none;
            padding: 0.5rem 1rem;
            background: #ffffff;
            position: sticky;
            bottom: 0;
            z-index: 1000;
            min-height: auto;
            margin-top: 0;
        }

        .modal-fullscreen .modal-footer .btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .modal-fullscreen .modal-footer .btn svg {
            margin-right: 0.25rem;
        }

        .modal-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.25rem;
        }

        .modal-body {
            padding: 2rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .modal-footer {
            border-top: none;
            padding: 1.5rem;
            background: #ffffff;
        }

        .btn-close {
            filter: invert(1) brightness(100);
            opacity: 0.8;
            transition: all 0.3s ease;
        }

        .btn-close:hover {
            opacity: 1;
            transform: rotate(90deg);
        }

        /* Product Cards Styling */
        .product-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .product-card .card-body {
            padding: 0.75rem;
        }

        .product-card .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .product-card .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px 0 rgba(16, 185, 129, 0.3);
        }

        /* Product Image Styling */
        .product-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.2);
        }

        /* Order Summary Product Image */
        .order-product-image {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
        }

        /* Order Summary Styling */
        .order-summary-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }

        .order-summary-card .card-body {
            padding: 0.75rem;
        }

        /* Badge Styling */
        .badge {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 0.75rem;
        }

        .badge.bg-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
        }

        .badge.bg-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%) !important;
        }

        /* Form Controls */
        .form-control {
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Button Styling */
        .btn {
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px 0 rgba(16, 185, 129, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px 0 rgba(59, 130, 246, 0.3);
        }

        .btn-outline-secondary {
            border: 2px solid #6b7280;
            color: #6b7280;
            background: transparent;
        }

        .btn-outline-secondary:hover {
            background: #6b7280;
            border-color: #6b7280;
            transform: translateY(-2px);
        }

        .btn-outline-danger {
            border: 2px solid #ef4444;
            color: #ef4444;
            background: transparent;
        }

        .btn-outline-danger:hover {
            background: #ef4444;
            border-color: #ef4444;
            transform: translateY(-2px);
        }

        /* Alert Styling */
        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .alert .btn-close {
            filter: none;
            opacity: 0.7;
        }

        .alert .btn-close:hover {
            opacity: 1;
        }

        /* Place Name Badge */
        .place-name-badge {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-block;
            box-shadow: 0 2px 4px -1px rgba(16, 185, 129, 0.3);
        }

        /* Remove gaps between modal sections */
        .modal-fullscreen .modal-content > *:last-child {
            margin-bottom: 0;
        }

        .modal-fullscreen .row:last-child {
            margin-bottom: 0;
        }

        .modal-fullscreen .col:last-child {
            margin-bottom: 0;
        }

        /* Compact content styling */
        .modal-fullscreen .mb-3 {
            margin-bottom: 0.5rem !important;
        }

        .modal-fullscreen .mb-2 {
            margin-bottom: 0.25rem !important;
        }

        .modal-fullscreen .py-5 {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }

        .modal-fullscreen .py-3 {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('admin.orders.order-in-cafe-livewire')
        </div>
    </div>
@endsection

@section('scripts')
   //
@endsection
