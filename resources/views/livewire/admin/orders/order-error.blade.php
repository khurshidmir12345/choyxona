{{-- Error Display --}}
<div style="text-align: center; padding: 50px 20px; font-family: Arial, sans-serif;">
    <div style="max-width: 400px; margin: 0 auto; border: 2px solid #dc3545; border-radius: 10px; padding: 30px; background: #fff;">
        <div style="font-size: 48px; color: #dc3545; margin-bottom: 20px;">
            ⚠️
        </div>
        
        <h2 style="color: #dc3545; margin-bottom: 20px;">Xatolik</h2>
        
        <p style="color: #666; margin-bottom: 30px; line-height: 1.6;">
            {{ $error }}
        </p>
        
        <div style="margin-top: 30px;">
            <a href="{{ route('orders.index') }}" 
               style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                ← Buyurtmalar ro'yxatiga qaytish
            </a>
        </div>
        
        <div style="margin-top: 20px; font-size: 12px; color: #999;">
            Buyurtma ID: {{ request()->route('id') }}
        </div>
    </div>
</div> 