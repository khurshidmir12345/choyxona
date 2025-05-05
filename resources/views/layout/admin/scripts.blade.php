<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Phone number input mask
    function inputmask(y) {
        var x = y.value.replace(/\D/g, '');
        if (x.length > 9) x = x.slice(0, 9);

        var a = x.match(/(\d{0,2})(\d{0,3})(\d{0,2})(\d{0,2})/);
        y.value = !a[2] ? a[1] : '(' + a[1] + ') ' + a[2] + (a[3] ? '-' + a[3] + (a[4] ? '-' + a[4] : '') : '');
    }
</script>

<script>
    function price_format(input) {
        // Faqat raqamlarni olamiz (0-9)
        let value = input.value.replace(/\D/g, '');

        // Formatlab inputga yozamiz
        input.value = Number(value).toLocaleString('uz-UZ');
    }
</script>
