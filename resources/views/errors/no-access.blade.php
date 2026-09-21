@component('layouts.admin', ['title' => 'Ruxsat yo\'q'])
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <i class="mdi mdi-lock-outline"></i>
                <h6>Sizga hech qaysi bo'lim ochilmagan</h6>
                <p>Lavozimingizga ruxsatlar berilmagan. Rahbaringizga murojaat qiling.</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-inverse-danger btn-rounded">
                        <i class="mdi mdi-power me-1"></i> Chiqish
                    </button>
                </form>
            </div>
        </div>
    </div>
@endcomponent
